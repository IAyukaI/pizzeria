-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Paź 19, 2025 at 07:40 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pizzeria`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `login` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `phone`, `email`, `address`, `login`, `password`) VALUES
(1, 'asd', '', '', '', 'dsd', '$2y$10$mbxEAaelXS1rRBAbSz4GQevdirwasFNIydDFBz3hfz8CZx0ga35hq'),
(2, 'abc 123', '123', 'abc@a', 'abc', 'abc', '$2y$10$Ac4doyAXSt.m/qKg1bHBLOpoGJsdspQhKGB/vyED5J37ouuNmFylW');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Złożone'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_date`, `total_price`, `status`) VALUES
(1, 1, '2025-10-19 01:24:51', 110.00, 'Złożone'),
(2, 2, '2025-10-19 15:01:11', 139.00, 'Złożone'),
(3, 2, '2025-10-19 15:24:43', 90.00, 'Złożone');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `item_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `pizza_id`, `quantity`, `item_price`) VALUES
(1, 1, 1, 2, 25.00),
(2, 1, 6, 1, 31.00),
(3, 1, 4, 1, 29.00),
(4, 2, 5, 1, 27.00),
(5, 2, 2, 4, 28.00),
(6, 3, 3, 3, 30.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pizzas`
--

CREATE TABLE `pizzas` (
  `pizza_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `pizzas`
--

INSERT INTO `pizzas` (`pizza_id`, `name`, `base_price`, `description`, `image_url`) VALUES
(1, 'Margherita', 25.00, 'Klasyczna pizza z sosem pomidorowym, mozzarellą i bazylią.', 'pizza_margherita.jpg'),
(2, 'Pepperoni', 28.00, 'Ostra pizza z plasterkami pepperoni i mozzarellą.', 'pizza_pepperoni.jpg'),
(3, 'Capricciosa', 30.00, 'Szynka, pieczarki, karczochy i oliwki.', 'pizza_capricciosa.jpg'),
(4, 'Vegetariana', 29.00, 'Papryka, cukinia, bakłażan, cebula i oliwki.', 'pizza_vegetariana.jpg'),
(5, 'Hawajska', 27.00, 'Z szynką i ananasem – klasyczna kontrowersja!', 'pizza_hawajska.jpg'),
(6, 'Diavola', 31.00, 'Pikantna pizza z salami i papryczkami chili.', 'pizza_diavola.jpg');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Indeksy dla tabeli `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeksy dla tabeli `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `pizza_id` (`pizza_id`);

--
-- Indeksy dla tabeli `pizzas`
--
ALTER TABLE `pizzas`
  ADD PRIMARY KEY (`pizza_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pizzas`
--
ALTER TABLE `pizzas`
  MODIFY `pizza_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
