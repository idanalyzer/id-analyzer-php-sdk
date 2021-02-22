<?php
require("../src/CoreAPI.php");

use IDAnalyzer\CoreAPI;
use IDAnalyzer\Vault;


$apikey = "Your API Key"; // ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$api_region = "US"; // or EU if you are from Europe



$coreapi = new CoreAPI();

// Initialize Core API US Region with your credentials
$coreapi->init($apikey, $api_region);

// Enable authentication module v2 to check if ID is authentic
$coreapi->enableAuthentication(true, 2);

// Analyze the ID image by passing URL of the ID image (you may also use a local file)
$result = $coreapi->scan("https://www.idanalyzer.com/img/sampleid1.jpg");

// All the information about this ID will be returned in an associative array
$data_result = $result['result'];
$authentication_result = $result['authentication'];

// Print result
echo("Hello your name is {$data_result['firstName']} {$data_result['lastName']}<br>");

// Parse document authentication results
if($authentication_result){
    if($authentication_result['score'] > 0.5) {
        echo("The document uploaded is authentic<br>");
    }else if($authentication_result['score'] > 0.3){
        echo("The document uploaded looks little bit suspicious<br>");
    }else{
        echo("The document uploaded is fake<br>");
    }
}