<?php
/**
 * Converso Seed Data Script
 * Run this from the command line: php bin/seed-data.php
 * Or visit it in the browser if you move it to the root, but CLI is better.
 */

// Load WordPress
$wp_load_path = __DIR__ . '/../../../../wp-load.php';

if (!file_exists($wp_load_path)) {
    die("Error: wp-load.php not found at $wp_load_path\n");
}

require_once($wp_load_path);

// Check if user is admin or running via CLI
if (php_sapi_name() !== 'cli' && !current_user_can('manage_options')) {
    die("Unauthorized access.");
}

echo "Starting seeding process...\n";

use Connectapre\Database\TestSeeder;

// 1. Seed some extra agents
echo "Seeding extra agents...\n";
TestSeeder::seed_extra_agents(50);

// 2. Seed 150 clicks
echo "Seeding 150 clicks...\n";
$count = TestSeeder::seed_test_data(15000);

echo "Successfully seeded $count clicks and extra agents.\n";
echo "You can now check your Converso Dashboard to see the results.\n";
