-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 04:06 PM
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
-- Database: `petrol_pump`
--

-- --------------------------------------------------------

--
-- Table structure for table `fuel_inventory`
--

CREATE TABLE `fuel_inventory` (
  `id` int(11) NOT NULL,
  `tank_name` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `stock` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `capacity` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_inventory`
--

INSERT INTO `fuel_inventory` (`id`, `tank_name`, `fuel_type`, `stock`, `status`, `capacity`) VALUES
(7, 'tank1', 'Petrol', 5400.00, 'Normal', 25000.00),
(8, 'tank2', 'Diesel', 5500.00, 'Low', 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `fuel_sales`
--

CREATE TABLE `fuel_sales` (
  `id` int(11) NOT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `liters` decimal(10,2) DEFAULT NULL,
  `price_per_liter` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `nozzle_number` int(11) DEFAULT NULL,
  `profit_per_liter` decimal(10,2) DEFAULT NULL,
  `total_profit` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_sales`
--

INSERT INTO `fuel_sales` (`id`, `fuel_type`, `liters`, `price_per_liter`, `total`, `payment_method`, `nozzle_number`, `profit_per_liter`, `total_profit`, `sale_date`) VALUES
(13, 'Petrol', 100.00, 410.00, 41000.00, 'Cash', 1, 7.00, 700.00, '2026-05-21');

-- --------------------------------------------------------

--
-- Table structure for table `khata`
--

CREATE TABLE `khata` (
  `id` int(11) NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `purchase_item` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `advance_amount` decimal(10,2) NOT NULL,
  `remaining_amount` decimal(10,2) NOT NULL,
  `promise_date` date NOT NULL,
  `entry_date` date NOT NULL,
  `reminder_ok` tinyint(1) DEFAULT 0,
  `reminder_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khata`
--

INSERT INTO `khata` (`id`, `person_name`, `contact_number`, `address`, `purchase_item`, `quantity`, `advance_amount`, `remaining_amount`, `promise_date`, `entry_date`, `reminder_ok`, `reminder_sent`) VALUES
(13, 'haji', '040404040404', 'tehsil depalpur distric okara city haveli lakha', 'dieswl', 123.00, 0.00, 12131321.00, '2026-05-22', '2026-05-21', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lubricants_inventory`
--

CREATE TABLE `lubricants_inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `purchase_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lubricants_inventory`
--

INSERT INTO `lubricants_inventory` (`id`, `name`, `brand`, `stock`, `purchase_price`, `sale_price`, `status`) VALUES
(4, 'Fashion', 'PSO', 123.00, 234.00, 600.00, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `lubricants_sales`
--

CREATE TABLE `lubricants_sales` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_per_liter` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `sale_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lubricants_sales`
--

INSERT INTO `lubricants_sales` (`id`, `customer_name`, `product_name`, `quantity`, `price_per_liter`, `total`, `payment_method`, `sale_date`) VALUES
(1, 'haad', 'blaze4t', 20.00, 124.00, 2480.00, 'Card', '2026-05-21');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `total_sales_base` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `total_sales_base`) VALUES
(1, 422.50);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fuel_inventory`
--
ALTER TABLE `fuel_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fuel_sales`
--
ALTER TABLE `fuel_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `khata`
--
ALTER TABLE `khata`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lubricants_inventory`
--
ALTER TABLE `lubricants_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lubricants_sales`
--
ALTER TABLE `lubricants_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fuel_inventory`
--
ALTER TABLE `fuel_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fuel_sales`
--
ALTER TABLE `fuel_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `khata`
--
ALTER TABLE `khata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `lubricants_inventory`
--
ALTER TABLE `lubricants_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lubricants_sales`
--
ALTER TABLE `lubricants_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
