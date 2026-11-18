CREATE DATABASE IF NOT EXISTS buana_group CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE buana_group;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS business_units;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE business_units (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 slug VARCHAR(80) NOT NULL UNIQUE,
 icon VARCHAR(20) DEFAULT '▣',
 description TEXT,
 is_active TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(160) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('super_admin','admin_unit','keuangan','operasional','customer') NOT NULL DEFAULT 'customer',
 phone VARCHAR(40) NULL,
 customer_type ENUM('umum','institusi') NOT NULL DEFAULT 'umum',
 credit_limit DECIMAL(15,2) NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
 id INT AUTO_INCREMENT PRIMARY KEY,
 unit_id INT NOT NULL,
 name VARCHAR(180) NOT NULL,
 description TEXT NULL,
 price DECIMAL(15,2) NOT NULL DEFAULT 0,
 unit_label VARCHAR(40) DEFAULT 'pcs',
 is_active TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_products_unit FOREIGN KEY(unit_id) REFERENCES business_units(id)
) ENGINE=InnoDB;

CREATE TABLE orders (
 id INT AUTO_INCREMENT PRIMARY KEY,
 order_no VARCHAR(40) NOT NULL UNIQUE,
 customer_id INT NOT NULL,
 unit_id INT NOT NULL,
 status ENUM('pending','approved','processing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
 total DECIMAL(15,2) NOT NULL DEFAULT 0,
 notes TEXT NULL,
 created_by INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL,
 CONSTRAINT fk_orders_customer FOREIGN KEY(customer_id) REFERENCES users(id),
 CONSTRAINT fk_orders_unit FOREIGN KEY(unit_id) REFERENCES business_units(id),
 CONSTRAINT fk_orders_creator FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 product_id INT NULL,
 product_name VARCHAR(180) NOT NULL,
 qty INT NOT NULL DEFAULT 1,
 unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
 subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
 CONSTRAINT fk_items_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_items_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE invoices (
 id INT AUTO_INCREMENT PRIMARY KEY,
 invoice_no VARCHAR(60) NOT NULL UNIQUE,
 order_id INT NOT NULL UNIQUE,
 total DECIMAL(15,2) NOT NULL,
 paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
 status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
 due_date DATE NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_invoice_order FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 invoice_id INT NOT NULL,
 amount DECIMAL(15,2) NOT NULL,
 method VARCHAR(50) NOT NULL,
 note VARCHAR(255) NULL,
 received_by INT NULL,
 paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_payment_invoice FOREIGN KEY(invoice_id) REFERENCES invoices(id),
 CONSTRAINT fk_payment_user FOREIGN KEY(received_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_status_history (
 id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 status VARCHAR(30) NOT NULL,
 notes VARCHAR(255) NULL,
 changed_by INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_history_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_history_user FOREIGN KEY(changed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 action VARCHAR(100) NOT NULL,
 detail TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_log_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO business_units(name,slug,icon,description) VALUES
('Air Mineral Unwahas','air-mineral','💧','Pemesanan air mineral botol, gelas, galon, langganan rutin, dan pesanan skala besar.'),
('Buana Food Services','food-services','🍱','Katering, snack box, nasi kotak, menu sesuai anggaran, dan kebutuhan acara.'),
('Buana Tour Travel','tour-travel','✈️','Paket wisata, study tour, ziarah, perjalanan rombongan, dan layanan perjalanan.'),
('Buana Ticketing','ticketing','🎫','Layanan reservasi tiket transportasi dan tiket acara internal maupun eksternal.'),
('Buana Konveksi','konveksi','👕','Produksi almamater, PDH, PDL, kaos, jaket, dan kebutuhan konveksi lainnya.');

-- Password awal: password (wajib diganti setelah instalasi)
INSERT INTO users(name,email,password,role,phone,customer_type,credit_limit) VALUES
('Administrator Buana','admin@buana.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','super_admin','', 'institusi',0),
('Pelanggan Demo','pelanggan@buana.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','customer','081234567890','umum',0);

INSERT INTO products(unit_id,name,description,price,unit_label) VALUES
(1,'Air Mineral Botol 600 ml','Harga contoh, sesuaikan dengan daftar harga resmi.',4000,'botol'),
(1,'Air Mineral Gelas','Harga contoh, sesuaikan dengan daftar harga resmi.',1000,'gelas'),
(1,'Air Mineral Galon','Harga contoh, sesuaikan dengan daftar harga resmi.',18000,'galon'),
(2,'Snack Box Standar','Paket contoh untuk rapat dan kegiatan.',15000,'box'),
(2,'Nasi Kotak','Paket contoh, menu dan harga dapat disesuaikan.',25000,'box'),
(2,'Katering Prasmanan','Harga awal per porsi, perlu quotation final.',35000,'porsi'),
(3,'Paket Study Tour','Harga indikatif; konfirmasi rute, tanggal, dan vendor.',250000,'peserta'),
(3,'Paket Ziarah','Harga indikatif; konfirmasi rute dan fasilitas.',300000,'peserta'),
(4,'Tiket Event','Harga tiket acara, isi sesuai event.',50000,'tiket'),
(4,'Layanan Tiket Transportasi','Harga layanan awal, tiket aktual perlu konfirmasi mitra.',25000,'transaksi'),
(5,'Kaos Custom','Harga awal; ukuran, bahan, sablon memerlukan quotation.',65000,'pcs'),
(5,'Jaket / Almamater','Harga awal; bahan dan desain perlu quotation.',150000,'pcs');
