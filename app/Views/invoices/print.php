<?php
/**
 * Smart Khata - Print-Friendly Invoice Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice <?= e($invoice['invoice_number']) ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; padding: 0 !important; }
    }
  </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-900" onload="window.print()">

  <div class="max-w-3xl mx-auto mb-4 no-print flex items-center justify-between">
    <a href="/invoices/show?id=<?= $invoice['id'] ?>" class="text-sm font-semibold text-blue-600 hover:underline">&larr; Back to Invoice</a>
    <button onclick="window.print()" class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow hover:bg-blue-700">Print / Save as PDF</button>
  </div>

  <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 border border-slate-300 rounded-xl shadow-sm print:border-none print:shadow-none">
    <!-- Header -->
    <div class="flex justify-between items-start pb-6 border-b border-slate-300">
      <div>
        <h1 class="text-2xl font-black text-slate-900"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h1>
        <p class="text-xs text-slate-600 mt-1"><?= e($settings['shop_address'] ?? '') ?></p>
        <?php if (!empty($settings['shop_phone'])): ?>
          <p class="text-xs text-slate-600">Phone: <?= e($settings['shop_phone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['shop_gstin'])): ?>
          <p class="text-xs text-slate-600">GSTIN: <?= e($settings['shop_gstin']) ?></p>
        <?php endif; ?>
      </div>
      <div class="text-right">
        <h2 class="text-lg font-bold text-slate-900">TAX INVOICE</h2>
        <div class="text-sm font-bold text-blue-800"><?= e($invoice['invoice_number']) ?></div>
        <div class="text-xs text-slate-600 mt-1">Date: <?= formatDate($invoice['invoice_date']) ?></div>
      </div>
    </div>

    <!-- Billed To -->
    <div class="py-6 border-b border-slate-300 flex justify-between items-start">
      <div>
        <div class="text-xs font-bold uppercase text-slate-500 mb-1">Customer / Billed To:</div>
        <div class="text-base font-bold text-slate-900"><?= e($invoice['customer_name']) ?></div>
        <div class="text-xs text-slate-600"><?= e($invoice['customer_phone'] ?: '') ?></div>
        <div class="text-xs text-slate-600"><?= e($invoice['customer_address'] ?: '') ?></div>
      </div>
      <div class="text-right text-xs">
        <div class="font-bold text-slate-700 uppercase">Payment Status</div>
        <div class="text-sm font-extrabold uppercase mt-1 <?= $invoice['status'] === 'paid' ? 'text-emerald-700' : 'text-rose-700' ?>">
          <?= e($invoice['status']) ?>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <table class="w-full text-left text-xs my-6">
      <thead class="border-b-2 border-slate-900 uppercase">
        <tr>
          <th class="py-2">#</th>
          <th class="py-2">Item Description</th>
          <th class="py-2 text-center">Qty</th>
          <th class="py-2 text-right">Rate</th>
          <th class="py-2 text-right">Amount</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200">
        <?php foreach ($items as $i => $item): ?>
          <tr>
            <td class="py-2.5 text-slate-500"><?= $i + 1 ?></td>
            <td class="py-2.5 font-semibold text-slate-900"><?= e($item['item_name']) ?></td>
            <td class="py-2.5 text-center"><?= e($item['quantity']) ?> Piece</td>
            <td class="py-2.5 text-right"><?= formatINR($item['unit_price']) ?></td>
            <td class="py-2.5 text-right font-bold text-slate-900"><?= formatINR($item['line_total']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Totals -->
    <div class="flex justify-end pt-4 border-t border-slate-300">
      <div class="w-64 space-y-2 text-xs">
        <div class="flex justify-between">
          <span>Subtotal:</span>
          <span class="font-semibold"><?= formatINR($invoice['subtotal']) ?></span>
        </div>
        <?php if ((int)$invoice['tax_amount'] > 0): ?>
          <div class="flex justify-between">
            <span>Tax / GST:</span>
            <span class="font-semibold"><?= formatINR($invoice['tax_amount']) ?></span>
          </div>
        <?php endif; ?>
        <div class="flex justify-between font-bold text-sm border-t border-slate-900 pt-2">
          <span>Total:</span>
          <span><?= formatINR($invoice['total_amount']) ?></span>
        </div>
        <div class="flex justify-between text-slate-600">
          <span>Paid:</span>
          <span><?= formatINR($invoice['paid_amount']) ?></span>
        </div>
        <div class="flex justify-between font-bold text-sm border-t border-slate-300 pt-1">
          <span>Balance Due:</span>
          <span class="text-rose-700"><?= formatINR($invoice['due_amount']) ?></span>
        </div>
      </div>
    </div>

    <!-- Footer Note & Sign -->
    <div class="mt-12 pt-6 border-t border-slate-300 flex justify-between items-end text-xs text-slate-500">
      <div>
        <p><?= e($settings['invoice_footer'] ?? 'Thank you for your business!') ?></p>
        <p class="mt-1 text-[10px]">Computer generated invoice &bull; Smart Khata</p>
      </div>
      <div class="text-right">
        <div class="h-10"></div>
        <div class="border-t border-slate-400 pt-1 font-semibold text-slate-800">Authorized Signatory</div>
      </div>
    </div>
  </div>

</body>
</html>
