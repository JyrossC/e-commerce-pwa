-- ============================================================
-- NovaMart E-Commerce PWA — Seed Data
-- Run AFTER schema.sql
-- ============================================================

USE `novamart_db`;

-- ─── Admin & Demo Users ──────────────────────────────────────────────────────
-- Admin password: Admin@1234 (bcrypt)
-- Demo  password: Demo@1234  (bcrypt)

INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`) VALUES
('Admin NovaMart', 'admin@novamart.com',
 '$2y$12$YKqFrxDGnPd0q1KcfqLw.OkB0XaKLrFMjYrTpBwpBrkpXYAOMUaVi',
 'admin', '+254700000001', '123 Admin Street, Nairobi, Kenya'),
('John Kamau', 'demo@novamart.com',
 '$2y$12$nB2yC3JLkXFW8mLBOKHr7.gH5T0u4aNnLKjVqW5DtM2lSBBaAyUYa',
 'customer', '+254712345678', '45 Westlands Road, Nairobi, Kenya'),
('Sarah Wanjiku', 'sarah@example.com',
 '$2y$12$nB2yC3JLkXFW8mLBOKHr7.gH5T0u4aNnLKjVqW5DtM2lSBBaAyUYa',
 'customer', '+254723456789', '12 Karen Boulevard, Nairobi, Kenya');

-- ─── Categories ──────────────────────────────────────────────────────────────
INSERT INTO `categories` (`name`, `slug`, `description`, `image`, `sort_order`) VALUES
('Electronics',    'electronics',    'Latest gadgets, phones, and tech accessories.',      'https://fastly.picsum.photos/id/1/400/300.jpg', 1),
('Fashion',        'fashion',        'Trendy clothing, shoes, and accessories.',            'https://fastly.picsum.photos/id/21/400/300.jpg', 2),
('Books',          'books',          'Best-sellers, textbooks, and e-books.',               'https://fastly.picsum.photos/id/24/400/300.jpg', 3),
('Sports & Outdoors', 'sports',      'Equipment for fitness, sports, and outdoor adventures.','https://fastly.picsum.photos/id/28/400/300.jpg', 4),
('Home & Garden',  'home-garden',    'Furniture, decor, and garden essentials.',            'https://fastly.picsum.photos/id/30/400/300.jpg', 5),
('Beauty & Health','beauty-health',  'Skincare, cosmetics, and wellness products.',         'https://fastly.picsum.photos/id/39/400/300.jpg', 6);

-- ─── Products ────────────────────────────────────────────────────────────────
-- Electronics
INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `sku`, `image`, `is_featured`) VALUES
(1, 'ProSound X5 Wireless Headphones',   'prosound-x5-wireless-headphones',
 'Premium over-ear headphones with active noise cancellation, 30-hour battery life, and crystal-clear sound quality. Bluetooth 5.2 with multi-device pairing.',
 12999.00, 9999.00, 45, 'ELEC-001',
 'https://fastly.picsum.photos/id/3/600/600.jpg', 1),

(1, 'NovaCam Z9 4K Action Camera',       'novacam-z9-4k-action-camera',
 'Waterproof action camera with 4K/60fps video, built-in stabilization, Wi-Fi, and 2-inch touch screen. Perfect for adventures.',
 21500.00, NULL, 30, 'ELEC-002',
 'https://fastly.picsum.photos/id/6/600/600.jpg', 1),

(1, 'SlimBook Pro 15 Laptop',            'slimbook-pro-15-laptop',
 'Ultra-thin laptop with Intel Core i7, 16GB RAM, 512GB NVMe SSD, and 15.6" Full HD IPS display. 10-hour battery life.',
 89900.00, 79900.00, 15, 'ELEC-003',
 'https://fastly.picsum.photos/id/9/600/600.jpg', 1),

(1, 'SmartWatch Series 8',               'smartwatch-series-8',
 'Feature-packed smartwatch with heart rate monitor, GPS, sleep tracking, and 7-day battery. Compatible with iOS & Android.',
 15500.00, NULL, 60, 'ELEC-004',
 'https://fastly.picsum.photos/id/10/600/600.jpg', 0),

(1, 'Portable Power Bank 26800mAh',      'portable-power-bank-26800mah',
 'High-capacity power bank with 65W PD fast charging, 3 USB ports, and LED battery indicator. Charges 2 devices simultaneously.',
 4500.00, 3799.00, 120, 'ELEC-005',
 'https://fastly.picsum.photos/id/11/600/600.jpg', 0),

-- Fashion
(2, 'Classic Leather Oxford Shoes',      'classic-leather-oxford-shoes',
 'Genuine leather oxford shoes with cushioned insole and durable rubber sole. Perfect for formal occasions.',
 7800.00, NULL, 40, 'FASH-001',
 'https://fastly.picsum.photos/id/26/600/600.jpg', 1),

(2, 'Urban Slim Fit Jacket',             'urban-slim-fit-jacket',
 'Modern slim-fit jacket in water-resistant fabric. Lightweight and stylish for everyday wear.',
 5500.00, 4200.00, 35, 'FASH-002',
 'https://fastly.picsum.photos/id/38/600/600.jpg', 0),

(2, 'Premium Cotton T-Shirt 3-Pack',     'premium-cotton-t-shirt-3pack',
 '100% organic cotton t-shirts in a pack of 3. Comfortable, durable, and available in multiple colors.',
 2100.00, NULL, 200, 'FASH-003',
 'https://fastly.picsum.photos/id/54/600/600.jpg', 0),

(2, 'Designer Sunglasses UV400',         'designer-sunglasses-uv400',
 'Polarized sunglasses with UV400 protection, lightweight titanium frame, and premium case included.',
 3200.00, 2500.00, 80, 'FASH-004',
 'https://fastly.picsum.photos/id/57/600/600.jpg', 0),

-- Books
(3, 'Clean Code: A Handbook',            'clean-code-handbook',
 'Robert C. Martins guide to writing clean, maintainable code. A must-read for every programmer.',
 1850.00, NULL, 150, 'BOOK-001',
 'https://fastly.picsum.photos/id/48/600/600.jpg', 0),

(3, 'The Psychology of Money',           'psychology-of-money',
 'Morgan Housels timeless lessons on wealth, greed, and happiness — and the behaviours that drive long-term financial success.',
 1650.00, 1299.00, 200, 'BOOK-002',
 'https://fastly.picsum.photos/id/60/600/600.jpg', 1),

(3, 'Atomic Habits',                     'atomic-habits',
 'James Clear reveals how tiny changes can generate remarkable results and build life-changing habits.',
 1750.00, NULL, 180, 'BOOK-003',
 'https://fastly.picsum.photos/id/65/600/600.jpg', 0),

-- Sports
(4, 'Pro Yoga Mat with Carry Strap',     'pro-yoga-mat-with-carry-strap',
 'Eco-friendly non-slip yoga mat, 6mm thick with alignment lines. Perfect for yoga, pilates, and stretching.',
 2800.00, 2200.00, 90, 'SPRT-001',
 'https://fastly.picsum.photos/id/42/600/600.jpg', 0),

(4, 'Adjustable Dumbbell Set 5-50kg',   'adjustable-dumbbell-set',
 'Quick-adjust dumbbell set replacing 15 sets of weights. Compact design ideal for home gyms.',
 14500.00, NULL, 25, 'SPRT-002',
 'https://fastly.picsum.photos/id/45/600/600.jpg', 1),

(4, 'Trail Running Shoes Pro',           'trail-running-shoes-pro',
 'Lightweight trail running shoes with aggressive grip sole, breathable mesh upper, and shock-absorbing midsole.',
 6800.00, 5500.00, 50, 'SPRT-003',
 'https://fastly.picsum.photos/id/47/600/600.jpg', 0),

-- Home & Garden
(5, 'Ergonomic Office Chair',            'ergonomic-office-chair',
 'Fully adjustable office chair with lumbar support, adjustable armrests, and breathable mesh back. Max weight 150kg.',
 22000.00, 18500.00, 20, 'HOME-001',
 'https://fastly.picsum.photos/id/56/600/600.jpg', 1),

(5, 'Smart LED Floor Lamp',              'smart-led-floor-lamp',
 'Wi-Fi enabled smart floor lamp with 16 million colours, adjustable brightness, and voice control compatibility.',
 8500.00, NULL, 35, 'HOME-002',
 'https://fastly.picsum.photos/id/58/600/600.jpg', 0),

(5, 'Bamboo Kitchen Utensil Set',        'bamboo-kitchen-utensil-set',
 '7-piece bamboo kitchen utensil set. Eco-friendly, heat-resistant, and dishwasher safe. Comes with storage holder.',
 1950.00, 1500.00, 150, 'HOME-003',
 'https://fastly.picsum.photos/id/63/600/600.jpg', 0),

-- Beauty & Health
(6, 'Vitamin C Brightening Serum',       'vitamin-c-brightening-serum',
 '15% Vitamin C serum with hyaluronic acid and niacinamide. Reduces dark spots and evens skin tone in 4 weeks.',
 3500.00, 2800.00, 100, 'BEAU-001',
 'https://fastly.picsum.photos/id/67/600/600.jpg', 0),

(6, 'Electric Sonic Toothbrush',         'electric-sonic-toothbrush',
 '5 cleaning modes, 31,000 strokes per minute, 3-month battery life. Includes 4 brush heads.',
 4200.00, NULL, 75, 'BEAU-002',
 'https://fastly.picsum.photos/id/70/600/600.jpg', 0);

-- ─── Sample Reviews ──────────────────────────────────────────────────────────
INSERT INTO `reviews` (`user_id`, `product_id`, `rating`, `title`, `comment`) VALUES
(2, 1, 5, 'Amazing headphones!', 'Best headphones I have ever used. The noise cancellation is incredible and battery lasts forever.'),
(3, 1, 4, 'Great value for money', 'Very comfortable and the sound quality is excellent. Would recommend!'),
(2, 3, 5, 'My new work companion', 'This laptop is super fast and the display is gorgeous. Very satisfied with the purchase.'),
(3, 11, 5, 'Life-changing book', 'Everyone should read The Psychology of Money. Changed how I think about finances completely.'),
(2, 16, 4, 'Comfortable chair', 'Great ergonomic chair. My back pain has reduced significantly since I started using it.');

-- ─── Sample Orders ──────────────────────────────────────────────────────────
INSERT INTO `orders` (`user_id`, `order_number`, `subtotal`, `shipping_cost`, `tax`, `total`, `status`, `payment_method`, `payment_status`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_country`) VALUES
(2, 'NVM-20240101-0001', 22999.00, 500.00, 2300.00, 25799.00, 'delivered', 'cod', 'paid', 'John Kamau', 'demo@novamart.com', '+254712345678', '45 Westlands Road', 'Nairobi', 'Kenya'),
(3, 'NVM-20240115-0002', 5650.00, 300.00, 565.00, 6515.00, 'shipped', 'cod', 'unpaid', 'Sarah Wanjiku', 'sarah@example.com', '+254723456789', '12 Karen Boulevard', 'Nairobi', 'Kenya');

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `product_image`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 'ProSound X5 Wireless Headphones', 'https://fastly.picsum.photos/id/3/600/600.jpg', 1, 9999.00, 9999.00),
(1, 3, 'SlimBook Pro 15 Laptop', 'https://fastly.picsum.photos/id/9/600/600.jpg', 1, 79900.00, 79900.00),
(2, 7, 'Urban Slim Fit Jacket', 'https://fastly.picsum.photos/id/38/600/600.jpg', 1, 4200.00, 4200.00),
(2, 19, 'Vitamin C Brightening Serum', 'https://fastly.picsum.photos/id/67/600/600.jpg', 1, 2800.00, 2800.00);
-- Update order 1 total (for correctness)
UPDATE `orders` SET `subtotal`=89899.00, `tax`=8990.00, `total`=99389.00 WHERE `id`=1;
