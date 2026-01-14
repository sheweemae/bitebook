-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 03:49 PM
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
-- Database: `bitebook_recipe_list`
--

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipe_name` varchar(255) NOT NULL,
  `imgfile` blob DEFAULT NULL,
  `is_favorite` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `user_id`, `recipe_name`, `imgfile`, `is_favorite`) VALUES
(1, 1, 'Chicken Adobo', 0x75706c6f6164732f636869636b656e2d61646f626f2d312e6a7067, 0),
(2, 1, 'Pork Giniling', 0x75706c6f6164732f506f726b2d47696e696c696e672d776974682d626f696c65642d656767732d5265636970652d37323278313032342e6a7067, 1),
(3, 1, 'Cassava Cake', 0x75706c6f6164732f636173736176612d63616b652d7265636970652d312d37333078313031342e77656270, 0),
(6, 1, 'Halo-Halo', 0x75706c6f6164732f68616c6f68616c6f2e77656270, 1),
(7, 1, 'Kare Kare', 0x75706c6f6164732f6b6172656b6172652e6a7067, 0),
(8, 1, 'Fudge Brownies', 0x75706c6f6164732f62726f776e6965732e6a7067, 1);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_attributes`
--

CREATE TABLE `recipe_attributes` (
  `attribute_id` int(11) NOT NULL,
  `attribute_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_attributes`
--

INSERT INTO `recipe_attributes` (`attribute_id`, `attribute_name`) VALUES
(10, 'Beverages'),
(6, 'Breakfast'),
(5, 'Comfort'),
(8, 'Dinner'),
(2, 'Healthy'),
(7, 'Lunch'),
(1, 'Quick'),
(9, 'Snacks & Dessert'),
(4, 'Spicy'),
(3, 'Sweet');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_attribute_values`
--

CREATE TABLE `recipe_attribute_values` (
  `recipe_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_attribute_values`
--

INSERT INTO `recipe_attribute_values` (`recipe_id`, `attribute_id`) VALUES
(1, 1),
(1, 2),
(1, 5),
(1, 6),
(1, 7),
(2, 2),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(3, 3),
(3, 9),
(6, 1),
(6, 3),
(6, 5),
(6, 9),
(6, 10),
(7, 2),
(7, 7),
(8, 3),
(8, 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipe_attributes`
--
ALTER TABLE `recipe_attributes`
  ADD PRIMARY KEY (`attribute_id`),
  ADD UNIQUE KEY `attribute_name` (`attribute_name`);

--
-- Indexes for table `recipe_attribute_values`
--
ALTER TABLE `recipe_attribute_values`
  ADD PRIMARY KEY (`recipe_id`,`attribute_id`),
  ADD KEY `attribute_id` (`attribute_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `recipe_attributes`
--
ALTER TABLE `recipe_attributes`
  MODIFY `attribute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bitebook_users`.`users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_attribute_values`
--
ALTER TABLE `recipe_attribute_values`
  ADD CONSTRAINT `recipe_attribute_values_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_attribute_values_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `recipe_attributes` (`attribute_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
