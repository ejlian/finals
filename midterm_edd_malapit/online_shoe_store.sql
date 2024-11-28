-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2024 at 04:34 PM
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
-- Database: `online_shoe_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(5, 'admin1', '$2y$10$L/zUNTcOR7eJHWdDTdCaJOgeTSBO4QLx5EmQu9kIqYVAxOI5R/7Zm', 'admin1@example.com', '2024-11-28 08:51:51'),
(6, 'admin2', 'admin123', 'admin2@example.com', '2024-11-28 08:51:51'),
(7, 'admin3', 'admin123', 'admin3@example.com', '2024-11-28 08:51:51');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(6, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 08:55:30'),
(7, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 09:00:25'),
(8, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 09:21:24'),
(9, 5, 'Logout', 'Admin logged out', '::1', '2024-11-28 12:50:27'),
(10, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 12:50:33'),
(11, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 13:08:14'),
(12, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 13:26:49'),
(13, 5, 'Login', 'Admin logged in successfully', '::1', '2024-11-28 15:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shoe_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `shoe_id`, `quantity`, `created_at`) VALUES
(113, 8, 2, 1, '2024-11-28 13:13:06'),
(114, 6, 4, 1, '2024-11-28 13:50:56');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `address`, `phone`) VALUES
(6, 'jack', 'daniels', 'jackdaniels@gmail.com', '$2y$10$aeogkk4b/CXhlo1NI5gBuO4UHkzO5XGmVfcj6Epf/g0hO8O2nt75y', 'mindanao', '01234567'),
(7, 'boss', 'idol', 'bossidol@gmail.com', '$2y$10$60HAO0LfYZD0ln0dFgRNUeAvzcnYwvbwwjFhxV3E/M7huUYwIcT5O', 'Balanga Bataan ', '555666'),
(8, 'pogi', 'ako', 'pogi@pogi', '$2y$10$YHExcD9AY9XFT.6gqEogmua68p3fX2ykZYnFk/EHoNEZJfvf7fHva', 'sermal', '111222');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `order_status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `estimated_delivery` int(11) DEFAULT NULL,
  `delivery_method` varchar(50) DEFAULT 'standard',
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `queue` enum('processing','packing','shipping') DEFAULT 'processing',
  `payment_method_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `address`, `phone`, `payment_method`, `order_status`, `created_at`, `shipping_address`, `total_amount`, `delivery_fee`, `estimated_delivery`, `delivery_method`, `payment_status`, `payment_date`, `queue`, `payment_method_id`) VALUES
(83, 7, '', '89283785', 'cash_on_delivery', 'Pending', '2024-11-12 15:31:13', 'sermal, atulano, bataan', 15014.00, 15.00, NULL, 'express', 'pending', NULL, 'processing', NULL),
(84, 6, '', '89283785', 'cash_on_delivery', 'Pending', '2024-11-12 15:32:08', 'sermal, atulano, bataan', 9004.75, 5.00, NULL, 'bike', 'pending', NULL, 'processing', NULL),
(85, 7, '', 'rr', 'cash_on_delivery', 'Pending', '2024-11-26 17:52:41', 'ee, rr, rr', 13004.00, 5.00, NULL, 'standard', 'pending', NULL, 'processing', NULL),
(86, 7, '', '', 'credit_card', 'Pending', '2024-11-28 14:55:44', NULL, 42002.00, 5.00, NULL, 'standard', 'pending', NULL, 'processing', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `shoe_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `shoe_id`, `quantity`, `price`, `created_at`) VALUES
(97, 83, 3, 1, 14999.00, '2024-11-12 15:31:13'),
(98, 84, 2, 1, 8999.75, '2024-11-12 15:32:08'),
(99, 85, 1, 1, 12999.00, '2024-11-26 17:52:41'),
(100, 86, 1, 1, 12999.00, '2024-11-28 14:55:44'),
(101, 86, 3, 1, 14999.00, '2024-11-28 14:55:44'),
(102, 86, 4, 1, 13999.00, '2024-11-28 14:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('CASHLESS','CASH','STORE','DELIVERY') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `type`, `is_active`) VALUES
(1, 'Credit/Debit Card', 'CASHLESS', 1),
(2, 'Cash Payment', 'CASH', 1),
(3, 'In-Store Payment', 'STORE', 1),
(4, 'Cash on Delivery', 'DELIVERY', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shoes`
--

CREATE TABLE `shoes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` decimal(4,1) NOT NULL DEFAULT 9.0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shoes`
--

INSERT INTO `shoes` (`id`, `name`, `description`, `price`, `size`, `stock`, `image`) VALUES
(1, 'Air Jordan 1 Low ', 'Hidden Nike Air unit in the heel provides lightweight cushioning.', 12999.00, 10.0, 49, 'image1.jpg'),
(2, 'Nike Kobe 8', 'Engineered mesh on the upper is soft and pliable, wrapping around your foot and conforming to its shape.', 8999.75, 9.0, 61, 'image2.jpg'),
(3, 'Air Jordan 4', 'Elegant leather dress shoe', 14999.00, 11.0, 23, 'image3.jpg'),
(4, 'Air Jordan 1 low', 'Encapsulated Air unit provides lightweight cushioning.\r\nLeather and textile materials in the upper are light and durable.', 13999.00, 10.5, 37, 'image4.jpg'),
(5, 'Nike Air Max', 'Classic comfort with modern style', 7999.45, 10.0, 49, 'image5.jpg'),
(6, 'Adidas Ultraboost', 'Premium running shoes with responsive cushioning', 9989.45, 11.0, 50, 'image6.jpg'),
(7, 'Puma RS-X', 'Retro-inspired chunky sneakers', 6999.45, 9.0, 49, 'image7.jpg'),
(8, 'New Balance 574', 'Timeless design with modern comfort', 7499.45, 10.0, 50, 'image8.jpg'),
(10, 'Converse Chuck Taylor', 'Iconic high-top sneakers', 6999.45, 9.0, 50, 'image10.jpg'),
(11, 'Reebok Classic', 'Vintage-inspired lifestyle shoes', 7999.45, 8.5, 50, 'image11.jpg'),
(12, 'ASICS Gel-Kayano', 'Premium running shoes with superior support', 8879.45, 9.0, 49, 'image12.jpg'),
(13, 'Jordan 1 Low', 'Iconic basketball shoes with street style', 8659.45, 10.0, 50, 'image13.jpg'),
(14, 'Skechers D Lites', 'Comfortable chunky sneakers', 6884.45, 9.5, 49, 'image14.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shoe_id` (`shoe_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `fk_payment_method` (`payment_method_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `shoe_id` (`shoe_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shoes`
--
ALTER TABLE `shoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shoes`
--
ALTER TABLE `shoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`shoe_id`) REFERENCES `shoes` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_customer_fk` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`shoe_id`) REFERENCES `shoes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
