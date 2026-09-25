-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 25, 2026 at 06:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `School_Project`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `full_name`, `username`, `email`, `phone_number`, `password`) VALUES
(1, 'Aloo', 'admin', 'admin@gmail.com', '0780765678', '$2y$10$T62Lq0ThnQImMTdqsZ4YY./O5N1RwaiPEc8GHf8ja0JgyGQ/Dus5C'),
(2, 'Samwel', 'Sam_admin', 'samwel12@gmail.com', '0742086326', '$2y$10$vktps5wyU2q.YEOtlvBDcud9v7lGg7mF.J5hTivho5ec4wgyM4Hge'),
(3, 'Roselyn', 'Roselyn', 'rosely@gmail.com', '0756545677', '$2y$10$xYlt5rP/bXILBykyWZBwUuwhtKJIRnMyHymTNQec6FXdw.qowAcLW');

-- --------------------------------------------------------

--
-- Table structure for table `artisans`
--

CREATE TABLE `artisans` (
  `artisan_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `location` varchar(150) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `kra` varchar(150) NOT NULL,
  `business_certificate` varchar(150) NOT NULL,
  `business_permit` varchar(150) NOT NULL,
  `kebs` varchar(150) NOT NULL,
  `bank_no` varchar(150) NOT NULL,
  `artisan_ip` varchar(100) NOT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `approved` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artisans`
--

INSERT INTO `artisans` (`artisan_id`, `full_name`, `username`, `email`, `phone_number`, `password`, `location`, `business_name`, `id_number`, `kra`, `business_certificate`, `business_permit`, `kebs`, `bank_no`, `artisan_ip`, `status`, `approved`) VALUES
(1, 'samwel aloo', 'samwel', 'samwel@gmail.com', '0742086326', '$2y$10$vQ2MmlLDZGrszlrrg.uiauZpBicGYW/4jG6PvnYsZ4.fmoRahOAHu', 'nairobi', 'Sam cyber', '34566555', 'A455577767788B', 'safaricom.png', 'Ankara shoes.png', 'phone5.jpeg', '3434556677', '::1', 'active', 1),
(3, 'joseph', 'joseph', 'joseph@gmail.com', '008080880', '$2y$10$mRBI2zAl5UoQM9iiwe5.WOT/oRYUhGCOL3Se90.Bpvu1hYJpdiTrG', 'nairobi', 'supermarket', '45454664', 'A87777878787878B', 'safaricom.png', 'olivewoodspatula.webp', 'c-d-x-PDX_a_82obo-unsplash.jpg', '3354545454', '::1', 'active', 1),
(4, 'tindi', 'tindi', 'tindi@gmail.com', '0534050458', '$2y$10$i60AqjZ39H/QbYiwXReNM.xMSWrWYqxHkbZ1RXqFxZvR1zDR2nNte', 'nairobi', 'software company', '435454', 'A4354545545B', 'usericon.png', 'Tiny_Tea_Spoon.webp', 'dish.webp', '4543646', '::1', 'active', 1),
(5, 'alem', 'alem404050', 'alem40@gmail.com', '0787654321', '$2y$10$08.cwP9iSYBFcFVTyXSEjeyKp9BjFRPbDQTxCtYarFPkhE6FF5kqu', 'kisumu', 'Smart shop', '78675436', '78877667766', 'wireBicycle.webp', 'safaricom.png', 'woven_basket.webp', '343455454566', '::1', 'active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `quantity` int(100) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `product_id`, `ip_address`, `quantity`, `added_at`) VALUES
(7, 13, '::1', 1, '2026-09-12 07:02:25');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'shoe'),
(2, 'clothes'),
(3, 'Handcraft'),
(4, 'jewelry'),
(5, 'Furniture'),
(14, 'Bags'),
(15, 'kitchen'),
(16, 'stationary');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `user_ip` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `username`, `email`, `phone_number`, `address`, `user_ip`, `password`, `status`) VALUES
(12, 'samwel', 'tindi', 'samwel123', 'samwel123@gmail.com', '254742086326', 'Nairobi', '::1', '$2y$10$ChnnmqjvOd1NEf.eu8m4R.J5dXtMwytiTVz9hfNH8.ZxHb1pI7syC', 'active'),
(13, 'lilian', 'aloo', 'lilian123', 'lilian@gmail.com', '254756784567', 'narok', '::1', '$2y$10$vrmHPNsJL3Hh4MVOqqEZ6uxgM1GwP0Fhm6HjEoegErzFxhTA9agnK', 'active'),
(16, 'William', 'Kariuki', 'William5050', 'william@gmail.com', '254740608970', 'Nakuru', '::1', '$2y$10$mj9kVpRtzjfkTqF8qfa2SeSOfsfidk/M9CnGPZxwOkarFVs0VYFB6', 'active'),
(17, 'Tindi', 'aloo', 'tindi123', 'tindi@gmail.com', '254756458967', 'nairobi', '::1', '$2y$10$hsEa.C1Vxe1QUiKnF17MQ.tn1Xygzxcck8oZvb./BqeKLHmF0LQl.', 'active'),
(18, 'Michael', 'alem', 'alem1234', 'alem@gmail.com', '254763235678', 'nakuru', '::1', '$2y$10$snXM.RLFQabGMN2ie.pAjuNd21jE/gM/lFgJsT1WPeTWV/0eNsegy', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `invoice_number` int(10) UNSIGNED NOT NULL,
  `customer_id` int(11) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_status` enum('pending','dispatched','in_transit','delivered','failed') NOT NULL DEFAULT 'pending',
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_notes` varchar(255) DEFAULT NULL,
  `dispatched_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `invoice_number`, `customer_id`, `recipient_name`, `phone_number`, `delivery_address`, `city`, `delivery_fee`, `delivery_status`, `courier_name`, `tracking_notes`, `dispatched_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 649294529, 12, 'samwel tindi', '0742086326', 'dfdfds', 'rongai', 0.00, 'in_transit', 'joseph', 'Rongai', NULL, NULL, '2026-09-11 17:56:13', '2026-09-11 18:00:49'),
(2, 615032152, 13, 'Lilian aloo', '0742086326', 'mayor road no 31', 'rongai', 0.00, 'in_transit', 'joseph', 'lilian', NULL, NULL, '2026-09-11 18:12:00', '2026-09-11 18:15:24'),
(3, 2082770355, 12, 'samwel', '0742086326', 'fddsf', 'rongai', 150.00, 'pending', 'joseph', '', NULL, NULL, '2026-09-12 05:04:55', '2026-09-12 05:04:55'),
(4, 2038093856, 12, 'samwel', '0742086326', 'mayor road no 23', 'rongai', 0.00, 'pending', 'joseph', '', NULL, NULL, '2026-09-12 06:00:34', '2026-09-12 06:00:34'),
(5, 1062648286, 12, 'samwel', '0742086326', 'mayor road no 3', 'rongai', 150.00, 'pending', 'joseph', '', NULL, NULL, '2026-09-12 07:01:06', '2026-09-12 07:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_transactions`
--

CREATE TABLE `mpesa_transactions` (
  `id` int(11) NOT NULL,
  `invoice_number` int(11) DEFAULT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `result_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mpesa_transactions`
--

INSERT INTO `mpesa_transactions` (`id`, `invoice_number`, `checkout_request_id`, `phone`, `amount`, `status`, `result_description`, `created_at`) VALUES
(1, 788851955, 'ws_CO_180820261133425742086326', '0742086326', 500.00, 'failed', 'The balance is insufficient for the transaction.', '2026-08-18 08:33:42'),
(2, 2120675040, 'ws_CO_180820261138162742086326', '0742086326', 100.00, 'completed', 'The service request is processed successfully.', '2026-08-18 08:38:16'),
(3, 2061802472, 'ws_CO_180820261228316708374149', '254708374149', 500.00, 'failed', 'DS timeout user cannot be reached.', '2026-08-18 09:28:31'),
(4, 1221293871, 'ws_CO_180820261235568708374149', '254708374149', 2000.00, 'failed', 'No response from user.', '2026-08-18 09:35:57'),
(5, 1274803821, 'ws_CO_200820260916321742086326', '0742086326', 730.00, 'failed', 'The balance is insufficient for the transaction.', '2026-08-20 06:16:32'),
(6, 1315818096, 'ws_CO_200820261209492742086326', '0742086326', 1890.00, 'failed', 'The balance is insufficient for the transaction.', '2026-08-20 09:09:49'),
(7, 1253934055, 'ws_CO_250820261022515742086326', '0742086326', 3630.00, 'failed', 'The balance is insufficient for the transaction.', '2026-08-25 07:22:51'),
(8, 1280556039, 'ws_CO_250820261348103742086326', '0742086326', 5.80, 'completed', 'The service request is processed successfully.', '2026-08-25 10:48:10'),
(9, 538671462, 'ws_CO_310820261309040742086326', '0742086326', 1542.00, 'pending', NULL, '2026-08-31 10:08:58'),
(10, 148653693, 'ws_CO_010920261101104742086326', '0742086326', 2.32, 'pending', NULL, '2026-09-01 08:01:10'),
(11, 1625570987, 'ws_CO_010920261117300742086326', '0742086326', 2.32, 'completed', 'The service request is processed successfully.', '2026-09-01 08:17:30'),
(12, 126353284, 'ws_CO_080920261533096742086326', '0742086326', 580.00, 'pending', NULL, '2026-09-08 12:33:02'),
(13, 1266246568, 'ws_CO_080920261538008742086326', '0742086326', 1.16, 'pending', NULL, '2026-09-08 12:37:54'),
(14, 1266246568, 'ws_CO_080920261540272742086326', '0742086326', 1.00, 'pending', NULL, '2026-09-08 12:40:20'),
(15, 289197417, 'ws_CO_080920261551130742086326', '0742086326', 1.16, 'completed', 'The service request is processed successfully.', '2026-09-08 12:51:06'),
(16, 767656083, 'ws_CO_080920261855363742086326', '0742086326', 1740.00, 'pending', NULL, '2026-09-08 15:55:29'),
(17, 126835401, 'ws_CO_090920261206160742086326', '0742086326', 3712.00, 'failed', 'No response from user.', '2026-09-09 09:06:09'),
(18, 588490063, 'ws_CO_090920261552579742086326', '0742086326', 1160.00, 'pending', NULL, '2026-09-09 12:52:58'),
(19, 1352522979, 'ws_CO_090920261621178742086326', '0742086326', 580.00, 'pending', NULL, '2026-09-09 13:21:18'),
(20, 1061678156, 'ws_CO_090920261624319742086326', '0742086326', 1.16, 'pending', NULL, '2026-09-09 13:24:32'),
(21, 1117144132, 'ws_CO_090920261627122742086326', '0742086326', 1.16, 'completed', 'The service request is processed successfully.', '2026-09-09 13:27:12'),
(22, 1040749320, 'ws_CO_100920261041013742086326', '0742086326', 2.32, 'completed', 'The service request is processed successfully.', '2026-09-10 07:41:01'),
(23, 533589021, 'ws_CO_100920261437301742086326', '0742086326', 1.16, 'completed', 'The service request is processed successfully.', '2026-09-10 11:37:30'),
(24, 649294529, 'ws_CO_110920262056124742086326', '0742086326', 1.16, 'pending', NULL, '2026-09-11 17:56:13'),
(25, 615032152, 'ws_CO_110920262111599742086326', '0742086326', 11.60, 'pending', NULL, '2026-09-11 18:12:00'),
(26, 2082770355, 'ws_CO_120920260804550742086326', '0742086326', 161.60, 'pending', NULL, '2026-09-12 05:04:55'),
(27, 2038093856, 'ws_CO_120920260900345742086326', '0742086326', 11.60, 'pending', NULL, '2026-09-12 06:00:34'),
(28, 1062648286, 'ws_CO_120920261001058742086326', '0742086326', 161.60, 'pending', NULL, '2026-09-12 07:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` int(50) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `platform_commission` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 150.00,
  `artisan_payout` decimal(10,2) DEFAULT NULL,
  `total_products` int(50) NOT NULL,
  `invoice_number` int(50) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_status` varchar(255) NOT NULL,
  `delivery_confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `total_amount`, `subtotal`, `platform_commission`, `tax_amount`, `delivery_fee`, `artisan_payout`, `total_products`, `invoice_number`, `order_date`, `order_status`, `delivery_confirmed_at`) VALUES
(7, 12, 1, 1.00, 0.03, 0.16, 0.00, 0.00, 1, 289197417, '2026-09-08 12:55:38', 'complete', '2026-09-08 12:55:38'),
(8, 12, 1740, 1500.00, 45.00, 240.00, 0.00, 1455.00, 1, 767656083, '2026-09-08 15:55:25', 'pending', NULL),
(9, 12, 3712, 3200.00, 96.00, 512.00, 0.00, 3104.00, 3, 126835401, '2026-09-09 09:06:35', 'payment_failed', NULL),
(10, 12, 1740, 1500.00, 45.00, 240.00, 0.00, 1455.00, 1, 2052503356, '2026-09-09 09:33:25', 'pending', NULL),
(11, 12, 522, 450.00, 13.50, 72.00, 0.00, 436.00, 1, 1347476154, '2026-09-09 11:51:28', 'pending', NULL),
(12, 12, 580, 500.00, 15.00, 80.00, 0.00, 485.00, 1, 1352522979, '2026-09-09 13:21:16', 'pending', NULL),
(13, 12, 1, 1.00, 0.03, 0.16, 0.00, 0.00, 1, 1061678156, '2026-09-09 13:24:30', 'pending', NULL),
(14, 12, 1, 1.00, 0.03, 0.16, 0.00, 0.00, 1, 1117144132, '2026-09-10 04:21:41', 'complete', '2026-09-10 04:21:41'),
(15, 13, 2, 2.00, 0.06, 0.32, 0.00, 1.00, 1, 1040749320, '2026-09-10 07:41:52', 'complete', '2026-09-10 07:41:52'),
(16, 12, 1, 1.00, 0.03, 0.16, 0.00, 0.00, 1, 533589021, '2026-09-10 11:37:58', 'complete', '2026-09-10 11:37:58'),
(17, 12, 1, 1.00, 0.03, 0.16, 0.00, 0.00, 1, 649294529, '2026-09-11 17:56:06', 'pending', NULL),
(18, 13, 12, 10.00, 0.30, 1.60, 0.00, 9.00, 1, 615032152, '2026-09-11 18:11:54', 'pending', NULL),
(19, 12, 162, 10.00, 0.30, 1.60, 150.00, 9.00, 1, 2082770355, '2026-09-12 05:04:53', 'pending', NULL),
(20, 12, 12, 10.00, 0.30, 1.60, 0.00, 9.00, 1, 885701089, '2026-09-12 05:54:48', 'pending', NULL),
(21, 12, 12, 10.00, 0.30, 1.60, 0.00, 9.00, 1, 2038093856, '2026-09-12 06:00:31', 'pending', NULL),
(22, 12, 162, 10.00, 0.30, 1.60, 150.00, 9.00, 1, 1062648286, '2026-09-12 07:01:04', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_pending`
--

CREATE TABLE `order_pending` (
  `item_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `invoice_number` int(11) NOT NULL,
  `order_status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_pending`
--

INSERT INTO `order_pending` (`item_id`, `customer_id`, `product_id`, `quantity`, `invoice_number`, `order_status`) VALUES
(2, 12, 6, 1, 148653693, 'pending'),
(3, 12, 6, 1, 1625570987, 'pending'),
(4, 12, 13, 1, 1716221848, 'pending'),
(5, 12, 15, 1, 1716221848, 'pending'),
(6, 12, 13, 1, 1946555680, 'pending'),
(7, 12, 15, 1, 1946555680, 'pending'),
(8, 12, 13, 1, 588490063, 'pending'),
(9, 12, 15, 1, 588490063, 'pending'),
(10, 12, 14, 1, 1039675738, 'pending'),
(11, 12, 15, 1, 1039675738, 'pending'),
(12, 12, 20, 1, 126353284, 'pending'),
(13, 12, 6, 1, 1266246568, 'pending'),
(14, 12, 6, 1, 289197417, 'pending'),
(15, 12, 14, 1, 767656083, 'pending'),
(16, 12, 14, 1, 126835401, 'pending'),
(17, 12, 15, 1, 126835401, 'pending'),
(18, 12, 16, 1, 126835401, 'pending'),
(19, 12, 14, 1, 2052503356, 'pending'),
(20, 12, 13, 1, 1347476154, 'pending'),
(21, 12, 15, 1, 1352522979, 'pending'),
(22, 12, 6, 1, 1061678156, 'pending'),
(23, 12, 6, 1, 1117144132, 'pending'),
(24, 13, 6, 2, 1040749320, 'pending'),
(25, 12, 6, 1, 533589021, 'pending'),
(26, 12, 6, 1, 649294529, 'pending'),
(27, 13, 7, 1, 615032152, 'pending'),
(28, 12, 7, 1, 2082770355, 'pending'),
(29, 12, 7, 1, 885701089, 'pending'),
(30, 12, 7, 1, 2038093856, 'pending'),
(31, 12, 7, 1, 1062648286, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `artisan_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` varchar(255) NOT NULL,
  `product_keywords` varchar(255) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `product_price` decimal(10,0) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(100) NOT NULL,
  `approval_status` enum('pending','approved','suspended') NOT NULL DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `artisan_id`, `product_name`, `product_description`, `product_keywords`, `product_image`, `stock_quantity`, `product_price`, `date`, `status`, `approval_status`) VALUES
(6, 1, 3, 'Mary and joseph', 'Best picture of mary and joseph', 'mary,joseph,jesus', 'tv4.jpeg', 7, 1, '2026-09-11 18:29:13', 'true', 'suspended'),
(7, 1, 3, 'Dish', 'The best dish around.', 'dish,plate', 'dish.webp', 20, 10, '2026-09-11 18:07:44', 'true', 'approved'),
(13, 1, 1, 'shoe', 'best shoes', 'shoes', 'Ankara_shoes.png', 17, 450, '2026-09-08 13:47:23', 'true', 'approved'),
(14, 3, 1, 'wire bicycle', 'Best wire bicycle', 'bicycle', 'wireBicycle.webp', 14, 1500, '2026-08-17 10:29:01', 'true', 'approved'),
(15, 2, 4, 'jamper', 'best jamper', 'jamper,sweate', 'AfricanJamper.png', 20, 500, '2026-08-17 12:28:41', 'true', 'approved'),
(16, 1, 4, 'clothe basket', 'Best cloth basket', 'basket,cloth', 'AfricanClothBasket.png', 20, 1200, '2026-08-20 06:23:31', 'true', 'approved'),
(17, 3, 3, 'Spatula', 'Best spatula', 'spoon', 'olivewoodspatula.webp', 30, 300, '2026-08-22 06:29:41', 'true', 'approved'),
(19, 4, 1, 'woven basket', 'best woven bag', 'woven,bag,carrier', 'woven_basket.webp', 20, 500, '2026-09-02 08:55:20', 'true', 'approved'),
(20, 1, 5, 'shoes', 'best shoes', 'shoes', 'wireBicycle.webp', 40, 500, '2026-09-09 19:20:37', 'true', 'approved'),
(21, 5, 3, 'ear phone', 'best earphone', 'earphone', 'woofer.webp', 45, 600, '2026-09-08 14:23:43', 'true', 'suspended'),
(22, 4, 5, 'watch', 'best watch', 'watch', 'laptop.jpg', 20, 2000, '2026-09-11 18:29:31', 'true', 'suspended'),
(23, 1, 3, 'product', '78786', '8876', 'safaricom.png', 6, 200, '2026-09-11 18:29:44', 'true', 'suspended'),
(24, 1, 4, 'shoes', 'best shoes', 'shoes', 'safaricom.png', 15, 400, '2026-09-11 18:29:54', 'true', 'suspended'),
(25, 4, 4, 'Ring', 'best ring', 'ring, wedding', 'ring.webp', 10, 500, '2026-09-11 18:47:36', 'true', 'approved'),
(26, 3, 3, 'Wired Keychain', 'Marvelous keychain', 'key,keychain', 'wiredkeychain.webp', 10, 200, '2026-09-11 18:49:36', 'true', 'approved'),
(27, 15, 1, 'Girafe Bowl', 'Best bowl', 'bowl,plate', 'girafebowl.webp', 25, 300, '2026-09-11 18:52:15', 'true', 'approved'),
(28, 4, 5, 'hearings', 'Best heaings', 'hearing', 'hearings.webp', 20, 500, '2026-09-11 18:58:39', 'true', 'approved'),
(29, 14, 5, 'Kikapu bag', 'Best bag', 'bag', 'kikapubag.webp', 20, 600, '2026-09-11 18:58:52', 'true', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `artisans`
--
ALTER TABLE `artisans`
  ADD PRIMARY KEY (`artisan_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_ip_product` (`ip_address`,`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `idx_invoice` (`invoice_number`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`delivery_status`);

--
-- Indexes for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_customer` (`customer_id`);

--
-- Indexes for table `order_pending`
--
ALTER TABLE `order_pending`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_orderpending_customer` (`customer_id`),
  ADD KEY `fk_orderpending_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `fk_products_category` (`category_id`),
  ADD KEY `fk_products_artisan` (`artisan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `artisans`
--
ALTER TABLE `artisans`
  MODIFY `artisan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_pending`
--
ALTER TABLE `order_pending`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_pending`
--
ALTER TABLE `order_pending`
  ADD CONSTRAINT `fk_orderpending_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderpending_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_artisan` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`artisan_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
