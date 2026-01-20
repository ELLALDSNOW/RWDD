-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2025 at 05:13 AM
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
-- Database: `task_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `organization_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `org_password` varchar(128) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logo` longblob DEFAULT NULL,
  `priority` enum('Heavy','Light','Medium','') NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`organization_id`, `name`, `description`, `org_password`, `created_at`, `updated_at`, `logo`, `priority`, `created_by`) VALUES
(2, 'Marketing Team', 'Internal marketing department', NULL, '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, 'Heavy', 2),
(8, 'meow', 'gggggggt', NULL, '2025-10-30 05:02:45', '2025-11-01 06:43:02', NULL, 'Heavy', 7),
(13, 'HEHE', 'oooooo', NULL, '2025-10-31 19:33:06', '2025-11-01 08:39:38', NULL, 'Heavy', 6),
(19, 'fff', 'ff', NULL, '2025-11-01 06:44:48', '2025-11-01 06:44:48', NULL, 'Heavy', 7);

-- --------------------------------------------------------

--
-- Table structure for table `organization_user`
--

CREATE TABLE `organization_user` (
  `organization_user_id` int(10) UNSIGNED NOT NULL,
  `organization_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_in_org` enum('owner','admin','member') DEFAULT 'member',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organization_user`
--

INSERT INTO `organization_user` (`organization_user_id`, `organization_id`, `user_id`, `role_in_org`, `created_at`) VALUES
(4, 2, 2, 'owner', '2025-10-29 21:35:09'),
(5, 2, 1, 'member', '2025-10-29 21:35:09'),
(12, 8, 7, 'owner', '2025-10-30 05:02:45'),
(17, 13, 6, 'owner', '2025-10-31 19:33:06'),
(23, 19, 7, 'owner', '2025-11-01 06:44:48');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` enum('planned','active','completed','archived') DEFAULT 'planned',
  `created_time` datetime DEFAULT current_timestamp(),
  `updated_time` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `due_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `visibility` enum('private','public') DEFAULT 'private',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `organization_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`project_id`, `name`, `description`, `status`, `created_time`, `updated_time`, `due_date`, `completion_date`, `priority`, `visibility`, `user_id`, `organization_id`) VALUES
(3, 'Q4 Marketing', 'Year-end marketing campaign', 'active', '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, NULL, 'high', 'private', 2, 2),
(4, 'Admin Admin', 'gggggg', 'planned', '2025-10-30 05:02:54', '2025-10-30 05:02:54', NULL, NULL, '', 'private', 7, 8),
(5, 'meow', 'dd', 'planned', '2025-10-30 05:04:04', '2025-10-30 05:04:04', NULL, NULL, '', 'private', 7, 8),
(6, 'd', 'ddd', 'planned', '2025-10-30 05:07:36', '2025-10-30 05:07:36', NULL, NULL, '', 'private', 7, 8),
(7, 'egegqegqee', 'gegwegwegweg', 'planned', '2025-10-30 09:34:48', '2025-10-30 09:34:48', NULL, NULL, '', 'private', 6, NULL),
(11, 'd', 'd', 'active', '2025-11-01 06:45:01', '2025-11-01 06:45:01', NULL, NULL, 'medium', 'private', 7, 19),
(12, 'c svsdvsdv', 'ddvdvsdv', 'active', '2025-11-01 06:56:07', '2025-11-01 06:56:07', '2025-11-28', NULL, 'medium', 'private', 7, 19),
(13, 'ccccccrrrr', 'ccccc', 'active', '2025-11-01 09:30:36', '2025-11-01 10:32:49', '2025-11-12', NULL, 'medium', 'private', 6, 13),
(14, 'k', 'r', 'active', '2025-11-01 09:41:27', '2025-11-01 10:56:52', '2025-12-31', NULL, 'medium', 'private', 6, 13);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(32) DEFAULT 'uninitiated',
  `priority` varchar(32) DEFAULT 'normal',
  `comments` text DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_assignee`
--

CREATE TABLE `task_assignee` (
  `task_assignee_id` int(10) UNSIGNED NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` enum('assignee','reviewer','observer') DEFAULT 'assignee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `todo`
--

CREATE TABLE `todo` (
  `todo_id` int(10) UNSIGNED NOT NULL,
  `todo_name` varchar(255) NOT NULL,
  `list_type` enum('personal','work','other') NOT NULL DEFAULT 'personal',
  `todo_data` longtext DEFAULT NULL,
  `created_time` datetime DEFAULT current_timestamp(),
  `last_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todo`
--

INSERT INTO `todo` (`todo_id`, `todo_name`, `list_type`, `todo_data`, `created_time`, `last_edited_date`) VALUES
(1, 'Review PRs', 'work', 'Check pending pull requests', '2025-10-29 21:35:09', '2025-10-29 21:35:09'),
(2, 'Team Meeting', 'work', 'Prepare agenda for tomorrow', '2025-10-29 21:35:09', '2025-10-29 21:35:09'),
(3, 'Grocery List', 'personal', 'Milk, Bread, Eggs', '2025-10-29 21:35:09', '2025-10-29 21:35:09');

-- --------------------------------------------------------

--
-- Table structure for table `todo_user`
--

CREATE TABLE `todo_user` (
  `todo_user_id` int(10) UNSIGNED NOT NULL,
  `todo_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todo_user`
--

INSERT INTO `todo_user` (`todo_user_id`, `todo_id`, `user_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email_address` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('admin','member','guest') DEFAULT 'member',
  `created_time` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `user_name`, `email_address`, `password_hash`, `user_type`, `created_time`, `last_login`) VALUES
(1, 'John Admin', 'john@example.com', 'dummy_hash_1', 'admin', '2025-10-29 21:35:09', '2025-10-30 04:04:57'),
(2, 'Jane Manager', 'jane@example.com', 'dummy_hash_2', 'member', '2025-10-29 21:35:09', NULL),
(3, 'Bob Developer', 'bob@example.com', 'dummy_hash_3', 'member', '2025-10-29 21:35:09', NULL),
(4, '@AleeiotDaGOat', 'idk@idk.com', 'hhhhhhh', 'member', '2025-10-30 00:31:56', NULL),
(5, 'Test User', 'q@q.com', 'password123', 'member', '2025-10-30 00:40:17', '2025-10-30 01:55:24'),
(6, 'iampoop', 'poop@gmail.com', 'poop', 'member', '2025-10-30 01:58:22', '2025-11-01 10:05:24'),
(7, 'hey', 'hey@hey.com', 'op', 'member', '2025-10-30 04:44:25', '2025-11-01 06:42:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_project`
--

CREATE TABLE `user_project` (
  `user_project_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_in_project` enum('owner','manager','contributor','viewer') DEFAULT 'contributor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`organization_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `organization_user`
--
ALTER TABLE `organization_user`
  ADD PRIMARY KEY (`organization_user_id`),
  ADD UNIQUE KEY `uk_org_user` (`organization_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `idx_project` (`project_id`);

--
-- Indexes for table `task_assignee`
--
ALTER TABLE `task_assignee`
  ADD PRIMARY KEY (`task_assignee_id`),
  ADD UNIQUE KEY `uk_task_user` (`task_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `todo`
--
ALTER TABLE `todo`
  ADD PRIMARY KEY (`todo_id`);

--
-- Indexes for table `todo_user`
--
ALTER TABLE `todo_user`
  ADD PRIMARY KEY (`todo_user_id`),
  ADD UNIQUE KEY `uk_todo_user` (`todo_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `user_project`
--
ALTER TABLE `user_project`
  ADD PRIMARY KEY (`user_project_id`),
  ADD UNIQUE KEY `uk_proj_user` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `organization`
--
ALTER TABLE `organization`
  MODIFY `organization_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `organization_user`
--
ALTER TABLE `organization_user`
  MODIFY `organization_user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_assignee`
--
ALTER TABLE `task_assignee`
  MODIFY `task_assignee_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `todo`
--
ALTER TABLE `todo`
  MODIFY `todo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `todo_user`
--
ALTER TABLE `todo_user`
  MODIFY `todo_user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_project`
--
ALTER TABLE `user_project`
  MODIFY `user_project_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `organization`
--
ALTER TABLE `organization`
  ADD CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user_account` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `organization_user`
--
ALTER TABLE `organization_user`
  ADD CONSTRAINT `organization_user_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE SET NULL;

--
-- Constraints for table `task_assignee`
--
ALTER TABLE `task_assignee`
  ADD CONSTRAINT `task_assignee_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_assignee_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `todo_user`
--
ALTER TABLE `todo_user`
  ADD CONSTRAINT `todo_user_ibfk_1` FOREIGN KEY (`todo_id`) REFERENCES `todo` (`todo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `todo_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_project`
--
ALTER TABLE `user_project`
  ADD CONSTRAINT `user_project_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_project_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
