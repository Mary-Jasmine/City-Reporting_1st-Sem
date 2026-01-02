-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 17, 2025 at 06:06 AM
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
-- Database: `updatcollab`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_resources`
--

CREATE TABLE `admin_resources` (
  `id` int(11) NOT NULL,
  `type` enum('guide','video') NOT NULL DEFAULT 'guide',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `source` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_resources`
--

INSERT INTO `admin_resources` (`id`, `type`, `title`, `description`, `category`, `thumbnail_path`, `content`, `source`, `created_at`, `updated_at`) VALUES
(1, 'guide', 'Basic CPR Steps for Adults', 'Learn the essential steps for performing cardiopulmonary resuscitation (CPR) on adults', 'Basic First Aid', NULL, NULL, 'uploads/resources/1765096900_177faeddd53b.pdf', '2025-12-06 18:50:56', '2025-12-07 08:41:40'),
(2, 'guide', 'Treating Minor Burns and Scalds', 'A quick guide on how to effectively treat minor degree burns and scalds', 'General', NULL, NULL, 'uploads/resources/1765048749_6b8f23bc6360.pdf', '2025-12-06 18:50:56', '2025-12-06 19:19:09'),
(3, 'video', 'Disaster Preparedness: Earthquake', 'Learn vital steps to prepare for, survive, and recover from an earthquake', 'Disaster Prep', NULL, NULL, 'https://www.youtube.com/watch?v=example', '2025-12-06 18:50:56', '2025-12-06 18:50:56'),
(4, 'guide', 'd m,s', 'c c,', 'Basic First Aid', NULL, NULL, 'uploads/resources/1765096912_e41e0a65ac1b.png', '2025-12-06 21:17:52', '2025-12-07 08:41:52');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcements_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `type` enum('announcement','alert','emergency','maintenance') DEFAULT 'announcement',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `author_id` int(11) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcements_id`, `title`, `content`, `cover_image`, `type`, `priority`, `status`, `author_id`, `published_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Barangay Clean-up Drive', 'Join our clean-up event this Saturday...', 'uploads/announcements/announcement_1765857959_6940daa7100f5.jpg', 'announcement', 'medium', 'published', NULL, '2025-11-14 10:16:38', NULL, '2025-11-14 10:16:38', '2025-12-16 04:05:59'),
(2, 'Vaccination Drive', 'Free flu vaccination available for senior citizens...', 'uploads/announcements/announcement_1765867407_6940ff8f14308.png', 'announcement', 'medium', 'published', NULL, '2025-11-14 10:16:38', NULL, '2025-11-14 10:16:38', '2025-12-16 06:43:27'),
(3, 'Typhoon Warning', 'Expected landfall tomorrow morning...', 'uploads/announcements/announcement_1765867369_6940ff693a0cc.jpg', 'emergency', 'urgent', 'published', NULL, '2025-11-14 10:16:38', NULL, '2025-11-14 10:16:38', '2025-12-16 06:42:49'),
(4, 'Water Interruption Notice', 'Scheduled maintenance in District 3...', 'uploads/announcements/announcement_1765867386_6940ff7a1922b.jpg', 'maintenance', 'high', 'published', NULL, '2025-11-14 10:16:38', NULL, '2025-11-14 10:16:38', '2025-12-16 06:43:06'),
(5, 'Community Assembly Meeting', 'A general assembly will be held this Sunday to discuss upcoming barangay programs.', 'uploads/announcements/announcement_1765854413_6940cccd5710b.png', 'announcement', 'medium', 'published', NULL, '2025-11-15 00:00:00', NULL, '2025-11-15 00:00:00', '2025-12-16 03:06:53'),
(6, 'Streetlight Repair Schedule', 'Maintenance team will repair defective streetlights along Zone 2 this week.', 'uploads/announcements/announcement_1765854251_6940cc2b7a833.png', 'maintenance', 'low', 'published', NULL, '2025-11-15 00:10:00', NULL, '2025-11-15 00:10:00', '2025-12-16 03:04:11'),
(7, 'Storm Surge Advisory', 'Residents in coastal areas are advised to prepare for possible storm surges due to incoming weather disturbance.', 'uploads/announcements/announcement_1765867428_6940ffa4d0dfd.jpg', 'emergency', 'urgent', 'published', NULL, '2025-11-15 00:20:00', NULL, '2025-11-15 00:20:00', '2025-12-16 06:43:48'),
(8, 'Tree Planting Activity', 'Volunteers needed for the barangay tree planting event this weekend.', 'uploads/announcements/announcement_1765190460_6936ab3c9c7bb.png', 'announcement', 'medium', 'published', NULL, '2025-11-15 00:30:00', NULL, '2025-11-15 00:30:00', '2025-12-08 10:41:00'),
(9, 'Fire Safety Seminar', 'Barangay fire department will conduct a fire safety orientation for households.', 'uploads/announcements/announcement_1765190069_6936a9b55ab0e.png', 'alert', 'high', 'published', NULL, '2025-11-15 00:40:00', NULL, '2025-11-15 00:40:00', '2025-12-08 10:34:29'),
(10, 'Road Closure Notice', 'Portion of Main Street will be temporarily closed due to drainage improvements.', 'uploads/announcements/announcement_1765190241_6936aa6159ffc.png', 'maintenance', 'high', 'published', NULL, '2025-11-15 00:50:00', NULL, '2025-11-15 00:50:00', '2025-12-08 10:37:21'),
(11, 'Barangay Sports Festival', 'Registration for the annual sports festival is now open for all residents.', 'uploads/announcements/announcement_1765187050_69369dea60e36.jpg', 'announcement', 'low', 'published', NULL, '2025-11-15 01:00:00', NULL, '2025-11-15 01:00:00', '2025-12-08 09:44:10'),
(12, 'Water Tank Cleaning', 'Water service may be temporarily interrupted during tank cleaning operations.', 'uploads/announcements/announcement_1765190155_6936aa0b5e57c.png', 'maintenance', 'medium', 'published', NULL, '2025-11-15 01:10:00', NULL, '2025-11-15 01:10:00', '2025-12-08 10:35:55'),
(13, 'Flood Warning', 'Low-lying areas expected to experience moderate flooding due to heavy rainfall.', 'uploads/announcements/announcement_1765854913_6940cec1d9485.png', 'emergency', 'urgent', 'published', NULL, '2025-11-15 01:20:00', NULL, '2025-11-15 01:20:00', '2025-12-16 03:15:13'),
(14, 'Garbage Collection', 'collection\r\n', 'uploads/announcements/announcement_1765188681_6936a44944fff.jpg', 'announcement', 'high', 'draft', 21, '2025-12-06 21:44:40', '2025-12-16 12:00:00', '2025-12-06 21:44:40', '2025-12-08 10:11:21'),
(15, 'Clean-up Drive', 'Santa Anna cleaning', 'uploads/announcements/announcement_1765189319_6936a6c7d0571.jpg', 'emergency', 'urgent', 'archived', 21, '2025-12-06 22:17:11', '2025-12-16 12:00:00', '2025-12-06 22:17:11', '2025-12-09 06:44:42'),
(16, 'Garbage Collection', 'collection', 'uploads/announcements/announcement_1765262746_6937c59a4ce39.png', 'announcement', 'low', 'draft', NULL, '2025-12-09 06:45:46', '2025-12-09 06:45:00', '2025-12-09 06:45:46', '2025-12-17 01:58:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `archived_items_summary`
-- (See below for the actual view)
--
CREATE TABLE `archived_items_summary` (
`item_type` varchar(13)
,`count` bigint(21)
,`last_archived` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `barangay_stats`
--

CREATE TABLE `barangay_stats` (
  `barangay_id` int(11) NOT NULL,
  `barangay_name` varchar(100) NOT NULL,
  `incident_count` int(11) DEFAULT 0,
  `resolved_count` int(11) DEFAULT 0,
  `pending_count` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_stats`
--

INSERT INTO `barangay_stats` (`barangay_id`, `barangay_name`, `incident_count`, `resolved_count`, `pending_count`, `last_updated`) VALUES
(1, 'Atisan', 9, 9, 0, '2025-11-29 02:52:49'),
(2, 'Bagong Bayan II-A', 2, 1, 1, '2025-11-29 02:52:49'),
(3, 'Bagong Pook VI-C', 0, 0, 0, '2025-11-29 02:52:49'),
(4, 'Barangay I-A', 5, 1, 4, '2025-11-29 02:52:49'),
(5, 'Barangay I-B', 8, 0, 8, '2025-11-29 02:52:49'),
(6, 'Barangay II-A', 8, 8, 0, '2025-11-29 02:52:49'),
(7, 'Barangay II-B', 3, 3, 0, '2025-11-29 02:52:49'),
(8, 'Barangay II-C', 4, 1, 3, '2025-11-29 02:52:49'),
(9, 'Barangay II-D', 1, 1, 0, '2025-11-29 02:52:49'),
(10, 'Barangay II-E', 4, 0, 4, '2025-11-29 02:52:49'),
(11, 'Barangay II-F', 6, 3, 3, '2025-11-29 02:52:49'),
(12, 'Barangay III-A', 7, 6, 1, '2025-11-29 02:52:49'),
(13, 'Barangay III-B', 10, 4, 6, '2025-11-29 02:52:49'),
(14, 'Barangay III-C', 0, 0, 0, '2025-11-29 02:52:49'),
(15, 'Barangay III-D', 0, 0, 0, '2025-11-29 02:52:49'),
(16, 'Barangay III-E', 0, 0, 0, '2025-11-29 02:52:49'),
(17, 'Barangay III-F', 5, 5, 0, '2025-11-29 02:52:49'),
(18, 'Barangay IV-A', 6, 4, 2, '2025-11-29 02:52:49'),
(19, 'Barangay IV-B', 3, 2, 1, '2025-11-29 02:52:49'),
(20, 'Barangay IV-C', 0, 0, 0, '2025-11-29 02:52:49'),
(21, 'Barangay V-A', 6, 6, 0, '2025-11-29 02:52:49'),
(22, 'Barangay V-B', 0, 0, 0, '2025-11-29 02:52:49'),
(23, 'Barangay V-C', 4, 1, 3, '2025-11-29 02:52:49'),
(24, 'Barangay V-D', 4, 0, 4, '2025-11-29 02:52:49'),
(25, 'Barangay VI-A', 2, 0, 2, '2025-11-29 02:52:49'),
(26, 'Barangay VI-B', 4, 4, 0, '2025-11-29 02:52:49'),
(27, 'Barangay VI-D', 1, 0, 1, '2025-11-29 02:52:49'),
(28, 'Barangay VI-E', 10, 5, 5, '2025-11-29 02:52:49'),
(29, 'Barangay VII-A', 7, 0, 7, '2025-11-29 02:52:49'),
(30, 'Barangay VII-B', 1, 1, 0, '2025-11-29 02:52:49'),
(31, 'Barangay VII-C', 9, 3, 6, '2025-11-29 02:52:49'),
(32, 'Barangay VII-D', 1, 1, 0, '2025-11-29 02:52:49'),
(33, 'Barangay VII-E', 7, 4, 3, '2025-11-29 02:52:49'),
(34, 'Bautista', 7, 5, 2, '2025-11-29 02:52:49'),
(35, 'Concepcion', 3, 2, 1, '2025-11-29 02:52:49'),
(36, 'Del Remedio', 2, 0, 2, '2025-11-29 02:52:49'),
(37, 'Dolores', 5, 5, 0, '2025-11-29 02:52:49'),
(38, 'San Antonio 1', 7, 0, 7, '2025-11-29 02:52:49'),
(39, 'San Antonio 2', 10, 6, 4, '2025-11-29 02:52:49'),
(40, 'San Bartolome', 3, 2, 1, '2025-11-29 02:52:49'),
(41, 'San Buenaventura', 5, 3, 2, '2025-11-29 02:52:49'),
(42, 'San Crispin', 5, 3, 2, '2025-11-29 02:52:49'),
(43, 'San Cristobal', 8, 6, 2, '2025-11-29 02:52:49'),
(44, 'San Diego', 5, 2, 3, '2025-11-29 02:52:49'),
(45, 'San Francisco', 4, 4, 0, '2025-11-29 02:52:49'),
(46, 'San Gabriel', 0, 0, 0, '2025-11-29 02:52:49'),
(47, 'San Gregorio', 4, 4, 0, '2025-11-29 02:52:49'),
(48, 'San Ignacio', 2, 1, 1, '2025-11-29 02:52:49'),
(49, 'San Isidro', 3, 3, 0, '2025-11-29 02:52:49'),
(50, 'San Joaquin', 3, 0, 3, '2025-11-29 02:52:49'),
(51, 'San Jose', 1, 1, 0, '2025-11-29 02:52:49'),
(52, 'San Juan', 6, 6, 0, '2025-11-29 02:52:49'),
(53, 'San Lorenzo', 3, 2, 1, '2025-11-29 02:52:49'),
(54, 'San Lucas 1', 2, 0, 2, '2025-11-29 02:52:49'),
(55, 'San Lucas 2', 0, 0, 0, '2025-11-29 02:52:49'),
(56, 'San Marcos', 5, 3, 2, '2025-11-29 02:52:49'),
(57, 'San Mateo', 0, 0, 0, '2025-11-29 02:52:49'),
(58, 'San Miguel', 6, 4, 2, '2025-11-29 02:52:49'),
(59, 'San Nicolas', 1, 0, 1, '2025-11-29 02:52:49'),
(60, 'San Pedro', 3, 3, 0, '2025-11-29 02:52:49'),
(61, 'San Rafael', 6, 1, 5, '2025-11-29 02:52:49'),
(62, 'San Roque', 4, 1, 3, '2025-11-29 02:52:49'),
(63, 'San Vicente', 3, 3, 0, '2025-11-29 02:52:49'),
(64, 'Santa Ana', 10, 6, 4, '2025-11-29 02:52:49'),
(65, 'Santa Catalina', 8, 3, 5, '2025-11-29 02:52:49'),
(66, 'Santa Cruz', 4, 0, 4, '2025-11-29 02:52:49'),
(67, 'Santa Elena', 3, 0, 3, '2025-11-29 02:52:49'),
(68, 'Santa Filomena', 7, 1, 6, '2025-11-29 02:52:49'),
(69, 'Santa Isabel', 0, 0, 0, '2025-11-29 02:52:49'),
(70, 'Santa Maria', 7, 5, 2, '2025-11-29 02:52:49'),
(71, 'Santa Maria Magdalena', 3, 2, 1, '2025-11-29 02:52:49'),
(72, 'Santa Monica', 3, 2, 1, '2025-11-29 02:52:49'),
(73, 'Santa Veronica', 5, 1, 4, '2025-11-29 02:52:49'),
(74, 'Santiago I', 0, 0, 0, '2025-11-29 02:52:49'),
(75, 'Santiago II', 10, 3, 7, '2025-11-29 02:52:49'),
(76, 'Santisimo Rosario', 6, 1, 5, '2025-11-29 02:52:49'),
(77, 'Santo Angel', 1, 1, 0, '2025-11-29 02:52:49'),
(78, 'Santo Cristo', 2, 1, 1, '2025-11-29 02:52:49'),
(79, 'Santo Niño', 10, 3, 7, '2025-11-29 02:52:49'),
(80, 'Soledad', 6, 0, 6, '2025-11-29 02:52:49');

-- --------------------------------------------------------

--
-- Table structure for table `cache_entries`
--

CREATE TABLE `cache_entries` (
  `cache_id` int(11) NOT NULL,
  `cache_key` varchar(255) NOT NULL,
  `cache_value` longtext DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `danger_zones`
--

CREATE TABLE `danger_zones` (
  `danger_zones_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `issue` varchar(255) NOT NULL,
  `incident_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `danger_zones`
--

INSERT INTO `danger_zones` (`danger_zones_id`, `barangay_id`, `issue`, `incident_id`, `created_at`) VALUES
(1, 1, 'Multiple severe incidents', 9, '2025-11-14 02:11:41'),
(2, 2, 'Recurring incidents', 2, '2025-11-14 02:11:41'),
(3, 3, 'Multiple severe incidents', 0, '2025-11-14 02:11:41'),
(4, 4, 'Recurring incidents', 5, '2025-11-14 02:11:41'),
(5, 5, 'Occasional incidents', 8, '2025-11-14 02:11:41'),
(6, 6, 'Occasional incidents', 8, '2025-11-14 02:11:41'),
(7, 7, 'Recurring incidents', 3, '2025-11-14 02:11:41'),
(8, 8, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(9, 9, 'Recurring incidents', 1, '2025-11-16 20:00:00'),
(10, 10, 'Occasional incidents', 4, '2025-11-16 20:00:00'),
(11, 11, 'Multiple severe incidents', 6, '2025-11-16 20:00:00'),
(12, 12, 'Occasional incidents', 7, '2025-11-16 20:00:00'),
(13, 13, 'Recurring incidents', 10, '2025-11-16 20:00:00'),
(14, 14, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(15, 15, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(16, 16, 'Occasional incidents', 0, '2025-11-16 20:00:00'),
(17, 17, 'Recurring incidents', 5, '2025-11-16 20:00:00'),
(18, 18, 'Multiple severe incidents', 6, '2025-11-16 20:00:00'),
(19, 19, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(20, 20, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(21, 21, 'Occasional incidents', 6, '2025-11-16 20:00:00'),
(22, 22, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(23, 23, 'Occasional incidents', 4, '2025-11-16 20:00:00'),
(24, 24, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(25, 25, 'Multiple severe incidents', 2, '2025-11-16 20:00:00'),
(26, 26, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(27, 27, 'Recurring incidents', 1, '2025-11-16 20:00:00'),
(28, 28, 'Occasional incidents', 10, '2025-11-16 20:00:00'),
(29, 29, 'Recurring incidents', 7, '2025-11-16 20:00:00'),
(30, 30, 'Occasional incidents', 1, '2025-11-16 20:00:00'),
(31, 31, 'Recurring incidents', 9, '2025-11-16 20:00:00'),
(32, 32, 'Multiple severe incidents', 1, '2025-11-16 20:00:00'),
(33, 33, 'Recurring incidents', 7, '2025-11-16 20:00:00'),
(34, 34, 'Recurring incidents', 7, '2025-11-16 20:00:00'),
(35, 35, 'Multiple severe incidents', 3, '2025-11-16 20:00:00'),
(36, 36, 'Multiple severe incidents', 2, '2025-11-16 20:00:00'),
(37, 37, 'Recurring incidents', 5, '2025-11-16 20:00:00'),
(38, 38, 'Multiple severe incidents', 7, '2025-11-16 20:00:00'),
(39, 39, 'Recurring incidents', 10, '2025-11-16 20:00:00'),
(40, 40, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(41, 41, 'Occasional incidents', 5, '2025-11-16 20:00:00'),
(42, 42, 'Occasional incidents', 5, '2025-11-16 20:00:00'),
(43, 43, 'Recurring incidents', 8, '2025-11-16 20:00:00'),
(44, 44, 'Occasional incidents', 5, '2025-11-16 20:00:00'),
(45, 45, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(46, 46, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(47, 47, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(48, 48, 'Occasional incidents', 2, '2025-11-16 20:00:00'),
(49, 49, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(50, 50, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(51, 51, 'Multiple severe incidents', 1, '2025-11-14 02:11:41'),
(52, 52, 'Recurring incidents', 6, '2025-11-16 20:00:00'),
(53, 53, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(54, 54, 'Occasional incidents', 2, '2025-11-16 20:00:00'),
(55, 55, 'Occasional incidents', 0, '2025-11-16 20:00:00'),
(56, 56, 'Recurring incidents', 5, '2025-11-16 20:00:00'),
(57, 57, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(58, 58, 'Multiple severe incidents', 6, '2025-11-16 20:00:00'),
(59, 59, 'Occasional incidents', 1, '2025-11-16 20:00:00'),
(60, 60, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(61, 61, 'Occasional incidents', 6, '2025-11-16 20:00:00'),
(62, 62, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(63, 63, 'Occasional incidents', 3, '2025-11-16 20:00:00'),
(64, 64, 'Recurring incidents', 10, '2025-11-16 20:00:00'),
(65, 65, 'Recurring incidents', 8, '2025-11-16 20:00:00'),
(66, 66, 'Recurring incidents', 4, '2025-11-16 20:00:00'),
(67, 67, 'Occasional incidents', 3, '2025-11-16 20:00:00'),
(68, 68, 'Occasional incidents', 7, '2025-11-16 20:00:00'),
(69, 69, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(70, 70, 'Occasional incidents', 7, '2025-11-16 20:00:00'),
(71, 71, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(72, 72, 'Recurring incidents', 3, '2025-11-16 20:00:00'),
(73, 73, 'Recurring incidents', 5, '2025-11-16 20:00:00'),
(74, 74, 'Recurring incidents', 0, '2025-11-16 20:00:00'),
(75, 75, 'Occasional incidents', 10, '2025-11-16 20:00:00'),
(76, 76, 'Recurring incidents', 6, '2025-11-16 20:00:00'),
(77, 77, 'Recurring incidents', 1, '2025-11-16 20:00:00'),
(78, 78, 'Occasional incidents', 2, '2025-11-16 20:00:00'),
(79, 79, 'Recurring incidents', 10, '2025-11-16 20:00:00'),
(80, 80, 'Occasional incidents', 6, '2025-11-16 20:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Optional: Link to registered users',
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `category` enum('general','bug_report','feature_request','service_quality','complaint','praise','suggestion','other') NOT NULL DEFAULT 'general',
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `rating` int(1) DEFAULT 0 COMMENT 'Rating from 0-5 stars',
  `status` enum('pending','reviewed','resolved','archived') DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium' COMMENT 'Admin assigned priority',
  `admin_response` text DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL COMMENT 'Admin user who responded',
  `responded_at` datetime DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores user feedback and suggestions about the municipal system';

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `phone`, `category`, `subject`, `message`, `rating`, `status`, `priority`, `admin_response`, `responded_by`, `responded_at`, `submitted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mary Jasmine Manalo', 'blancamaja825@gmail.com', '09773071679', 'praise', 'Excellent Response Time', 'The emergency response team arrived within 10 minutes of my incident report. Very impressed with the quick action!', 5, 'reviewed', 'low', NULL, NULL, NULL, '2025-12-07 06:40:46', '2025-12-07 06:40:46', NULL),
(2, 2, 'Jomaica Ecal', 'natashasobelino@gmail.com', '09055171589', 'feature_request', 'Add Mobile App', 'It would be great to have a dedicated mobile app for easier incident reporting on the go.', 4, 'pending', 'medium', NULL, NULL, NULL, '2025-12-08 06:40:46', '2025-12-08 06:40:46', NULL),
(3, NULL, 'Anonymous User', 'concerned.citizen@gmail.com', NULL, 'bug_report', 'Map Not Loading Properly', 'The incident map on the homepage sometimes fails to load. This happens especially on slower internet connections.', 3, 'pending', 'high', NULL, NULL, NULL, '2025-12-09 03:40:46', '2025-12-09 03:40:46', NULL),
(4, 3, 'Diana Gucela', 'dianagucela@gmail.com', '09055171557', 'complaint', 'Delayed Response to Road Hazard', 'I reported a dangerous pothole 5 days ago but have not received any update. This is affecting many residents.', 2, 'reviewed', 'urgent', NULL, NULL, NULL, '2025-12-04 06:40:46', '2025-12-04 06:40:46', NULL),
(5, NULL, 'Pedro Santos', 'pedro.santos@yahoo.com', '09123456789', 'suggestion', 'Improve Notification System', 'Would be helpful to receive SMS or email notifications when our reported incidents are updated.', 4, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 00:40:46', '2025-12-09 00:40:46', NULL),
(6, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 07:34:09', '2025-12-09 07:34:09', NULL),
(7, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 07:36:20', '2025-12-09 07:36:20', NULL),
(8, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 08:02:11', '2025-12-09 08:02:11', NULL),
(9, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 08:02:21', '2025-12-09 08:02:21', NULL),
(10, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 08:04:46', '2025-12-09 08:04:46', NULL),
(11, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 08:05:38', '2025-12-09 08:05:38', NULL),
(12, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 08:11:28', '2025-12-09 08:11:28', NULL),
(13, NULL, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '+639773071679', 'general', 'Feedback', 'i want easy location access', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-09 08:11:38', '2025-12-09 08:11:38', NULL),
(14, NULL, 'Brgy. Dolores', 'brgy.dolores@gmail.com', '76578800', 'general', 'font size', 'font size font size font size font size font size', 3, 'pending', 'medium', NULL, NULL, NULL, '2025-12-17 12:20:27', '2025-12-17 12:20:27', NULL);

--
-- Triggers `feedback`
--
DELIMITER $$
CREATE TRIGGER `log_feedback_response` AFTER UPDATE ON `feedback` FOR EACH ROW BEGIN
    IF NEW.admin_response IS NOT NULL AND OLD.admin_response IS NULL THEN
        INSERT INTO system_logs (
            user_id, 
            action, 
            details, 
            ip_address, 
            created_at
        )
        VALUES (
            NEW.responded_by,
            'Feedback Responded',
            CONCAT(
                'Responded to Feedback ID: ', NEW.feedback_id,
                ' | Status changed to: ', NEW.status,
                ' | User: ', NEW.name
            ),
            NULL,
            NOW()
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_feedback_submission` AFTER INSERT ON `feedback` FOR EACH ROW BEGIN
    INSERT INTO system_logs (
        user_id, 
        action, 
        details, 
        ip_address, 
        created_at
    )
    VALUES (
        NEW.user_id,
        'Feedback Submitted',
        CONCAT(
            'Feedback ID: ', NEW.feedback_id, 
            ' | Category: ', NEW.category,
            ' | Subject: ', LEFT(NEW.subject, 50),
            ' | Rating: ', NEW.rating, ' stars'
        ),
        NULL,
        NOW()
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `feedback_statistics`
-- (See below for the actual view)
--
CREATE TABLE `feedback_statistics` (
`total_feedback` bigint(21)
,`pending_count` decimal(22,0)
,`reviewed_count` decimal(22,0)
,`resolved_count` decimal(22,0)
,`archived_count` decimal(22,0)
,`average_rating` decimal(13,2)
,`positive_feedback` decimal(22,0)
,`negative_feedback` decimal(22,0)
,`bug_reports` decimal(22,0)
,`feature_requests` decimal(22,0)
,`complaints` decimal(22,0)
,`praise_count` decimal(22,0)
,`response_rate` varchar(30)
,`avg_response_time_days` decimal(8,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `feedback_with_user_details`
-- (See below for the actual view)
--
CREATE TABLE `feedback_with_user_details` (
`feedback_id` int(11)
,`user_id` int(11)
,`name` varchar(255)
,`email` varchar(255)
,`phone` varchar(50)
,`category` enum('general','bug_report','feature_request','service_quality','complaint','praise','suggestion','other')
,`subject` varchar(500)
,`message` text
,`rating` int(1)
,`status` enum('pending','reviewed','resolved','archived')
,`priority` enum('low','medium','high','urgent')
,`admin_response` text
,`responded_at` datetime
,`submitted_at` datetime
,`user_full_name` varchar(100)
,`barangay_id` int(11)
,`barangay_name` varchar(100)
,`responded_by_name` varchar(100)
,`responder_role` enum('admin','barangay_official')
);

-- --------------------------------------------------------

--
-- Table structure for table `hotlines`
--

CREATE TABLE `hotlines` (
  `hotlines_id` int(11) NOT NULL,
  `agency_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `landline_number` varchar(50) DEFAULT NULL,
  `logo_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotlines`
--

INSERT INTO `hotlines` (`hotlines_id`, `agency_name`, `description`, `phone_number`, `landline_number`, `logo_type`, `created_at`, `updated_at`) VALUES
(1, 'CDRRMO', 'City Disaster Risk Reduction and Management Office', '0998-574-7171', '(049) 800-0405', 'uploads/logos/692d9a012017d.jpg', '2025-11-18 10:42:32', '2025-12-01 13:37:05'),
(2, 'PHIVOLCS', 'Philippine Institute of Volcanology and Seismology', '0905-313-4677', '(02) 426-1468 to 79', 'uploads/logos/692d99eca438e.jpg', '2025-11-18 10:42:32', '2025-12-01 13:36:44'),
(3, 'Red Cross  San Pablo City', 'Philippine Red Cross, San Pablo City, Provincial Office', '0917 882 4204', '(049) 562-4025', 'uploads/logos/692d99dfbeedc.jpg', '2025-11-18 10:42:32', '2025-12-01 13:36:31'),
(4, 'CIO', 'City Information Office', 'N/A', '(049) 503-5783', 'uploads/logos/692d99d122820.jpg', '2025-11-18 10:42:32', '2025-12-01 13:36:17'),
(5, 'General Hospital', 'San Pablo City General Hospital', 'N/A', '(049) 503-1431', 'uploads/logos/692d99be844bc.jpg', '2025-11-18 10:42:32', '2025-12-01 13:35:58'),
(6, 'SPC-CHO', 'City Health Office. Provides primary healthcare services', 'N/A', '(049) 562-8116', 'uploads/logos/692d99ab99160.jpg', '2025-11-18 10:42:32', '2025-12-01 13:35:39'),
(7, 'BFP', 'San Pablo Fire Station, Regional Fire Station', '0999-578-4943', '(049) 562-7654', 'uploads/logos/692d9991960df.jpg', '2025-11-18 10:42:32', '2025-12-01 13:35:13'),
(8, 'SPC PNP', 'San Pablo Philippine National Police, Municipal Police Station', '0908-193-0819', '049-562-64-74 ', 'uploads/logos/692d9985605c1.jpg', '2025-11-18 10:42:32', '2025-12-01 13:35:01'),
(9, 'DSWD', 'Department of Social Welfare and Development', '0908-193-0819', '(049) 521-2036', 'uploads/logos/692d9975aceb2.jpg', '2025-11-18 10:42:32', '2025-12-01 13:34:45'),
(10, 'BRO', 'Barangay Radio Control', 'N/A', '(049) 552-3086', 'uploads/logos/692d9aff3823f.png', '2025-11-18 10:42:32', '2025-12-01 13:41:19'),
(11, 'CTMO', 'City Traffic Management Office', '0917-362-1655', '(049) 503-2200', 'uploads/logos/6937c422812e5.png', '2025-11-18 10:42:32', '2025-12-09 06:39:30'),
(15, 'Dolores Patrol', 'Panktjhriuol', '0917-362-1678', '(049) 503-2232', 'uploads/logos/6937c463e3ddc.png', '2025-12-09 06:40:35', '2025-12-09 06:40:35');

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `incident_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `incident_type` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `status` enum('pending','in-progress','resolved','rejected','archived') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`incident_id`, `user_id`, `incident_type`, `description`, `location`, `barangay_id`, `status`, `submitted_at`, `resolved_at`, `archived_at`, `archived_by`) VALUES
(1, 1, 'fire', 'Small kitchen fire reported by a resident', 'Purok 1, Atisan', 1, 'resolved', '2025-11-14 02:05:00', '2025-11-14 02:40:00', NULL, NULL),
(2, 1, 'medical', 'Elderly resident experiencing difficulty breathing', 'Purok 2, Atisan', 1, 'resolved', '2025-11-14 02:12:00', '2025-11-14 02:50:00', NULL, NULL),
(3, 1, 'traffic', 'Minor motorcycle collision along Atisan Road', 'Atisan Road', 1, 'pending', '2025-11-14 02:20:00', NULL, NULL, NULL),
(4, 1, 'crime', 'Reported petty theft in a nearby store', 'Sitio Centro, Atisan', 1, 'pending', '2025-11-14 02:30:00', NULL, NULL, NULL),
(5, 1, 'flood', 'Flooding observed after overnight rainfall', 'Riverside Area, Atisan', 1, 'resolved', '2025-11-14 02:45:00', '2025-11-14 03:10:00', NULL, NULL),
(6, 1, 'medical', 'Child treated for minor wounds due to fall', 'Purok 3, Atisan', 1, 'resolved', '2025-11-14 03:00:00', '2025-11-14 03:35:00', NULL, NULL),
(7, 1, 'fire', 'Burning debris reported behind a residence', 'Backyard Area, Atisan', 1, 'resolved', '2025-11-14 03:20:00', '2025-11-14 03:55:00', NULL, NULL),
(8, 1, 'crime', 'Noise disturbance complaint in residential area', 'Sitio Proper, Atisan', 1, 'resolved', '2025-11-14 03:40:00', NULL, NULL, NULL),
(9, 1, 'traffic', 'Tricycle malfunction causing short road obstruction', 'Crossing Area, Atisan', 1, 'resolved', '2025-11-14 03:50:00', '2025-11-14 04:10:00', NULL, NULL),
(10, 1, 'disturbance', 'Reported loud argument between neighbors, no injuries involved', 'Zone 1, Bagong Bayan II-A', 2, 'resolved', '2025-11-14 02:20:00', '2025-11-14 02:45:00', NULL, NULL),
(11, 1, 'traffic', 'Minor tricycle congestion during morning rush hour', 'Main Road, Bagong Bayan II-A', 2, 'pending', '2025-11-14 02:35:00', NULL, NULL, NULL),
(12, 2, 'disturbance', 'Residents reported repeated loud noise from a nearby residence', 'Purok 1, Barangay I-A', 4, 'pending', '2025-11-14 02:15:00', NULL, NULL, NULL),
(13, 2, 'medical', 'Senior citizen experiencing recurring dizziness, assisted by responders', 'Purok 2, Barangay I-A', 4, 'resolved', '2025-11-14 02:25:00', '2025-11-14 02:55:00', NULL, NULL),
(14, 2, 'traffic', 'Recurring minor traffic buildup due to tricycle queue', 'Barangay I-A Central Road', 4, 'pending', '2025-11-14 02:40:00', NULL, NULL, NULL),
(15, 2, 'crime', 'Repeated reports of petty theft attempts around small stores', 'Sitio Proper, Barangay I-A', 4, 'in-progress', '2025-11-14 02:50:00', NULL, NULL, NULL),
(16, 2, 'fire', 'Small recurring backyard burning causing smoke complaints', 'Back Area, Barangay I-A', 4, 'resolved', '2025-11-14 03:05:00', '2025-11-14 03:30:00', NULL, NULL),
(17, 2, 'medical', 'Resident treated for mild fever and nausea, no hospitalization required', 'Purok 1, Barangay I-B', 5, 'resolved', '2025-11-14 02:20:00', '2025-11-14 02:45:00', NULL, NULL),
(18, 2, 'disturbance', 'Occasional neighborhood dispute reported, no injuries', 'Zone 2, Barangay I-B', 5, 'pending', '2025-11-14 02:30:00', NULL, NULL, NULL),
(19, 2, 'traffic', 'Light traffic congestion caused by parked vehicles', 'Barangay I-B Main Road', 5, 'pending', '2025-11-14 02:45:00', NULL, NULL, NULL),
(20, 2, 'fire', 'Smoke spotted from small garbage burning area', 'Sitio Centro, Barangay I-B', 5, 'resolved', '2025-11-14 02:55:00', '2025-11-14 03:20:00', NULL, NULL),
(21, 2, 'accident', 'Minor slip-and-fall incident reported at sari-sari store entrance', 'Purok 3, Barangay I-B', 5, 'resolved', '2025-11-14 03:05:00', '2025-11-14 03:40:00', NULL, NULL),
(22, 2, 'crime', 'Report of occasional vandalism near abandoned structure', 'Old Warehouse Area, Barangay I-B', 5, 'in-progress', '2025-11-14 03:20:00', NULL, NULL, NULL),
(23, 2, 'medical', 'Child treated for mild allergic reaction', 'Purok 4, Barangay I-B', 5, 'resolved', '2025-11-14 03:35:00', '2025-11-14 04:00:00', NULL, NULL),
(24, 2, 'disturbance', 'Occasional loud music complaint in residential zone', 'Zone 1, Barangay I-B', 5, 'pending', '2025-11-14 03:50:00', NULL, NULL, NULL),
(25, 2, 'medical', 'Resident experienced mild stomach cramps and was assisted at home', 'Purok 1, Barangay II-A', 6, 'resolved', '2025-11-14 04:05:00', '2025-11-14 04:35:00', NULL, NULL),
(26, 2, 'disturbance', 'Occasional reports of loud karaoke noise in the neighborhood', 'Zone 2, Barangay II-A', 6, 'pending', '2025-11-14 04:20:00', NULL, NULL, NULL),
(27, 2, 'traffic', 'Minor vehicle slowdown near school area during noon dismissal', 'School Road, Barangay II-A', 6, 'pending', '2025-11-14 04:30:00', NULL, NULL, NULL),
(28, 2, 'fire', 'Small trash burning causing brief smoke disturbance', 'Sitio Proper, Barangay II-A', 6, 'resolved', '2025-11-14 04:45:00', '2025-11-14 05:10:00', NULL, NULL),
(29, 2, 'accident', 'Minor bicycle mishap involving a child, no serious injuries', 'Purok 3, Barangay II-A', 6, 'resolved', '2025-11-14 05:00:00', '2025-11-14 05:30:00', NULL, NULL),
(30, 2, 'crime', 'Occasional report of suspicious individuals near vacant lot', 'Back Area, Barangay II-A', 6, 'in-progress', '2025-11-14 05:15:00', NULL, NULL, NULL),
(31, 3, 'medical', 'Elderly resident requested aid due to dizziness', 'Zone 1, Barangay II-A', 6, 'resolved', '2025-11-14 05:30:00', '2025-11-14 05:55:00', NULL, NULL),
(32, 3, 'disturbance', 'Random barking complaints from stray dogs in the area', 'Purok 4, Barangay II-A', 6, 'pending', '2025-11-14 05:45:00', NULL, NULL, NULL),
(33, 3, 'disturbance', 'Recurring noise complaints involving late-night gatherings', 'Purok 1, Barangay II-B', 7, 'pending', '2025-11-14 06:00:00', NULL, NULL, NULL),
(34, 3, 'medical', 'Resident reported recurring headaches and asked for first aid', 'Zone 2, Barangay II-B', 7, 'resolved', '2025-11-14 06:15:00', '2025-11-14 06:45:00', NULL, NULL),
(35, 3, 'crime', 'Repeated reports of petty theft attempts near small shops', 'Sitio Centro, Barangay II-B', 7, 'in-progress', '2025-11-14 06:30:00', NULL, NULL, NULL),
(36, 3, 'disturbance', 'Neighbors reported recurring loud music during late evenings', 'Purok 1, Barangay II-C', 8, 'pending', '2025-11-14 06:50:00', NULL, NULL, NULL),
(37, 3, 'crime', 'Repeated reports of suspected theft attempts near residential area', 'Zone 2, Barangay II-C', 8, 'in-progress', '2025-11-14 07:05:00', NULL, NULL, NULL),
(38, 3, 'medical', 'Resident experiencing recurring chest discomfort assisted by responders', 'Sitio Proper, Barangay II-C', 8, 'resolved', '2025-11-14 07:20:00', '2025-11-14 07:50:00', NULL, NULL),
(39, 3, 'traffic', 'Recurring slow traffic due to narrow portion of the road', 'Main Road, Barangay II-C', 8, 'pending', '2025-11-14 07:35:00', NULL, NULL, NULL),
(40, 3, 'disturbance', 'Recurring report of loud shouting between residents, no physical harm reported', 'Purok 1, Barangay II-D', 9, 'pending', '2025-11-14 07:50:00', NULL, NULL, NULL),
(41, 3, 'medical', 'Resident experienced sudden dizziness while walking outside', 'Purok 1, Barangay II-E', 10, 'resolved', '2025-11-14 08:05:00', '2025-11-14 08:30:00', NULL, NULL),
(42, 3, 'disturbance', 'Occasional loud barking from neighborhood dogs reported', 'Zone 2, Barangay II-E', 10, 'pending', '2025-11-14 08:20:00', NULL, NULL, NULL),
(43, 3, 'traffic', 'Short traffic delay due to temporary road obstruction', 'Main Road, Barangay II-E', 10, 'pending', '2025-11-14 08:35:00', NULL, NULL, NULL),
(44, 3, 'fire', 'Small trash fire spotted and quickly extinguished by locals', 'Sitio Proper, Barangay II-E', 10, 'resolved', '2025-11-14 08:50:00', '2025-11-14 09:10:00', NULL, NULL),
(45, 3, 'fire', 'Large debris fire reported causing heavy smoke affecting nearby homes', 'Purok 1, Barangay II-F', 11, 'in-progress', '2025-11-14 09:05:00', NULL, NULL, NULL),
(46, 3, 'medical', 'Severe asthma attack requiring immediate responder assistance', 'Zone 2, Barangay II-F', 11, 'resolved', '2025-11-14 09:20:00', '2025-11-14 09:55:00', NULL, NULL),
(47, 3, 'accident', 'Two-motorcycle collision resulting in multiple injuries', 'Barangay II-F Main Road', 11, 'in-progress', '2025-11-14 09:35:00', NULL, NULL, NULL),
(48, 3, 'crime', 'Serious burglary incident reported involving forced entry', 'Sitio Centro, Barangay II-F', 11, 'pending', '2025-11-14 09:50:00', NULL, NULL, NULL),
(49, 3, 'fire', 'Electrical short circuit triggered heavy smoke in a household', 'Back Area, Barangay II-F', 11, 'resolved', '2025-11-14 10:05:00', '2025-11-14 10:40:00', NULL, NULL),
(50, 3, 'medical', 'Severe allergic reaction requiring rapid medical support', 'Purok 3, Barangay II-F', 11, 'resolved', '2025-11-14 10:20:00', '2025-11-14 10:55:00', NULL, NULL),
(51, 3, 'medical', 'Resident reported mild chest tightness and requested basic assistance', 'Purok 1, Barangay III-A', 12, 'resolved', '2025-11-14 10:40:00', '2025-11-14 11:05:00', NULL, NULL),
(52, 3, 'disturbance', 'Occasional loud karaoke from nearby houses reported', 'Zone 2, Barangay III-A', 12, 'pending', '2025-11-14 10:55:00', NULL, NULL, NULL),
(53, 3, 'traffic', 'Light traffic buildup caused by stalled tricycle', 'Main Road, Barangay III-A', 12, 'pending', '2025-11-14 11:10:00', NULL, NULL, NULL),
(54, 3, 'accident', 'Minor slip-and-fall incident outside a store, no serious injury', 'Sitio Proper, Barangay III-A', 12, 'resolved', '2025-11-14 11:25:00', '2025-11-14 11:55:00', NULL, NULL),
(55, 3, 'fire', 'Small rubbish fire near residential area, quickly extinguished', 'Back Area, Barangay III-A', 12, 'resolved', '2025-11-14 11:40:00', '2025-11-14 12:05:00', NULL, NULL),
(56, 3, 'medical', 'Child experienced mild fever and was assisted by barangay responders', 'Purok 3, Barangay III-A', 12, 'resolved', '2025-11-14 11:55:00', '2025-11-14 12:20:00', NULL, NULL),
(57, 3, 'disturbance', 'Occasional yelling reported from a nearby residence', 'Zone 1, Barangay III-A', 12, 'pending', '2025-11-14 12:10:00', NULL, NULL, NULL),
(58, 3, 'disturbance', 'Repeated loud arguments reported between neighboring households', 'Purok 1, Barangay III-B', 12, 'pending', '2025-11-14 12:25:00', NULL, NULL, NULL),
(59, 3, 'crime', 'Recurring reports of attempted petty theft near small stores', 'Zone 2, Barangay III-B', 12, 'in-progress', '2025-11-14 12:40:00', NULL, NULL, NULL),
(60, 3, 'medical', 'Resident experiencing recurring migraine episodes assisted by responders', 'Sitio Proper, Barangay III-B', 12, 'resolved', '2025-11-14 12:55:00', '2025-11-14 13:20:00', NULL, NULL),
(61, 4, 'traffic', 'Frequent tricycle congestion causing slow movement', 'Barangay III-B Main Road', 12, 'pending', '2025-11-14 13:10:00', NULL, NULL, NULL),
(62, 4, 'disturbance', 'Recurring karaoke complaints reported during late hours', 'Zone 1, Barangay III-B', 12, 'pending', '2025-11-14 13:25:00', NULL, NULL, NULL),
(63, 4, 'crime', 'Residents reported repeated cases of loitering near dark alley', 'Back Area, Barangay III-B', 12, 'in-progress', '2025-11-14 13:40:00', NULL, NULL, NULL),
(64, 4, 'fire', 'Burning of debris reported multiple times throughout the week', 'Purok 3, Barangay III-B', 12, 'resolved', '2025-11-14 13:55:00', '2025-11-14 14:20:00', NULL, NULL),
(65, 4, 'traffic', 'Recurring blockage caused by parked delivery vehicles', 'Market Road, Barangay III-B', 12, 'pending', '2025-11-14 14:10:00', NULL, NULL, NULL),
(66, 4, 'medical', 'Resident with recurring high blood pressure requested monitoring', 'Purok 4, Barangay III-B', 13, 'resolved', '2025-11-14 14:25:00', '2025-11-14 14:55:00', NULL, NULL),
(67, 4, 'disturbance', 'Frequent barking disturbance from multiple stray dogs', 'Zone 3, Barangay III-B', 13, 'pending', '2025-11-14 14:40:00', NULL, NULL, NULL),
(68, 4, 'disturbance', 'Recurring late-night shouting reported from the same household', 'Purok 1, Barangay III-F', 17, 'pending', '2025-11-14 14:55:00', NULL, NULL, NULL),
(69, 4, 'traffic', 'Repeated minor congestion caused by tricycles during peak hours', 'Barangay III-F Central Road', 17, 'pending', '2025-11-14 15:10:00', NULL, NULL, NULL),
(70, 4, 'crime', 'Recurring reports of suspicious individuals loitering near alleyways', 'Sitio Proper, Barangay III-F', 17, 'in-progress', '2025-11-14 15:20:00', NULL, NULL, NULL),
(71, 4, 'medical', 'Resident experiencing recurring dizziness requested assistance', 'Zone 2, Barangay III-F', 17, 'resolved', '2025-11-14 15:30:00', '2025-11-14 15:55:00', NULL, NULL),
(72, 4, 'disturbance', 'Frequent dog barking disturbing nearby households', 'Purok 3, Barangay III-F', 17, 'pending', '2025-11-14 15:45:00', NULL, NULL, NULL),
(73, 4, 'fire', 'Large residential fire reported, requiring immediate suppression response', 'Purok 1, Barangay IV-A', 18, 'in-progress', '2025-11-14 16:10:00', NULL, NULL, NULL),
(74, 4, 'accident', 'Severe vehicular collision involving two motorcycles and one tricycle', 'Barangay IV-A Main Road', 18, 'in-progress', '2025-11-14 16:25:00', NULL, NULL, NULL),
(75, 4, 'medical', 'Critical asthma attack requiring urgent transport to medical facility', 'Zone 2, Barangay IV-A', 18, 'resolved', '2025-11-14 16:40:00', '2025-11-14 17:10:00', NULL, NULL),
(76, 4, 'crime', 'Serious break-in incident with stolen valuables reported', 'Sitio Centro, Barangay IV-A', 18, 'pending', '2025-11-14 16:55:00', NULL, NULL, NULL),
(77, 4, 'fire', 'Electrical short circuit caused heavy smoke inside residence', 'Back Area, Barangay IV-A', 18, 'resolved', '2025-11-14 17:10:00', '2025-11-14 17:40:00', NULL, NULL),
(78, 4, 'medical', 'Severe allergic reaction requiring emergency assistance', 'Purok 3, Barangay IV-A', 18, 'resolved', '2025-11-14 17:25:00', '2025-11-14 17:55:00', NULL, NULL),
(79, 4, 'disturbance', 'Recurring loud altercations reported between household members', 'Purok 1, Barangay IV-B', 19, 'pending', '2025-11-14 17:45:00', NULL, NULL, NULL),
(80, 4, 'traffic', 'Frequent minor congestion caused by delivery vans blocking roadway', 'Barangay IV-B Main Road', 19, 'pending', '2025-11-14 18:00:00', NULL, NULL, NULL),
(81, 4, 'crime', 'Repeated complaints of petty theft around residential stores', 'Zone 2, Barangay IV-B', 19, 'in-progress', '2025-11-14 18:15:00', NULL, NULL, NULL),
(82, 4, 'medical', 'Resident experienced sudden dizziness while doing household chores', 'Purok 1, Barangay V-A', 21, 'resolved', '2025-11-14 18:30:00', '2025-11-14 18:55:00', NULL, NULL),
(83, 4, 'disturbance', 'Occasional loud music complaint from a nearby residence', 'Zone 2, Barangay V-A', 21, 'pending', '2025-11-14 18:45:00', NULL, NULL, NULL),
(84, 4, 'traffic', 'Temporary traffic slowdown due to tricycle breakdown', 'Main Road, Barangay V-A', 21, 'pending', '2025-11-14 19:00:00', NULL, NULL, NULL),
(85, 4, 'accident', 'Minor slip incident involving a pedestrian near store entrance', 'Sitio Proper, Barangay V-A', 21, 'resolved', '2025-11-14 19:15:00', '2025-11-14 19:40:00', NULL, NULL),
(86, 4, 'fire', 'Small garbage fire reported behind a residential area', 'Back Area, Barangay V-A', 21, 'resolved', '2025-11-14 19:30:00', '2025-11-14 20:00:00', NULL, NULL),
(87, 4, 'disturbance', 'Residents reported occasional dog barking during late hours', 'Purok 3, Barangay V-A', 21, 'pending', '2025-11-14 19:45:00', NULL, NULL, NULL),
(88, 4, 'medical', 'Resident experienced mild stomach pain and requested assistance', 'Purok 1, Barangay V-C', 23, 'resolved', '2025-11-14 20:10:00', '2025-11-14 20:35:00', NULL, NULL),
(89, 4, 'disturbance', 'Occasional loud noise from neighborhood gatherings reported', 'Zone 2, Barangay V-C', 23, 'pending', '2025-11-14 20:25:00', NULL, NULL, NULL),
(90, 4, 'traffic', 'Light traffic delay caused by a stalled tricycle', 'Main Road, Barangay V-C', 23, 'pending', '2025-11-14 20:40:00', NULL, NULL, NULL),
(91, 5, 'accident', 'Minor bicycle accident involving a youth, no serious injuries', 'Sitio Proper, Barangay V-C', 23, 'resolved', '2025-11-14 20:55:00', '2025-11-14 21:20:00', NULL, NULL),
(92, 5, 'disturbance', 'Residents reported recurring loud shouting during late evenings', 'Purok 1, Barangay V-D', 24, 'pending', '2025-11-14 21:35:00', NULL, NULL, NULL),
(93, 5, 'crime', 'Repeated petty theft attempts reported near small stores', 'Zone 2, Barangay V-D', 24, 'in-progress', '2025-11-14 21:50:00', NULL, NULL, NULL),
(94, 5, 'traffic', 'Tricycles frequently causing temporary road blockage during peak hours', 'Main Road, Barangay V-D', 24, 'pending', '2025-11-14 22:05:00', NULL, NULL, NULL),
(95, 5, 'medical', 'Resident reported recurring dizziness and requested assistance', 'Sitio Proper, Barangay V-D', 24, 'resolved', '2025-11-14 22:20:00', '2025-11-14 22:45:00', NULL, NULL),
(96, 5, 'fire', 'Strong electrical fire reported inside a residence causing heavy smoke', 'Purok 1, Barangay VI-A', 25, 'in-progress', '2025-11-14 23:00:00', NULL, NULL, NULL),
(97, 5, 'accident', 'Severe motorcycle-tricycle collision resulting in multiple injuries', 'Main Road, Barangay VI-A', 25, 'resolved', '2025-11-14 23:20:00', '2025-11-14 23:55:00', NULL, NULL),
(98, 5, 'disturbance', 'Residents reported recurring loud arguments from the same household', 'Purok 1, Barangay VI-B', 26, 'pending', '2025-11-15 00:05:00', NULL, NULL, NULL),
(99, 5, 'crime', 'Repeated reports of suspicious individuals loitering near dark alley', 'Zone 2, Barangay VI-B', 26, 'in-progress', '2025-11-15 00:20:00', NULL, NULL, NULL),
(100, 5, 'traffic', 'Recurring tricycle congestion causing temporary delays', 'Main Road, Barangay VI-B', 26, 'pending', '2025-11-15 00:35:00', NULL, NULL, NULL),
(101, 5, 'medical', 'Resident experiencing recurring headaches requested medical assistance', 'Sitio Proper, Barangay VI-B', 26, 'resolved', '2025-11-15 00:50:00', '2025-11-15 01:20:00', NULL, NULL),
(102, 5, 'disturbance', 'Recurring loud noise complaints from the same household reported by neighbors', 'Purok 1, Barangay VI-D', 28, 'pending', '2025-11-15 01:35:00', NULL, NULL, NULL),
(103, 5, 'medical', 'Resident experienced mild dizziness while walking near the plaza', 'Purok 1, Barangay VI-E', 28, 'resolved', '2025-11-15 02:00:00', '2025-11-15 02:25:00', NULL, NULL),
(104, 5, 'disturbance', 'Occasional loud music reported from a nearby house', 'Zone 2, Barangay VI-E', 28, 'pending', '2025-11-15 02:15:00', NULL, NULL, NULL),
(105, 5, 'traffic', 'Temporary slowdown due to a stalled tricycle', 'Main Road, Barangay VI-E', 28, 'pending', '2025-11-15 02:30:00', NULL, NULL, NULL),
(106, 5, 'accident', 'Minor slip incident involving a child playing near a store', 'Sitio Proper, Barangay VI-E', 28, 'resolved', '2025-11-15 02:45:00', '2025-11-15 03:10:00', NULL, NULL),
(107, 5, 'fire', 'Small trash fire behind a residential area quickly extinguished', 'Back Area, Barangay VI-E', 28, 'resolved', '2025-11-15 03:00:00', '2025-11-15 03:25:00', NULL, NULL),
(108, 5, 'medical', 'Child experienced mild fever and was checked by barangay responders', 'Purok 3, Barangay VI-E', 28, 'resolved', '2025-11-15 03:15:00', '2025-11-15 03:40:00', NULL, NULL),
(109, 5, 'disturbance', 'Occasional barking from stray dogs reported by residents', 'Purok 4, Barangay VI-E', 28, 'pending', '2025-11-15 03:30:00', NULL, NULL, NULL),
(110, 5, 'traffic', 'Brief road obstruction due to parked motorcycles', 'Crossing Area, Barangay VI-E', 28, 'pending', '2025-11-15 03:45:00', NULL, NULL, NULL),
(111, 5, 'accident', 'Minor bicycle fall involving a teenager, treated on site', 'Zone 1, Barangay VI-E', 28, 'resolved', '2025-11-15 04:00:00', '2025-11-15 04:30:00', NULL, NULL),
(112, 5, 'disturbance', 'Occasional yelling heard between residents during afternoon hours', 'Sitio Centro, Barangay VI-E', 28, 'pending', '2025-11-15 04:15:00', NULL, NULL, NULL),
(113, 5, 'disturbance', 'Repeated loud arguments between neighbors reported throughout the week', 'Purok 1, Barangay VII-A', 29, 'pending', '2025-11-15 04:35:00', NULL, NULL, NULL),
(114, 5, 'crime', 'Recurring reports of individuals attempting to steal parked bicycles', 'Zone 2, Barangay VII-A', 29, 'in-progress', '2025-11-15 04:50:00', NULL, NULL, NULL),
(115, 5, 'traffic', 'Tricycles frequently causing minor congestion during peak hours', 'Main Road, Barangay VII-A', 29, 'pending', '2025-11-15 05:05:00', NULL, NULL, NULL),
(116, 5, 'medical', 'Resident experiencing recurring headaches sought barangay medical help', 'Sitio Proper, Barangay VII-A', 29, 'resolved', '2025-11-15 05:20:00', '2025-11-15 05:45:00', NULL, NULL),
(117, 5, 'disturbance', 'Frequent night-time karaoke sessions causing multiple complaints', 'Zone 1, Barangay VII-A', 29, 'pending', '2025-11-15 05:35:00', NULL, NULL, NULL),
(118, 5, 'accident', 'Repeated minor motorcycle skidding incidents on slippery road curve', 'Curved Road, Barangay VII-A', 29, 'in-progress', '2025-11-15 05:50:00', NULL, NULL, NULL),
(119, 5, 'crime', 'Residents reported recurring vandalism near the basketball court', 'Barangay Court Area, Barangay VII-A', 29, 'pending', '2025-11-15 06:05:00', NULL, NULL, NULL),
(120, 5, 'medical', 'Resident experienced mild dizziness outside a sari-sari store and requested assistance', 'Purok 1, Barangay VII-B', 30, 'resolved', '2025-11-15 06:20:00', '2025-11-15 06:45:00', NULL, NULL),
(121, 5, 'disturbance', 'Repeated complaints of loud late-night gatherings in a residential area', 'Purok 1, Barangay VII-C', 31, 'pending', '2025-11-15 06:55:00', NULL, NULL, NULL),
(122, 5, 'crime', 'Recurring reports of suspicious individuals scouting parked motorcycles', 'Zone 2, Barangay VII-C', 31, 'in-progress', '2025-11-15 07:10:00', NULL, NULL, NULL),
(123, 5, 'traffic', 'Frequent minor congestion near the tricycle terminal during peak hours', 'Terminal Road, Barangay VII-C', 31, 'pending', '2025-11-15 07:25:00', NULL, NULL, NULL),
(124, 5, 'medical', 'Resident experiencing recurring fatigue requested barangay medical checkup', 'Sitio Proper, Barangay VII-C', 31, 'resolved', '2025-11-15 07:40:00', '2025-11-15 08:05:00', NULL, NULL),
(125, 5, 'disturbance', 'Repeated noise complaints from ongoing home renovation works', 'Purok 3, Barangay VII-C', 31, 'pending', '2025-11-15 07:55:00', NULL, NULL, NULL),
(126, 5, 'crime', 'Multiple reports of attempted break-ins around commercial stalls', 'Market Area, Barangay VII-C', 31, 'in-progress', '2025-11-15 08:10:00', NULL, NULL, NULL),
(127, 5, 'medical', 'Resident with recurring asthma symptoms requested periodic assistance', 'Zone 1, Barangay VII-C', 31, 'resolved', '2025-11-15 08:25:00', '2025-11-15 08:55:00', NULL, NULL),
(128, 5, 'traffic', 'Repeated motorcycle skidding incidents due to slippery intersection', 'Crossing Area, Barangay VII-C', 31, 'pending', '2025-11-15 08:40:00', NULL, NULL, NULL),
(129, 5, 'disturbance', 'Recurring dog barking issues affecting nearby households', 'Purok 4, Barangay VII-C', 31, 'pending', '2025-11-15 08:55:00', NULL, NULL, NULL),
(130, 5, 'fire', 'Major residential fire reported causing heavy smoke and partial property damage', 'Purok 1, Barangay VII-D', 32, 'in-progress', '2025-11-15 09:10:00', NULL, NULL, NULL),
(131, 6, 'disturbance', 'Residents reported recurring loud shouting during evening hours', 'Purok 1, Barangay VII-E', 33, 'pending', '2025-11-15 09:25:00', NULL, NULL, NULL),
(132, 6, 'crime', 'Repeated petty theft attempts near neighborhood stores', 'Zone 2, Barangay VII-E', 33, 'in-progress', '2025-11-15 09:40:00', NULL, NULL, NULL),
(133, 6, 'traffic', 'Recurring tricycle buildup causing minor road delays', 'Main Road, Barangay VII-E', 33, 'pending', '2025-11-15 09:55:00', NULL, NULL, NULL),
(134, 6, 'medical', 'Resident experiencing recurring dizziness requested assistance', 'Sitio Proper, Barangay VII-E', 33, 'resolved', '2025-11-15 10:10:00', '2025-11-15 10:35:00', NULL, NULL),
(135, 6, 'disturbance', 'Multiple complaints about loud music during late-night hours', 'Purok 3, Barangay VII-E', 33, 'pending', '2025-11-15 10:25:00', NULL, NULL, NULL),
(136, 6, 'crime', 'Recurring reports of trespassing near abandoned structures', 'Back Area, Barangay VII-E', 33, 'in-progress', '2025-11-15 10:40:00', NULL, NULL, NULL),
(137, 6, 'medical', 'Resident with recurring asthma episodes assisted by responders', 'Zone 1, Barangay VII-E', 33, 'resolved', '2025-11-15 10:55:00', '2025-11-15 11:20:00', NULL, NULL),
(138, 6, 'disturbance', 'Recurring noise complaints caused by late-night gatherings', 'Purok 1, Bautista', 34, 'pending', '2025-11-15 11:35:00', NULL, NULL, NULL),
(139, 6, 'crime', 'Repeated reports of individuals attempting to steal unattended bicycles', 'Zone 2, Bautista', 34, 'in-progress', '2025-11-15 11:50:00', NULL, NULL, NULL),
(140, 6, 'medical', 'Resident experiencing recurring migraine episodes requested help', 'Sitio Proper, Bautista', 34, 'resolved', '2025-11-15 12:05:00', '2025-11-15 12:30:00', NULL, NULL),
(141, 6, 'traffic', 'Frequent tricycle buildup causing minor delays near the plaza', 'Plaza Road, Bautista', 34, 'pending', '2025-11-15 12:20:00', NULL, NULL, NULL),
(142, 6, 'disturbance', 'Recurring complaints of loud music during weekend nights', 'Purok 3, Bautista', 34, 'pending', '2025-11-15 12:35:00', NULL, NULL, NULL),
(143, 6, 'crime', 'Multiple reports of loitering near abandoned structures', 'Back Area, Bautista', 34, 'in-progress', '2025-11-15 12:50:00', NULL, NULL, NULL),
(144, 6, 'medical', 'Elderly resident experiencing recurring dizziness was assisted by responders', 'Zone 1, Bautista', 34, 'resolved', '2025-11-15 13:05:00', '2025-11-15 13:30:00', NULL, NULL),
(145, 6, 'fire', 'Major fire broke out in a residential home causing significant damage', 'Purok 1, Concepcion', 35, 'in-progress', '2025-11-15 13:45:00', NULL, NULL, NULL),
(146, 6, 'accident', 'Severe tricycle–motorcycle collision resulting in multiple injuries', 'Main Road, Concepcion', 35, 'resolved', '2025-11-15 14:00:00', '2025-11-15 14:35:00', NULL, NULL),
(147, 6, 'crime', 'Serious break-in incident reported with valuables stolen', 'Zone 2, Concepcion', 35, 'pending', '2025-11-15 14:15:00', NULL, NULL, NULL),
(148, 6, 'fire', 'A major fire erupted in a residential area causing heavy structural damage', 'Purok 1, Del Remedio', 36, 'in-progress', '2025-11-15 14:30:00', NULL, NULL, NULL),
(149, 6, 'accident', 'Severe vehicular crash involving two motorcycles resulted in multiple injured individuals', 'Main Road, Del Remedio', 36, 'resolved', '2025-11-15 14:45:00', '2025-11-15 15:15:00', NULL, NULL),
(150, 6, 'disturbance', 'Recurring late-night shouting reported from the same residence', 'Purok 1, Dolores', 37, 'pending', '2025-11-15 15:30:00', NULL, NULL, NULL),
(151, 6, 'traffic', 'Frequent minor traffic buildup caused by tricycles during peak hours', 'Dolores Main Road', 37, 'pending', '2025-11-15 15:45:00', NULL, NULL, NULL),
(152, 6, 'crime', 'Multiple reports of suspicious individuals roaming near dark alleyways', 'Zone 2, Dolores', 37, 'in-progress', '2025-11-15 16:00:00', NULL, NULL, NULL),
(153, 6, 'medical', 'Resident experiencing recurring dizziness sought medical assistance', 'Sitio Proper, Dolores', 37, 'resolved', '2025-11-15 16:15:00', '2025-11-15 16:40:00', NULL, NULL),
(154, 6, 'disturbance', 'Repeated complaints of loud music during late-night gatherings', 'Purok 3, Dolores', 37, 'pending', '2025-11-15 16:30:00', NULL, NULL, NULL),
(155, 6, 'fire', 'A large blaze engulfed part of a residential area causing extensive structural damage', 'Purok 1, San Antonio 1', 38, 'in-progress', '2025-11-15 16:50:00', NULL, NULL, NULL),
(156, 6, 'accident', 'Major vehicular collision involving a jeepney and two motorcycles, multiple injured', 'San Antonio 1 Highway', 38, 'resolved', '2025-11-15 17:05:00', '2025-11-15 17:40:00', NULL, NULL),
(157, 6, 'medical', 'Critical asthma attack requiring immediate medical evacuation', 'Zone 2, San Antonio 1', 38, 'resolved', '2025-11-15 17:20:00', '2025-11-15 17:50:00', NULL, NULL),
(158, 6, 'crime', 'Serious burglary incident involving forced entry and stolen appliances', 'Sitio Proper, San Antonio 1', 38, 'pending', '2025-11-15 17:35:00', NULL, NULL, NULL),
(159, 6, 'fire', 'Electrical fire caused heavy smoke inside a residential home', 'Purok 3, San Antonio 1', 38, 'resolved', '2025-11-15 17:50:00', '2025-11-15 18:15:00', NULL, NULL),
(160, 6, 'accident', 'Severe motorcycle skid accident due to slippery pavement after rain', 'Crossing Area, San Antonio 1', 38, 'in-progress', '2025-11-15 18:05:00', NULL, NULL, NULL),
(161, 7, 'medical', 'Resident suffered a severe allergic reaction requiring urgent intervention', 'Zone 1, San Antonio 1', 38, 'resolved', '2025-11-15 18:20:00', '2025-11-15 18:55:00', NULL, NULL),
(162, 7, 'disturbance', 'Recurring loud arguments between neighbors reported every week', 'Purok 1, San Antonio 2', 39, 'pending', '2025-11-15 19:05:00', NULL, NULL, NULL),
(163, 7, 'crime', 'Repeated incidents of attempted theft around small commercial stalls', 'Zone 2, San Antonio 2', 39, 'in-progress', '2025-11-15 19:20:00', NULL, NULL, NULL),
(164, 7, 'traffic', 'Tricycles frequently causing minor congestion near the school zone', 'School Road, San Antonio 2', 39, 'pending', '2025-11-15 19:35:00', NULL, NULL, NULL),
(165, 7, 'medical', 'Resident reported recurring dizziness and required medical monitoring', 'Sitio Proper, San Antonio 2', 39, 'resolved', '2025-11-15 19:50:00', '2025-11-15 20:20:00', NULL, NULL),
(166, 7, 'disturbance', 'Repeated complaints about loud karaoke past midnight', 'Zone 1, San Antonio 2', 39, 'pending', '2025-11-15 20:05:00', NULL, NULL, NULL),
(167, 7, 'crime', 'Multiple reports of vandalism near the barangay gymnasium', 'Gym Area, San Antonio 2', 39, 'in-progress', '2025-11-15 20:20:00', NULL, NULL, NULL),
(168, 7, 'traffic', 'Recurring slow traffic due to narrow road passage', 'Main Road, San Antonio 2', 39, 'pending', '2025-11-15 20:35:00', NULL, NULL, NULL),
(169, 7, 'medical', 'Resident experiencing repeated asthma episodes required barangay checkups', 'Purok 3, San Antonio 2', 39, 'resolved', '2025-11-15 20:50:00', '2025-11-15 21:15:00', NULL, NULL),
(170, 7, 'disturbance', 'Continuous barking from stray dogs reported by multiple households', 'Street Side, San Antonio 2', 39, 'pending', '2025-11-15 21:05:00', NULL, NULL, NULL),
(171, 7, 'crime', 'Recurring reports of loiterers causing concern among residents', 'Market Area, San Antonio 2', 39, 'in-progress', '2025-11-15 21:20:00', NULL, NULL, NULL),
(172, 7, 'disturbance', 'Recurring loud arguments between households reported during late evenings', 'Purok 1, San Bartolome', 40, 'pending', '2025-11-15 21:35:00', NULL, NULL, NULL),
(173, 7, 'traffic', 'Frequent minor tricycle congestion causing slow movement near marketplace', 'Market Road, San Bartolome', 40, 'pending', '2025-11-15 21:50:00', NULL, NULL, NULL),
(174, 7, 'crime', 'Repeated reports of suspicious individuals loitering in dimly lit areas', 'Zone 2, San Bartolome', 40, 'in-progress', '2025-11-15 22:05:00', NULL, NULL, NULL),
(175, 7, 'medical', 'Resident experienced mild dizziness while walking home and requested assistance', 'Purok 1, San Buenaventura', 41, 'resolved', '2025-11-15 22:20:00', '2025-11-15 22:45:00', NULL, NULL),
(176, 7, 'disturbance', 'Occasional loud music from a nearby residence reported by neighbors', 'Zone 2, San Buenaventura', 41, 'pending', '2025-11-15 22:35:00', NULL, NULL, NULL),
(177, 7, 'traffic', 'Small traffic slowdown due to a stalled motorcycle along the road', 'Main Road, San Buenaventura', 41, 'pending', '2025-11-15 22:50:00', NULL, NULL, NULL),
(178, 7, 'accident', 'Minor slip incident involving a child playing outdoors, treated on site', 'Sitio Proper, San Buenaventura', 41, 'resolved', '2025-11-15 23:05:00', '2025-11-15 23:30:00', NULL, NULL),
(179, 7, 'disturbance', 'Occasional dog barking complaint from nearby houses', 'Purok 3, San Buenaventura', 41, 'pending', '2025-11-15 23:20:00', NULL, NULL, NULL),
(180, 7, 'medical', 'Resident experienced mild stomach pain while walking and requested help', 'Purok 1, San Crispin', 42, 'resolved', '2025-11-15 23:35:00', '2025-11-16 00:00:00', NULL, NULL),
(181, 7, 'disturbance', 'Occasional loud arguing between neighbors reported', 'Zone 2, San Crispin', 42, 'pending', '2025-11-15 23:50:00', NULL, NULL, NULL),
(182, 7, 'traffic', 'Brief traffic delay due to tricycle unloading passengers', 'Main Road, San Crispin', 42, 'pending', '2025-11-16 00:05:00', NULL, NULL, NULL),
(183, 7, 'accident', 'Minor bicycle fall involving a teenager, treated with first aid', 'Sitio Proper, San Crispin', 42, 'resolved', '2025-11-16 00:20:00', '2025-11-16 00:45:00', NULL, NULL),
(184, 7, 'disturbance', 'Occasional dog barking reported by nearby residents', 'Purok 3, San Crispin', 42, 'pending', '2025-11-16 00:35:00', NULL, NULL, NULL),
(185, 7, 'disturbance', 'Recurring loud shouting reported between neighbors during late hours', 'Purok 1, San Cristobal', 43, 'pending', '2025-11-16 00:50:00', NULL, NULL, NULL),
(186, 7, 'crime', 'Repeated theft attempts around sari-sari stores reported', 'Zone 2, San Cristobal', 43, 'in-progress', '2025-11-16 01:05:00', NULL, NULL, NULL),
(187, 7, 'traffic', 'Frequent tricycle buildup causing short traffic delays', 'Main Road, San Cristobal', 43, 'pending', '2025-11-16 01:20:00', NULL, NULL, NULL),
(188, 7, 'medical', 'Resident experiencing recurring dizziness requested barangay help', 'Sitio Proper, San Cristobal', 43, 'resolved', '2025-11-16 01:35:00', '2025-11-16 01:55:00', NULL, NULL),
(189, 7, 'disturbance', 'Multiple complaints about loud karaoke during evening hours', 'Purok 3, San Cristobal', 43, 'pending', '2025-11-16 01:50:00', NULL, NULL, NULL),
(190, 7, 'crime', 'Recurring reports of suspicious activity near abandoned buildings', 'Back Area, San Cristobal', 43, 'in-progress', '2025-11-16 02:05:00', NULL, NULL, NULL),
(191, 7, 'traffic', 'Repeated reports of minor road obstructions caused by parked vehicles', 'Crossing Area, San Cristobal', 43, 'pending', '2025-11-16 02:20:00', NULL, NULL, NULL),
(192, 7, 'medical', 'Resident with recurring hypertension symptoms monitored by responders', 'Zone 1, San Cristobal', 43, 'resolved', '2025-11-16 02:35:00', '2025-11-16 03:00:00', NULL, NULL),
(193, 7, 'medical', 'Resident experienced mild dizziness while walking near the plaza', 'Purok 1, San Diego', 44, 'resolved', '2025-11-16 03:15:00', '2025-11-16 03:40:00', NULL, NULL),
(194, 7, 'disturbance', 'Occasional loud laughter and noise from a small gathering reported', 'Zone 2, San Diego', 44, 'pending', '2025-11-16 03:30:00', NULL, NULL, NULL),
(195, 7, 'traffic', 'Brief slowdown caused by a stalled tricycle on the road', 'Main Road, San Diego', 44, 'pending', '2025-11-16 03:45:00', NULL, NULL, NULL),
(196, 7, 'accident', 'Minor slip involving a vendor carrying goods, treated at the scene', 'Sitio Proper, San Diego', 44, 'resolved', '2025-11-16 04:00:00', '2025-11-16 04:25:00', NULL, NULL),
(197, 7, 'disturbance', 'Occasional barking from stray dogs caused concern among residents', 'Purok 3, San Diego', 44, 'pending', '2025-11-16 04:15:00', NULL, NULL, NULL),
(198, 7, 'disturbance', 'Residents reported recurring loud arguments from a nearby household', 'Purok 1, San Francisco', 45, 'pending', '2025-11-16 04:35:00', NULL, NULL, NULL),
(199, 7, 'crime', 'Repeated reports of petty theft attempts near local stores', 'Zone 2, San Francisco', 45, 'in-progress', '2025-11-16 04:50:00', NULL, NULL, NULL),
(200, 7, 'traffic', 'Frequent tricycle congestion during late afternoon hours', 'Main Road, San Francisco', 45, 'pending', '2025-11-16 05:05:00', NULL, NULL, NULL),
(201, 8, 'medical', 'Resident experiencing recurring dizziness sought barangay medical assistance', 'Sitio Proper, San Francisco', 45, 'resolved', '2025-11-16 05:20:00', '2025-11-16 05:45:00', NULL, NULL),
(202, 8, 'disturbance', 'Recurring late-night shouting from a nearby residence reported', 'Purok 1, San Gregorio', 47, 'pending', '2025-11-16 06:00:00', NULL, NULL, NULL),
(203, 8, 'crime', 'Repeated cases of attempted theft involving bicycles and small items', 'Zone 2, San Gregorio', 47, 'in-progress', '2025-11-16 06:15:00', NULL, NULL, NULL),
(204, 8, 'traffic', 'Frequent minor traffic buildup caused by tricycles along the main road', 'Main Road, San Gregorio', 47, 'pending', '2025-11-16 06:30:00', NULL, NULL, NULL),
(205, 8, 'medical', 'Resident experiencing recurring headaches requested barangay responders', 'Sitio Proper, San Gregorio', 47, 'resolved', '2025-11-16 06:45:00', '2025-11-16 07:10:00', NULL, NULL),
(206, 8, 'medical', 'Resident reported mild chest discomfort and requested basic assistance', 'Purok 1, San Ignacio', 48, 'resolved', '2025-11-16 07:25:00', '2025-11-16 07:50:00', NULL, NULL),
(207, 8, 'disturbance', 'Occasional loud karaoke noise heard from a nearby household', 'Zone 2, San Ignacio', 48, 'pending', '2025-11-16 07:40:00', NULL, NULL, NULL),
(208, 8, 'disturbance', 'Recurring loud arguments reported between neighbors during late evenings', 'Purok 1, San Isidro', 49, 'pending', '2025-11-16 07:55:00', NULL, NULL, NULL),
(209, 8, 'crime', 'Repeated incidents of petty theft attempts around sari-sari stores', 'Zone 2, San Isidro', 49, 'in-progress', '2025-11-16 08:10:00', NULL, NULL, NULL),
(210, 8, 'traffic', 'Frequent minor traffic delays caused by tricycle congestion near the main road', 'Main Road, San Isidro', 49, 'pending', '2025-11-16 08:25:00', NULL, NULL, NULL),
(211, 8, 'disturbance', 'Recurring loud arguments between neighboring households reported weekly', 'Purok 1, San Joaquin', 50, 'pending', '2025-11-16 08:40:00', NULL, NULL, NULL),
(212, 8, 'crime', 'Repeated reports of petty theft attempts targeting outdoor items', 'Zone 2, San Joaquin', 50, 'in-progress', '2025-11-16 08:55:00', NULL, NULL, NULL),
(213, 8, 'traffic', 'Recurring tricycle congestion causing minor delays during peak hours', 'Main Road, San Joaquin', 50, 'pending', '2025-11-16 09:10:00', NULL, NULL, NULL),
(214, 8, 'fire', 'A major kitchen fire erupted inside a residence causing heavy smoke and interior damage', 'Purok 1, San Jose', 51, 'in-progress', '2025-11-16 09:25:00', NULL, NULL, NULL),
(215, 8, 'disturbance', 'Recurring loud arguments reported between households late at night', 'Purok 1, San Juan', 52, 'pending', '2025-11-16 09:40:00', NULL, NULL, NULL),
(216, 8, 'crime', 'Repeated reports of petty theft involving unattended bicycles and items', 'Zone 2, San Juan', 52, 'in-progress', '2025-11-16 09:55:00', NULL, NULL, NULL),
(217, 8, 'traffic', 'Frequent tricycle congestion causing short road delays during mornings', 'Main Road, San Juan', 52, 'pending', '2025-11-16 10:10:00', NULL, NULL, NULL),
(218, 8, 'medical', 'Resident experiencing recurring headaches requested barangay assistance', 'Sitio Proper, San Juan', 52, 'resolved', '2025-11-16 10:25:00', '2025-11-16 10:50:00', NULL, NULL),
(219, 8, 'disturbance', 'Multiple complaints about loud karaoke sessions late in the evening', 'Purok 3, San Juan', 52, 'pending', '2025-11-16 10:40:00', NULL, NULL, NULL),
(220, 8, 'crime', 'Recurring reports of suspicious loiterers near abandoned structures', 'Back Area, San Juan', 52, 'in-progress', '2025-11-16 10:55:00', NULL, NULL, NULL),
(221, 8, 'disturbance', 'Recurring noise complaints caused by late-night gatherings', 'Purok 1, San Lorenzo', 53, 'pending', '2025-11-16 11:10:00', NULL, NULL, NULL),
(222, 8, 'crime', 'Repeated reports of petty theft attempts near sari-sari stores', 'Zone 2, San Lorenzo', 53, 'in-progress', '2025-11-16 11:25:00', NULL, NULL, NULL),
(223, 8, 'traffic', 'Frequent tricycle buildup causing minor delays during peak hours', 'Main Road, San Lorenzo', 53, 'pending', '2025-11-16 11:40:00', NULL, NULL, NULL),
(224, 8, 'medical', 'Resident reported mild dizziness while doing errands outdoors', 'Purok 1, San Lucas 1', 54, 'resolved', '2025-11-16 11:55:00', '2025-11-16 12:20:00', NULL, NULL),
(225, 8, 'disturbance', 'Occasional loud music from a nearby home reported by neighbors', 'Zone 2, San Lucas 1', 54, 'pending', '2025-11-16 12:10:00', NULL, NULL, NULL),
(226, 8, 'disturbance', 'Recurring loud arguments reported between households late at night', 'Purok 1, San Marcos', 56, 'pending', '2025-11-16 12:25:00', NULL, NULL, NULL),
(227, 8, 'crime', 'Repeated incidents of attempted petty theft near small stores', 'Zone 2, San Marcos', 56, 'in-progress', '2025-11-16 12:40:00', NULL, NULL, NULL),
(228, 8, 'traffic', 'Frequent tricycle congestion causing short delays during morning hours', 'Main Road, San Marcos', 56, 'pending', '2025-11-16 12:55:00', NULL, NULL, NULL),
(229, 8, 'medical', 'Resident experiencing recurring headache episodes requested barangay checkup', 'Sitio Proper, San Marcos', 56, 'resolved', '2025-11-16 13:10:00', '2025-11-16 13:35:00', NULL, NULL),
(230, 8, 'disturbance', 'Repeated complaints about loud karaoke sessions during late evening', 'Purok 3, San Marcos', 56, 'pending', '2025-11-16 13:25:00', NULL, NULL, NULL),
(231, 8, 'fire', 'Major structural fire erupted in a residential area causing heavy damage', 'Purok 1, San Miguel', 58, 'in-progress', '2025-11-16 13:45:00', NULL, NULL, NULL),
(232, 8, 'accident', 'Severe collision involving two motorcycles resulting in multiple injuries', 'Main Road, San Miguel', 58, 'resolved', '2025-11-16 14:00:00', '2025-11-16 14:35:00', NULL, NULL),
(233, 8, 'crime', 'Serious break-in incident reported with high-value items stolen', 'Zone 2, San Miguel', 58, 'pending', '2025-11-16 14:15:00', NULL, NULL, NULL),
(234, 8, 'medical', 'Resident suffered a critical asthma attack requiring urgent responders', 'Sitio Proper, San Miguel', 58, 'resolved', '2025-11-16 14:30:00', '2025-11-16 15:00:00', NULL, NULL),
(235, 8, 'fire', 'Electrical fire caused heavy smoke and partial damage to a residence', 'Back Area, San Miguel', 58, 'resolved', '2025-11-16 15:45:00', '2025-11-16 16:15:00', NULL, NULL),
(236, 8, 'accident', 'Major motorcycle skid incident due to slippery intersection, multiple victims', 'Crossing Area, San Miguel', 58, 'in-progress', '2025-11-16 16:05:00', NULL, NULL, NULL),
(237, 8, 'medical', 'Resident experienced mild dizziness while doing errands outside and requested brief assistance', 'Purok 1, San Nicolas', 59, 'resolved', '2025-11-16 16:20:00', '2025-11-16 16:40:00', NULL, NULL),
(238, 8, 'disturbance', 'Recurring reports of loud late-night arguments between nearby households', 'Purok 1, San Pedro', 60, 'pending', '2025-11-16 16:55:00', NULL, NULL, NULL),
(239, 8, 'crime', 'Repeated petty theft attempts involving small outdoor items and bicycles', 'Zone 2, San Pedro', 60, 'in-progress', '2025-11-16 17:10:00', NULL, NULL, NULL),
(240, 8, 'traffic', 'Frequent tricycle congestion causing minor road delays during rush hours', 'Main Road, San Pedro', 60, 'pending', '2025-11-16 17:25:00', NULL, NULL, NULL),
(241, 8, 'medical', 'Resident experienced mild dizziness while walking outdoors and asked for assistance', 'Purok 1, San Rafael', 61, 'resolved', '2025-11-16 17:40:00', '2025-11-16 18:05:00', NULL, NULL),
(242, 8, 'disturbance', 'Occasional loud laughter and noise from a neighborhood gathering reported', 'Zone 2, San Rafael', 61, 'pending', '2025-11-16 17:55:00', NULL, NULL, NULL),
(243, 8, 'traffic', 'Short traffic buildup due to a stalled tricycle blocking part of the road', 'Main Road, San Rafael', 61, 'pending', '2025-11-16 18:10:00', NULL, NULL, NULL),
(244, 8, 'accident', 'Minor bicycle slip incident involving a teenager, treated with first aid', 'Sitio Proper, San Rafael', 61, 'resolved', '2025-11-16 18:25:00', '2025-11-16 18:50:00', NULL, NULL),
(245, 8, 'disturbance', 'Occasional barking from neighborhood dogs reported', 'Purok 3, San Rafael', 61, 'pending', '2025-11-16 18:40:00', NULL, NULL, NULL),
(246, 8, 'medical', 'Elderly resident experienced mild fatigue and was assisted by barangay responders', 'Zone 1, San Rafael', 61, 'resolved', '2025-11-16 18:55:00', '2025-11-16 19:20:00', NULL, NULL),
(247, 8, 'disturbance', 'Recurring loud arguments reported between neighboring households during late hours', 'Purok 1, San Roque', 62, 'pending', '2025-11-16 19:35:00', NULL, NULL, NULL),
(248, 8, 'crime', 'Repeated reports of petty theft involving unattended items around residential stores', 'Zone 2, San Roque', 62, 'in-progress', '2025-11-16 19:50:00', NULL, NULL, NULL),
(249, 8, 'traffic', 'Frequent minor traffic delays due to tricycle buildup at peak times', 'Main Road, San Roque', 62, 'pending', '2025-11-16 20:05:00', NULL, NULL, NULL),
(250, 8, 'medical', 'Resident experiencing recurring dizziness requested barangay medical support', 'Sitio Proper, San Roque', 62, 'resolved', '2025-11-16 20:20:00', '2025-11-16 20:45:00', NULL, NULL),
(251, 8, 'medical', 'Resident reported mild dizziness while buying supplies at a sari-sari store', 'Purok 1, San Vicente', 63, 'resolved', '2025-11-16 21:00:00', '2025-11-16 21:25:00', NULL, NULL),
(252, 8, 'disturbance', 'Occasional loud music from a nearby household reported by neighbors', 'Zone 2, San Vicente', 63, 'pending', '2025-11-16 21:15:00', NULL, NULL, NULL),
(253, 8, 'traffic', 'Brief slowdown caused by a tricycle unloading passengers on the roadside', 'Main Road, San Vicente', 63, 'pending', '2025-11-16 21:30:00', NULL, NULL, NULL),
(254, 8, 'disturbance', 'Recurring noise complaints due to late-night gatherings reported weekly', 'Purok 1, Santa Ana', 64, 'pending', '2025-11-16 21:45:00', NULL, NULL, NULL),
(255, 8, 'crime', 'Repeated petty theft attempts involving unattended bicycles and items', 'Zone 2, Santa Ana', 64, 'in-progress', '2025-11-16 22:00:00', NULL, NULL, NULL),
(256, 8, 'traffic', 'Frequent tricycle congestion causing short delays during morning hours', 'Main Road, Santa Ana', 64, 'pending', '2025-11-16 22:15:00', NULL, NULL, NULL),
(257, 8, 'medical', 'Resident experiencing recurring dizziness requested barangay assistance', 'Sitio Proper, Santa Ana', 64, 'resolved', '2025-11-16 22:30:00', '2025-11-16 22:55:00', NULL, NULL),
(258, 8, 'disturbance', 'Multiple complaints about loud karaoke sessions every weekend night', 'Purok 3, Santa Ana', 64, 'pending', '2025-11-16 22:45:00', NULL, NULL, NULL),
(259, 8, 'crime', 'Recurring reports of suspicious loiterers near residential streets', 'Back Area, Santa Ana', 64, 'in-progress', '2025-11-16 23:00:00', NULL, NULL, NULL),
(260, 8, 'traffic', 'Repeated minor road obstructions caused by parked delivery vehicles', 'Market Road, Santa Ana', 64, 'pending', '2025-11-16 23:15:00', NULL, NULL, NULL),
(261, 8, 'medical', 'Elderly resident experiencing recurring hypertension symptoms assisted by responders', 'Zone 1, Santa Ana', 64, 'resolved', '2025-11-16 23:30:00', '2025-11-16 23:55:00', NULL, NULL),
(262, 8, 'disturbance', 'Recurring dog barking complaints affecting nearby households', 'Purok 4, Santa Ana', 64, 'pending', '2025-11-16 23:45:00', NULL, NULL, NULL),
(263, 8, 'crime', 'Multiple reports of vandalism occurring near the barangay gymnasium', 'Barangay Gym Area, Santa Ana', 64, 'in-progress', '2025-11-17 00:00:00', NULL, NULL, NULL),
(264, 8, 'disturbance', 'Recurring loud shouting between neighbors reported during late evenings', 'Purok 1, Santa Catalina', 65, 'pending', '2025-11-17 00:15:00', NULL, NULL, NULL),
(265, 8, 'crime', 'Repeated reports of petty theft involving unattended household items', 'Zone 2, Santa Catalina', 65, 'in-progress', '2025-11-17 00:30:00', NULL, NULL, NULL),
(266, 8, 'traffic', 'Frequent tricycle congestion causing minor delays during morning hours', 'Main Road, Santa Catalina', 65, 'pending', '2025-11-17 00:45:00', NULL, NULL, NULL),
(267, 8, 'medical', 'Resident experiencing recurring headaches requested barangay responders', 'Sitio Proper, Santa Catalina', 65, 'resolved', '2025-11-17 01:00:00', '2025-11-17 01:25:00', NULL, NULL),
(268, 8, 'disturbance', 'Repeated loud karaoke sessions reported on weekend nights', 'Purok 3, Santa Catalina', 65, 'pending', '2025-11-17 01:15:00', NULL, NULL, NULL),
(269, 8, 'crime', 'Recurring reports of loiterers around abandoned houses at night', 'Back Area, Santa Catalina', 65, 'in-progress', '2025-11-17 01:30:00', NULL, NULL, NULL),
(270, 8, 'traffic', 'Repeated minor obstructions caused by parked delivery vans', 'Market Road, Santa Catalina', 65, 'pending', '2025-11-17 01:45:00', NULL, NULL, NULL),
(271, 9, 'medical', 'Elderly resident experiencing recurring dizziness received barangay medical assistance', 'Zone 1, Santa Catalina', 65, 'resolved', '2025-11-17 02:00:00', '2025-11-17 02:25:00', NULL, NULL),
(272, 9, 'disturbance', 'Recurring noise complaints caused by late-night gatherings in nearby homes', 'Purok 1, Santa Cruz', 66, 'pending', '2025-11-17 02:40:00', NULL, NULL, NULL),
(273, 9, 'crime', 'Repeated reports of petty theft attempts targeting outdoor items', 'Zone 2, Santa Cruz', 66, 'in-progress', '2025-11-17 02:55:00', NULL, NULL, NULL),
(274, 9, 'traffic', 'Frequent slowdowns caused by tricycle congestion during peak hours', 'Main Road, Santa Cruz', 66, 'pending', '2025-11-17 03:10:00', NULL, NULL, NULL),
(275, 9, 'medical', 'Resident experiencing recurring dizziness requested barangay medical assistance', 'Sitio Proper, Santa Cruz', 66, 'resolved', '2025-11-17 03:25:00', '2025-11-17 03:50:00', NULL, NULL),
(276, 9, 'medical', 'Resident felt mild dizziness while walking near the plaza and requested quick assistance', 'Purok 1, Santa Elena', 67, 'resolved', '2025-11-17 04:05:00', '2025-11-17 04:30:00', NULL, NULL),
(277, 9, 'disturbance', 'Occasional loud singing from a nearby home during afternoon hours', 'Zone 2, Santa Elena', 67, 'pending', '2025-11-17 04:20:00', NULL, NULL, NULL),
(278, 9, 'traffic', 'Brief road slowdown caused by a tricycle unloading passengers', 'Main Road, Santa Elena', 67, 'pending', '2025-11-17 04:35:00', NULL, NULL, NULL),
(279, 9, 'medical', 'Resident experienced mild dizziness while doing outdoor chores and requested brief assistance', 'Purok 1, Santa Filomena', 68, 'resolved', '2025-11-17 04:50:00', '2025-11-17 05:15:00', NULL, NULL),
(280, 9, 'disturbance', 'Occasional loud conversations from a nearby residence reported by neighbors', 'Zone 2, Santa Filomena', 68, 'pending', '2025-11-17 05:05:00', NULL, NULL, NULL),
(281, 9, 'traffic', 'Short traffic slowdown caused by a tricycle stopping in the middle of the road', 'Main Road, Santa Filomena', 68, 'pending', '2025-11-17 05:20:00', NULL, NULL, NULL),
(282, 9, 'accident', 'Minor slip incident involving a vendor carrying goods, treated on-site', 'Sitio Proper, Santa Filomena', 68, 'resolved', '2025-11-17 05:35:00', '2025-11-17 06:00:00', NULL, NULL);
INSERT INTO `incidents` (`incident_id`, `user_id`, `incident_type`, `description`, `location`, `barangay_id`, `status`, `submitted_at`, `resolved_at`, `archived_at`, `archived_by`) VALUES
(283, 9, 'disturbance', 'Occasional dog barking reported from a nearby residence', 'Purok 3, Santa Filomena', 68, 'pending', '2025-11-17 05:50:00', NULL, NULL, NULL),
(284, 9, 'medical', 'Child experienced mild fever and was checked by barangay responders', 'Zone 1, Santa Filomena', 68, 'resolved', '2025-11-17 06:05:00', '2025-11-17 06:30:00', NULL, NULL),
(285, 9, 'disturbance', 'Neighbors reported occasional loud karaoke during early evening hours', 'Purok 4, Santa Filomena', 68, 'pending', '2025-11-17 06:20:00', NULL, NULL, NULL),
(286, 9, 'medical', 'Resident experienced mild dizziness and asked for quick responder support', 'Purok 1, Santa Maria', 70, 'resolved', '2025-11-17 06:40:00', '2025-11-17 07:05:00', NULL, NULL),
(287, 9, 'disturbance', 'Occasional loud music during weekends reported by nearby households', 'Zone 2, Santa Maria', 70, 'pending', '2025-11-17 06:55:00', NULL, NULL, NULL),
(288, 9, 'traffic', 'Temporary road slowdown due to a tricycle unloading goods', 'Main Road, Santa Maria', 70, 'pending', '2025-11-17 07:10:00', NULL, NULL, NULL),
(289, 9, 'accident', 'Minor fall incident involving a pedestrian near a wet sidewalk, treated onsite', 'Sitio Proper, Santa Maria', 70, 'resolved', '2025-11-17 07:25:00', '2025-11-17 07:55:00', NULL, NULL),
(290, 9, 'disturbance', 'Occasional barking from stray dogs causing brief noise complaints', 'Purok 3, Santa Maria', 70, 'pending', '2025-11-17 07:40:00', NULL, NULL, NULL),
(291, 9, 'medical', 'Child experienced mild fever and was assisted by barangay responders', 'Zone 1, Santa Maria', 70, 'resolved', '2025-11-17 07:55:00', '2025-11-17 08:20:00', NULL, NULL),
(292, 9, 'disturbance', 'Neighbors reported occasional shouting from a nearby residence', 'Purok 4, Santa Maria', 70, 'pending', '2025-11-17 08:10:00', NULL, NULL, NULL),
(293, 9, 'disturbance', 'Recurring loud arguments between residents reported several times this month', 'Purok 1, Santa Maria Magdalena', 71, 'pending', '2025-11-17 08:25:00', NULL, NULL, NULL),
(294, 9, 'crime', 'Repeated reports of petty theft attempts around small neighborhood stores', 'Zone 2, Santa Maria Magdalena', 71, 'in-progress', '2025-11-17 08:40:00', NULL, NULL, NULL),
(295, 9, 'traffic', 'Frequent minor road congestion caused by tricycles stopping along the narrow path', 'Main Road, Santa Maria Magdalena', 71, 'pending', '2025-11-17 08:55:00', NULL, NULL, NULL),
(296, 9, 'disturbance', 'Recurring loud shouting between neighbors reported late at night', 'Purok 1, Santa Monica', 72, 'pending', '2025-11-17 09:10:00', NULL, NULL, NULL),
(297, 9, 'crime', 'Repeated complaints of petty theft attempts targeting bicycles and small items', 'Zone 2, Santa Monica', 72, 'in-progress', '2025-11-17 09:25:00', NULL, NULL, NULL),
(298, 9, 'traffic', 'Frequent minor road congestion caused by tricycles stopping along the roadway', 'Main Road, Santa Monica', 72, 'pending', '2025-11-17 09:40:00', NULL, NULL, NULL),
(299, 9, 'disturbance', 'Recurring noise complaints from late-night gatherings reported frequently', 'Purok 1, Santa Veronica', 73, 'pending', '2025-11-17 09:55:00', NULL, NULL, NULL),
(300, 9, 'crime', 'Repeated petty theft attempts involving unattended belongings near residential stores', 'Zone 2, Santa Veronica', 73, 'in-progress', '2025-11-17 10:10:00', NULL, NULL, NULL),
(301, 9, 'traffic', 'Frequent tricycle congestion causing delays during afternoon rush hours', 'Main Road, Santa Veronica', 73, 'pending', '2025-11-17 10:25:00', NULL, NULL, NULL),
(302, 9, 'medical', 'Resident experiencing recurring dizziness was assisted by barangay responders', 'Sitio Proper, Santa Veronica', 73, 'resolved', '2025-11-17 10:40:00', '2025-11-17 11:05:00', NULL, NULL),
(303, 9, 'disturbance', 'Multiple complaints of loud karaoke sessions occurring several times a week', 'Purok 3, Santa Veronica', 73, 'pending', '2025-11-17 10:55:00', NULL, NULL, NULL),
(304, 9, 'medical', 'Resident reported mild dizziness while walking home and requested assistance', 'Purok 1, Santiago II', 75, 'resolved', '2025-11-17 11:20:00', '2025-11-17 11:45:00', NULL, NULL),
(305, 9, 'disturbance', 'Occasional loud conversations from a nearby household reported', 'Zone 2, Santiago II', 75, 'pending', '2025-11-17 11:35:00', NULL, NULL, NULL),
(306, 9, 'traffic', 'Short traffic delay due to a tricycle unloading passengers', 'Main Road, Santiago II', 75, 'pending', '2025-11-17 11:50:00', NULL, NULL, NULL),
(307, 9, 'accident', 'Minor slip incident involving a pedestrian near a grocery store, treated onsite', 'Sitio Proper, Santiago II', 75, 'resolved', '2025-11-17 12:05:00', '2025-11-17 12:30:00', NULL, NULL),
(308, 9, 'disturbance', 'Occasional barking from stray dogs reported by nearby residents', 'Purok 3, Santiago II', 75, 'pending', '2025-11-17 12:20:00', NULL, NULL, NULL),
(309, 9, 'medical', 'Child experienced mild fever and was checked by barangay responders', 'Zone 1, Santiago II', 75, 'resolved', '2025-11-17 12:35:00', '2025-11-17 12:55:00', NULL, NULL),
(310, 9, 'disturbance', 'Neighbors reported occasional loud singing during afternoon hours', 'Purok 4, Santiago II', 75, 'pending', '2025-11-17 12:50:00', NULL, NULL, NULL),
(311, 9, 'traffic', 'Minor slowdown caused by a delivery tricycle briefly blocking the road', 'Crossing Area, Santiago II', 75, 'pending', '2025-11-17 13:05:00', NULL, NULL, NULL),
(312, 9, 'accident', 'Minor bicycle fall involving a child, treated with basic first aid', 'Plaza Area, Santiago II', 75, 'resolved', '2025-11-17 13:20:00', '2025-11-17 13:45:00', NULL, NULL),
(313, 9, 'disturbance', 'Occasional shouting heard between nearby households', 'Zone 3, Santiago II', 75, 'pending', '2025-11-17 13:35:00', NULL, NULL, NULL),
(314, 9, 'disturbance', 'Recurring loud shouting between nearby households reported multiple times this week', 'Purok 1, Santisimo Rosario', 76, 'pending', '2025-11-17 13:50:00', NULL, NULL, NULL),
(315, 9, 'crime', 'Repeated petty theft attempts targeting bicycles and outdoor items', 'Zone 2, Santisimo Rosario', 76, 'in-progress', '2025-11-17 14:05:00', NULL, NULL, NULL),
(316, 9, 'traffic', 'Frequent tricycle congestion caused temporary traffic delays during peak hours', 'Main Road, Santisimo Rosario', 76, 'pending', '2025-11-17 14:20:00', NULL, NULL, NULL),
(317, 9, 'medical', 'Resident experiencing recurring dizziness was assisted by barangay responders', 'Sitio Proper, Santisimo Rosario', 76, 'resolved', '2025-11-17 14:35:00', '2025-11-17 15:00:00', NULL, NULL),
(318, 9, 'disturbance', 'Multiple complaints about loud karaoke sessions occurring every weekend', 'Purok 3, Santisimo Rosario', 76, 'pending', '2025-11-17 14:50:00', NULL, NULL, NULL),
(319, 9, 'crime', 'Recurring reports of suspicious individuals roaming near dark alleys', 'Back Area, Santisimo Rosario', 76, 'in-progress', '2025-11-17 15:05:00', NULL, NULL, NULL),
(320, 9, 'disturbance', 'Recurring noise complaints caused by late-night conversations in nearby houses', 'Purok 1, Santo Angel', 77, 'pending', '2025-11-17 15:20:00', NULL, NULL, NULL),
(321, 9, 'medical', 'Resident felt mild dizziness while walking to the market and requested brief assistance', 'Purok 1, Santo Cristo', 78, 'resolved', '2025-11-17 15:35:00', '2025-11-17 16:00:00', NULL, NULL),
(322, 9, 'disturbance', 'Occasional loud music from a nearby home reported by neighbors', 'Zone 2, Santo Cristo', 78, 'pending', '2025-11-17 15:50:00', NULL, NULL, NULL),
(323, 9, 'disturbance', 'Recurring loud arguments between neighbors reported multiple times weekly', 'Purok 1, Santo Niño', 79, 'pending', '2025-11-17 16:15:00', NULL, NULL, NULL),
(324, 9, 'crime', 'Repeated petty theft attempts targeting bicycles and outdoor belongings', 'Zone 2, Santo Niño', 79, 'in-progress', '2025-11-17 16:30:00', NULL, NULL, NULL),
(325, 9, 'traffic', 'Frequent tricycle congestion causing short delays during morning hours', 'Main Road, Santo Niño', 79, 'pending', '2025-11-17 16:45:00', NULL, NULL, NULL),
(326, 9, 'medical', 'Resident experiencing recurring dizziness requested barangay responders', 'Sitio Proper, Santo Niño', 79, 'resolved', '2025-11-17 17:00:00', '2025-11-17 17:25:00', NULL, NULL),
(327, 9, 'disturbance', 'Repeated karaoke sessions causing noise complaints late at night', 'Purok 3, Santo Niño', 79, 'pending', '2025-11-17 17:15:00', NULL, NULL, NULL),
(328, 9, 'crime', 'Recurring reports of suspicious individuals roaming near dimly lit areas', 'Back Area, Santo Niño', 79, 'in-progress', '2025-11-17 17:30:00', NULL, NULL, NULL),
(329, 9, 'traffic', 'Minor traffic buildup caused by improperly parked motorcycles', 'Market Road, Santo Niño', 79, 'pending', '2025-11-17 17:45:00', NULL, NULL, NULL),
(330, 9, 'disturbance', 'Frequent complaints about barking dogs during late-night hours', 'Purok 4, Santo Niño', 79, 'pending', '2025-11-17 18:00:00', NULL, NULL, NULL),
(331, 9, 'medical', 'Elderly resident experiencing recurring hypertension episodes received assistance', 'Zone 1, Santo Niño', 79, 'resolved', '2025-11-17 18:15:00', '2025-11-17 18:40:00', NULL, NULL),
(332, 9, 'crime', 'Series of vandalism cases near abandoned structures reported repeatedly', 'Old Compound, Santo Niño', 79, 'in-progress', '2025-11-17 18:30:00', NULL, NULL, NULL),
(333, 9, 'medical', 'Resident experienced mild dizziness while walking home and requested brief assistance', 'Purok 1, Soledad', 80, 'resolved', '2025-11-17 18:50:00', '2025-11-17 19:10:00', NULL, NULL),
(334, 9, 'disturbance', 'Occasional loud voices from a neighbor’s gathering reported', 'Zone 2, Soledad', 80, 'pending', '2025-11-17 19:05:00', NULL, NULL, NULL),
(335, 9, 'traffic', 'Short traffic slowdown caused by a tricycle stopping in the middle of the road', 'Main Road, Soledad', 80, 'pending', '2025-11-17 19:20:00', NULL, NULL, NULL),
(336, 9, 'accident', 'Minor slip incident involving a pedestrian near a wet area; treated onsite', 'Sitio Proper, Soledad', 80, 'resolved', '2025-11-17 19:35:00', '2025-11-17 20:00:00', NULL, NULL),
(337, 9, 'disturbance', 'Occasional barking complaints from neighborhood dogs', 'Purok 3, Soledad', 80, 'pending', '2025-11-17 19:50:00', NULL, NULL, NULL),
(338, 9, 'medical', 'Child experienced mild fever and was checked by barangay responders', 'Zone 1, Soledad', 80, 'resolved', '2025-11-17 20:05:00', '2025-11-17 20:25:00', NULL, NULL),
(341, 20, 'infrastructure', 'nabagsakan ng semento', 'Dolores', NULL, '', '2025-12-05 21:25:18', NULL, NULL, NULL),
(342, 20, 'infrastructure', 'nabagsakan', 'Dolores', NULL, '', '2025-12-05 21:25:50', NULL, NULL, NULL),
(343, 20, 'waste-management', 'fkmkgb', 'Dolores', NULL, '', '2025-12-05 21:26:47', NULL, NULL, NULL),
(353, 20, 'infrastructure', 'vdbfbf', 'Bañagale, San Pablo, Laguna', 17, 'pending', '2025-12-05 21:57:39', NULL, NULL, NULL),
(354, 20, 'infrastructure', 'vdbfbf', 'Bañagale, San Pablo, Laguna', 17, 'pending', '2025-12-05 21:59:00', NULL, NULL, NULL),
(355, 20, 'infrastructure', 'nabagsakan', 'Bañagale, San Pablo, Laguna', 17, 'pending', '2025-12-05 22:01:10', NULL, NULL, NULL),
(356, 20, 'infaastructure', 'nabagsakan', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 11, 'pending', '2025-12-05 22:02:09', NULL, NULL, NULL),
(358, 20, 'road-hazard', 'loophole', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 11, 'pending', '2025-12-05 22:10:18', NULL, NULL, NULL),
(359, 20, 'road-hazard', 'loophole', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 11, 'pending', '2025-12-05 22:15:12', NULL, NULL, NULL),
(360, 20, 'road-hazard', 'loophole', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 11, 'pending', '2025-12-05 22:16:29', NULL, NULL, NULL),
(361, 20, 'road-hazard', 'nasagasaan', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 11, 'pending', '2025-12-05 22:19:15', NULL, NULL, NULL),
(362, 20, 'infastructure', 'nabagsakan ng semento', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 12, 'pending', '2025-12-05 22:21:11', NULL, NULL, NULL),
(363, 20, 'infastructure', 'nabagsakan ng semento', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 12, 'pending', '2025-12-05 22:22:44', NULL, NULL, NULL),
(364, 20, 'fire', 'nasunugan', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 12, 'pending', '2025-12-05 22:26:00', NULL, NULL, NULL),
(365, 20, 'road hazard', 'nasagasaan', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 12, 'pending', '2025-12-05 22:27:19', NULL, NULL, NULL),
(366, 20, 'accident', '\r\nnasagasaaan', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 12, 'rejected', '2025-12-05 22:28:23', NULL, NULL, NULL),
(367, 20, 'accident', 'nasagasaaan', 'Ramon Cruz, Sr., General Mariano Alvarez, Cavite, Calabarzon, 4117, Philippines', 12, 'resolved', '2025-12-05 22:29:09', NULL, NULL, NULL),
(368, 20, 'road-hazard', 'loophole on the road', 'San Antonio 1', 38, 'resolved', '2025-12-05 22:31:37', NULL, NULL, NULL),
(369, 25, 'Noise', 'noise in the neighbor', 'Silangan I, Rosario, Cavite, Calabarzon, 4106, Philippines', 37, 'resolved', '2025-12-07 06:32:49', NULL, NULL, NULL),
(370, 25, 'road-hazard', 'nabangga sa purok 1, gawa ng aso', 'purok.1', 37, 'pending', '2025-12-09 06:21:34', NULL, NULL, NULL),
(371, 25, 'road-hazard', 'naka bangga ng aso si renz', 'Dolores', 37, 'pending', '2025-12-11 02:11:28', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incident_files`
--

CREATE TABLE `incident_files` (
  `incident_files_id` int(11) NOT NULL,
  `incident_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_files`
--

INSERT INTO `incident_files` (`incident_files_id`, `incident_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 2, 'logo.png', 'uploads/incidents/6917021a75801_logo.png', '2025-11-14 10:19:06'),
(2, 3, 'logo.png', 'uploads/incidents/69171081bf846_logo.png', '2025-11-14 11:20:33'),
(3, 4, 'bas_Screenshot 2025-09-05 153854.png', 'uploads/incidents/6917f5f3d2cc1_bas_Screenshot 2025-09-05 153854.png', '2025-11-15 03:39:31'),
(4, 5, 'photo1.jpg', 'uploads/incidents/69180001_photo1.jpg', '2025-11-14 02:25:00'),
(5, 6, 'scene.png', 'uploads/incidents/69180002_scene.png', '2025-11-14 02:30:12'),
(6, 7, 'evidence1.jpg', 'uploads/incidents/69180003_evidence1.jpg', '2025-11-14 02:35:45'),
(7, 8, 'report.pdf', 'uploads/incidents/69180004_report.pdf', '2025-11-14 02:40:22'),
(8, 9, 'photo2.jpg', 'uploads/incidents/69180005_photo2.jpg', '2025-11-14 02:45:10'),
(9, 10, 'damage.png', 'uploads/incidents/69180006_damage.png', '2025-11-14 02:50:33'),
(10, 11, 'scene2.jpg', 'uploads/incidents/69180007_scene2.jpg', '2025-11-14 02:55:05'),
(11, 12, 'witness.mp4', 'uploads/incidents/69180008_witness.mp4', '2025-11-14 03:00:20'),
(12, 13, 'photo3.jpg', 'uploads/incidents/69180009_photo3.jpg', '2025-11-14 03:05:42'),
(13, 14, 'damage_report.pdf', 'uploads/incidents/69180010_damage_report.pdf', '2025-11-14 03:10:15'),
(14, 15, 'video1.mp4', 'uploads/incidents/69180011_video1.mp4', '2025-11-14 03:15:33'),
(15, 16, 'scene3.png', 'uploads/incidents/69180012_scene3.png', '2025-11-14 03:20:48'),
(16, 17, 'photo4.jpg', 'uploads/incidents/69180013_photo4.jpg', '2025-11-14 03:25:12'),
(17, 18, 'report2.pdf', 'uploads/incidents/69180014_report2.pdf', '2025-11-14 03:30:01'),
(18, 19, 'video2.mp4', 'uploads/incidents/69180015_video2.mp4', '2025-11-14 03:35:22'),
(19, 20, 'scene4.jpg', 'uploads/incidents/69180016_scene4.jpg', '2025-11-14 03:40:55'),
(20, 21, 'photo5.png', 'uploads/incidents/69180017_photo5.png', '2025-11-14 03:45:30'),
(21, 22, 'evidence2.jpg', 'uploads/incidents/69180018_evidence2.jpg', '2025-11-14 03:50:10'),
(22, 23, 'damage2.png', 'uploads/incidents/69180019_damage2.png', '2025-11-14 03:55:42'),
(23, 24, 'scene5.jpg', 'uploads/incidents/69180020_scene5.jpg', '2025-11-14 04:00:25'),
(24, 25, 'report3.pdf', 'uploads/incidents/69180021_report3.pdf', '2025-11-14 04:05:17'),
(25, 26, 'photo6.jpg', 'uploads/incidents/69180022_photo6.jpg', '2025-11-14 04:10:40'),
(26, 27, 'video3.mp4', 'uploads/incidents/69180023_video3.mp4', '2025-11-14 04:15:05'),
(27, 28, 'evidence3.jpg', 'uploads/incidents/69180024_evidence3.jpg', '2025-11-14 04:20:33'),
(28, 29, 'scene6.png', 'uploads/incidents/69180025_scene6.png', '2025-11-14 04:25:18'),
(29, 30, 'photo7.jpg', 'uploads/incidents/69180026_photo7.jpg', '2025-11-14 04:30:00'),
(30, 31, 'report4.pdf', 'uploads/incidents/69180027_report4.pdf', '2025-11-14 04:35:42'),
(31, 32, 'video4.mp4', 'uploads/incidents/69180028_video4.mp4', '2025-11-14 04:40:15'),
(32, 33, 'damage3.png', 'uploads/incidents/69180029_damage3.png', '2025-11-14 04:45:50'),
(33, 34, 'scene7.jpg', 'uploads/incidents/69180030_scene7.jpg', '2025-11-14 04:50:22'),
(34, 35, 'photo8.jpg', 'uploads/incidents/69180031_photo8.jpg', '2025-11-14 04:55:12'),
(35, 36, 'report5.pdf', 'uploads/incidents/69180032_report5.pdf', '2025-11-14 05:00:05'),
(36, 37, 'video5.mp4', 'uploads/incidents/69180033_video5.mp4', '2025-11-14 05:05:33'),
(37, 38, 'scene8.png', 'uploads/incidents/69180034_scene8.png', '2025-11-14 05:10:15'),
(38, 39, 'photo9.jpg', 'uploads/incidents/69180035_photo9.jpg', '2025-11-14 05:15:42'),
(39, 40, 'damage4.png', 'uploads/incidents/69180036_damage4.png', '2025-11-14 05:20:05'),
(40, 41, 'report6.pdf', 'uploads/incidents/69180037_report6.pdf', '2025-11-14 05:25:33'),
(41, 42, 'scene9.jpg', 'uploads/incidents/69180038_scene9.jpg', '2025-11-14 05:30:10'),
(42, 43, 'video6.mp4', 'uploads/incidents/69180039_video6.mp4', '2025-11-14 05:35:22'),
(43, 44, 'photo10.jpg', 'uploads/incidents/69180040_photo10.jpg', '2025-11-14 05:40:01'),
(44, 45, 'report7.pdf', 'uploads/incidents/69180041_report7.pdf', '2025-11-14 05:45:42'),
(45, 46, 'damage5.png', 'uploads/incidents/69180042_damage5.png', '2025-11-14 05:50:15'),
(46, 47, 'scene10.jpg', 'uploads/incidents/69180043_scene10.jpg', '2025-11-14 05:55:33'),
(47, 48, 'photo11.jpg', 'uploads/incidents/69180044_photo11.jpg', '2025-11-14 06:00:05'),
(48, 49, 'report8.pdf', 'uploads/incidents/69180045_report8.pdf', '2025-11-14 06:05:18'),
(49, 50, 'video7.mp4', 'uploads/incidents/69180046_video7.mp4', '2025-11-14 06:10:33'),
(50, 51, 'scene11.png', 'uploads/incidents/69180047_scene11.png', '2025-11-14 06:15:50'),
(51, 101, 'b897c4d7-6013-40fb-b179-dc23749cf313.jpg', 'uploads/incidents/691ff9c510634_b897c4d7-6013-40fb-b179-dc23749cf313.jpg', '2025-11-21 05:33:57'),
(52, 102, 'b897c4d7-6013-40fb-b179-dc23749cf313.jpg', 'uploads/incidents/691ffa3cc3a2f_b897c4d7-6013-40fb-b179-dc23749cf313.jpg', '2025-11-21 05:35:56'),
(53, 103, '1 Login.png', 'uploads/incidents/69206f9e4968e_1 Login.png', '2025-11-21 13:56:46'),
(54, 104, 'User Profile.png', 'uploads/incidents/6920727a24594_User Profile.png', '2025-11-21 14:08:58'),
(55, 341, 'logo.png', 'uploads/incidents/69334dbef0ab7_logo.png', '2025-12-05 21:25:18'),
(56, 342, 'logo.png', 'uploads/incidents/69334dde21d17_logo.png', '2025-12-05 21:25:50'),
(57, 343, 'Screenshot 2025-06-16 151523.png', 'uploads/incidents/69334e170ca93_Screenshot 2025-06-16 151523.png', '2025-12-05 21:26:47'),
(58, 353, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/6933555315bbb_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 21:57:39'),
(59, 354, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/693355a493d7d_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 21:59:00'),
(60, 355, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/69335626e4ecc_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 22:01:10'),
(61, 356, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/693356616e65f_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 22:02:09'),
(62, 358, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/6933584a9bfbb_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 22:10:18'),
(63, 359, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/693359701b1b2_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 22:15:12'),
(64, 360, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/693359bd2abee_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 22:16:29'),
(65, 361, 'screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', 'uploads/incidents/69335a631ae03_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', '2025-12-05 22:19:15'),
(66, 362, 'church.jpeg', 'uploads/incidents/69335ad72a39c_church.jpeg', '2025-12-05 22:21:11'),
(67, 363, 'church.jpeg', 'uploads/incidents/69335b3473ced_church.jpeg', '2025-12-05 22:22:44'),
(68, 364, 'church.jpeg', 'uploads/incidents/69335bf89cfea_church.jpeg', '2025-12-05 22:26:00'),
(69, 365, 'church.jpeg', 'uploads/incidents/69335c4768542_church.jpeg', '2025-12-05 22:27:19'),
(70, 366, 'church.jpeg', 'uploads/incidents/69335c878875e_church.jpeg', '2025-12-05 22:28:23'),
(71, 367, 'church.jpeg', 'uploads/incidents/69335cb5d78e5_church.jpeg', '2025-12-05 22:29:09'),
(72, 368, 'Screenshot 2025-06-16 151523.png', 'uploads/incidents/69335d497245b_Screenshot 2025-06-16 151523.png', '2025-12-05 22:31:37'),
(73, 369, 'logowo1.png', 'uploads/incidents/69351f9123a09_logowo1.png', '2025-12-07 06:32:49'),
(74, 370, 'screencapture-lucid-app-lucidchart-362af484-dddc-4f4b-8eac-f5b765d3cbbd-edit-2025-12-09-07_45_32.png', 'uploads/incidents/6937bfee13290_screencapture-lucid-app-lucidchart-362af484-dddc-4f4b-8eac-f5b765d3cbbd-edit-2025-12-09-07_45_32.png', '2025-12-09 06:21:34'),
(75, 371, 'RENZ.png', 'uploads/incidents/693a28500b736_RENZ.png', '2025-12-11 02:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `system_backups`
--

CREATE TABLE `system_backups` (
  `backup_id` int(11) NOT NULL,
  `backup_path` varchar(500) NOT NULL,
  `backup_size` bigint(20) DEFAULT NULL,
  `backup_type` enum('manual','automatic','scheduled') DEFAULT 'manual',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('in_progress','completed','failed') DEFAULT 'in_progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `system_logs_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`system_logs_id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Login', 'User logged in successfully', '192.168.1.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-11-14 01:00:01'),
(2, 2, 'Login', 'User failed to login: wrong password', '192.168.1.3', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2025-11-14 01:05:22'),
(3, 3, 'Submit Incident', 'User submitted a new incident report', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-11-14 01:10:12'),
(4, 1, 'Update Profile', 'User updated profile picture', '192.168.1.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2025-11-14 01:15:45'),
(5, 2, 'Logout', 'User logged out successfully', '192.168.1.3', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2025-11-14 01:20:01'),
(6, 1, 'system_backup', 'Backup created: backups/backup_2025-12-08_10-34-51.sql', NULL, NULL, '2025-12-08 09:34:53'),
(7, 1, 'system_backup', 'Backup created: backups/backup_2025-12-08_13-36-03.sql', NULL, NULL, '2025-12-08 12:36:03'),
(8, 1, 'Feedback Submitted', 'Feedback ID: 1 | Category: praise | Subject: Excellent Response Time | Rating: 5 stars', NULL, NULL, '2025-12-08 22:40:46'),
(9, 2, 'Feedback Submitted', 'Feedback ID: 2 | Category: feature_request | Subject: Add Mobile App | Rating: 4 stars', NULL, NULL, '2025-12-08 22:40:46'),
(10, NULL, 'Feedback Submitted', 'Feedback ID: 3 | Category: bug_report | Subject: Map Not Loading Properly | Rating: 3 stars', NULL, NULL, '2025-12-08 22:40:46'),
(11, 3, 'Feedback Submitted', 'Feedback ID: 4 | Category: complaint | Subject: Delayed Response to Road Hazard | Rating: 2 stars', NULL, NULL, '2025-12-08 22:40:46'),
(12, NULL, 'Feedback Submitted', 'Feedback ID: 5 | Category: suggestion | Subject: Improve Notification System | Rating: 4 stars', NULL, NULL, '2025-12-08 22:40:46'),
(13, NULL, 'Feedback Submitted', 'Feedback ID: 6 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-08 23:34:09'),
(14, NULL, 'Feedback Submitted', 'Feedback ID: 7 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-08 23:36:20'),
(15, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-08 23:36:20'),
(16, NULL, 'Feedback Submitted', 'Feedback ID: 8 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-09 00:02:11'),
(17, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-09 00:02:11'),
(18, NULL, 'Feedback Submitted', 'Feedback ID: 9 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-09 00:02:21'),
(19, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-09 00:02:21'),
(20, NULL, 'Feedback Submitted', 'Feedback ID: 10 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-09 00:04:46'),
(21, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-09 00:04:46'),
(22, NULL, 'Feedback Submitted', 'Feedback ID: 11 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-09 00:05:38'),
(23, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-09 00:05:38'),
(24, NULL, 'Feedback Submitted', 'Feedback ID: 12 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-09 00:11:28'),
(25, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-09 00:11:28'),
(26, NULL, 'Feedback Submitted', 'Feedback ID: 13 | Category: general | Subject: Feedback | Rating: 3 stars', NULL, NULL, '2025-12-09 00:11:38'),
(27, NULL, 'Feedback Submitted', 'Feedback from: Manalo, Mary Jasmine Bolado - Feedback', '::1', NULL, '2025-12-09 00:11:38'),
(28, 1, 'system_backup', 'Backup created: backups/backup_2025-12-12_15-21-21.sql', NULL, NULL, '2025-12-12 14:21:22'),
(29, NULL, 'Feedback Submitted', 'Feedback ID: 14 | Category: general | Subject: font size | Rating: 3 stars', NULL, NULL, '2025-12-17 04:20:27'),
(30, NULL, 'Feedback Submitted', 'Feedback from: Brgy. Dolores - font size', '::1', NULL, '2025-12-17 04:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'default_language', 'en', 'Default system language', '2025-12-13 02:19:17'),
(2, 'default_font_size', '15', 'Default font size in pixels', '2025-12-16 22:27:37'),
(3, 'dark_mode_enabled', '0', 'Global dark mode setting', '2025-12-13 02:18:33'),
(4, 'maintenance_mode', '0', 'System maintenance mode', '2025-12-08 06:06:44'),
(5, 'backup_frequency', 'weekly', 'Automatic backup frequency', '2025-12-08 06:06:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `sex` enum('male','female','other') DEFAULT NULL,
  `age_group` varchar(10) DEFAULT NULL,
  `role` enum('admin','barangay_official') DEFAULT 'barangay_official',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_photo` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `contact_number`, `barangay_id`, `sex`, `age_group`, `role`, `created_at`, `id_photo`, `profile_picture`) VALUES
(1, 'Manalo, Mary Jasmine Bolado', 'blancamaja825@gmail.com', '$2y$10$zPbKHmEddtj5/5R6L5FDeOjgzywoDVgXMq/7.yS8DtESYZILYIkz2', '09773071679', 80, 'female', '18-25', 'admin', '2025-10-18 05:29:07', NULL, NULL),
(2, 'Manalo, Mary Jasmine Bolado', 'maryjasmineboladomanalo7@gmail.com', '$2y$10$jfclSRXp1F8PjEvAOx303unNl1Itj0.foWO4ygJwD6CpuqFXv/GTC', '09773071679', 80, 'male', '18-25', 'barangay_official', '2025-10-25 02:56:34', NULL, NULL),
(3, 'Ecal, Jomaica', 'natashasobelino@gmail.com', '$2y$10$Zjr/SDZ5zCIqCZJ6TP/fb.MEwMaALtsWMmcS8GBrS29Q54TRlXjZa', '09055171589', 80, 'female', '18-25', 'barangay_official', '2025-10-25 05:56:29', NULL, NULL),
(4, 'Placeholder User 4', 'placeholder4@example.com', '$2y$10$fakepasswordhashxxxxxxxxxx', '0000000000', 1, 'male', '18-25', 'barangay_official', '2025-11-29 09:15:27', NULL, NULL),
(5, 'Manalo, Mary Jasmine Bolado', 'majayjay0123@gmail.com', '$2y$10$0K.NrLGtnBvGHKqEMgRT5egeu7wvhXLMFs2/pAeeUktQW4aGjhc8O', '09773071679', 1, 'female', '18-25', 'barangay_official', '2025-11-14 23:46:20', NULL, NULL),
(6, 'Placeholder User 6', 'placeholder6@example.com', '$2y$10$fakepasswordhashxxxxxxxxxx', '0000000000', 1, 'female', '18-25', 'barangay_official', '2025-11-29 09:15:27', NULL, NULL),
(7, 'Marrry Manalo', 'irmamanalo7@gmail.com', '$2y$10$ydcxo8GMD9IqoHeJDW1hRu07yzBNyLLZFdoOoT5BaOAqG1NgFqFce', '977307168', 1, 'female', '26-35', 'barangay_official', '2025-11-21 13:53:56', NULL, NULL),
(8, 'Marrry Manalo', 'emamanalo7@gmail.com', '$2y$10$YOqKIRF2dKYCV1WFocuGKexHvMM83QWcjNWb4oX0baz2rGhFINNxm', '0977307168', 64, 'female', '18-25', 'barangay_official', '2025-11-21 14:07:06', NULL, NULL),
(9, 'Jomaica Ecal', 'jomaicaeca@gmail.com', '$2y$10$RcfHU.P6bvz3IFUMo3k.QugZ2a6W9d4.rnEw.AuoUiKrVArHLPHGu', '095602935522', 56, 'female', '18-25', 'barangay_official', '2025-11-22 06:42:42', NULL, NULL),
(20, 'Diana Gucela', 'dianagucela@gmail.com', '$2y$10$djDdG.ktyyaZy1EDTgkIVe7FCCPmTulA.qHxOB30RB2AsysJiXnIi', '09055171557', 10, 'female', '18-25', 'barangay_official', '2025-12-05 06:46:01', '69327fa9335cb_screencapture-localhost-Municipal-report-ADMinn-admin-announcements-php-2025-12-04-07_48_14.png', NULL),
(21, 'Brgy. Santa Maria', 'brgy.stamaria@gmail.com', '$2y$10$wOoLmPZr1qKChVVI7mNCtetybW2UMr/S5ai.P3icnEW1ylTxpt1GK', '09123456778', 1, 'male', '18-25', 'barangay_official', '2025-12-06 03:33:11', '6933a3f7d2048_chujjrch.jpeg', 'uploads/profile_pictures/profile_21_6934678648456.png'),
(22, 'Barangay Soledad', 'soledad@gmail.com', '$2y$10$3R1A6VDjjuSdPvkbnevGXe33.jFFeUgXqlpmLsvG5yOT.OhdAz8Ru', '09055171589', 80, 'male', '18-25', 'barangay_official', '2025-12-06 17:00:07', NULL, 'uploads/profile_pictures/profile_22_69356ce4ef2fb.jpg'),
(23, 'Brgy. Santa Isabel', 'santaisabel@gmail.com', '$2y$10$F0ud2tdvyMtXgSEgEV24hOEkrxyidiidh/lNyH4xjh3L0.IHo4UqG', '09055178589', 69, 'male', '18-25', 'barangay_official', '2025-12-06 17:05:02', NULL, 'uploads/profile_pictures/profile_23_693462cc89b93.png'),
(25, 'Barangay Dolores', 'brgy.dolores@gmail.com', '$2y$10$LNSrPRDC7Rc406dzefEe0OiOLQ8Z/yLX.CYgTwo0L/X.KcOeyT6CC', '09634569476', 37, 'male', '18-25', 'barangay_official', '2025-12-07 06:14:34', NULL, 'uploads/profile_pictures/profile_25_69377ca5d37fb.jpg'),
(27, 'Barangay San Mateo', 'brgy.sanmateo@gmail.com', '$2y$10$u0boB6nhWHgmA6X4ylEHk.9CrrqxXDMaENxvzQ12B6Z24gFlplMIm', '09634557476', 57, 'male', '18-25', 'barangay_official', '2025-12-08 12:41:57', NULL, 'uploads/profile_pictures/profile_temp_6936c795c178f_6936c795c1bcb.jpg'),
(28, 'Barangay Santa Maria', 'stamaria@gmail.com', '$2y$10$2NkigUmA8LqauPONaPyCpuLZkQrPhg2P1IqcqTx/xvhiiRHQJDKWC', '09634569477', 70, 'male', '18-25', 'barangay_official', '2025-12-09 05:46:24', NULL, 'uploads/profile_pictures/profile_temp_6937b7afefcf5_6937b7aff04c0.jpg'),
(29, 'Barangay Bagong Bayan', 'brgybagongbayan@gmail.con', '$2y$10$uB3qK6JLZzJWZH8e.o5H/.RU12O/pL1nXam1FriVlz6jXE/1p0X/e', '09123454542', 2, 'male', '18-25', 'barangay_official', '2025-12-09 06:38:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure for view `archived_items_summary`
--
DROP TABLE IF EXISTS `archived_items_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `archived_items_summary`  AS SELECT 'incidents' AS `item_type`, count(0) AS `count`, max(`incidents`.`archived_at`) AS `last_archived` FROM `incidents` WHERE `incidents`.`status` = 'archived'union all select 'announcements' AS `item_type`,count(0) AS `count`,max(`announcements`.`updated_at`) AS `last_archived` from `announcements` where `announcements`.`status` = 'archived' union all select 'feedback' AS `item_type`,count(0) AS `count`,max(`feedback`.`updated_at`) AS `last_archived` from `feedback` where `feedback`.`status` = 'archived'  ;

-- --------------------------------------------------------

--
-- Structure for view `feedback_statistics`
--
DROP TABLE IF EXISTS `feedback_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `feedback_statistics`  AS SELECT count(0) AS `total_feedback`, sum(case when `feedback`.`status` = 'pending' then 1 else 0 end) AS `pending_count`, sum(case when `feedback`.`status` = 'reviewed' then 1 else 0 end) AS `reviewed_count`, sum(case when `feedback`.`status` = 'resolved' then 1 else 0 end) AS `resolved_count`, sum(case when `feedback`.`status` = 'archived' then 1 else 0 end) AS `archived_count`, round(avg(nullif(`feedback`.`rating`,0)),2) AS `average_rating`, sum(case when `feedback`.`rating` >= 4 then 1 else 0 end) AS `positive_feedback`, sum(case when `feedback`.`rating` <= 2 and `feedback`.`rating` > 0 then 1 else 0 end) AS `negative_feedback`, sum(case when `feedback`.`category` = 'bug_report' then 1 else 0 end) AS `bug_reports`, sum(case when `feedback`.`category` = 'feature_request' then 1 else 0 end) AS `feature_requests`, sum(case when `feedback`.`category` = 'complaint' then 1 else 0 end) AS `complaints`, sum(case when `feedback`.`category` = 'praise' then 1 else 0 end) AS `praise_count`, concat(round(sum(case when `feedback`.`admin_response` is not null then 1 else 0 end) / count(0) * 100,1),'%') AS `response_rate`, round(avg(case when `feedback`.`responded_at` is not null then to_days(`feedback`.`responded_at`) - to_days(`feedback`.`submitted_at`) else NULL end),1) AS `avg_response_time_days` FROM `feedback` ;

-- --------------------------------------------------------

--
-- Structure for view `feedback_with_user_details`
--
DROP TABLE IF EXISTS `feedback_with_user_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `feedback_with_user_details`  AS SELECT `f`.`feedback_id` AS `feedback_id`, `f`.`user_id` AS `user_id`, `f`.`name` AS `name`, `f`.`email` AS `email`, `f`.`phone` AS `phone`, `f`.`category` AS `category`, `f`.`subject` AS `subject`, `f`.`message` AS `message`, `f`.`rating` AS `rating`, `f`.`status` AS `status`, `f`.`priority` AS `priority`, `f`.`admin_response` AS `admin_response`, `f`.`responded_at` AS `responded_at`, `f`.`submitted_at` AS `submitted_at`, `u`.`full_name` AS `user_full_name`, `u`.`barangay_id` AS `barangay_id`, `bs`.`barangay_name` AS `barangay_name`, `responder`.`full_name` AS `responded_by_name`, `responder`.`role` AS `responder_role` FROM (((`feedback` `f` left join `users` `u` on(`f`.`user_id` = `u`.`user_id`)) left join `barangay_stats` `bs` on(`u`.`barangay_id` = `bs`.`barangay_id`)) left join `users` `responder` on(`f`.`responded_by` = `responder`.`user_id`)) ORDER BY `f`.`submitted_at` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_resources`
--
ALTER TABLE `admin_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcements_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_published_at` (`published_at`);

--
-- Indexes for table `barangay_stats`
--
ALTER TABLE `barangay_stats`
  ADD PRIMARY KEY (`barangay_id`);

--
-- Indexes for table `cache_entries`
--
ALTER TABLE `cache_entries`
  ADD PRIMARY KEY (`cache_id`),
  ADD UNIQUE KEY `cache_key` (`cache_key`),
  ADD UNIQUE KEY `cache_key_2` (`cache_key`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `danger_zones`
--
ALTER TABLE `danger_zones`
  ADD PRIMARY KEY (`danger_zones_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_submitted` (`submitted_at`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `fk_feedback_responder` (`responded_by`),
  ADD KEY `idx_feedback_admin_query` (`status`,`category`,`submitted_at`),
  ADD KEY `idx_feedback_email` (`email`),
  ADD KEY `idx_feedback_user_history` (`user_id`,`submitted_at`);

--
-- Indexes for table `hotlines`
--
ALTER TABLE `hotlines`
  ADD PRIMARY KEY (`hotlines_id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `incidents_fk1` (`barangay_id`),
  ADD KEY `incidents_fk2` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_archived_at` (`archived_at`),
  ADD KEY `fk_incidents_archived_by_new` (`archived_by`);

--
-- Indexes for table `incident_files`
--
ALTER TABLE `incident_files`
  ADD PRIMARY KEY (`incident_files_id`),
  ADD KEY `incident_id` (`incident_id`);

--
-- Indexes for table `system_backups`
--
ALTER TABLE `system_backups`
  ADD PRIMARY KEY (`backup_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_backup_user` (`created_by`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`system_logs_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD UNIQUE KEY `setting_key_2` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_fk1` (`barangay_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_resources`
--
ALTER TABLE `admin_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcements_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cache_entries`
--
ALTER TABLE `cache_entries`
  MODIFY `cache_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `danger_zones`
--
ALTER TABLE `danger_zones`
  MODIFY `danger_zones_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hotlines`
--
ALTER TABLE `hotlines`
  MODIFY `hotlines_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=372;

--
-- AUTO_INCREMENT for table `incident_files`
--
ALTER TABLE `incident_files`
  MODIFY `incident_files_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `system_backups`
--
ALTER TABLE `system_backups`
  MODIFY `backup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `system_logs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `danger_zones`
--
ALTER TABLE `danger_zones`
  ADD CONSTRAINT `danger_zones_fk1` FOREIGN KEY (`danger_zones_id`) REFERENCES `barangay_stats` (`barangay_id`),
  ADD CONSTRAINT `danger_zonez_fk2` FOREIGN KEY (`danger_zones_id`) REFERENCES `incidents` (`incident_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_responder` FOREIGN KEY (`responded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `fk_incidents_archived_by` FOREIGN KEY (`archived_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_incidents_archived_by_new` FOREIGN KEY (`archived_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `incidents_fk1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay_stats` (`barangay_id`),
  ADD CONSTRAINT `incidents_fk2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `incident_files`
--
ALTER TABLE `incident_files`
  ADD CONSTRAINT `incident_files_fk1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`);

--
-- Constraints for table `system_backups`
--
ALTER TABLE `system_backups`
  ADD CONSTRAINT `fk_backup_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_fk1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay_stats` (`barangay_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
