<?php
/**
 * Converso Routing Test Script
 * This script verifies that the agent selection logic (Helper::filter_agent) correctly picks the best agent
 * based on the visitor's location and agent settings.
 */

// Load WordPress
$wp_load_path = __DIR__ . '/../../../../wp-load.php';
if (!file_exists($wp_load_path)) {
    die("Error: wp-load.php not found at $wp_load_path\n");
}
require_once($wp_load_path);

// Check if user is admin or running via CLI
if (PHP_SAPI !== 'cli' && !current_user_can('manage_options')) {
    die("Unauthorized access.");
}

use Converso\Helpers\Helper;
use Converso\Database\Repositories\ConversoAgentsRepository;

echo "--------------------------------------------------\n";
echo "CONVERSO AGENT ROUTING TEST\n";
echo "--------------------------------------------------\n\n";

$repo = new ConversoAgentsRepository();

// 1. Setup: Clear existing test agents or just use fresh ones
// For safety in this test, we'll create unique markers
$test_suffix = '_' . time();

function create_test_agent($repo, $name, $country, $city, $is_default = 0, $is_active = 1) {
    return $repo->create([
        'uuid' => wp_generate_uuid4(),
        'name' => $name,
        'phone' => '1234567890',
        'greeting' => 'Hi from ' . $name,
        'location_country' => $country,
        'location_city' => $city,
        'is_default' => $is_default,
        'is_active' => $is_active,
        'created_at' => current_time('mysql')
    ]);
}

echo "Setting up test environment...\n";

// Clear existing agents for a clean test
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->prefix}converso_agents");
$wpdb->query("DELETE FROM {$wpdb->prefix}converso_agent_presence");

// Clear existing defaults to avoid confusion
$repo->reset_defaults();

$agentA_id = create_test_agent($repo, "USA-NY-Agent$test_suffix", "USA", "New York");
$agentB_id = create_test_agent($repo, "UK-London-Agent$test_suffix", "United Kingdom", "London");
$agentE_id = create_test_agent($repo, "USA-Only-Agent$test_suffix", "USA", ""); // No city
$agentC_id = create_test_agent($repo, "Default-Agent$test_suffix", "", "", 1); // Only Default
$agentD_id = create_test_agent($repo, "Inactive-Agent$test_suffix", "USA", "New York", 0, 0); // Should be ignored

echo "Agents Created:\n";
echo "- Agent A (USA, New York)\n";
echo "- Agent B (UK, London)\n";
echo "- Agent E (USA, No City)\n";
echo "- Agent C (Default)\n";
echo "- Agent D (USA, New York, INACTIVE)\n\n";

$test_cases = [
    [
        'name' => 'Exact Match (New York, USA)',
        'location' => ['city' => 'New York', 'country' => 'USA'],
        'expected' => "USA-NY-Agent$test_suffix" // Should win with score 40 (10+30) vs Agent E (score 10)
    ],
    [
        'name' => 'Exact Match (London, United Kingdom)',
        'location' => ['city' => 'London', 'country' => 'United Kingdom'],
        'expected' => "UK-London-Agent$test_suffix"
    ],
    [
        'name' => 'Fallback to Default (Paris, France)',
        'location' => ['city' => 'Paris', 'country' => 'France'],
        'expected' => "Default-Agent$test_suffix"
    ],
    [
        'name' => 'Country Match (Los Angeles, USA)',
        'location' => ['city' => 'Los Angeles', 'country' => 'USA'],
        'expected' => "USA-Only-Agent$test_suffix" // Score 10 vs Agent A (score 0 due to city mismatch)
    ]
];

$passed = 0;
$total = count($test_cases);

foreach ($test_cases as $index => $test) {
    echo "Running Test #" . ($index + 1) . ": " . $test['name'] . "...\n";
    
    $selected = Helper::filter_agent($test['location']);
    
    if ($selected && $selected['name'] === $test['expected']) {
        echo "PASSED: Selected " . $selected['name'] . "\n";
        $passed++;
    } else {
        $found_name = $selected ? $selected['name'] : 'None';
        echo "FAILED: Expected '" . $test['expected'] . "', but got '" . $found_name . "'\n";
    }
    echo "--------------------------------------------------\n";
}

echo "\nSummary: $passed / $total tests passed.\n";

// Cleanup (Optional: uncomment to delete test agents)

$repo->delete($agentA_id);
$repo->delete($agentB_id);
$repo->delete($agentC_id);
$repo->delete($agentD_id);
echo "Cleanup completed.\n";


if ($passed === $total) {
    echo "\n🎉 ALL ROUTING TESTS PASSED SUCCESSFULLY!\n";
} else {
    echo "\n⚠️ SOME TESTS FAILED. Please review the selection logic.\n";
}
