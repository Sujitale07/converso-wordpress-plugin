<?php

namespace Converso\Helpers;

use Converso\Core\Log;
use Converso\Services\AgentsService;

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

        $lat = sanitize_text_field($lat);
        $lon = sanitize_text_field($lon);

        if (!$lat || !$lon) {
            return $location;
        }

        $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}&format=json&accept-language=en";

        $args = [
            'headers' => [ 'User-Agent' => 'ConversoPlugin/1.0' ],
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
            'city'    => $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? 'Unknown',
            'state'   => $addr['state'] ?? '',
            'country' => $addr['country'] ?? 'Unknown',
            'road'    => $addr['road'] ?? '',
            'postcode'=> $addr['postcode'] ?? ''
        ];
    }

    public static function get_client_location_by_ip() {
        $location = [
            'city'    => 'Unknown',
            'state'   => '',
            'country' => 'Unknown'
        ];

        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
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
        if (!$data || $data['status'] !== 'success') {
            return $location;
        }

        return [
            'city'    => $data['city'] ?? 'Unknown',
            'state'   => $data['regionName'] ?? '',
            'country' => $data['country'] ?? 'Unknown'
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
        $fallback_enabled = get_option('converso_fallback_primary_number', '');
        $primary_number = get_option('converso_primary_number', '');

        if ($fallback_enabled === '1' && !empty($primary_number)) {
            // Return pseudo-agent
            return [
                'id' => 0,
                'name' => 'Support',
                'phone' => $primary_number,
                'photo_url' => '', 
                'greeting' => get_option('converso_default_greeting', ''),
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

        $repo = new \Converso\Database\Repositories\ConversoDynamicFieldsRepository();
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
