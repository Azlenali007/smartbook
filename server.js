/**
 * Smart Khata - Node HTTP Proxy & PHP Development Server Supervisor
 * Runs on Port 3000 for AI Studio Dev Server & Cloud Run
 * Pure JavaScript - ZERO TypeScript
 */

import http from 'node:http';
import { spawn } from 'node:child_process';
import path from 'node:path';
import fs from 'node:fs';

const PHP_PORT = 8088;
const PROXY_PORT = parseInt(process.env.PORT || '3000', 10);

console.log('[SmartKhata] Spawning PHP 8.2 built-in server on 127.0.0.1:' + PHP_PORT);

// Ensure directories exist
const dirs = ['storage/backups', 'storage/logs', 'storage/database', 'config'];
for (const d of dirs) {
  if (!fs.existsSync(d)) {
    fs.mkdirSync(d, { recursive: true });
  }
}

// Resilient PHP process supervisor
let phpProcess = null;
function startPhp() {
  try {
    phpProcess = spawn('php', ['-S', `127.0.0.1:${PHP_PORT}`, 'router.php'], {
      stdio: 'inherit'
    });

    phpProcess.on('error', (err) => {
      console.error('[SmartKhata] Failed to spawn PHP server:', err.message);
    });

    phpProcess.on('exit', (code, signal) => {
      if (signal !== 'SIGINT' && signal !== 'SIGTERM') {
        console.warn(`[SmartKhata] PHP process exited (code ${code}, signal ${signal}). Restarting in 1s...`);
        setTimeout(startPhp, 1000);
      }
    });
  } catch (e) {
    console.error('[SmartKhata] Exception starting PHP:', e.message);
  }
}

startPhp();

// Wait briefly for PHP to bind
setTimeout(() => {
  const server = http.createServer((req, res) => {
    const options = {
      hostname: '127.0.0.1',
      port: PHP_PORT,
      path: req.url,
      method: req.method,
      headers: req.headers
    };

    const proxyReq = http.request(options, (proxyRes) => {
      res.writeHead(proxyRes.statusCode, proxyRes.headers);
      proxyRes.pipe(res, { end: true });
    });

    proxyReq.on('error', (err) => {
      console.error('[SmartKhata] Proxy error:', err.message);
      if (!res.headersSent) {
        res.writeHead(502, { 'Content-Type': 'text/plain' });
        res.end('Smart Khata: Waiting for PHP backend worker...');
      }
    });

    req.pipe(proxyReq, { end: true });
  });

  server.listen(PROXY_PORT, '0.0.0.0', () => {
    console.log(`[SmartKhata] Proxy active on http://0.0.0.0:${PROXY_PORT} -> PHP 127.0.0.1:${PHP_PORT}`);
  });

  process.on('SIGINT', () => {
    phpProcess.kill();
    process.exit(0);
  });
  process.on('SIGTERM', () => {
    phpProcess.kill();
    process.exit(0);
  });
}, 800);
