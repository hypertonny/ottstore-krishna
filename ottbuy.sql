-- OTT Store Database Dump
-- Compatible with MySQL 5.7+ / MariaDB 10.3+ / PHP 8+
-- Charset: utf8mb4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table structure for `categorys`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categorys`;
CREATE TABLE `categorys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `info` text NOT NULL,
  `amount` double NOT NULL,
  `total_stock` int(11) NOT NULL DEFAULT '0',
  `logo_url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorys` (`id`, `name`, `info`, `amount`, `total_stock`, `logo_url`) VALUES
(1, 'Netflix Premium 4K UHD', '✓ 4K Ultra HD + Dolby Atmos<br>✓ 1 Private Screen with PIN<br>✓ Works on TV, Mobile, Laptop<br>✓ 30 Days Replacement Warranty', 199, 4, 'assets/icons/netflix.svg'),
(2, 'Amazon Prime Video', '✓ Prime Video HD/4K Streaming<br>✓ Ad-free Prime Music included<br>✓ 1 Screen with Private Profile<br>✓ 30 Days Active Warranty', 129, 3, 'assets/icons/prime.svg'),
(3, 'Disney+ Hotstar Super', '✓ Live Cricket, Sports & Movies<br>✓ Full HD Streaming quality<br>✓ Direct Login access<br>✓ 30 Days Guaranteed Warranty', 99, 3, 'assets/icons/hotstar.svg'),
(4, 'YouTube Premium', '✓ Ad-Free Videos & Background Play<br>✓ YouTube Music Premium included<br>✓ Zero interruptions<br>✓ Instant Activation', 79, 3, 'assets/icons/youtube.svg'),
(5, 'Spotify Premium', '✓ Ad-free High Quality Music<br>✓ Unlimited Skips & Offline Downloads<br>✓ High Fidelity Audio<br>✓ 30 Days Warranty', 59, 4, 'assets/icons/spotify.svg'),
(6, 'Crunchyroll Mega Fan', '✓ All Anime Episodes Simulcast<br>✓ Ad-Free Offline Viewing<br>✓ 1 Private Profile<br>✓ 30 Days Replacement Warranty', 89, 2, 'assets/icons/crunchyroll.svg'),
(7, 'Fresh Gmail Account', '✓ Format: email:password:recovery<br>✓ Verified phone & recovery mail<br>✓ 100% Working Accounts<br>✓ Instant Delivery', 25, 5, 'assets/icons/gmail.svg');

-- --------------------------------------------------------
-- Table structure for `sell` (Stock Inventory)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sell`;
CREATE TABLE `sell` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1: Available, 2: Sold',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sell` (`id`, `category_id`, `username`, `password`, `status`) VALUES
-- Netflix Stock
(1, 1, 'nflx.user101@ottvip.in', 'NfPass!2026', 1),
(2, 1, 'nflx.user102@ottvip.in', 'NfPass!3037', 1),
(3, 1, 'nflx.user103@ottvip.in', 'NfPass!4048', 1),
(4, 1, 'nflx.user104@ottvip.in', 'NfPass!5059', 1),
-- Prime Video Stock
(5, 2, 'prime.stream01@ottvip.in', 'Prime!Watch26', 1),
(6, 2, 'prime.stream02@ottvip.in', 'Prime!Watch27', 1),
(7, 2, 'prime.stream03@ottvip.in', 'Prime!Watch28', 1),
-- Disney+ Hotstar Stock
(8, 3, 'hotstar.cricket01@ottvip.in', 'Hotstar!Pass26', 1),
(9, 3, 'hotstar.cricket02@ottvip.in', 'Hotstar!Pass27', 1),
(10, 3, 'hotstar.cricket03@ottvip.in', 'Hotstar!Pass28', 1),
-- YouTube Premium Stock
(11, 4, 'ytprem.play01@ottvip.in', 'YtAdFree!2026', 1),
(12, 4, 'ytprem.play02@ottvip.in', 'YtAdFree!2027', 1),
(13, 4, 'ytprem.play03@ottvip.in', 'YtAdFree!2028', 1),
-- Spotify Premium Stock
(14, 5, 'spotify.music01@ottvip.in', 'SpMusic!2026', 1),
(15, 5, 'spotify.music02@ottvip.in', 'SpMusic!2027', 1),
(16, 5, 'spotify.music03@ottvip.in', 'SpMusic!2028', 1),
(17, 5, 'spotify.music04@ottvip.in', 'SpMusic!2029', 1),
-- Crunchyroll Stock
(18, 6, 'anime.cr01@ottvip.in', 'Crunchy!2026', 1),
(19, 6, 'anime.cr02@ottvip.in', 'Crunchy!2027', 1),
-- Gmail Stock
(20, 7, 'aged.freshmail01@gmail.com:RecovPass99:recov01@mail.com', 'Gmail!Pass2026', 1),
(21, 7, 'aged.freshmail02@gmail.com:RecovPass99:recov02@mail.com', 'Gmail!Pass2027', 1),
(22, 7, 'aged.freshmail03@gmail.com:RecovPass99:recov03@mail.com', 'Gmail!Pass2028', 1),
(23, 7, 'aged.freshmail04@gmail.com:RecovPass99:recov04@mail.com', 'Gmail!Pass2029', 1),
(24, 7, 'aged.freshmail05@gmail.com:RecovPass99:recov05@mail.com', 'Gmail!Pass2030', 1);

-- --------------------------------------------------------
-- Table structure for `sold` (Delivered Orders)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sold`;
CREATE TABLE `sold` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(255) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `txn_id` (`txn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sold` (`id`, `txn_id`, `username`, `password`) VALUES
(1, '371447845116', 'demo.sample01@ottvip.in', 'SamplePass99');

-- --------------------------------------------------------
-- Table structure for `login` (Admin Access)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `login` (`id`, `username`) VALUES
(1, 'radium');

-- --------------------------------------------------------
-- Table structure for `settings` (System & Gateway Config)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('upi_id', 'paytmqr1rudv8w05q@paytm'),
('upi_name', 'OTT Store Premium'),
('support_whatsapp', '13082229928'),
('support_telegram', 'ottbuyofficial'),
('support_email', 'buy@ottstore.help'),
('payment_mode', 'test'),
('bharatpe_merchant_id', '45636937'),
('bharatpe_token', '31be0c416489437baf1ee12135870582'),
('admin_code', 'radium');

-- --------------------------------------------------------
-- Table structure for `users` (Customer Accounts)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo Customer User (Password: 123456)
INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'Demo Customer', 'customer@ottstore.com', '$2y$10$wT/pZg4WvI9g3f9j0R2fO.B58Q14qjJ8r0z6hPkWZ5m0L1t9K0wOa');

-- --------------------------------------------------------
-- Table structure for `purchase_history` (Order Records)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `purchase_history`;
CREATE TABLE `purchase_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `purchase_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `txn_id` (`txn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
