<?php

namespace Ohms;

use simple_html_dom;

/**
 * Utils Class, Contains commonly used utility Methods. 
 */
class Utils {

    /**
     * Get Parsed URL for Aviary player in particular.
     * @param string $embed
     * @return string
     */
    public static function getAviaryUrl($embed) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $embed);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        // Create DOM from URL or file
        $content = str_get_html($response);
        $mediaURL = "";
        if ($content != "") {

            $source = $content->find('source', 0);
            if ($source) {

                if ($source->src) {
                    $mediaURL = $source->src;
                }
            }
        }
        return $mediaURL;
    }

    /**
     * Get Aviary MediaFormat
     * @param type $aviaryUrl
     * @return type
     */
    public static function getAviaryMediaFormat($aviaryUrl) {
        $parsedUrl = parse_url($aviaryUrl);
        $mediaFormat = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);
        $mediaFormat = (strtolower($mediaFormat) == 'mp4v') ? "mp4" : $mediaFormat;

        return $mediaFormat;
    }

    public static function formatTimePoint($time) {
        $hours = floor($time / 3600);
        $minutes = floor(($time - ($hours * 3600)) / 60);
        $seconds = $time - (($hours * 3600) + ($minutes * 60));

        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}";
    }

    public static function splitAndConvertTime($time_str) {
        // Split the input string into start and end times
        list($start_time, $end_time) = explode(" --> ", $time_str);

        // Convert start time to seconds
        list($hours, $minutes, $seconds_ms) = explode(":", $start_time);
        list($seconds, $milliseconds) = explode(".", $seconds_ms);
        $start_time_seconds = ($hours * 3600) + ($minutes * 60) + intval($seconds); //+ (intval($milliseconds) / 1000);

        return array(
            "start_time" => $start_time,
            "end_time" => $end_time,
            "start_time_seconds" => $start_time_seconds
        );
    }

    public static function buildTimelineFlat(array $annotations): array {
        $timeline = [];

        foreach ($annotations as $ann) {
            $ref = $ann['ref'] ?? null;
            $text = $ann['text'] ?? null;
            $meta = $ann['meta'] ?? [];

            // Prefer wiki_label, fallback to label
            $label = $meta['wiki_label'] ?? ($meta['label'] ?? null);

            $wiki = [
                'name' => $meta['wiki_name'] ?? null,
                'code' => $meta['wiki_code'] ?? null,
                'url1' => $meta['wiki_url_1'] ?? null,
                'url2' => $meta['wiki_url_2'] ?? null,
                'website' => $meta['wiki_website'] ?? null,
                'description_1' => $meta['wiki_description_1'] ?? null,
                'description_2' => $meta['wiki_description_2'] ?? null,
            ];

            // Fields to inspect
            $candidates = [
                'dob_wiki_dod' => $meta['dob_wiki_dod'] ?? null,
                'event_start_end_time' => $meta['event_start_end_time'] ?? null,
                'wiki_event_time' => $meta['wiki_event_time'] ?? null,
                'wiki_dob' => $meta['wiki_dob'] ?? null,
                'wiki_dod' => $meta['wiki_dod'] ?? null,
            ];

            foreach ($candidates as $field => $raw) {
                if (!is_string($raw) || trim($raw) === '')
                    continue;

                $parts = Utils::parseDateToFlatParts($raw);
                if (!$parts)
                    continue;

                foreach ($parts as $p) {
                    $timeline[] = [
                        'date' => $p['iso'], // ISO YYYY-MM-DD
                        'precision' => $p['precision'], // year|month|day
                        'range_part' => $p['range_part'], // single|start|end
                        'ref' => $ref,
                        'text' => $text,
                        'label' => $label,
                        'wiki' => $wiki,
                        'source_field' => $field,
                        'raw' => $raw,
                    ];
                }
            }
        }

        // Sort by date ascending
        usort($timeline, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $timeline;
    }

    /**
     * Convert a possibly-range string into 1 or 2 flat parts.
     * Returns:
     * [
     *   ['iso' => 'YYYY-MM-DD', 'precision' => 'year|month|day', 'range_part' => 'single|start|end'],
     *   ...
     * ]
     */
    private static function parseDateToFlatParts(string $raw): ?array {
        $s = trim($raw);
        if ($s === '')
            return null;

        // Normalize common range delimiters to a single hyphen
        $normalized = str_replace(['—', '–', ' to '], '-', $s);

        // Range like "1918-07-18 - 2013-12-05" or "1918 - 2013"
        if (preg_match('/\s*-\s*/', $normalized)) {
            [$left, $right] = array_map('trim', preg_split('/\s*-\s*/', $normalized, 2));

            $L = Utils::parseSingleDateToIso($left);
            $R = Utils::parseSingleDateToIso($right);

            if ($L && $R) {
                return [
                    ['iso' => $L['iso'], 'precision' => $L['precision'], 'range_part' => 'start'],
                    ['iso' => $R['iso'], 'precision' => $R['precision'], 'range_part' => 'end'],
                ];
            }
            if ($L && !$R) {
                return [
                    ['iso' => $L['iso'], 'precision' => $L['precision'], 'range_part' => 'start'],
                ];
            }
            if (!$L && $R) {
                return [
                    ['iso' => $R['iso'], 'precision' => $R['precision'], 'range_part' => 'end'],
                ];
            }
            return null;
        }

        // Single date
        $single = Utils::parseSingleDateToIso($s);
        if ($single) {
            return [
                ['iso' => $single['iso'], 'precision' => $single['precision'], 'range_part' => 'single']
            ];
        }

        return null;
    }

    /**
     * Parse a single token to ISO Y-m-d with precision.
     * Supports:
     *  - YYYY
     *  - YYYY-MM or YYYY/MM
     *  - Month YYYY (e.g., "July 1970", "Sept 1970")
     *  - Full dates with -, /, . (Y-m-d, d/m/Y, m.d.Y, etc.)
     */
    private static function parseSingleDateToIso(string $token): ?array {
        $t = trim($token);
        if ($t === '')
            return null;

        // Year only
        if (preg_match('/^\d{4}$/', $t)) {
            return ['iso' => $t . '-01-01', 'precision' => 'year'];
        }

        // Year-Month (dash or slash)
        if (preg_match('/^\d{4}[-\/]\d{1,2}$/', $t)) {
            $t = str_replace('/', '-', $t);
            [$Y, $m] = array_map('intval', explode('-', $t));
            $m = max(1, min(12, $m));
            return ['iso' => sprintf('%04d-%02d-01', $Y, $m), 'precision' => 'month'];
        }

        // Month YYYY (long or short month names)
        if (preg_match('/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Sept|Oct|Nov|Dec)[a-z]*\s+\d{4}$/i', $t)) {
            $ts = strtotime('01 ' . $t);
            if ($ts !== false) {
                return ['iso' => date('Y-m-d', $ts), 'precision' => 'month'];
            }
        }

        // Try common full-date formats
        $formats = [
            'Y-m-d', 'Y/m/d', 'Y.m.d',
            'd-m-Y', 'd/m/Y', 'd.m.Y',
            'm-d-Y', 'm/d/Y', 'm.d.Y',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $t);
            if ($dt && $dt->format($fmt) === $t) {
                return ['iso' => $dt->format('Y-m-d'), 'precision' => 'day'];
            }
        }

        // Last resort: strtotime
        $ts = strtotime($t);
        if ($ts !== false) {
            return ['iso' => date('Y-m-d', $ts), 'precision' => 'day'];
        }

        return null;
    }
}

/* Location: ./app/Ohms/Utils.php */