-- Smart Khata Full Database Backup
-- Generated: 2026-11-02 00:23:12
-- Driver: sqlite

PRAGMA foreign_keys = OFF;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT NULL
                );

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('1', 'shop_name', 'Smart Khata Store');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('2', 'shop_phone', '9876543210');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('3', 'shop_address', 'Main Commercial Complex, Mumbai');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('4', 'shop_gstin', '');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('5', 'currency', 'INR');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('6', 'currency_symbol', '₹');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('7', 'timezone', 'Asia/Kolkata');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('8', 'date_format', 'd-m-Y');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('9', 'invoice_prefix', 'INV-');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('10', 'gst_rate', '0');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('11', 'invoice_footer', 'Thank you for your business! Payment due upon presentation.');
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES ('12', 'installed_at', '2026-11-02 00:22:11');

DROP TABLE IF EXISTS `admins`;
CREATE TABLE admins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password_hash TEXT NOT NULL,
                    failed_attempts INTEGER DEFAULT 0,
                    locked_until TEXT NULL,
                    last_login_at TEXT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                );

INSERT INTO `admins` (`id`, `username`, `password_hash`, `failed_attempts`, `locked_until`, `last_login_at`, `created_at`, `updated_at`) VALUES ('1', 'admin', '$argon2id$v=19$m=65536,t=4,p=1$d0RkMnhaNkt2SVdrdlFrQg$0Ik0ZeGZtn2/kN4AaNiu1wd4eOmHX8W7Cbp9UaISoJ8', '0', NULL, '2026-11-02 00:22:30', '2026-11-02 00:22:11', '2026-11-02 00:22:11');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE customers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    phone TEXT NULL,
                    address TEXT NULL,
                    notes TEXT NULL,
                    opening_balance INTEGER NOT NULL DEFAULT 0,
                    opening_balance_date TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                );

INSERT INTO `customers` (`id`, `name`, `phone`, `address`, `notes`, `opening_balance`, `opening_balance_date`, `status`, `created_at`, `updated_at`) VALUES ('1', 'Rajesh Sharma', '9820011223', 'Shop 12, Gandhi Market, Mumbai', 'Regular wholesale buyer', '250000', '2026-10-01', 'active', '2026-11-01 18:52:45', '2026-11-01 18:52:45');
INSERT INTO `customers` (`id`, `name`, `phone`, `address`, `notes`, `opening_balance`, `opening_balance_date`, `status`, `created_at`, `updated_at`) VALUES ('2', 'Priya Enterprises', '9833445566', 'Plot 45, MIDC Industrial Area, Pune', 'Prompt UPI payer', '0', '2026-10-01', 'active', '2026-11-01 18:52:45', '2026-11-01 18:52:45');
INSERT INTO `customers` (`id`, `name`, `phone`, `address`, `notes`, `opening_balance`, `opening_balance_date`, `status`, `created_at`, `updated_at`) VALUES ('3', 'Amit Traders', '9819283746', 'Sector 17, Vashi, Navi Mumbai', 'Cash transactions', '0', '2026-10-01', 'active', '2026-11-01 18:52:45', '2026-11-01 18:52:45');

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE invoices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    invoice_number TEXT UNIQUE NOT NULL,
                    invoice_date TEXT NOT NULL,
                    subtotal INTEGER NOT NULL,
                    tax_amount INTEGER NOT NULL DEFAULT 0,
                    total_amount INTEGER NOT NULL,
                    paid_amount INTEGER NOT NULL DEFAULT 0,
                    due_amount INTEGER NOT NULL,
                    status TEXT NOT NULL DEFAULT 'unpaid',
                    notes TEXT NULL,
                    is_reversed INTEGER NOT NULL DEFAULT 0,
                    reversal_reason TEXT NULL,
                    reversed_at TEXT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                );

INSERT INTO `invoices` (`id`, `customer_id`, `invoice_number`, `invoice_date`, `subtotal`, `tax_amount`, `total_amount`, `paid_amount`, `due_amount`, `status`, `notes`, `is_reversed`, `reversal_reason`, `reversed_at`, `created_at`, `updated_at`) VALUES ('1', '1', 'INV-2026-0001', '2026-10-15', '400000', '0', '400000', '0', '400000', 'unpaid', 'Delivered via express courier', '0', NULL, NULL, '2026-11-02 00:22:45', '2026-11-02 00:22:45');
INSERT INTO `invoices` (`id`, `customer_id`, `invoice_number`, `invoice_date`, `subtotal`, `tax_amount`, `total_amount`, `paid_amount`, `due_amount`, `status`, `notes`, `is_reversed`, `reversal_reason`, `reversed_at`, `created_at`, `updated_at`) VALUES ('2', '2', 'INV-2026-0002', '2026-10-10', '350000', '0', '350000', '350000', '0', 'paid', 'Industrial components', '0', NULL, NULL, '2026-11-02 00:22:45', '2026-11-02 00:22:45');
INSERT INTO `invoices` (`id`, `customer_id`, `invoice_number`, `invoice_date`, `subtotal`, `tax_amount`, `total_amount`, `paid_amount`, `due_amount`, `status`, `notes`, `is_reversed`, `reversal_reason`, `reversed_at`, `created_at`, `updated_at`) VALUES ('3', '3', 'INV-2026-0003', '2026-10-20', '200000', '0', '200000', '200000', '0', 'paid', 'Retail counter sale', '0', NULL, NULL, '2026-11-02 00:22:45', '2026-11-02 00:22:45');

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE invoice_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_id INTEGER NOT NULL,
                    item_name TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    unit TEXT NOT NULL DEFAULT 'Piece',
                    unit_price INTEGER NOT NULL,
                    line_total INTEGER NOT NULL,
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
                );

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_name`, `quantity`, `unit`, `unit_price`, `line_total`) VALUES ('1', '1', 'Cotton Formal Shirts', '5', 'Piece', '80000', '400000');
INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_name`, `quantity`, `unit`, `unit_price`, `line_total`) VALUES ('2', '2', 'Steel Hardware Nag', '10', 'Piece', '35000', '350000');
INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_name`, `quantity`, `unit`, `unit_price`, `line_total`) VALUES ('3', '3', 'Denim Fabric Nag', '4', 'Piece', '50000', '200000');

DROP TABLE IF EXISTS `payments`;
CREATE TABLE payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    payment_number TEXT UNIQUE NOT NULL,
                    payment_date TEXT NOT NULL,
                    amount INTEGER NOT NULL,
                    allocated_amount INTEGER NOT NULL DEFAULT 0,
                    advance_amount INTEGER NOT NULL DEFAULT 0,
                    payment_mode TEXT NOT NULL,
                    reference_no TEXT NULL,
                    notes TEXT NULL,
                    is_reversed INTEGER NOT NULL DEFAULT 0,
                    reversal_reason TEXT NULL,
                    reversed_at TEXT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                );

INSERT INTO `payments` (`id`, `customer_id`, `payment_number`, `payment_date`, `amount`, `allocated_amount`, `advance_amount`, `payment_mode`, `reference_no`, `notes`, `is_reversed`, `reversal_reason`, `reversed_at`, `created_at`, `updated_at`) VALUES ('1', '2', 'PAY-20261011-001', '2026-10-11', '500000', '350000', '150000', 'UPI', 'UPI/26101198745', 'Advance payment', '0', NULL, NULL, '2026-11-01 18:52:45', '2026-11-02 00:22:45');
INSERT INTO `payments` (`id`, `customer_id`, `payment_number`, `payment_date`, `amount`, `allocated_amount`, `advance_amount`, `payment_mode`, `reference_no`, `notes`, `is_reversed`, `reversal_reason`, `reversed_at`, `created_at`, `updated_at`) VALUES ('2', '3', 'PAY-20261020-002', '2026-10-20', '200000', '200000', '0', 'Cash', NULL, NULL, '0', NULL, NULL, '2026-11-01 18:52:45', '2026-11-02 00:22:45');

DROP TABLE IF EXISTS `payment_allocations`;
CREATE TABLE payment_allocations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    payment_id INTEGER NOT NULL,
                    invoice_id INTEGER NOT NULL,
                    allocated_amount INTEGER NOT NULL,
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (payment_id) REFERENCES payments(id),
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
                );

INSERT INTO `payment_allocations` (`id`, `payment_id`, `invoice_id`, `allocated_amount`, `created_at`) VALUES ('1', '1', '2', '350000', '2026-11-02 00:22:45');
INSERT INTO `payment_allocations` (`id`, `payment_id`, `invoice_id`, `allocated_amount`, `created_at`) VALUES ('2', '2', '3', '200000', '2026-11-02 00:22:45');

DROP TABLE IF EXISTS `ledger_entries`;
CREATE TABLE ledger_entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    entry_date TEXT NOT NULL,
                    entry_type TEXT NOT NULL,
                    reference_id INTEGER NULL,
                    reference_no TEXT NULL,
                    description TEXT NOT NULL,
                    debit INTEGER NOT NULL DEFAULT 0,
                    credit INTEGER NOT NULL DEFAULT 0,
                    running_balance INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                );

INSERT INTO `ledger_entries` (`id`, `customer_id`, `entry_date`, `entry_type`, `reference_id`, `reference_no`, `description`, `debit`, `credit`, `running_balance`, `created_at`) VALUES ('1', '1', '2026-10-01', 'opening_balance', NULL, 'OPENING', 'Opening Balance (Previous Udhaar)', '250000', '0', '250000', '2026-11-02 00:22:45');
INSERT INTO `ledger_entries` (`id`, `customer_id`, `entry_date`, `entry_type`, `reference_id`, `reference_no`, `description`, `debit`, `credit`, `running_balance`, `created_at`) VALUES ('2', '1', '2026-10-15', 'invoice', '1', 'INV-2026-0001', 'Invoice #INV-2026-0001 (1 items)', '400000', '0', '650000', '2026-11-02 00:22:45');
INSERT INTO `ledger_entries` (`id`, `customer_id`, `entry_date`, `entry_type`, `reference_id`, `reference_no`, `description`, `debit`, `credit`, `running_balance`, `created_at`) VALUES ('3', '2', '2026-10-10', 'invoice', '2', 'INV-2026-0002', 'Invoice #INV-2026-0002 (1 items)', '350000', '0', '350000', '2026-11-02 00:22:45');
INSERT INTO `ledger_entries` (`id`, `customer_id`, `entry_date`, `entry_type`, `reference_id`, `reference_no`, `description`, `debit`, `credit`, `running_balance`, `created_at`) VALUES ('4', '2', '2026-10-11', 'payment', '1', 'PAY-20261011-001', 'Payment Received (UPI) Ref: UPI/26101198745 [Incl. Advance: ₹1,500.00]', '0', '500000', '-150000', '2026-11-02 00:22:45');
INSERT INTO `ledger_entries` (`id`, `customer_id`, `entry_date`, `entry_type`, `reference_id`, `reference_no`, `description`, `debit`, `credit`, `running_balance`, `created_at`) VALUES ('5', '3', '2026-10-20', 'invoice', '3', 'INV-2026-0003', 'Invoice #INV-2026-0003 (1 items)', '200000', '0', '200000', '2026-11-02 00:22:45');
INSERT INTO `ledger_entries` (`id`, `customer_id`, `entry_date`, `entry_type`, `reference_id`, `reference_no`, `description`, `debit`, `credit`, `running_balance`, `created_at`) VALUES ('6', '3', '2026-10-20', 'payment', '2', 'PAY-20261020-002', 'Payment Received (Cash)', '0', '200000', '0', '2026-11-02 00:22:45');

DROP TABLE IF EXISTS `adjustments`;
CREATE TABLE adjustments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    adjustment_number TEXT UNIQUE NOT NULL,
                    adjustment_date TEXT NOT NULL,
                    invoice_id INTEGER NULL,
                    amount INTEGER NOT NULL,
                    reason TEXT NOT NULL,
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                );


DROP TABLE IF EXISTS `login_logs`;
CREATE TABLE login_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    ip_address TEXT NULL,
                    user_agent TEXT NULL,
                    status TEXT NOT NULL,
                    attempted_at TEXT NOT NULL
                );

INSERT INTO `login_logs` (`id`, `username`, `ip_address`, `user_agent`, `status`, `attempted_at`) VALUES ('1', 'admin', '127.0.0.1', 'curl/7.88.1', 'success', '2026-11-02 00:22:30');

DROP TABLE IF EXISTS `backup_logs`;
CREATE TABLE backup_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    filename TEXT NOT NULL,
                    filepath TEXT NOT NULL,
                    filesize INTEGER NOT NULL,
                    backup_type TEXT NOT NULL,
                    status TEXT NOT NULL,
                    created_at TEXT NOT NULL
                );


PRAGMA foreign_keys = ON;
