
CREATE DATABASE IF NOT EXISTS task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_manager;


CREATE TABLE user_account (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(100) NOT NULL,
  email_address VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('admin','member','guest') DEFAULT 'member',
  created_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE organization (
  organization_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(250),
  org_password VARCHAR(128),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  logo LONGBLOB,
  created_by INT UNSIGNED,
  FOREIGN KEY (created_by) REFERENCES user_account(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE organization_user (
  organization_user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role_in_org ENUM('owner','admin','member') DEFAULT 'member',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_org_user (organization_id, user_id),
  FOREIGN KEY (organization_id) REFERENCES organization(organization_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE project (
  project_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description VARCHAR(500),
  status ENUM('planned','active','completed','archived') DEFAULT 'planned',
  created_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  due_date DATE DEFAULT NULL,
  completion_date DATE DEFAULT NULL,
  priority ENUM('low','medium','high') DEFAULT 'medium',
  visibility ENUM('private','public') DEFAULT 'private',
  user_id INT UNSIGNED DEFAULT NULL,
  organization_id INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE SET NULL,
  FOREIGN KEY (organization_id) REFERENCES organization(organization_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE user_project (
  user_project_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role_in_project ENUM('owner','manager','contributor','viewer') DEFAULT 'contributor',
  UNIQUE KEY uk_proj_user (project_id, user_id),
  FOREIGN KEY (project_id) REFERENCES project(project_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE tasks (
  task_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  description TEXT,
  status ENUM('todo','in_progress','done','blocked') DEFAULT 'todo',
  priority ENUM('low','medium','high') DEFAULT 'medium',
  comments LONGTEXT,
  start_date DATE DEFAULT NULL,
  due_date DATE DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  project_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (project_id) REFERENCES project(project_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE task_assignee (
  task_assignee_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  task_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('assignee','reviewer','observer') DEFAULT 'assignee',
  UNIQUE KEY uk_task_user (task_id, user_id),
  FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE todo (
  todo_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  todo_name VARCHAR(255) NOT NULL,
  list_type ENUM('personal','work','other') NOT NULL DEFAULT 'personal',
  todo_data LONGTEXT,
  created_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_edited_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE todo_user (
  todo_user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  todo_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uk_todo_user (todo_id, user_id),
  FOREIGN KEY (todo_id) REFERENCES todo(todo_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
