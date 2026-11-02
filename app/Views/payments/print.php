<?php
/**
 * Smart Khata - Printable Payment Receipt
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Receipt <?= e($payment['payment_number']) ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; padding: 0 !important; }
    }
  </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-900" onload="window.print()">

  <div class="max-w-2xl mx-auto mb-4 no-print flex items-center justify-between">
    <a href="/payments/show?id=<?= $payment['id'] ?>" class="text-sm font-semibold text-blue-600 hover:underline">&larr; Back to Payment</a>
    <button onclick="window.print()" class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow hover:bg-blue-700">Print Receipt</button>
  </div>

  <div class="max-w-2xl mx-auto bg-white p-8 sm:p-12 border border-slate-300 rounded-xl shadow-sm print:border-none print:shadow-none">
    <div class="flex justify-between items-start pb-6 border-b border-slate-300">
      <div>
        <h1 class="text-2xl font-black text-slate-900"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h1>
        <p class="text-xs text-slate-600 mt-1"><?= e($settings['shop_address'] ?? '') ?></p>
        <?php if (!empty($settings['shop_phone'])): ?>
          <p class="text-xs text-slate-600">Phone: <?= e($settings['shop_phone']) ?></p>
        <?php endif; ?>
      </div>
      <div class="text-right">
        <h2 class="text-base font-bold text-slate-900 uppercase">Payment Receipt</h2>
        <div class="text-sm font-bold text-blue-800"><?= e($payment['payment_number']) ?></div>
        <div class="text-xs text-slate-600 mt-1">Date: <?= formatDate($payment['payment_date']) ?></div>
      </div>
    </div>

    <div class="py-6 border-b border-slate-300 flex justify-between items-start">
      <div>
        <div class="text-xs font-bold uppercase text-slate-500 mb-1">Received From:</div>
        <div class="text-base font-bold text-slate-900"><?= e($customer['name']) ?></div>
        <div class="text-xs text-slate-600"><?= e($customer['phone'] ?: '') ?></div>
        <div class="text-xs text-slate-600"><?= e($customer['address'] ?: '') ?></div>
      </div>
      <div class="text-right text-xs">
        <div class="text-slate-500 font-bold uppercase">Payment Mode</div>
        <div class="font-bold text-slate-800 mt-1"><?= e($payment['payment_mode']) ?></div>
        <?php if (!empty($payment['reference_no'])): ?>
          <div class="text-[11px] text-slate-600">Ref: <?= e($payment['reference_no']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="my-6 p-6 bg-slate-50 border border-slate-300 rounded-lg text-center">
      <div class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Amount Received</div>
      <div class="text-3xl font-black text-slate-900"><?= formatINR($payment['amount']) ?></div>
    </div>

    <div class="text-xs text-slate-700 space-y-1 mb-8">
      <div><strong>Allocated to Invoices:</strong> <?= formatINR($payment['allocated_amount']) ?></div>
      <div><strong>Retained as Customer Advance:</strong> <?= formatINR($payment['advance_amount']) ?></div>
      <?php if (!empty($payment['notes'])): ?>
        <div><strong>Notes:</strong> <?= e($payment['notes']) ?></div>
      <?php endif; ?>
    </div>

    <div class="mt-12 pt-6 border-t border-slate-300 flex justify-between items-end text-xs text-slate-500">
      <div>
        <p>Thank you for your payment!</p>
        <p class="mt-1 text-[10px]">Smart Khata System</p>
      </div>
      <div class="text-right">
        <div class="h-10"></div>
        <div class="border-t border-slate-400 pt-1 font-semibold text-slate-800">Authorized Signature</div>
      </div>
    </div>
  </div>

</body>
</html>
