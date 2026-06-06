-- Database: db_brosuli
CREATE DATABASE IF NOT EXISTS db_brosuli;
USE db_brosuli;

-- Branches table
CREATE TABLE IF NOT EXISTS tb_branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin table
CREATE TABLE IF NOT EXISTS tb_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin_cabang') DEFAULT 'superadmin',
    branch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES tb_branches(id) ON DELETE SET NULL
);

-- Categories table
CREATE TABLE IF NOT EXISTS tb_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Products table
CREATE TABLE IF NOT EXISTS tb_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    barcode VARCHAR(50) UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES tb_categories(id) ON DELETE SET NULL
);

-- Banners table
CREATE TABLE IF NOT EXISTS tb_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    subtitle TEXT,
    image_url VARCHAR(255) NOT NULL,
    link_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Best Sellers table
CREATE TABLE IF NOT EXISTS tb_best_sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    display_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES tb_products(id) ON DELETE CASCADE
);

-- Scan Queue for Python Integration
CREATE TABLE IF NOT EXISTS tb_scan_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Label Print Queue
CREATE TABLE IF NOT EXISTS tb_label_print_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES tb_products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS tb_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(100),
    customer_address TEXT,
    total_amount DECIMAL(15, 2),
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_method VARCHAR(20) DEFAULT 'Midtrans',
    items_json TEXT,
    branch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES tb_branches(id) ON DELETE SET NULL
);

-- Branch Inventory table
CREATE TABLE IF NOT EXISTS tb_branch_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    stock INT DEFAULT 0,
    UNIQUE KEY (branch_id, product_id),
    FOREIGN KEY (branch_id) REFERENCES tb_branches(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tb_products(id) ON DELETE CASCADE
);

-- ==================== HOMEPAGE SECTIONS MANAGEMENT ====================

-- About Section (Tentang Kami)
CREATE TABLE IF NOT EXISTS tb_about_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(100) NOT NULL DEFAULT 'Tentang Kami',
    main_heading VARCHAR(255) NOT NULL DEFAULT 'Warisan Kelezatan Sejak 2009',
    main_description TEXT NOT NULL,
    description_2 TEXT NOT NULL,
    button_text VARCHAR(50) DEFAULT 'Jelajahi Produk Kami',
    button_link VARCHAR(255) DEFAULT 'frontend/catalog.php',
    feature_1_title VARCHAR(100) DEFAULT 'Bahan Premium',
    feature_1_description TEXT DEFAULT 'Menggunakan bahan-bahan berkualitas tinggi pilihan untuk setiap produk.',
    feature_2_title VARCHAR(100) DEFAULT 'Resep Rahasia',
    feature_2_description TEXT DEFAULT 'Resep tradisional yang telah disempurnakan selama lebih dari satu dekade.',
    feature_3_title VARCHAR(100) DEFAULT 'Konsistensi Terjamin',
    feature_3_description TEXT DEFAULT 'Kualitas rasa yang sama di setiap pembelian, di mana pun Anda berada.',
    is_active BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Why Us Section (Keunggulan Kami)
CREATE TABLE IF NOT EXISTS tb_whyus_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(100) NOT NULL DEFAULT 'Keunggulan Kami',
    main_heading VARCHAR(255) NOT NULL DEFAULT 'Mengapa Memilih Brosuli?',
    feature_1_icon VARCHAR(50) DEFAULT 'fa-heart',
    feature_1_title VARCHAR(100) DEFAULT 'Dibuat dengan Cinta',
    feature_1_description TEXT DEFAULT 'Setiap gigitan mencerminkan dedikasi dan passion kami dalam menciptakan produk terbaik untuk Anda.',
    feature_2_icon VARCHAR(50) DEFAULT 'fa-truck',
    feature_2_title VARCHAR(100) DEFAULT 'Pengiriman Cepat',
    feature_2_description TEXT DEFAULT 'Nikmati produk fresh kami dengan sistem pengiriman yang cepat dan aman langsung ke pintu rumah Anda.',
    feature_3_icon VARCHAR(50) DEFAULT 'fa-star',
    feature_3_title VARCHAR(100) DEFAULT 'Kualitas Terjamin',
    feature_3_description TEXT DEFAULT 'Standar kualitas internasional dengan sentuhan lokal yang membuat produk kami unik dan istimewa.',
    is_active BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials Section (Testimonial Pelanggan)
CREATE TABLE IF NOT EXISTS tb_testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(100) NOT NULL DEFAULT 'Kepuasan Pelanggan',
    main_heading VARCHAR(255) NOT NULL DEFAULT 'Apa Kata Pelanggan Kami?',
    customer_name VARCHAR(100) NOT NULL,
    customer_initial VARCHAR(5) NOT NULL,
    rating INT DEFAULT 5,
    testimonial_text TEXT NOT NULL,
    customer_type VARCHAR(50) DEFAULT 'Pelanggan Setia',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact Section (Hubungi Kami)
CREATE TABLE IF NOT EXISTS tb_contact_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(100) NOT NULL DEFAULT 'Hubungi Kami',
    main_heading VARCHAR(255) NOT NULL DEFAULT 'Kirim Pesan untuk Kami',
    subtitle TEXT DEFAULT 'Ada pertanyaan atau saran? Hubungi kami sekarang dan kami akan merespons dalam waktu 24 jam.',
    form_submit_button VARCHAR(50) DEFAULT 'Kirim Pesan',
    contact_note TEXT DEFAULT 'Atau hubungi kami langsung: WhatsApp | Telepon',
    whatsapp_number VARCHAR(20) DEFAULT '62895327349264',
    phone_number VARCHAR(20) DEFAULT '+6289532734926',
    is_active BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact Messages (Pesan dari Form Kontak)
CREATE TABLE IF NOT EXISTS tb_contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero/Banner Section (sudah ada, diperluas untuk metadata)
-- tb_banners sudah ada, tidak perlu modifikasi

-- Categories Section (sudah ada di tb_categories, diperluas untuk homepage metadata)
CREATE TABLE IF NOT EXISTS tb_categories_display (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL UNIQUE,
    icon_class VARCHAR(100) DEFAULT 'fa-bread-slice',
    display_description TEXT DEFAULT 'Jelajahi koleksi lezat kami.',
    is_featured BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES tb_categories(id) ON DELETE CASCADE
);

-- Products Display Section (Kreasi Unggulan)
CREATE TABLE IF NOT EXISTS tb_products_display_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(100) NOT NULL DEFAULT 'Kreasi Unggulan',
    main_heading VARCHAR(255) NOT NULL DEFAULT 'Kreasi Unggulan Kami',
    subtitle TEXT DEFAULT 'Koleksi produk terbaik pilihan chef kami yang paling diminati pelanggan.',
    display_limit INT DEFAULT 6,
    sort_by VARCHAR(50) DEFAULT 'best_sellers',
    is_active BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CTA Section (Call To Action)
CREATE TABLE IF NOT EXISTS tb_cta_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(100) NOT NULL DEFAULT 'Call To Action',
    main_heading VARCHAR(255) NOT NULL DEFAULT 'Siap Mencicipi Kelezatan Kami?',
    subtitle TEXT DEFAULT 'Jangan lewatkan kesempatan untuk merasakan pengalaman kuliner yang tak terlupakan.',
    button_1_text VARCHAR(50) DEFAULT 'Pesan Sekarang',
    button_1_link VARCHAR(255) DEFAULT 'frontend/catalog.php',
    button_1_icon VARCHAR(50) DEFAULT 'fa-shopping-bag',
    button_2_text VARCHAR(50) DEFAULT 'Hubungi Kami',
    button_2_link VARCHAR(255) DEFAULT '#contact-form',
    button_2_icon VARCHAR(50) DEFAULT 'fa-phone',
    background_color VARCHAR(50) DEFAULT 'bg-primary',
    text_color VARCHAR(50) DEFAULT 'text-white',
    is_active BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Initial Branches
INSERT IGNORE INTO tb_branches (name, address) VALUES 
('Brosuli Boyolali (Pusat)', 'Jl. Pandanaran No.275, Sidoharjo, Banaran, Kec. Boyolali'),
('Brosuli Mojosongo', 'Ruko Techno Park, Jl. Merdeka Timur, Mojosongo'),
('Brosuli Kartasura', 'Jl. Brigjen Katamso, Ngemplak, Kartasura'),
('Brosuli Baki', 'Jl. Ovensari Raya No.21, Kadilangu, Baki'),
('Brosuli Mojolaban', 'Jl. Lettu Rm.Hartono No.39, Gadingan, Mojolaban'),
('Brosuli Colomadu', 'Jl. Adi Sumarmo, Krobyongan, Gawanan'),
('Brosuli Pedan', 'Jl. Raya Ps. Pedan, Kedungan, Pedan'),
('Brosuli Jatinom', 'Jl. Klaten-Boyolali No.KM. 8, Bonyokan, Jatinom');

-- Initial About Section Data
INSERT IGNORE INTO tb_about_section (id, section_title, main_heading, main_description, description_2) VALUES 
(1, 'Tentang Kami', 'Warisan Kelezatan Sejak 2009', 
'Brosuli Bakery memulai perjalanan dengan visi sederhana: menghadirkan brownies berkualitas tinggi dengan cita rasa autentik yang tak terlupakan. Dari sebuah toko kecil, kami telah berkembang menjadi jaringan bakery terpercaya di Jawa Tengah.',
'Setiap produk dibuat dengan bahan-bahan pilihan terbaik dan resep rahasia yang telah teruji selama bertahun-tahun. Komitmen kami adalah memberikan pengalaman kuliner terbaik untuk setiap pelanggan setia.');

-- Initial Why Us Section Data
INSERT IGNORE INTO tb_whyus_section (id, section_title, main_heading) VALUES 
(1, 'Keunggulan Kami', 'Mengapa Memilih Brosuli?');

-- Initial Testimonials Data
INSERT IGNORE INTO tb_testimonials (customer_name, customer_initial, rating, testimonial_text, display_order) VALUES 
('Siti Rahayu', 'SR', 5, 'Brownies Brosuli adalah yang terbaik yang pernah saya coba! Teksturnya lembut, rasanya nikmat, dan pelayanannya super ramah. Pasti beli lagi!', 1),
('Bambang Dwi', 'BD', 5, 'Sering membeli untuk hadiah teman dan keluarga. Kemasan cantik, isi fresh, dan selalu membuat orang senang. Highly recommended!', 2),
('Ani Wijaya', 'AW', 5, 'Setiap kali ada acara spesial, pasti pesan Brosuli. Kualitas konsisten, delivery tepat waktu, dan harganya worth it banget!', 3);

-- Initial Contact Section Data
INSERT IGNORE INTO tb_contact_section (id, section_title, main_heading, subtitle) VALUES 
(1, 'Hubungi Kami', 'Kirim Pesan untuk Kami', 'Ada pertanyaan atau saran? Hubungi kami sekarang dan kami akan merespons dalam waktu 24 jam.');

-- Initial Products Display Section Data
INSERT IGNORE INTO tb_products_display_section (id, section_title, main_heading, subtitle) VALUES 
(1, 'Kreasi Unggulan', 'Kreasi Unggulan Kami', 'Koleksi produk terbaik pilihan chef kami yang paling diminati pelanggan.');

-- Initial CTA Section Data
INSERT IGNORE INTO tb_cta_section (id, section_title, main_heading, subtitle) VALUES 
(1, 'Call To Action', 'Siap Mencicipi Kelezatan Kami?', 'Jangan lewatkan kesempatan untuk merasakan pengalaman kuliner yang tak terlupakan.');