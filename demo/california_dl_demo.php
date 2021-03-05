<?php
require("../src/CoreAPI.php");

use IDAnalyzer\CoreAPI;

// ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$apikey = "Your API Key";

// API region: US or EU
$api_region = "US";

try{
    // Initialize Core API US Region with your credentials
    $coreapi = new CoreAPI($apikey, $api_region);

    // Make API error raise exceptions for API level errors (such as out of quota, document not recognized)
    $coreapi->throwAPIException(true);

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
        if($face_result['error']){
            // View complete error codes under API reference: https://developer.idanalyzer.com/coreapi.html
            echo("Biometric verification failed! Code: {$face_result['error']}, Reason: {$face_result['error_message']}<br>");
        }else{
            if($face_result['isIdentical'] === true){
                echo("Great! Your photo looks identical to the photo on document<br>");
            }else{
                echo("Oh no! Your photo looks different to the photo on document<br>");
            }
            echo("Similarity score: {$face_result['confidence']}<br>");
        }
    }

}catch(\IDAnalyzer\APIException $ex){
    echo("Error Code: " . $ex->getCode() . ", Error Message: " . $ex->getMessage());
    // View complete error codes under API reference: https://developer.idanalyzer.com/coreapi.html
    switch($ex->getCode()){
        case 1:
            // Invalid API Key
            break;
        case 8:
            // Out of API quota
            break;
        case 9:
            // Document not recognized
            break;
        default:
            // Other error
    }
}catch(InvalidArgumentException $ex){
    echo("Argument Error! " . $ex->getMessage());
}catch(Exception $ex){
    echo("Unexpected Error! " . $ex->getMessage());
}