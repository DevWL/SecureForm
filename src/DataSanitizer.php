<?php

namespace Devwl\Email;

use Symfony\Component\HttpFoundation\Session;

/**
 * User input / string manipulation static helpers class
 */
class DataSanitizer{

    /**
     * Removes HTML from string
     *
     * @param string $string
     * @return string
     */
    static function userString($string){
        $string = filter_var($string, FILTER_SANITIZE_STRING);
        return $string;
    }    

    /**
     * Removes unwanted chars from email / validating email data
     *
     * @param string $string
     * @return string
     */
    static function userEmail($string){
        $string = filter_var($string, FILTER_SANITIZE_EMAIL);
        return $string;
    }    

    /**
     * Removes js from user input strings. Preservs HTML tags.
     *
     * @param string $string
     * @return string
     */
    static function userHTML($string){
        // $string = filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $string = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $string);
        return $string;
    }  
}