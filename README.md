# SMS-based Market Price Alert System for Farmers

This project is a web-based platform designed to empower Irish potato farmers in Rubanda District, Uganda, by providing them with timely and reliable market price information via SMS. The system addresses the critical information gap that leads to exploitation by middlemen, enabling farmers to make informed decisions, negotiate better prices, and ultimately improve their livelihoods.

This system is built using PHP, a MySQL database, and the Ego SMS API.

## Project Vision

The goal is to bridge the information gap in the agricultural value chain. By leveraging the high mobile phone penetration in Uganda, this system delivers real-time price alerts for Irish potatoes from key markets (e.g., Kabale, Mbarara, Kampala) directly to farmers' phones, enhancing their market participation and profitability.

## Core Features

*   **Admin Dashboard:** A secure panel for administrators to manage the system.
*   **Farmer Registration:** Admins can register farmers by adding their phone numbers and preferred language.
*   **Price Data Management:** Admins can input and update daily market prices for Irish potatoes from various regional markets.
*   **SMS Broadcasts:** A mechanism to send customized price alerts to all registered farmers. This can be triggered manually by an admin or run automatically on a schedule.
*   **Multi-language Support:** SMS templates can be created in different languages (e.g., English, Rukiga) to ensure the information is accessible to all farmers.
*   **Logging:** The system keeps a log of all sent SMS broadcasts for tracking and auditing.
*   **Data Export:** Export the list of registered farmers to CSV and PDF formats.
*   **Data Visualization:** A pie chart on the dashboard shows the distribution of farmers by language.
*   **Pagination:** The farmers list is paginated to ensure the dashboard remains fast and responsive.
*   **Automatic Broadcasts:** The system can automatically send out SMS alerts whenever a new price is added.

## System Architecture and Workflow

1.  **Data Collection:** An administrator logs into the admin panel and enters the latest Irish potato prices for different markets (e.g., Kampala, Mbarara).
2.  **Farmer Management:** The administrator registers farmers by adding their phone numbers and selecting their preferred language for receiving alerts.
3.  **SMS Dissemination:**
    *   **Manual Broadcast:** The admin can compose a message (or use a template) and click a "Send" button to broadcast the latest prices to all registered farmers instantly.
    *   **Automated Broadcast:** A scheduled script (cron job) runs automatically (e.g., every day at 4 PM), fetches the latest prices, formats the message, and sends it to all farmers.

## Prerequisites

*   **PHP** (version 8.0 or higher)
*   **Composer** for PHP dependency management
*   **XAMPP**: A local development environment that includes Apache (web server) and MariaDB (database).
*   An active **Ego SMS API account** with an API Key.
*   Server access to set up **Cron Jobs** for automated broadcasts.

## File Structure

This structure separates concerns (logic, presentation, configuration) and enhances security by exposing only the `public` directory to the web.

```
market-price-sms-system/
│
├── public/                 # Web root, accessible to users
│   ├── index.php           # Main entry point (router to admin login)
│   ├── admin/
│   │   ├── dashboard.php   # Admin dashboard (price entry, farmer management)
│   │   ├── login.php       # Handles admin login logic
│   │   └── broadcast.php   # Script to trigger manual SMS broadcast
│   └── assets/
│       └── css/
│           └── style.css   # Styles for the admin panel
│
├── src/
│   ├── Config.php          # DB and API credentials
│   ├── Database.php        # Database connection handler (using PDO)
│   ├── FarmerService.php   # Logic for managing farmers (add, list)
│   ├── PriceService.php    # Logic for managing prices (add, get latest)
│   └── SmsService.php      # Logic for sending bulk SMS via Ego SMS API
│
├── scripts/
│   └── cron_broadcast.php  # Cron job script for automated sending
│
├── templates/              # HTML templates/views for the admin panel
│   ├── admin/
│   │   ├── dashboard.phtml
│   │   └── login.phtml
│   └── partials/
│       ├── header.phtml
│       └── footer.phtml
│
├── vendor/                 # Composer dependencies (e.g., Guzzle)
│
├── database.sql            # SQL schema for creating necessary tables
├── .gitignore
├── composer.json
└── README.md
```

## Installation and Setup

**Step 1: Database Setup**

1.  Create a new MySQL database for the project.
2.  Import the table structure using the `database.sql` file. This file will contain `CREATE TABLE` statements for `farmers`, `prices`, and `sms_logs`.

**`database.sql` Example:**

```sql
CREATE TABLE `farmers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `names` VARCHAR(50) NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL UNIQUE,
  `preferred_language` VARCHAR(10) DEFAULT 'en',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `prices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `market_name` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `price_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `sms_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `message` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Step 2: Clone and Install Dependencies**

```bash
git clone <your-repository-url>
cd market-price-sms-system
composer install
```

**Step 3: Configure the System**

1.  Create a `src/Config.php` file.
2.  Open `src/Config.php` and fill in your database credentials and Ego SMS API Key.

```php
// src/Config.php
<?php
namespace App;

class Config {
    // Database Credentials
    public const DB_HOST = 'localhost';
    public const DB_NAME = 'market_price_db';
    public const DB_USER = 'your_db_user';
    public const DB_PASS = 'your_db_password';

    // Ego SMS API Credentials
    public const EGO_SMS_API_KEY = 'YOUR_EGO_SMS_API_KEY';
    public const EGO_SMS_SENDER_ID = 'MarketInfo';
    public const EGO_SMS_API_ENDPOINT = 'https://api.egosms.com/v1/sms/send/bulk'; // Use bulk endpoint
}
```

**Step 4: Set Up the Web Server**

Configure your web server (Apache/Nginx) to use `public/` as the document root. This is a crucial security measure to prevent direct access to your source code and configuration files.

**Step 5: Set Up the Cron Job**

To automate the SMS alerts, set up a cron job on your server to execute the `cron_broadcast.php` script at a regular interval (e.g., daily).

```bash
# Example cron job to run every day at 4:00 PM
0 16 * * * /usr/bin/php /path/to/your/project/scripts/cron_broadcast.php
```

## How It Works: Code Implementation Guide

### 1. Admin Dashboard (`public/admin/dashboard.php` & `templates/admin/dashboard.phtml`)

This is the main control center. It will have two primary sections:
*   **Price Entry Form:** A form to submit the price for a specific market (e.g., a dropdown for Market and a text field for Price). This form will post to a script that uses `PriceService.php` to save the data.
*   **Farmer Management:** A form to add a new farmer's phone number and preferred language. This will use `FarmerService.php`. It will also list all currently registered farmers.

### 2. The Services (`src/` directory)

*   **`FarmerService.php`:** Contains methods like `addFarmer(string $phone, string $lang)` and `getAllFarmers()`. These methods will interact with the `farmers` table in the database.
*   **`PriceService.php`:** Contains methods like `addPrice(string $market, float $price)` and `getLatestPrices()`. The `getLatestPrices()` method will be crucial for composing the SMS message.
*   **`SmsService.php`:** This class will be adapted for bulk sending. The Ego SMS API likely has a different endpoint or payload structure for sending messages to multiple recipients in a single API call. The `sendBulk(array $recipients, string $message)` method would be ideal.

### 3. The Broadcast Script (`scripts/cron_broadcast.php`)

This script is the engine of the system. It will:
1.  Instantiate `PriceService` and call `getLatestPrices()` to get the data.
2.  Format the price data into a human-readable string. Example: `Irish Potato Prices: KLA - 1500/kg, MBA - 1200/kg.`
3.  Instantiate `FarmerService` and call `getAllFarmers()` to get a list of all phone numbers.
4.  Instantiate `SmsService` and call the bulk sending method with the list of numbers and the formatted message.
5.  Log the result of the broadcast (success or failure) into the `sms_logs` table.

This structured approach provides a robust and scalable foundation for the project, directly aligning with the objectives laid out in the proposal.