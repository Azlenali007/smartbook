<?php
/**
 * Smart Khata - Edit Customer Profile
 */
$pageTitle = 'Edit Customer — Smart Khata';
ob_start();
?>

<div class="max-w-2xl mx-auto">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Customer Profile</h1>
      <p class="text-sm text-slate-500 mt-1">Update contact details and active/inactive status</p>
    </div>
    <a href="/customers/show?id=<?= $customer['id'] ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Profile</a>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="/customers/update" method="POST" class="space-y-6">
      <?= \SmartKhata\CSRF::getField() ?>
      <input type="hidden" name="id" value="<?= $customer['id'] ?>">

      <div>
        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Customer Name <span class="text-rose-500">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($customer['name']) ?>" required
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
      </div>

      <div>
        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Mobile / Phone Number</label>
        <input type="tel" id="phone" name="phone" value="<?= e($customer['phone'] ?? '') ?>"
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
      </div>

      <div>
        <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Shop / Residence Address</label>
        <textarea id="address" name="address" rows="2"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"><?= e($customer['address'] ?? '') ?></textarea>
      </div>

      <div>
        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Account Status</label>
        <select id="status" name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
          <option value="active" <?= $customer['status'] === 'active' ? 'selected' : '' ?>>Active (Can issue new invoices and accept payments)</option>
          <option value="inactive" <?= $customer['status'] === 'inactive' ? 'selected' : '' ?>>Inactive (Archived without deleting financial history)</option>
        </select>
      </div>

      <div>
        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Internal Notes</label>
        <textarea id="notes" name="notes" rows="2"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"><?= e($customer['notes'] ?? '') ?></textarea>
      </div>

      <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
        <a href="/customers/show?id=<?= $customer['id'] ?>" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow">
          Update Customer Profile
        </button>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
