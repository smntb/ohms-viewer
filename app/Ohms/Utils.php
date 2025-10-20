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

    public static function extractTextThumbIndex(array $annotations): array {
        $index = [];

        foreach ($annotations as $a) {
            if (!is_array($a))
                continue;

            $text = isset($a['text']) ? trim((string) $a['text']) : '';
            if ($text === '')
                continue;

            $ref = isset($a['ref']) ? (int) $a['ref'] : PHP_INT_MAX;
            $meta = (isset($a['meta']) && is_array($a['meta'])) ? $a['meta'] : [];

            $label = isset($meta['label']) ? (string) $meta['label'] : '';
            $thumb = isset($meta['thumbnail_url']) ? trim((string) $meta['thumbnail_url']) : '';
            $hasThumb = self::isValidHttpUrl($thumb);

            if (!isset($index[$text])) {
                $index[$text] = [
                    'text' => $text,
                    'first_ref' => $ref,
                    'count' => 1,
                    'label' => $label, // may be ''
                    'thumbnail_url' => $hasThumb ? $thumb : null, // set if valid
                    '_thumb_ref' => $hasThumb ? $ref : null, // helper to track earliest thumb
                ];
                continue;
            }

            // Update first_ref and count
            $index[$text]['count']++;
            if ($ref < $index[$text]['first_ref']) {
                $index[$text]['first_ref'] = $ref;
            }

            // Fill label if previously empty
            if ($index[$text]['label'] === '' && $label !== '') {
                $index[$text]['label'] = $label;
            }

            // Prefer the thumbnail from the earliest occurrence that has one
            if ($hasThumb) {
                if ($index[$text]['_thumb_ref'] === null || $ref < $index[$text]['_thumb_ref']) {
                    $index[$text]['_thumb_ref'] = $ref;
                    $index[$text]['thumbnail_url'] = $thumb;
                }
            }
        }

        // Keep only texts that ended up with a thumbnail
        $rows = [];
        foreach ($index as $rec) {
            if (!empty($rec['thumbnail_url'])) {
                unset($rec['_thumb_ref']); // drop helper field
                $rows[] = $rec;
            }
        }

        // Sort: count desc, then first_ref asc, then text asc
        usort($rows, function ($a, $b) {
            if ($a['count'] !== $b['count'])
                return $b['count'] <=> $a['count'];
            if ($a['first_ref'] !== $b['first_ref'])
                return $a['first_ref'] <=> $b['first_ref'];
            return strcmp($a['text'], $b['text']);
        });

        return $rows;
    }

    /**
     * Validate an http(s) URL.
     */
    private static function isValidHttpUrl(?string $url): bool {
        if (empty($url))
            return false;
        if (!filter_var($url, FILTER_VALIDATE_URL))
            return false;
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array(strtolower((string) $scheme), ['http', 'https'], true);
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

    public static function buildGeoIndex(array $annotations): array {
        $idx = [];

        foreach ($annotations as $a) {
            if (!is_array($a))
                continue;

            $text = isset($a['text']) ? trim((string) $a['text']) : '';
            $ref = isset($a['ref']) ? (int) $a['ref'] : PHP_INT_MAX;
            $meta = (isset($a['meta']) && is_array($a['meta'])) ? $a['meta'] : [];
            $label = isset($meta['label']) ? (string) $meta['label'] : '';

            // Pull geolocation from meta['geo_location'] or meta['geolocation'] or fallback "href=?q=lat,lng"
            $geoStr = self::firstNonEmpty($meta['geo_location'] ?? null, $meta['geolocation'] ?? null, $meta['href'] ?? null);
            [$lat, $lng] = self::parseLatLng($geoStr);
            if ($lat === null || $lng === null)
                continue; // skip non-geo items

            // Use normalized key: label|lower(text)|lat,lng
            $key = strtoupper($label) . '|' . mb_strtolower($text) . '|' . $lat . ',' . $lng;

            if (!isset($idx[$key])) {
                $idx[$key] = [
                    'text' => $text,
                    'label' => $label,
                    'count' => 1,
                    'first_ref' => $ref,
                    'geo' => $lat . ',' . $lng,
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                ];
            } else {
                $idx[$key]['count']++;
                if ($ref < $idx[$key]['first_ref']) {
                    $idx[$key]['first_ref'] = $ref;
                }
            }
        }

        // Flatten + sort: count desc, first_ref asc, text asc
        $rows = array_values($idx);
        usort($rows, function ($a, $b) {
            if ($a['count'] !== $b['count'])
                return $b['count'] <=> $a['count'];
            if ($a['first_ref'] !== $b['first_ref'])
                return $a['first_ref'] <=> $b['first_ref'];
            return strcmp($a['text'], $b['text']);
        });

        return $rows;
    }

    /** Helpers * */
    public static function firstNonEmpty(...$vals): ?string {
        foreach ($vals as $v) {
            if (isset($v)) {
                $s = trim((string) $v);
                if ($s !== '')
                    return $s;
            }
        }
        return null;
    }

    /**
     * Accepts:
     *  - "lat,lng"
     *  - strings with junk chars like " 37.77 , -122.42 "
     *  - Google href "...?q=lat,lng"
     * Returns [lat, lng] as strings normalized, or [null, null] if invalid.
     */
    public static function parseLatLng(?string $val): array {
        if (!$val)
            return [null, null];

        // If it's a full URL with ?q=lat,lng, try to pull q
        if (stripos($val, 'http') === 0) {
            $parts = parse_url($val);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $q);
                if (!empty($q['q']))
                    $val = $q['q'];
            }
        }

        // Extract two numbers (lat,lng)
        $val = trim($val);
        $m = [];
        if (!preg_match('/^\s*([+\-]?\d+(?:\.\d+)?)\s*,\s*([+\-]?\d+(?:\.\d+)?)\s*$/', $val, $m)) {
            return [null, null];
        }

        $lat = (float) $m[1];
        $lng = (float) $m[2];
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return [null, null];
        }
        // return as strings to keep stable key formatting
        return [rtrim(rtrim(sprintf('%.6f', $lat), '0'), '.'), rtrim(rtrim(sprintf('%.6f', $lng), '0'), '.')];
    }

     /**
 * Render popovers for all annotations, grouped by text.
 * - Groups case-insensitively by `text`
 * - For each annotation, shows LABEL, text, optional wiki description + link
 * - Adds Prev/Next pagination only when the same text appears multiple times
 *
 * @param array $annotations  Raw annotations array (like the one you shared)
 * @return string             HTML for all popovers
 */
public static function renderAnnotationPopoversGroupedByText(array $annotations): string
{
    // 1) Build groups: key = lower(text) => ['text','refs'=>[...], 'meta_by_ref'=>[ref=>meta]]
    $groups = [];
    foreach ($annotations as $a) {
        if (!is_array($a)) continue;
        $text = trim($a['text'] ?? '');
        if ($text === '') continue;

        $ref  = isset($a['ref']) ? (int)$a['ref'] : PHP_INT_MAX;
        $meta = (isset($a['meta']) && is_array($a['meta'])) ? $a['meta'] : [];
        $key  = mb_strtolower($text);

        if (!isset($groups[$key])) {
            $groups[$key] = ['text' => $text, 'refs' => [], 'meta_by_ref' => []];
        }
        $groups[$key]['refs'][] = $ref;
        $groups[$key]['meta_by_ref'][$ref] = $meta;
    }

    // 2) Normalize each group's refs
    foreach ($groups as &$g) {
        $g['refs'] = array_values(array_unique(array_map('intval', $g['refs'])));
        sort($g['refs'], SORT_NUMERIC);
    }
    unset($g);

    // 3) Render popover HTML for every annotation
    $descKeys = ['wiki_description_2','wiki_description_1','wiki_description'];
    $linkKeys = ['wiki_url_2','wiki_url_1','wiki_url'];

    $html = '';
    foreach ($annotations as $a) {
        if (!is_array($a)) continue;

        $text = trim($a['text'] ?? '');
        if ($text === '') continue;

        $ref   = (int)($a['ref'] ?? 0);
        $meta  = (isset($a['meta']) && is_array($a['meta'])) ? $a['meta'] : [];
        $label = strtoupper((string)($meta['label'] ?? 'UNKNOWN'));

        $key   = mb_strtolower($text);
        $g     = $groups[$key] ?? null;
        if (!$g) continue;

        $refs  = $g['refs'];
        $total = count($refs);
        $pos0  = array_search($ref, $refs, true);
        if ($pos0 === false) continue; // safety
        $pos   = $pos0 + 1;
        $prev  = ($pos > 1)      ? $refs[$pos0 - 1] : null;
        $next  = ($pos < $total) ? $refs[$pos0 + 1] : null;

        // Pick wiki description and link (prefer current, else any in group)
        $desc = self::pickFirstNonEmptyFrom($meta, $descKeys);
        if ($desc === '') {
            foreach ($g['meta_by_ref'] as $m) {
                $desc = self::pickFirstNonEmptyFrom($m, $descKeys);
                if ($desc !== '') break;
            }
        }
        $link = self::pickFirstValidUrlFrom($meta, $linkKeys);
        if ($link === '') {
            foreach ($g['meta_by_ref'] as $m) {
                $link = self::pickFirstValidUrlFrom($m, $linkKeys);
                if ($link !== '') break;
            }
        }

        // Build one popover
        $html .= '<div class="popover-body d-none transcript_'.self::h($ref).'" data-ref="'.self::h($ref).'">';
        $html .=   '<div><strong>'.self::h($label).':</strong> '.self::h($text).'</div>';
        if ($desc !== '') {
            $html .= '<div>'.self::h($desc).'</div>';
        }
        if ($link !== '') {
            $html .= '<div><a href="'.self::h($link).'" target="_blank" rel="noopener">Wikipedia link</a></div>';
        }
        

        if ($total > 1) {
            $html .= '<div class="simple-pagination"><ul>';

            // Prev
            if ($prev === null) {
                $html .= '<li class="disabled"><span class="current prev"><img src="/imgs/arrow-square-prev.webp" alt="Previous"></span></li>';
            } else {
                $html .= '<li><a href="javascript://" data-ref="'.self::h($prev).'"  class="pop-page-link prev"><img src="/imgs/arrow-square-prev.webp" alt="Previous"></a></li>';
            }

            // Info
            $html .= '<li><span id="popover_paginate_info">Showing '.self::h($pos).' of '.self::h($total).'</span></li>';

            // Next
            if ($next === null) {
                $html .= '<li class="disabled"><span class="current next"><img src="/imgs/arrow-square-next.webp" alt="Next"></span></li>';
            } else {
                $html .= '<li><span href="#javascript://" data-ref="'.self::h($next).'" class="pop-page-link next"><img src="/imgs/arrow-square-next.webp" alt="Next"></span></li>';
            }

            $html .= '</ul></div>';
        }

        $html .= '</div>';
    }

    return $html;
}

/* ---------------- helpers ---------------- */

public static function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

public static function pickFirstNonEmptyFrom(array $meta, array $keys): string {
    foreach ($keys as $k) {
        if (!empty($meta[$k])) {
            $s = trim((string)$meta[$k]);
            if ($s !== '') return $s;
        }
    }
    return '';
}

public static function pickFirstValidUrlFrom(array $meta, array $keys): string {
    foreach ($keys as $k) {
        if (!empty($meta[$k])) {
            $u = trim((string)$meta[$k]);
            if (filter_var($u, FILTER_VALIDATE_URL)) return $u;
        }
    }
    return '';
}
}

/* Location: ./app/Ohms/Utils.php */