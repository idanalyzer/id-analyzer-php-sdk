<?php

require("../src/AMLAPI.php");

use IDAnalyzer\AMLAPI;

// ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$apikey = "Your API Key";

// API region: US or EU
$api_region = "US";

try{

    // Initialize AML API with your credentials
    $aml = new AMLAPI($apikey, $api_region);

    // Make API error raise exceptions for API level errors
    $aml->throwAPIException(true);

    // Set AML database to only search the PEP category
    $aml->setAMLDatabase("global_politicians,eu_cors,eu_meps");

    // Search for a politician
    $result = $aml->searchByName("Joe Biden");
    print_r($result);

    // Set AML database to all databases
    $aml->setAMLDatabase("");

    // Search for a sanctioned ID number
    $result = $aml->searchByIDNumber("AALH750218HBCLPC02");
    print_r($result);

}catch(\IDAnalyzer\APIException $ex){
    echo("Error Code: " . $ex->getCode() . ", Error Message: " . $ex->getMessage());
}catch(InvalidArgumentException $ex){
    echo("Argument Error! " . $ex->getMessage());
}catch(Exception $ex){
    echo("Unexpected Error! " . $ex->getMessage());
}

