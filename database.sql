CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  workspace_id VARCHAR(100) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  username VARCHAR(80) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Admin','Member') NOT NULL DEFAULT 'Member',
  perms JSON NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username),
  KEY idx_workspace (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS workspace_state (
  workspace_id VARCHAR(100) NOT NULL,
  data_json LONGTEXT NOT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO workspace_state (workspace_id, data_json, updated_by)
SELECT 'winnerhc-main', '{"settings":{"reminderEmail":"hr@winnerhc.com","reminderDays":[30,20,15,10]},"docs":[],"openings":[],"tasks":[],"candidates":[],"nextId":1000}', NULL
WHERE NOT EXISTS (SELECT 1 FROM workspace_state WHERE workspace_id='winnerhc-main');
