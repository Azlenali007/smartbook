/**
 * Smart Khata - Build & Verification Script
 * Pure JavaScript - ZERO TypeScript
 */

import { execSync } from 'node:child_process';
import fs from 'node:fs';

console.log('[SmartKhata Build] Verifying zero TypeScript restriction...');

// Scan recursively for .ts and .tsx files
function checkNoTypeScript(dir) {
  const files = fs.readdirSync(dir, { withFileTypes: true });
  for (const f of files) {
    if (f.name === 'node_modules' || f.name === '.git') continue;
    const fullPath = `${dir}/${f.name}`;
    if (f.isDirectory()) {
      checkNoTypeScript(fullPath);
    } else {
      const ext = f.name.split('.').pop().toLowerCase();
      if (ext === 'ts' || ext === 'tsx') {
        throw new Error(`CRITICAL: Found forbidden TypeScript file: ${fullPath}`);
      }
    }
  }
}

checkNoTypeScript('.');
console.log('[SmartKhata Build] Zero TypeScript files confirmed.');

console.log('[SmartKhata Build] Executing test suite...');
execSync('php tests/run_tests.php', { stdio: 'inherit' });

console.log('[SmartKhata Build] Build completed successfully.');
