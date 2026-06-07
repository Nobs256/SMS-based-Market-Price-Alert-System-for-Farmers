CREATE TABLE `farmers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `names` VARCHAR(50) NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `preferred_language` VARCHAR(10) DEFAULT 'en',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `prices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `market_name` VARCHAR(50) NOT NULL,
  `price_per_kg` DECIMAL(10, 2) NOT NULL,
  `price_per_sack` DECIMAL(10, 2) NOT NULL,
  `price_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `sms_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `message` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `category` ENUM('news', 'guideline', 'blog') DEFAULT 'blog',
  `author_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `admins`(`id`)
);

CREATE TABLE `listings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `farmer_id` INT NOT NULL,
  `variety` VARCHAR(100) NOT NULL,
  `quantity_available` DECIMAL(10, 2) NOT NULL,
  `unit` VARCHAR(20) DEFAULT 'Sack',
  `price_per_unit` DECIMAL(10, 2) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('available', 'sold_out') DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`farmer_id`) REFERENCES `farmers`(`id`) ON DELETE CASCADE
);

CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);