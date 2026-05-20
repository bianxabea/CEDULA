-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 04:50 AM
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
-- Database: `cedula_pizza_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_creation_requests`
--

CREATE TABLE `admin_creation_requests` (
  `id` int(11) NOT NULL,
  `requested_by` varchar(11) NOT NULL COMMENT 'User ID of superadmin requesting',
  `target_username` varchar(50) NOT NULL,
  `target_email` varchar(100) NOT NULL,
  `target_role` enum('admin','superadmin') NOT NULL,
  `target_firstName` varchar(50) NOT NULL,
  `target_lastName` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` varchar(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `id` int(11) NOT NULL,
  `requested_by` varchar(11) NOT NULL COMMENT 'User ID of admin requesting',
  `action_type` enum('delete_user','delete_restaurant','delete_menu_item','other') NOT NULL DEFAULT 'delete_user',
  `target_id` varchar(32) NOT NULL COMMENT 'ID of user / restaurant / menu_item',
  `target_type` enum('user','restaurant','menu_item') NOT NULL DEFAULT 'user',
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` varchar(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `action` enum('login','logout') NOT NULL,
  `log_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `action`, `log_time`) VALUES
(1, '0001-0001', 'login', '2026-02-26 07:20:28'),
(2, '0001-0001', 'login', '2026-02-26 07:22:51'),
(3, '0001-0001', 'logout', '2026-02-26 07:23:08'),
(4, '0001-0001', 'login', '2026-02-26 07:24:35'),
(5, '0001-0001', 'logout', '2026-02-26 07:24:44'),
(8, '0001-0002', 'login', '2026-02-26 07:28:04'),
(9, '0001-0002', 'logout', '2026-02-26 07:28:25'),
(10, '0001-0002', 'login', '2026-02-26 07:30:56'),
(11, '0001-0002', 'login', '2026-02-26 07:35:59'),
(12, '0001-0002', 'login', '2026-02-26 07:37:43'),
(13, '0001-0001', 'login', '2026-02-26 07:38:42'),
(14, '0001-0001', 'logout', '2026-02-26 07:38:55'),
(15, '0001-0001', 'login', '2026-02-26 07:44:45'),
(16, '0001-0001', 'logout', '2026-02-26 07:45:22'),
(19, '0001-0002', 'login', '2026-02-26 07:46:18'),
(20, '0001-0002', 'logout', '2026-02-26 07:47:25'),
(21, '0001-0001', 'login', '2026-02-26 07:47:45'),
(22, '0001-0001', 'logout', '2026-02-26 07:47:51'),
(23, '0001-0002', 'login', '2026-02-26 07:48:11'),
(24, '0001-0002', 'logout', '2026-02-26 07:48:18'),
(25, '0001-0001', 'login', '2026-02-26 07:48:40'),
(26, '0001-0002', 'logout', '2026-02-26 08:01:34'),
(27, '0001-0002', 'login', '2026-02-26 08:02:05'),
(28, '0001-0001', 'logout', '2026-02-26 08:05:01'),
(29, '0001-0002', 'logout', '2026-02-26 08:05:33'),
(30, '0001-0001', 'login', '2026-02-26 08:05:36'),
(31, '0001-0001', 'login', '2026-02-26 08:06:03'),
(32, '0001-0001', 'logout', '2026-02-26 08:06:11'),
(33, '0001-0001', 'login', '2026-02-26 08:06:17'),
(34, '0001-0001', 'logout', '2026-02-26 08:06:23'),
(35, '0001-0002', 'login', '2026-02-26 08:06:43'),
(36, '0001-0002', 'logout', '2026-02-26 08:07:32'),
(39, '0001-0001', 'login', '2026-02-26 08:08:25'),
(40, '0001-0001', 'logout', '2026-02-26 08:10:36'),
(41, '0001-0002', 'logout', '2026-02-26 08:11:08'),
(44, '0001-0002', 'login', '2026-02-26 08:15:30'),
(45, '0001-0002', 'logout', '2026-02-26 08:15:47'),
(46, '0001-0001', 'login', '2026-02-26 08:16:14'),
(47, '0001-0001', 'logout', '2026-02-26 08:16:30'),
(55, '0001-0001', 'login', '2026-02-26 08:22:01'),
(56, '0001-0001', 'logout', '2026-02-26 08:22:20'),
(60, '0001-0002', 'login', '2026-02-26 08:52:17'),
(61, '0001-0002', 'logout', '2026-02-26 08:52:45'),
(64, '0001-0001', 'login', '2026-02-26 08:54:06'),
(65, '0001-0001', 'logout', '2026-02-26 08:54:51'),
(68, '0001-0002', 'login', '2026-02-26 08:56:06'),
(75, '0001-0001', 'login', '2026-02-28 21:33:19'),
(76, '0001-0001', 'logout', '2026-02-28 21:33:49'),
(77, '0001-0002', 'login', '2026-02-28 21:33:51'),
(78, '0001-0002', 'logout', '2026-02-28 21:34:46'),
(79, '0001-0002', 'login', '2026-02-28 21:34:55'),
(80, '0001-0002', 'logout', '2026-02-28 21:39:35'),
(81, '0001-0001', 'login', '2026-02-28 21:39:39'),
(82, '0001-0001', 'logout', '2026-02-28 21:39:48'),
(83, '0001-0002', 'login', '2026-02-28 21:39:51'),
(84, '0001-0002', 'logout', '2026-02-28 21:41:07'),
(85, '0001-0001', 'login', '2026-02-28 21:41:10'),
(86, '0001-0001', 'logout', '2026-02-28 21:43:10'),
(87, '0001-0001', 'login', '2026-02-28 21:43:22'),
(88, '0001-0001', 'logout', '2026-02-28 21:43:25'),
(92, '0001-0001', 'login', '2026-02-28 21:57:18'),
(94, '0001-0001', 'login', '2026-02-28 22:14:53'),
(95, '0001-0001', 'logout', '2026-02-28 22:16:01'),
(96, '0001-0002', 'login', '2026-02-28 22:16:09'),
(97, '0001-0002', 'logout', '2026-02-28 22:18:26'),
(98, '0001-0001', 'login', '2026-02-28 22:18:30'),
(99, '0001-0001', 'logout', '2026-02-28 22:19:07'),
(102, '0001-0002', 'login', '2026-02-28 22:20:17'),
(103, '0001-0002', 'logout', '2026-02-28 22:22:48'),
(104, '0001-0002', 'login', '2026-02-28 22:28:00'),
(105, '0001-0002', 'login', '2026-02-28 22:30:30'),
(106, '0001-0002', 'logout', '2026-02-28 22:30:58'),
(107, '0001-0002', 'login', '2026-02-28 22:36:40'),
(108, '0001-0002', 'logout', '2026-02-28 22:59:43'),
(109, '0001-0002', 'logout', '2026-02-28 23:09:27'),
(112, '0001-0002', 'login', '2026-02-28 23:12:25'),
(113, '0001-0002', 'logout', '2026-02-28 23:12:40'),
(114, '0001-0002', 'login', '2026-02-28 23:19:48'),
(115, '0001-0002', 'logout', '2026-02-28 23:19:59'),
(124, '0001-0002', 'login', '2026-02-28 23:33:44'),
(125, '0001-0002', 'logout', '2026-02-28 23:33:50'),
(126, '0001-0001', 'login', '2026-02-28 23:34:05'),
(127, '0001-0001', 'logout', '2026-02-28 23:34:44'),
(136, '0001-0002', 'login', '2026-02-28 23:48:38'),
(137, '0001-0002', 'logout', '2026-02-28 23:52:57'),
(140, '0001-0002', 'login', '2026-02-28 23:53:32'),
(141, '0001-0002', 'logout', '2026-02-28 23:53:50'),
(144, '0001-0002', 'login', '2026-02-28 23:54:13'),
(145, '0001-0002', 'logout', '2026-02-28 23:54:23'),
(153, '0001-0002', 'login', '2026-03-01 00:27:17'),
(154, '0001-0002', 'logout', '2026-03-01 00:32:23'),
(155, '0001-0001', 'login', '2026-03-01 00:32:27'),
(156, '0001-0001', 'logout', '2026-03-01 00:32:37'),
(157, '0001-0002', 'login', '2026-03-01 00:32:40'),
(158, '0001-0002', 'logout', '2026-03-01 00:33:41'),
(159, '0001-0001', 'login', '2026-03-01 00:33:51'),
(160, '0001-0001', 'logout', '2026-03-01 00:33:59'),
(161, '0001-0002', 'login', '2026-03-01 00:34:07'),
(162, '0001-0002', 'logout', '2026-03-01 00:35:22'),
(163, '0001-0001', 'login', '2026-03-01 00:35:26'),
(164, '0001-0001', 'logout', '2026-03-01 00:35:39'),
(165, '0001-0002', 'login', '2026-03-01 00:35:41'),
(166, '0001-0002', 'logout', '2026-03-01 00:37:09'),
(167, '0001-0001', 'login', '2026-03-01 00:37:12'),
(168, '0001-0001', 'logout', '2026-03-01 00:37:17'),
(169, '0001-0002', 'login', '2026-03-01 00:37:19'),
(170, '0001-0002', 'logout', '2026-03-01 00:37:43'),
(171, '0001-0002', 'login', '2026-03-01 00:37:49'),
(172, '0001-0002', 'logout', '2026-03-01 00:38:52'),
(173, '0001-0001', 'login', '2026-03-01 00:38:56'),
(174, '0001-0001', 'logout', '2026-03-01 00:39:45'),
(175, '0001-0002', 'login', '2026-03-01 00:39:48'),
(176, '0001-0002', 'logout', '2026-03-01 00:51:00'),
(177, '0001-0002', 'login', '2026-03-01 00:51:22'),
(178, '0001-0002', 'logout', '2026-03-01 00:51:24'),
(179, '0001-0002', 'login', '2026-03-01 00:51:37'),
(180, '0001-0002', 'logout', '2026-03-01 00:51:49'),
(181, '0001-0002', 'login', '2026-03-01 00:54:08'),
(182, '0001-0002', 'logout', '2026-03-01 00:54:10'),
(183, '0001-0001', 'login', '2026-03-01 00:54:14'),
(184, '0001-0001', 'logout', '2026-03-01 00:54:25'),
(185, '0001-0002', 'login', '2026-03-01 00:59:42'),
(186, '0001-0002', 'logout', '2026-03-01 01:00:41'),
(188, '0001-0002', 'login', '2026-03-01 01:05:52'),
(189, '0001-0002', 'logout', '2026-03-01 01:15:09'),
(190, '0001-0001', 'login', '2026-03-01 01:15:11'),
(191, '0001-0001', 'logout', '2026-03-01 01:15:17'),
(192, '0001-0002', 'login', '2026-03-01 01:15:23'),
(193, '0001-0002', 'logout', '2026-03-01 01:17:48'),
(194, '0001-0002', 'login', '2026-03-01 01:18:11'),
(195, '0001-0002', 'logout', '2026-03-01 01:18:25'),
(196, '0001-0001', 'login', '2026-03-19 10:41:57'),
(197, '0001-0001', 'logout', '2026-03-19 10:42:33'),
(198, '0001-0002', 'login', '2026-03-19 10:42:36'),
(199, '0001-0002', 'logout', '2026-03-19 10:47:15'),
(200, '0001-0002', 'login', '2026-03-19 11:33:24'),
(201, '0001-0002', 'logout', '2026-03-19 11:33:34'),
(204, '0001-0002', 'login', '2026-03-19 11:37:01'),
(205, '0001-0002', 'logout', '2026-03-19 11:38:50'),
(206, '0001-0002', 'login', '2026-03-19 11:40:40'),
(207, '0001-0002', 'logout', '2026-03-19 11:40:49'),
(208, '0001-0002', 'login', '2026-03-19 11:40:55'),
(209, '0001-0002', 'logout', '2026-03-19 11:41:13'),
(210, '0001-0002', 'login', '2026-03-19 11:41:33'),
(211, '0001-0002', 'logout', '2026-03-19 12:02:05'),
(212, '0001-0002', 'login', '2026-03-19 12:45:19'),
(213, '0001-0002', 'login', '2026-03-19 12:50:10'),
(214, '0001-0002', 'logout', '2026-03-19 12:51:13'),
(215, '0001-0002', 'login', '2026-03-19 12:51:19'),
(216, '0001-0002', 'logout', '2026-03-19 12:51:23'),
(217, '0001-0002', 'login', '2026-03-19 12:51:38'),
(218, '0001-0002', 'logout', '2026-03-19 12:52:45'),
(219, '0001-0002', 'login', '2026-03-19 12:52:48'),
(220, '0001-0002', 'logout', '2026-03-19 12:56:17'),
(221, '0001-0002', 'login', '2026-03-19 12:56:25'),
(222, '0001-0002', 'logout', '2026-03-19 12:57:18'),
(223, '0001-0002', 'login', '2026-03-19 12:57:41'),
(224, '0001-0002', 'logout', '2026-03-19 12:58:59'),
(225, '0001-0002', 'login', '2026-03-19 12:59:32'),
(226, '0001-0002', 'logout', '2026-03-19 13:00:57'),
(227, '0001-0002', 'login', '2026-03-19 13:01:56'),
(228, '0001-0002', 'logout', '2026-03-19 13:30:23'),
(229, '0001-0001', 'login', '2026-03-19 13:30:29'),
(230, '0001-0001', 'logout', '2026-03-19 13:30:47'),
(231, '0001-0002', 'login', '2026-03-19 13:31:11'),
(232, '0001-0002', 'logout', '2026-03-19 13:31:47'),
(233, '0001-0001', 'login', '2026-03-19 13:31:50'),
(234, '0001-0001', 'logout', '2026-03-19 13:32:03'),
(235, '0001-0002', 'login', '2026-03-19 13:33:44'),
(236, '0001-0002', 'logout', '2026-03-19 13:37:26'),
(237, '0001-0001', 'login', '2026-03-19 13:37:29'),
(238, '0001-0001', 'logout', '2026-03-19 13:37:37'),
(239, '0001-0002', 'login', '2026-03-19 13:37:39'),
(240, '0001-0002', 'logout', '2026-03-19 13:37:51'),
(241, '0001-0001', 'login', '2026-03-19 13:37:53'),
(242, '0001-0001', 'logout', '2026-03-19 13:38:02'),
(243, '0001-0001', 'login', '2026-03-19 13:38:05'),
(244, '0001-0001', 'logout', '2026-03-19 13:38:18'),
(245, '0001-0002', 'login', '2026-03-19 13:38:22'),
(246, '0001-0002', 'login', '2026-03-19 13:43:19'),
(247, '0001-0002', 'logout', '2026-03-19 15:58:20'),
(248, '0001-0002', 'login', '2026-03-19 15:58:24'),
(249, '0001-0002', 'login', '2026-05-08 03:09:08'),
(250, '0001-0002', 'logout', '2026-05-08 03:11:01'),
(251, '0001-0001', 'login', '2026-05-08 03:13:51'),
(252, '0001-0001', 'logout', '2026-05-08 03:14:36'),
(253, '0001-0001', 'login', '2026-05-08 03:16:16'),
(254, '0001-0001', 'logout', '2026-05-08 03:16:25'),
(255, '2022-2525', 'login', '2026-05-08 03:16:39'),
(256, '2022-2525', 'logout', '2026-05-08 03:17:23'),
(257, '2022-2525', 'login', '2026-05-08 03:21:30'),
(258, '2022-2525', 'logout', '2026-05-08 03:21:34'),
(259, '0001-0002', 'login', '2026-05-08 03:22:05'),
(260, '2022-2525', 'login', '2026-05-08 03:26:54'),
(261, '2022-2525', 'logout', '2026-05-08 03:26:57'),
(262, '0001-0002', 'logout', '2026-05-08 03:41:19'),
(263, '0001-0001', 'login', '2026-05-08 03:41:23'),
(264, '0001-0001', 'logout', '2026-05-08 03:41:33'),
(265, '0001-0002', 'login', '2026-05-08 03:41:39'),
(266, '0001-0002', 'logout', '2026-05-08 03:41:58'),
(267, '0001-0001', 'login', '2026-05-08 03:42:11'),
(268, '0001-0001', 'logout', '2026-05-08 03:43:24'),
(269, '0001-0002', 'login', '2026-05-08 03:43:29'),
(270, '0001-0002', 'logout', '2026-05-08 03:43:44'),
(271, '0001-0001', 'login', '2026-05-08 03:44:14'),
(272, '0001-0001', 'logout', '2026-05-08 03:44:30'),
(273, '0001-0002', 'login', '2026-05-08 03:44:34'),
(274, '0001-0002', 'logout', '2026-05-08 03:44:42'),
(275, '2022-2525', 'login', '2026-05-08 03:44:49'),
(276, '2022-2525', 'logout', '2026-05-08 03:45:01'),
(277, '0001-0001', 'login', '2026-05-08 03:45:20'),
(278, '0001-0001', 'logout', '2026-05-08 03:52:30'),
(279, '0001-0001', 'login', '2026-05-08 03:53:30'),
(280, '0001-0001', 'logout', '2026-05-08 04:04:02'),
(281, '0001-0001', 'login', '2026-05-08 04:06:07'),
(282, '0001-0001', 'logout', '2026-05-08 04:07:49'),
(283, '0001-0002', 'login', '2026-05-08 04:07:55'),
(284, '0001-0002', 'logout', '2026-05-08 04:08:21'),
(285, '0001-0001', 'login', '2026-05-08 04:08:34'),
(288, '0001-0002', 'login', '2026-05-08 04:10:57'),
(290, '0001-0002', 'login', '2026-05-08 09:35:19'),
(291, '0001-0002', 'login', '2026-05-08 09:36:57'),
(292, '0001-0002', 'login', '2026-05-13 07:02:26'),
(293, '0001-0002', 'logout', '2026-05-13 07:16:17'),
(294, '0001-0002', 'login', '2026-05-13 07:27:51'),
(295, '0001-0002', 'logout', '2026-05-13 07:27:54'),
(296, '0001-0002', 'login', '2026-05-13 07:28:16'),
(297, '0001-0002', 'logout', '2026-05-13 07:32:32'),
(298, '2022-2525', 'login', '2026-05-13 07:34:33'),
(299, '2022-2525', 'logout', '2026-05-13 07:34:49'),
(300, '0001-0002', 'login', '2026-05-13 07:48:15'),
(301, '0001-0002', 'logout', '2026-05-13 07:49:21'),
(307, '0001-0001', 'login', '2026-05-15 09:24:42'),
(308, '0001-0001', 'logout', '2026-05-15 09:26:42'),
(309, '0001-0001', 'login', '2026-05-15 09:26:46'),
(310, '0001-0001', 'logout', '2026-05-15 09:26:51'),
(311, '0001-0001', 'login', '2026-05-15 09:26:58'),
(312, '0001-0001', 'logout', '2026-05-15 09:27:00'),
(313, '0001-0002', 'login', '2026-05-15 09:27:06'),
(314, '0001-0002', 'logout', '2026-05-15 09:27:11'),
(315, '0001-0001', 'login', '2026-05-15 09:31:48'),
(316, '0001-0001', 'logout', '2026-05-15 09:31:52'),
(317, '0001-0002', 'login', '2026-05-15 09:31:57'),
(318, '0001-0002', 'logout', '2026-05-15 09:36:20'),
(321, '0001-0002', 'login', '2026-05-15 09:42:56'),
(322, '0001-0002', 'logout', '2026-05-15 09:44:04'),
(323, '0001-0002', 'login', '2026-05-15 16:03:57'),
(324, '0001-0002', 'logout', '2026-05-15 16:05:01'),
(325, '0001-0001', 'login', '2026-05-17 10:47:56'),
(326, '0001-0001', 'logout', '2026-05-17 10:47:59'),
(327, '0001-0001', 'login', '2026-05-17 10:48:03'),
(328, '0001-0001', 'logout', '2026-05-17 10:48:06'),
(329, '0001-0002', 'login', '2026-05-17 10:48:10'),
(330, '0001-0002', 'logout', '2026-05-17 10:48:44'),
(333, '0001-0002', 'login', '2026-05-17 10:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `restaurant_id`, `name`, `description`, `price`, `image_path`, `is_available`, `created_at`) VALUES
(1, 1, 'Pepperoni Delight', 'Classic pepperoni with mozzarella and our signature tomato sauce.', 299.00, NULL, 1, '2026-02-25 21:57:49'),
(2, 1, 'Hawaiian Overload', 'Sweet pineapples, sliced ham, and double cheese.', 320.00, NULL, 1, '2026-02-25 21:57:49'),
(3, 1, 'Meat Lovers Extreme', 'Packed with pepperoni, sausage, beef, and bacon.', 399.00, NULL, 1, '2026-02-25 21:57:49'),
(4, 1, 'Veggie Supreme', 'Fresh bell peppers, onions, mushrooms, and black olives.', 280.00, NULL, 1, '2026-02-25 21:57:49'),
(5, 1, 'Four Cheese Bliss', 'A rich blend of mozzarella, cheddar, parmesan, and cream cheese.', 350.00, NULL, 1, '2026-02-25 21:57:49'),
(6, 1, 'Garlic Bread', 'Oven-toasted garlic bread.', 65.00, NULL, 1, '2026-02-25 21:57:49'),
(7, 1, 'Cheese Sticks', 'Mozzarella sticks with marinara dip.', 99.00, NULL, 1, '2026-02-25 21:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otp`
--

CREATE TABLE `password_reset_otp` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_otp`
--

INSERT INTO `password_reset_otp` (`id`, `user_id`, `otp_code`, `expires_at`, `used`, `created_at`) VALUES
(3, '0001-0001', '762654', '2026-02-25 23:35:01', 1, '2026-02-25 22:20:01'),
(4, '0001-0001', '622929', '2026-02-28 18:33:48', 1, '2026-02-28 17:18:48'),
(5, '0001-0001', '448176', '2026-03-19 04:02:50', 1, '2026-03-19 02:47:50'),
(6, '0001-0001', '570896', '2026-03-19 04:45:03', 1, '2026-03-19 03:30:03'),
(9, '0001-0001', '487592', '2026-03-19 05:18:15', 1, '2026-03-19 04:03:15'),
(18, '2022-2525', '019522', '2026-05-15 03:45:56', 0, '2026-05-15 01:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `type` enum('cash_on_delivery','gcash','card','bank') NOT NULL DEFAULT 'cash_on_delivery',
  `label` varchar(50) NOT NULL COMMENT 'e.g. GCash 09xx',
  `details` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `description`, `address`, `image_path`, `is_active`, `created_at`) VALUES
(1, 'Pizza Crust Delight', 'The finest homemade style pizza perfectly baked for your cravings.', 'National Highway, Pizza Street', '', 1, '2026-01-01 08:00:00'),
(2, 'Pizza Crust Delight Backup', 'Alternative store backup.', 'Downtown Pizza Rd', NULL, 0, '2026-02-25 21:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `middleInitial` varchar(1) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `sex` enum('male','female') NOT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) NOT NULL,
  `purok` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `zipCode` varchar(10) NOT NULL,
  `country` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secure_question` varchar(100) DEFAULT NULL,
  `secure_answer` varchar(255) DEFAULT NULL,
  `secure_question2` varchar(100) DEFAULT NULL,
  `secure_answer2` varchar(255) DEFAULT NULL,
  `secure_question3` varchar(100) DEFAULT NULL,
  `secure_answer3` varchar(255) DEFAULT NULL,
  `role` enum('consumer','admin','superadmin') NOT NULL DEFAULT 'consumer',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `middleInitial`, `extension`, `sex`, `birthdate`, `age`, `purok`, `barangay`, `city`, `province`, `zipCode`, `country`, `username`, `email`, `password`, `secure_question`, `secure_answer`, `secure_question2`, `secure_answer2`, `secure_question3`, `secure_answer3`, `role`, `status`, `is_blocked`) VALUES
('0001-0001', 'Jisa', 'Cedula', '', '', 'male', '1992-01-15', 34, 'Purok 1', 'Barangay 1', 'Cabadbaran City', 'Agusan Del Norte', '8605', 'Philippines', 'jisa21', 'jisa@gmail.com', '$2y$10$2NCj/09LLHXFgtLxPG/5Z.MzCf4v6vQ3wavL4Rxg.06ftHxIw8d56', '1. Who is your bestfriend in elementary? *', '\\.AHb4zjwAn0WCwOXCvgWg/CVza1riJ7DgNA03JbLC8vWjZ0Jdt.6', '2. What is the name of your pet? *', '\\.AHb4zjwAn0WCwOXCvgWg/CVza1riJ7DgNA03JbLC8vWjZ0Jdt.6', '3. Who is your favorite teacher in highschool? *', '\\.AHb4zjwAn0WCwOXCvgWg/CVza1riJ7DgNA03JbLC8vWjZ0Jdt.6', 'admin', 'approved', 0),
('0001-0002', 'Bea', 'Cedula', '', '', 'male', '1996-06-20', 29, 'Purok 2', 'Barangay 2', 'Cabadbaran City', 'Agusan Del Norte', '8605', 'Philippines', 'bea21', 'bea@gmail.com', '$2y$10$2NCj/09LLHXFgtLxPG/5Z.MzCf4v6vQ3wavL4Rxg.06ftHxIw8d56', 'What is the name of your pet?', '$2y$10$aqEQyKKcyrCDOr9VrS9DM.0fIwJfsFqenzncx0DMkTk/DxjG7XJWq', 'What elementary school did you attend?', '$2y$10$dnIv8uKPz2Rh0kHa0eOu3.mplfmdSjn8X.6I2mfvtyfrN7HdPVLzO', 'What street did you grow up on?', '$2y$10$S3/y7Hybh7atd9//inIFIOiZ9u/Cd7zO7a0kZDoiDcd8HQ6j7bJBq', 'superadmin', 'approved', 0),
('2022-2525', 'Jed', 'Mars', '', '', 'male', '2003-02-25', 23, 'Purok 5', 'Baranggay 6', 'City of Cabadbaran', 'Agusan Del Norte', '8605', 'Philippines', 'jedmars', 'jed@gmail.com', '$2y$10$SZY3SK29C6GGnYiihyjYeeHbuc8Z3SJLETUMiuWADLHCA.BGwVe0u', 'What is the name of your pet?', '$2y$10$UA3Zmg1AyNJBAogYVHAuW.T4kKj0XObdlZnA3oMc5vCFWKhkd19OO', 'What is your mother\'s maiden name?', '$2y$10$Yjk4gRrGwyt9m4AH4jqBZeIyaTAF1jj817y28FRJmLSq3LKbEkm5G', 'What is your favorite movie?', '$2y$10$E.nyBMgzrLld5eten3nncu9OcxHTvMOEUBCyTYmItgKGES/J/Wj2e', 'consumer', 'approved', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_block_requests`
--

CREATE TABLE `user_block_requests` (
  `id` int(11) NOT NULL,
  `requester_id` varchar(11) NOT NULL,
  `target_id` varchar(11) NOT NULL,
  `request_type` enum('block','unblock','registration') DEFAULT 'block',
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_block_requests`
--

INSERT INTO `user_block_requests` (`id`, `requester_id`, `target_id`, `request_type`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(10, '2022-2525', '2022-2525', 'registration', 'New User Registration', 'approved', '2026-05-08 03:12:58', '2026-05-08 03:16:21'),
(11, '0001-0001', '2022-2525', 'block', 'needs blocking', 'approved', '2026-05-08 03:42:25', '2026-05-08 03:43:42'),
(12, '0001-0001', '2022-2525', 'unblock', 'needs blocking', 'approved', '2026-05-08 03:44:28', '2026-05-08 03:44:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_creation_requests`
--
ALTER TABLE `admin_creation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `status` (`status`),
  ADD KEY `admin_creation_reviewed_by_fk` (`reviewed_by`);

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `approvals_reviewed_by_fk` (`reviewed_by`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cart_user` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cart_item_unique` (`cart_id`,`menu_item_id`),
  ADD KEY `fk_cartitem_menu` (`menu_item_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_logs_user` (`user_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_menu_restaurant` (`restaurant_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `fk_orders_restaurant` (`restaurant_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orderitems_order` (`order_id`),
  ADD KEY `fk_orderitems_menu` (`menu_item_id`);

--
-- Indexes for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_user` (`user_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_block_requests`
--
ALTER TABLE `user_block_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_block_requester` (`requester_id`),
  ADD KEY `fk_block_target` (`target_id`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_menu_unique` (`user_id`,`menu_item_id`),
  ADD KEY `fk_fav_user` (`user_id`),
  ADD KEY `fk_fav_menu` (`menu_item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_creation_requests`
--
ALTER TABLE `admin_creation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=334;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_block_requests`
--
ALTER TABLE `user_block_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_creation_requests`
--
ALTER TABLE `admin_creation_requests`
  ADD CONSTRAINT `admin_creation_requested_by_fk` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `admin_creation_reviewed_by_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_requested_by_fk` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvals_reviewed_by_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cartitem_cart` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cartitem_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `fk_menu_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitems_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD CONSTRAINT `password_reset_otp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_block_requests`
--
ALTER TABLE `user_block_requests`
  ADD CONSTRAINT `fk_block_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_block_target` FOREIGN KEY (`target_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `fk_fav_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
