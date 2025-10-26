<?php

// This script is designed to be run from the command line or via a cron job.

// Set the correct timezone
date_default_timezone_set('Africa/Kampala');

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\BroadcastService;

echo "Cron Job: Starting SMS broadcast process at " . date('Y-m-d H:i:s') . "\n";

// 1. Instantiate Services
$broadcastService = new BroadcastService();

// 2. Execute the broadcast
$result = $broadcastService->executeBroadcast();

// 3. Output the result to the cron log
if ($result['success']) {
    echo "Success: " . $result['message'] . "\n";
} else {
    echo "Error: " . $result['message'] . "\n";
}

echo "Cron Job: Process finished at " . date('Y-m-d H:i:s') . "\n";