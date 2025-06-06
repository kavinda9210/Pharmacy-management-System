-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2025 at 10:30 AM
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
-- Database: `pharmacydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `location`, `contact_number`, `address`, `created_at`) VALUES
(6, 'Kandy', '0715485968ghhg', 'ghghghhggdgdgddffdvvb', '2025-03-13 14:46:32'),
(7, 'Magni ullamco cupida', '6767567567', 'Nam nisi distinctiobvnvdfd', '2025-03-13 14:47:58'),
(8, 'In corporis officiis', '145ddfdfdf', 'Mollitia autem asper', '2025-03-13 16:07:21'),
(9, 'In corporis officiis', '145ng', 'Mollitia autem asper', '2025-03-13 16:08:25'),
(12, 'Nihil totam et nulla', '617', 'In natus voluptatem', '2025-03-13 16:10:08'),
(13, 'Nihil totam et nulla', '617', 'In natus voluptatem', '2025-03-13 16:10:35'),
(14, 'gjhvvhgghghghg', 'hghghg', 'ghjghghghg', '2025-03-13 16:11:07');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `created_at`) VALUES
(6, 'Basil Wallersads', '2025-03-13 16:12:32');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
(4, 'ghgggghhq', '2025-03-13 15:51:59'),
(5, 'aaaa', '2025-03-16 04:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `status` enum('in stock','out of stock') DEFAULT 'in stock',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_name`, `description`, `price`, `quantity`, `expiry_date`, `supplier_id`, `status`, `timestamp`, `category_id`, `brand_id`) VALUES
(5, 'Davis Kemp', 'Eveniet praesentium', 650.00, 5278, '2025-03-31', 2, 'in stock', '2025-03-13 15:07:15', 5, 6),
(13, 'Adrian Wolf', 'Aliqua Qui perferen', 421.00, 764, '2025-03-13', 2, 'in stock', '2025-03-13 15:16:03', 3, 5),
(18, 'Jemima James', 'Quidem iusto id volu', 144.00, 707, '2025-03-13', 2, 'in stock', '2025-03-13 15:51:20', 4, 5),
(19, 'Jemima James', 'Quidem iusto id voludsddfdf', 144.00, 707, '2025-03-13', 2, 'in stock', '2025-03-13 15:51:42', 4, 5),
(20, 'ghhghhhg', 'hgjgghhghgh', 3200.00, 4, '2025-03-27', 2, 'in stock', '2025-03-13 15:52:34', 4, 5),
(22, 'dvg', 'sg', 150.00, 700, '2025-03-31', 2, 'in stock', '2025-03-16 04:26:48', 5, 6);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `customer` varchar(255) NOT NULL,
  `payment` enum('Cash','Credit Card','Online Payment','') NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_number` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `branch_id`, `medicine_id`, `quantity`, `customer`, `payment`, `total_price`, `sale_date`, `reference_number`) VALUES
(2, 6, 5, 12, 'dinna', 'Cash', 7800.00, '2025-03-17 04:21:36', 'REFTN0GUCH0XSF');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `location` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_number`, `location`, `address`, `created_at`) VALUES
(3, 'aaa', '0771631321', 'Walapane', 'assaas', '2025-03-17 04:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `role` enum('admin','pharmacist') NOT NULL DEFAULT 'pharmacist',
  `branch_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `password` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `nic` varchar(20) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `contact_number`, `role`, `branch_id`, `is_active`, `password`, `username`, `address`, `nic`, `photo`, `created_at`) VALUES
(5, 'Admin', 'admin@gmail.com', '0710852093', 'admin', 6, 1, '$2y$10$B4BdEU6kffZ0mpyf27yWsOAtfM5lSaHW84NOSCb/JTKg.bk8R2i6O', 'admin', 'no65, 2nd lane, kanduratasandella,Mailapitiya', '200019901030', 'profile_67d7aac8d4f02.png', '2025-03-17 04:20:34'),
(8, 'Abc', 'abc@gmail.com', '0710852094', 'admin', 6, 1, '$2y$10$E1R94sHRR24deo4Pw4n1ResC5UYy.gi/nDqbusFkv40VHU59.cc5O', 'abc', 'no65, 2nd lane, kanduratasandella', '200019901037', 'profile_67d7d5c4a1fa8.png', '2025-03-17 07:55:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `supplier_name` (`supplier_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nic` (`nic`),
  ADD KEY `branches_id_n` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `branches_id_n` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
