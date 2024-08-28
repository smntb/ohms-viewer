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

}
/* Location: ./app/Ohms/Utils.php */