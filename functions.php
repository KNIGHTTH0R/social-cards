<?php
// Ignore notice errors when var is not set.
error_reporting(E_ALL & ~E_NOTICE);
require_once ('simple_html_dom.php');

// Make URL request and get data
function parseURL($src_url) {  

    // Alter from file_get_contents to use CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $src_url);
    curl_setopt($curl, CURLOPT_REFERER, $src_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $str = curl_exec($curl);
    curl_close($curl);

    return $str;

}


// Developer function to help map out items for templating.
function devPrint() {
    
    global $sections, $social_feed;    
    
    echo '<pre>';
        print_r($sections);
        print_r($social_feed);
    echo '</pre>';
}


?>