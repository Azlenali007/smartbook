<?php
/**
 * Smart Khata - Printable Customer Ledger Statement
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Statement - <?= e($customer['name']) ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; padding: 0 !important; }
    }
  </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-900" onload="window.print()">

  <div class="max-w-4xl mx-auto mb-4 no-print flex items-center justify-between">
    <a href="/ledger?id=<?= $customer['id'] ?>" class="text-sm font-semibold text-blue-600 hover:underline">&larr; Back to Ledger</a>
    <button onclick="window.print()" class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow hover:bg-blue-700">Print Statement</button>
  </div>

  <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 border border-slate-300 rounded-xl shadow-sm print:border-none print:shadow-none">
    <div class="flex justify-between items-start pb-6 border-b border-slate-300">
      <div>
        <h1 class="text-2xl font-black text-slate-900"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h1>
        <p class="text-xs text-slate-600 mt-1"><?= e($settings['shop_address'] ?? '') ?></p>
        <?php if (!empty($settings['shop_phone'])): ?>
          <p class="text-xs text-slate-600">Phone: <?= e($settings['shop_phone']) ?></p>
        <?php endif; ?>
      </div>
      <div class="text-right">
        <h2 class="text-base font-bold text-slate-900 uppercase">Customer Statement / Khata</h2>
        <div class="text-xs text-slate-600 mt-1">Generated: <?= date('d-m-Y H:i') ?></div>
      </div>
    </div>

    <div class="py-6 border-b border-slate-300 flex justify-between items-start">
      <div>
        <div class="text-xs font-bold uppercase text-slate-500 mb-1">Customer Details:</div>
        <div class="text-lg font-bold text-slate-900"><?= e($customer['name']) ?></div>
        <div class="text-xs text-slate-600"><?= e($customer['phone'] ?: '') ?></div>
        <div class="text-xs text-slate-600"><?= e($customer['address'] ?: '') ?></div>
      </div>
      <div class="text-right">
        <div class="text-xs font-bold uppercase text-slate-500 mb-1">Current Balance</div>
        <?php if ($balance['outstanding_due'] > 0): ?>
          <div class="text-xl font-black text-rose-700"><?= formatINR($balance['outstanding_due']) ?> Due (Dr)</div>
        <?php elseif ($balance['advance_credit'] > 0): ?>
          <div class="text-xl font-black text-emerald-700"><?= formatINR($balance['advance_credit']) ?> Advance (Cr)</div>
        <?php else: ?>
          <div class="text-lg font-bold text-slate-700">Settled (₹0.00)</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ledger Table -->
    <table class="w-full text-left text-xs my-6">
      <thead class="border-b-2 border-slate-900 uppercase">
        <tr>
          <th class="py-2">Date</th>
          <th class="py-2">Type</th>
          <th class="py-2">Ref #</th>
          <th class="py-2">Description</th>
          <th class="py-2 text-right">Debit (+)</th>
          <th class="py-2 text-right">Credit (-)</th>
          <th class="py-2 text-right">Balance</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200">
        <?php foreach ($entries as $e): ?>
          <?php
            $isDr = ($e['running_balance'] > 0);
            $isCr = ($e['running_balance'] < 0);
            $balLabel = $isDr ? ' Dr' : ($isCr ? ' Cr' : '');
          ?>
          <tr>
            <td class="py-2 font-medium"><?= formatDate($e['entry_date']) ?></td>
            <td class="py-2 uppercase font-semibold text-[10px] text-slate-600"><?= e(str_replace('_', ' ', $e['entry_type'])) ?></td>
            <td class="py-2 font-semibold"><?= e($e['reference_no'] ?: '—') ?></td>
            <td class="py-2"><?= e($e['description']) ?></td>
            <td class="py-2 text-right font-medium"><?= $e['debit'] > 0 ? formatINR($e['debit']) : '—' ?></td>
            <td class="py-2 text-right font-medium"><?= $e['credit'] > 0 ? formatINR($e['credit']) : '—' ?></td>
            <td class="py-2 text-right font-bold"><?= formatINR(abs($e['running_balance'])) ?><?= $balLabel ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="mt-12 pt-6 border-t border-slate-300 flex justify-between items-end text-xs text-slate-500">
      <div>
        <p>This statement reflects all debits, payments, and running balance as of <?= date('d-m-Y') ?>.</p>
        <p class="mt-1 text-[10px]">Smart Khata Accounting System</p>
      </div>
      <div class="text-right">
        <div class="h-10"></div>
        <div class="border-t border-slate-400 pt-1 font-semibold text-slate-800">Authorized Signature</div>
      </div>
    </div>
  </div>

</body>
</html>
