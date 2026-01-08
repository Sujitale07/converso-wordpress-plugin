<?php

namespace Converso\Helpers;

use Converso\Core\Log\Log;

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

    public static function filter_agent(array $agents, array $locationParts)
    {
        $city    = strtolower(trim($locationParts['city'] ?? ''));
        $state   = strtolower(trim($locationParts['state'] ?? ''));
        $country = strtolower(trim($locationParts['country'] ?? ''));

        // Normalize agent locations
        foreach ($agents as &$agent) {
            $agent['location_lower'] = strtolower($agent['location'] ?? '');
        }

        $onlineAgents = array_filter($agents, function ($agent) {
            return !empty(!$agent['converso_agents_is_offline']);
        });

        $cityMatches = array_filter($onlineAgents, function ($agent) use ($city) {
            return $city && stripos($agent['location_lower'], $city) !== false;
        });

        if (count($cityMatches) === 1) {
            return array_values($cityMatches)[0];
        }

        $stateMatches = array_filter($cityMatches, function ($agent) use ($state) {
            return $state && stripos($agent['location_lower'], $state) !== false;
        });

        if (count($stateMatches) === 1) {
            return array_values($stateMatches)[0];
        }

        $countryMatches = array_filter(
            $stateMatches ?: $cityMatches,
            function ($agent) use ($country) {
                return $country && stripos($agent['location_lower'], $country) !== false;
            }
        );

        if (count($countryMatches) === 1) {
            return array_values($countryMatches)[0];
        }

        foreach ($agents as $agent) {
            if (!empty($agent['default']) && !empty($agent['is_online'])) {
                return $agent;
            }
        }

        foreach ($agents as $agent) {
            if (!empty($agent['default'])) {
                return $agent;
            }
        }

        return null;
    }



    public static function decode_dynamic_fields(array $agent) {
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
