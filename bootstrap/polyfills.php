<?php

/**
 * Global Multibyte Polyfills for Live Server Compatibility.
 *
 * Ensures required PHP multibyte functions exist even if the ext-mbstring PHP extension
 * is missing or if certain functions (e.g. mb_strcut, mb_strimwidth) are not polyfilled
 * by Symfony's polyfill-mbstring package.
 */

if (!function_exists('mb_strcut')) {
    /**
     * Polyfill for mb_strcut.
     * Cuts a string by byte offset while respecting multi-byte character boundaries.
     *
     * @param string|null $string
     * @param int $start
     * @param int|null $length
     * @param string|null $encoding
     * @return string
     */
    function mb_strcut(?string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        $byteLen = strlen($string);
        if ($start < 0) {
            $start = max(0, $byteLen + $start);
        }

        if ($start >= $byteLen) {
            return '';
        }

        // Rewind start to the first byte of a UTF-8 sequence if it lands in the middle (continuation byte: 10xxxxxx)
        while ($start > 0 && (ord($string[$start]) & 0xC0) === 0x80) {
            $start--;
        }

        if ($length === null) {
            $sub = substr($string, $start);
        } else {
            if ($length < 0) {
                $length = max(0, $byteLen + $length - $start);
            }
            $sub = substr($string, $start, $length);
        }

        if ($sub === false || $sub === '') {
            return '';
        }

        // Discard any trailing incomplete UTF-8 multi-byte sequences at the end of the slice
        $sub = preg_replace('/[\xC0-\xDF]$|[\xE0-\xEF][\x80-\xBF]?$|[\xF0-\xF4][\x80-\xBF]{0,2}$/', '', $sub);

        return $sub ?? '';
    }
}

if (!function_exists('mb_strimwidth')) {
    /**
     * Polyfill for mb_strimwidth.
     * Truncates string to specified width, taking multi-byte characters into account.
     *
     * @param string|null $string
     * @param int $start
     * @param int $width
     * @param string $trim_marker
     * @param string|null $encoding
     * @return string
     */
    function mb_strimwidth(?string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        $encoding = $encoding ?? 'UTF-8';

        if (function_exists('mb_substr') && function_exists('mb_strwidth')) {
            $sub = mb_substr($string, $start, null, $encoding);
            if (mb_strwidth($sub, $encoding) <= $width) {
                return $sub;
            }

            $markerWidth = mb_strwidth($trim_marker, $encoding);
            $targetWidth = max(0, $width - $markerWidth);
            $result = '';
            $chars = function_exists('mb_str_split') ? mb_str_split($sub, 1, $encoding) : preg_split('//u', $sub, -1, PREG_SPLIT_NO_EMPTY);
            $currentWidth = 0;

            foreach ($chars as $char) {
                $cw = mb_strwidth($char, $encoding);
                if ($currentWidth + $cw > $targetWidth) {
                    break;
                }
                $result .= $char;
                $currentWidth += $cw;
            }

            return $result . $trim_marker;
        }

        $sub = substr($string, $start);
        if (strlen($sub) <= $width) {
            return $sub;
        }

        return substr($sub, 0, max(0, $width - strlen($trim_marker))) . $trim_marker;
    }
}
