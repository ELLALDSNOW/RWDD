-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 02:38 PM
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



CREATE TABLE `organization` (
  `organization_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `org_password` varchar(128) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logo` longblob DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `organization` (`organization_id`, `name`, `description`, `org_password`, `created_at`, `updated_at`, `logo`, `created_by`) VALUES
(1, 'Tech Solutions Inc', 'Main software development organization', NULL, '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, 1),
(2, 'Marketing Team', 'Internal marketing department', NULL, '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, 2);



CREATE TABLE `organization_user` (
  `organization_user_id` int(10) UNSIGNED NOT NULL,
  `organization_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_in_org` enum('owner','admin','member') DEFAULT 'member',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `organization_user` (`organization_user_id`, `organization_id`, `user_id`, `role_in_org`, `created_at`) VALUES
(1, 1, 1, 'owner', '2025-10-29 21:35:09'),
(2, 1, 2, 'admin', '2025-10-29 21:35:09'),
(3, 1, 3, 'member', '2025-10-29 21:35:09'),
(4, 2, 2, 'owner', '2025-10-29 21:35:09'),
(5, 2, 1, 'member', '2025-10-29 21:35:09');


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


INSERT INTO `project` (`project_id`, `name`, `description`, `status`, `created_time`, `updated_time`, `due_date`, `completion_date`, `priority`, `visibility`, `user_id`, `organization_id`) VALUES
(1, 'Website Redesign', 'Revamp company website with modern UI', 'active', '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, NULL, 'high', 'private', 1, 1),
(2, 'Mobile App', 'Develop iOS and Android apps', 'planned', '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, NULL, 'medium', 'private', 2, 1),
(3, 'Q4 Marketing', 'Year-end marketing campaign', 'active', '2025-10-29 21:35:09', '2025-10-29 21:35:09', NULL, NULL, 'high', 'private', 2, 2);



CREATE TABLE `tasks` (
  `task_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('todo','in_progress','done','blocked') DEFAULT 'todo',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `comments` longtext DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `project_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `tasks` (`task_id`, `title`, `description`, `status`, `priority`, `comments`, `start_date`, `due_date`, `created_at`, `updated_at`, `project_id`) VALUES
(1, 'Design Homepage', 'Create mockups for new homepage', 'in_progress', 'high', NULL, NULL, NULL, '2025-10-29 21:35:09', '2025-10-29 21:35:09', 1),
(2, 'User Authentication', 'Implement login/register system', 'todo', 'high', NULL, NULL, NULL, '2025-10-29 21:35:09', '2025-10-29 21:35:09', 1),
(3, 'Mobile Responsive', 'Ensure site works on all devices', 'todo', 'medium', NULL, NULL, NULL, '2025-10-29 21:35:09', '2025-10-29 21:35:09', 1);


CREATE TABLE `task_assignee` (
  `task_assignee_id` int(10) UNSIGNED NOT NULL,
  `task_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` enum('assignee','reviewer','observer') DEFAULT 'assignee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `task_assignee` (`task_assignee_id`, `task_id`, `user_id`, `role`) VALUES
(1, 1, 2, 'assignee'),
(2, 1, 1, 'reviewer'),
(3, 2, 3, 'assignee'),
(4, 3, 2, 'assignee');



CREATE TABLE `todo` (
  `todo_id` int(10) UNSIGNED NOT NULL,
  `todo_name` varchar(255) NOT NULL,
  `list_type` enum('personal','work','other') NOT NULL DEFAULT 'personal',
  `todo_data` longtext DEFAULT NULL,
  `created_time` datetime DEFAULT current_timestamp(),
  `last_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `todo` (`todo_id`, `todo_name`, `list_type`, `todo_data`, `created_time`, `last_edited_date`) VALUES
(1, 'Review PRs', 'work', 'Check pending pull requests', '2025-10-29 21:35:09', '2025-10-29 21:35:09'),
(2, 'Team Meeting', 'work', 'Prepare agenda for tomorrow', '2025-10-29 21:35:09', '2025-10-29 21:35:09'),
(3, 'Grocery List', 'personal', 'Milk, Bread, Eggs', '2025-10-29 21:35:09', '2025-10-29 21:35:09');


CREATE TABLE `todo_user` (
  `todo_user_id` int(10) UNSIGNED NOT NULL,
  `todo_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `todo_user` (`todo_user_id`, `todo_id`, `user_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3);


CREATE TABLE `user_account` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email_address` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('admin','member','guest') DEFAULT 'member',
  `created_time` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `user_account` (`user_id`, `user_name`, `email_address`, `password_hash`, `user_type`, `created_time`, `last_login`) VALUES
(1, 'John Admin', 'john@example.com', 'dummy_hash_1', 'admin', '2025-10-29 21:35:09', NULL),
(2, 'Jane Manager', 'jane@example.com', 'dummy_hash_2', 'member', '2025-10-29 21:35:09', NULL),
(3, 'Bob Developer', 'bob@example.com', 'dummy_hash_3', 'member', '2025-10-29 21:35:09', NULL);


CREATE TABLE `user_project` (
  `user_project_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_in_project` enum('owner','manager','contributor','viewer') DEFAULT 'contributor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `user_project` (`user_project_id`, `project_id`, `user_id`, `role_in_project`) VALUES
(1, 1, 1, 'owner'),
(2, 1, 2, 'contributor'),
(3, 1, 3, 'contributor'),
(4, 2, 2, 'owner'),
(5, 2, 3, 'contributor');


ALTER TABLE `organization`
  ADD PRIMARY KEY (`organization_id`),
  ADD KEY `created_by` (`created_by`);


ALTER TABLE `organization_user`
  ADD PRIMARY KEY (`organization_user_id`),
  ADD UNIQUE KEY `uk_org_user` (`organization_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `organization_id` (`organization_id`);


ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`);


ALTER TABLE `task_assignee`
  ADD PRIMARY KEY (`task_assignee_id`),
  ADD UNIQUE KEY `uk_task_user` (`task_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `todo`
  ADD PRIMARY KEY (`todo_id`);


ALTER TABLE `todo_user`
  ADD PRIMARY KEY (`todo_user_id`),
  ADD UNIQUE KEY `uk_todo_user` (`todo_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);


ALTER TABLE `user_project`
  ADD PRIMARY KEY (`user_project_id`),
  ADD UNIQUE KEY `uk_proj_user` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);



ALTER TABLE `organization`
  MODIFY `organization_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `organization_user`
  MODIFY `organization_user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


ALTER TABLE `project`
  MODIFY `project_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `tasks`
  MODIFY `task_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `task_assignee`
  MODIFY `task_assignee_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `todo`
  MODIFY `todo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `todo_user`
  MODIFY `todo_user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `user_account`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `user_project`
  MODIFY `user_project_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;



ALTER TABLE `organization`
  ADD CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user_account` (`user_id`) ON DELETE SET NULL;


ALTER TABLE `organization_user`
  ADD CONSTRAINT `organization_user_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;


ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE SET NULL;


ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE;


ALTER TABLE `task_assignee`
  ADD CONSTRAINT `task_assignee_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_assignee_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;


ALTER TABLE `todo_user`
  ADD CONSTRAINT `todo_user_ibfk_1` FOREIGN KEY (`todo_id`) REFERENCES `todo` (`todo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `todo_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;


ALTER TABLE `user_project`
  ADD CONSTRAINT `user_project_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_project_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
