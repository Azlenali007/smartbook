# Smart Khata — Complete Accounting, Billing & Customer Ledger System

Smart Khata is a high-performance, single-admin customer ledger, udhaar management, billing, invoice and payment management system with an interactive browser-based web installer and installable Progressive Web App (PWA) support for both Android and iPhone/iOS Safari.

---

## 🌟 Key Features

1. **Complete Accounting & Customer Ledger**:
   - Double-entry verified chronological ledger statements.
   - Traceable dated opening balances (debit for prior debt, credit for advance).
   - Running balance calculated in exact integer paise to eliminate rounding errors.
   - Automatic separation between customer debt (Udhaar) and customer advance credit.

2. **Accurate Financial Calculation Engine (Section 6 Verified)**:
   - All monetary figures stored as `BIGINT` in paise (e.g. ₹100.50 = 10050 paise).
   - Authoritative backend recalculation and reconciliation on every transaction.
   - Reversal records preserve historical values — no silent edits or deletions.

3. **Billing & Invoices**:
   - Unique sequential invoice numbering with customizable prefix (`INV-YYYY-XXXX`).
   - Manual item name entry with piece/nag quantity unit.
   - Line-item calculations and optional GST support.
   - PDF download (pure PHP) and print-friendly receipts.

4. **Payments & Allocations**:
   - Multiple modes: Cash, UPI, Bank Transfer, Cheque, and Other.
   - **Automatic Allocation**: FIFO settlement against the oldest unpaid invoices first.
   - **Manual Allocation**: Exact amount entry per unpaid invoice (validated $\le$ invoice due).
   - **Advance Retention**: Excess payment stored as customer advance credit.
   - **Advance Adjustments**: Apply customer advance credits to future/unpaid invoices.

5. **Progressive Web App (PWA)**:
   - Installable on Android (via Chromium install prompt).
   - Installable on iPhone/iPad iOS Safari (guided "Add to Home Screen" workflow).
   - Standalone display mode with custom theme colors and branding.
   - Secure Service Worker caching only static assets; sensitive financial data is never cached client-side.

6. **Web Installer (7 Steps)**:
   - Guided browser wizard (`/install`) for non-technical users.
   - Checks PHP 8.2+, PDO MySQL / SQLite drivers, OpenSSL, Mbstring, and folder permissions.
   - One-click SQLite support or connection testing with MySQL 8+ InnoDB.
   - Automatically writes `config/installed.lock` to permanently disable re-execution.

7. **Automated & Manual Backups**:
   - One-click admin SQL snapshot generation and authenticated download.
   - Scheduled CLI script (`php cli/backup.php`) ready for hosting cron jobs.
   - Safe restoration with user confirmation.

---

## 🛡️ Mandatory Zero TypeScript Certification

Smart Khata strictly enforces a zero-TypeScript codebase:
- `.ts` files count: **0**
- `.tsx` files count: **0**
- Built entirely with plain PHP 8.2+, HTML5, Tailwind CSS, Alpine.js, and vanilla JavaScript.

---

## 🚀 Hosting Installation Guide (cPanel / Apache / Nginx)

### Step 1: Upload Files
Upload the Smart Khata files to your hosting document root (e.g. `/public_html` or `/var/www/html`).

### Step 2: Set Folder Permissions
Ensure the following directories are writable (permission `0755` or `0775`):
```bash
chmod -R 775 storage/
chmod -R 775 config/
```

### Step 3: Run the Web Installer
1. Open your browser and navigate to:
   ```
   https://yourdomain.com/install
   ```
2. The installer will guide you through:
   - **Step 1**: Welcome & system overview.
   - **Step 2**: Server requirements verification.
   - **Step 3**: Database selection (Choose SQLite for instant 1-click setup, or enter your MySQL 8+ credentials and click "Test Connection").
   - **Step 4 & 5**: Admin username and strong password creation.
   - **Step 6**: Business / shop name and billing preferences.
   - **Step 7**: Completion and security lock creation.

Once installed, the installer is permanently disabled and visitors are redirected to `/login`.

---

## ⏰ Automated Cron Backups Configuration

To configure automatic daily database backups, add this cron job in your hosting control panel (cPanel &rarr; Cron Jobs, or `crontab -e`):

```bash
0 2 * * * /usr/bin/php /path/to/smart-khata/cli/backup.php > /dev/null 2>&1
```

- This triggers every night at 2:00 AM.
- Backups are stored in `storage/backups/`.
- Success and failure logs are written to `storage/logs/backup.log`.

---

## 📱 PWA Installation Instructions

### Android & Desktop Chrome:
1. Open the Smart Khata website in Google Chrome.
2. Click the **Install App** button in the top navigation or sidebar.
3. Confirm installation. Smart Khata will launch in its own standalone window without browser controls.

### iPhone / iPad iOS Safari:
1. Open Smart Khata in Apple Safari.
2. Tap the **Share** button (square with arrow pointing upwards) in the bottom toolbar.
3. Scroll down and tap **Add to Home Screen**.
4. Tap **Add** in the upper-right corner.
5. Smart Khata now launches full-screen with offline caching enabled.

---

## 🧪 Automated Testing

To run the automated financial test suite:
```bash
php tests/run_tests.php
```

Tests cover:
- All Section 6 numerical test cases (invoices, FIFO allocation, overpayment advances, partial payments, advance settlements).
- Integer paise currency accuracy.
- Reconciliations and balance audits.
- Argon2id / bcrypt password hashing.
- PWA manifest and icon specifications.
- Verification that zero `.ts` or `.tsx` files exist in the project.

---

## 📄 License
Commercial-grade proprietary software. Built for single-admin accounting accuracy.
