<?php

/**
 * Polyfill for pre PHP 7.3 
 */

if (! function_exists("array_key_last")) {
    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return null;
        }
       
        return array_keys($array)[count($array)-1];
    }
}

if (! function_exists("array_key_first")) {
    function array_key_first($array) {
        if (!is_array($array) || empty($array)) {
            return null;
        }
       
        return array_keys($array)[0];
    }
}