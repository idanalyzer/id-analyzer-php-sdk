<?php
require("../src/CoreAPI.php");

use IDAnalyzer\CoreAPI;
use IDAnalyzer\Vault;


$apikey = "Your API Key"; // ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$api_region = "US"; // or EU if you are from Europe


// Initialize Core API US Region with your credentials
$coreapi = new CoreAPI($apikey, $api_region);

// Enable authentication module v2 to check if ID is authentic
$coreapi->enableAuthentication(true, 2);

// Analyze the ID image by passing URL of the ID image (you may also use a local file)
$result = $coreapi->scan("https://www.idanalyzer.com/img/sampleid1.jpg","","https://upload.wikimedia.org/wikipedia/en/7/7d/Lenna_%28test_image%29.png");

// All the information about this ID will be returned in an associative array
$data_result = $result['result'];
$authentication_result = $result['authentication'];
$face_result = $result['face'];

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

// Parse face verification results
if($face_result){
    if($face_result['isIdentical'] === true){
        echo("Great! Your photo looks identical to the photo on document<br>");
    }else{
        echo("Oh no! Your photo looks different to the photo on document<br>");
    }
    echo("Similarity score: {$face_result['confidence']}<br>");
}