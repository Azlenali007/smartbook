<?php
/**
 * Installer Step 3: Database Selection & Configuration
 */
ob_start();
?>

<div class="space-y-6" x-data="dbSetup()">
  <div>
    <h2 class="text-xl font-bold text-white tracking-tight">Step 3: Database Configuration</h2>
    <p class="text-xs text-slate-400 mt-1">Select your database engine (MySQL 8+ with InnoDB or SQLite)</p>
  </div>

  <form id="dbForm" action="/install?step=5" method="GET" class="space-y-5">
    <input type="hidden" name="step" value="5">

    <!-- Driver Selector -->
    <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700 space-y-3">
      <label class="block text-xs font-bold text-white uppercase tracking-wider">Choose Database Engine</label>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label class="flex items-center space-x-3 p-3 rounded-xl border cursor-pointer transition"
               :class="driver === 'sqlite' ? 'bg-blue-950/40 border-blue-500 text-white' : 'bg-slate-800 border-slate-700 text-slate-400'">
          <input type="radio" name="db_driver" value="sqlite" x-model="driver" class="text-blue-600 focus:ring-blue-500">
          <div>
            <div class="text-xs font-bold text-white">SQLite (Zero-Config)</div>
            <div class="text-[10px] text-slate-400">Single-file database in /storage. 1-click instant setup!</div>
          </div>
        </label>

        <label class="flex items-center space-x-3 p-3 rounded-xl border cursor-pointer transition"
               :class="driver === 'mysql' ? 'bg-blue-950/40 border-blue-500 text-white' : 'bg-slate-800 border-slate-700 text-slate-400'">
          <input type="radio" name="db_driver" value="mysql" x-model="driver" class="text-blue-600 focus:ring-blue-500">
          <div>
            <div class="text-xs font-bold text-white">MySQL 8+ (InnoDB)</div>
            <div class="text-[10px] text-slate-400">Standard hosting MySQL database</div>
          </div>
        </label>
      </div>
    </div>

    <!-- MySQL Parameters (conditionally shown) -->
    <div x-show="driver === 'mysql'" class="space-y-4 p-4 bg-slate-900/60 rounded-xl border border-slate-700">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
          <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Database Host</label>
          <input type="text" name="db_host" x-model="host" value="127.0.0.1"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Port</label>
          <input type="text" name="db_port" x-model="port" value="3306"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
      </div>

      <div>
        <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Database Name</label>
        <input type="text" name="db_name" x-model="dbname" value="smart_khata"
               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Username</label>
          <input type="text" name="db_user" x-model="user" value="root"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Password</label>
          <input type="password" name="db_pass" x-model="pass"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
      </div>

      <!-- Test Connection Button -->
      <div class="pt-2 flex items-center space-x-3">
        <button type="button" @click="testConnection()" :disabled="testing"
                class="bg-slate-700 hover:bg-slate-600 text-white font-semibold text-xs px-4 py-2 rounded-xl transition flex items-center space-x-1.5">
          <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
        </button>
        <span x-show="testStatus" :class="testSuccess ? 'text-emerald-400' : 'text-rose-400'" class="text-xs font-medium" x-text="testMessage"></span>
      </div>
    </div>

    <!-- SQLite Info Box -->
    <div x-show="driver === 'sqlite'" class="p-4 bg-blue-950/40 rounded-xl border border-blue-900/60 text-xs text-blue-300 space-y-1">
      <div class="font-bold text-white">Recommended for Quick Setup:</div>
      <p>SQLite is fully ACID-compliant, self-contained, requires no database server configuration, and supports all accounting transactions with WAL mode enabled.</p>
    </div>

    <div class="pt-4 flex items-center justify-between border-t border-slate-700/80">
      <a href="/install?step=2" class="text-xs text-slate-400 hover:text-white">&larr; Back</a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs transition shadow flex items-center space-x-2">
        <span>Proceed to Admin Setup</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </button>
    </div>
  </form>
</div>

<script>
function dbSetup() {
  return {
    driver: 'sqlite',
    host: '127.0.0.1',
    port: '3306',
    dbname: 'smart_khata',
    user: 'root',
    pass: '',
    testing: false,
    testStatus: false,
    testSuccess: false,
    testMessage: '',

    async testConnection() {
      this.testing = true;
      this.testStatus = false;
      const formData = new FormData();
      formData.append('csrf_token', '<?= \SmartKhata\CSRF::getToken() ?>');
      formData.append('db_driver', this.driver);
      formData.append('db_host', this.host);
      formData.append('db_port', this.port);
      formData.append('db_name', this.dbname);
      formData.append('db_user', this.user);
      formData.append('db_pass', this.pass);

      try {
        const res = await fetch('/install/test-db', { method: 'POST', body: formData });
        const data = await res.json();
        this.testSuccess = data.success;
        this.testMessage = data.message;
        this.testStatus = true;
      } catch (err) {
        this.testSuccess = false;
        this.testMessage = 'Connection request failed.';
        this.testStatus = true;
      } finally {
        this.testing = false;
      }
    }
  };
}
</script>

<?php
$installerContent = ob_get_clean();
require SK_APP . '/Views/layouts/installer.php';
?>
