<?php

namespace Converso\Helpers;

class Helper {

    public static function get_client_location($lat, $lon) {

        // Sanitize input
        $lat = sanitize_text_field($lat);
        $lon = sanitize_text_field($lon);

        if (!$lat || !$lon) {
            return [
                'error' => 'Missing latitude or longitude'
            ];
        }

        $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}&format=json&accept-language=en";

        $args = [
            'headers' => [
                'User-Agent' => 'ConversoPlugin/1.0 (https://yourwebsite.com)',
            ],
            'timeout' => 15
        ];

        $res = wp_remote_get($url, $args);

        if (is_wp_error($res)) {
            return [
                'error' => 'API request failed'
            ];
        }

        $body = wp_remote_retrieve_body($res);

        if (!$body) {
            return [
                'error' => 'Empty response from Nominatim'
            ];
        }

        $data = json_decode($body, true);

        if (!$data) {
            return [
                'error' => 'Failed to decode API response'
            ];
        }

        // Return only useful fields as a simple array
        return [
            'road'      => $data['address']['road'] ?? '',
            'city'      => $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? '',
            'state'     => $data['address']['state'] ?? '',
            'postcode'  => $data['address']['postcode'] ?? '',
            'country'   => $data['address']['country'] ?? '',
            'latitude'  => $data['lat'] ?? '',
            'longitude' => $data['lon'] ?? '',
            'display_name' => $data['display_name'] ?? ''
        ];
    }

    public static function filter_agent(array $agents, string $city) {
        $city = strtolower(trim($city));

        // Try to find a matching agent
        foreach ($agents as $agent) {
            if (isset($agent['location']) && stripos($agent['location'], $city) !== false) {
                return $agent; // Return the first matching agent
            }
        }

        // If no match, return the default agent
        foreach ($agents as $agent) {
            if (isset($agent['default']) && $agent['default'] === true) {
                return $agent;
            }
        }

        // Fallback: return null if no default agent found
        return null;
    }

    public static function decode_dynamic_fields(array $agent) {
        // Get dynamic fields from options
        $dynamic_fields = get_option("converso_dynamic_fields_data", []);

        // Get agent greetings
        $greetings = $agent['greetings'] ?? '';

        // Replace placeholders with corresponding values
        foreach ($dynamic_fields as $field) {
            if (isset($field['callable'], $field['value'])) {
                $greetings = str_replace($field['callable'], $field['value'], $greetings);
            }
        }

        // Return modified agent with decoded greetings
        $agent['greetings'] = $greetings;
        return $agent;
    }



}
