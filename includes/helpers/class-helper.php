<?php

namespace Connectapre\Helpers;

use Connectapre\Core\Log;
use Connectapre\Services\AgentsService;

class Helper {

    public static function get_client_location($lat, $lon) {

        // Default empty structure
        $location = [
            'city'    => 'Unknown',
            'state'   => '',
            'country' => 'Unknown',
            'road'    => '',
            'postcode'=> ''
        ];

        // Sanitize and Validate Coordinates
        $lat = sanitize_text_field($lat);
        $lon = sanitize_text_field($lon);

        if ( ! is_numeric( $lat ) || ! is_numeric( $lon ) ) {
             return $location;
        }

        // Additional validation for coordinate ranges
        if ( $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180 ) {
             return $location;
        }

        $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}&format=json&accept-language=en";

        $args = [
            'headers' => [ 'User-Agent' => 'ConnectaprePlugin/1.0' ],
            'timeout' => 15
        ];

        $res = wp_remote_get($url, $args);

        if (is_wp_error($res)) {
            Log::info("Nominatim API Error: " . $res->get_error_message());
            return $location;
        }

        $body = wp_remote_retrieve_body($res);
        $data = json_decode($body, true);

        if (!$data || !isset($data['address'])) {
            return $location;
        }

        $addr = $data['address'];
        return [
            'city'    => isset($addr['city']) ? sanitize_text_field($addr['city']) : (isset($addr['town']) ? sanitize_text_field($addr['town']) : (isset($addr['village']) ? sanitize_text_field($addr['village']) : 'Unknown')),
            'state'   => isset($addr['state']) ? sanitize_text_field($addr['state']) : '',
            'country' => isset($addr['country']) ? sanitize_text_field($addr['country']) : 'Unknown',
            'road'    => isset($addr['road']) ? sanitize_text_field($addr['road']) : '',
            'postcode'=> isset($addr['postcode']) ? sanitize_text_field($addr['postcode']) : ''
        ];
    }

    public static function get_client_location_by_ip() {
        $location = [
            'city'    => 'Unknown',
            'state'   => '',
            'country' => 'Unknown'
        ];

        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // Use only the first IP if multiple are present
            $forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $parts     = explode( ',', $forwarded );
            $ip        = trim( $parts[0] );
        } else {
            $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        }

        // Validate IP
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return $location;
        }

        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
            return $location;
        }

        $url = "http://ip-api.com/json/{$ip}";
        $res = wp_remote_get($url, ['timeout' => 5]);

        if (is_wp_error($res)) {
            Log::info("IP-API Error: " . $res->get_error_message());
            return $location;
        }

        $data = json_decode(wp_remote_retrieve_body($res), true);
        if (!$data || ( isset($data['status']) && $data['status'] !== 'success' ) ) {
            return $location;
        }

        return [
            'city'    => isset($data['city']) ? sanitize_text_field($data['city']) : 'Unknown',
            'state'   => isset($data['regionName']) ? sanitize_text_field($data['regionName']) : '',
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : 'Unknown'
        ];
    }

    public static function filter_agent(array $locationParts = [])
    {
        // Helper to normalize strings for comparison
        $normalize = function($str) {
            if (empty($str)) return '';
            $str = strtolower(trim($str));
            $str = str_replace([' province', ' state', ' department', ' region'], '', $str);
            return preg_replace('/[^a-z0-9]/', '', $str); // Remove all non-alphanumeric
        };

        $vCity    = isset($locationParts['city']) ? $normalize($locationParts['city']) : '';
        $vState   = isset($locationParts['state']) ? $normalize($locationParts['state']) : '';
        $vCountry = isset($locationParts['country']) ? $normalize($locationParts['country']) : '';

        Log::info("Normalized Visitor Location: Country[$vCountry], State[$vState], City[$vCity]");

        $agents_data = AgentsService::get_agents(['limit' => 999]);
        $agents = isset($agents_data['agents']) ? $agents_data['agents'] : [];        

        $activeAgents = array_filter($agents, function($agent) {
            return isset($agent['is_active']) && (int)$agent['is_active'] === 1;
        });

        $bestMatches = [];
        $bestScore = -1;

        foreach ($activeAgents as $agent) {
            $score = 0;

            $aCity    = $normalize($agent['location_city'] ?? '');
            $aState   = $normalize($agent['location_state'] ?? '');
            $aCountry = $normalize($agent['location_country'] ?? '');

            // 1. Strict Requirement: Country must match IF agent specifies one
            if (!empty($aCountry)) {
                if ($aCountry !== $vCountry) {
                    continue; // Skip if in a different country
                }
                $score += 10;
            }

            // 2. Score City and State
            if (!empty($aState)) {
                if ($aState === $vState) {
                    $score += 20;
                } else if (!empty($vState)) {
                    // Mismatch in defined state - lower priority but don't strictly skip if country/city match
                    $score -= 5; 
                }
            }

            if (!empty($aCity)) {
                if ($aCity === $vCity) {
                    $score += 30;
                } else if (!empty($vCity)) {
                    $score -= 10;
                }
            }

            Log::info("Checking Agent: " . ($agent['name'] ?? 'ID:'.$agent['id']) . " | Score: " . $score);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatches = [$agent];
            } elseif ($score === $bestScore && $score >= 0) {
                $bestMatches[] = $agent;
            }
        }

        if ($bestScore >= 10 && !empty($bestMatches)) {
            $selected = $bestMatches[array_rand($bestMatches)];
            Log::info("Match Found: " . ($selected['name'] ?? 'ID:'.$selected['id']) . " with score " . $bestScore);
            return $selected;
        }

        Log::info("No strong location match found (Best Score: $bestScore). Checking for default agent...");

        foreach ($activeAgents as $agent) {
            if (isset($agent['is_default']) && (int)$agent['is_default'] === 1) {
                Log::info("Returning Default Agent: " . ($agent['name'] ?? 'ID:'.$agent['id']));
                return $agent;
            }
        }

        if (!empty($activeAgents)) {
            $fallback = reset($activeAgents);
            Log::info("No default found. Falling back to first available active agent: " . ($fallback['name'] ?? 'ID:'.$fallback['id']));
            return $fallback;
        }

        Log::info("CRITICAL: No active agents found at all.");

        // Check if Fallback to Primary Number is enabled
        $fallback_enabled = get_option('connectapre_fallback_primary_number', '');
        $primary_number = get_option('connectapre_primary_number', '');

        if ($fallback_enabled === '1' && !empty($primary_number)) {
            // Return pseudo-agent
            return [
                'id' => 0,
                'name' => 'Support',
                'phone' => $primary_number,
                'photo_url' => '', 
                'greeting' => get_option('connectapre_default_greeting', ''),
                'location_city' => '',
                'location_state' => '',
                'location_country' => '',
                'is_active' => 1,
                'is_default' => 0
            ];
        }

        return null;
    }

    public static function decode_dynamic_fields($agent) {
        if (!$agent) return null;

        $repo = new \Connectapre\Database\Repositories\ConnectapreDynamicFieldsRepository();
        $dynamic_fields = $repo->all();

        // Get agent greeting (Schema 'greeting')
        $greeting = $agent['greeting'] ?? '';

        // Replace placeholders with corresponding values
        if (is_array($dynamic_fields)) {
            foreach ($dynamic_fields as $field) {
                if (isset($field['callable'], $field['value'])) {
                    $greeting = str_replace($field['callable'], $field['value'], $greeting);
                }
            }
        }

        // Return modified agent with decoded greeting
        // Frontend expects 'greetings'
        $agent['greetings'] = $greeting; 
        
        return $agent;
    }



}

