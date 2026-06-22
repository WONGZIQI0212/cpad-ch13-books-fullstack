CREATE DATABASE IF NOT EXISTS books_api
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE books_api;

DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('member','admin') NOT NULL DEFAULT 'member',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(150) NOT NULL,
  year INT NULL,
  genre VARCHAR(80) NOT NULL DEFAULT 'Uncategorised',
  created_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_books_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actor_id INT NULL,
  action VARCHAR(50) NOT NULL,
  target VARCHAR(80) NULL,
  ip_address VARCHAR(45) NULL,
  detail VARCHAR(500) NULL,
  INDEX idx_action (action),
  INDEX idx_actor (actor_id)
) ENGINE=InnoDB;

INSERT INTO users (name, email, password_hash, role) VALUES
  ('Demo Admin', 'admin@books.test', '$2y$10$99x2xeucqFw.gltDN6zHj.m5WNyR99UwecQhCPIaez8GmRBPe23kK', 'admin'),
  ('Demo Member', 'member@books.test', '$2y$10$99x2xeucqFw.gltDN6zHj.m5WNyR99UwecQhCPIaez8GmRBPe23kK', 'member');

INSERT INTO books (title, author, year) VALUES
  ('Clean Code', 'Robert C. Martin', 2008),
  ('The Pragmatic Programmer', 'Andrew Hunt and David Thomas', 1999),
  ('Refactoring', 'Martin Fowler', 2018);

UPDATE books SET created_by = 1, genre = 'Software Engineering' WHERE id IN (1, 3);
UPDATE books SET created_by = 2, genre = 'Software Engineering' WHERE id = 2;
