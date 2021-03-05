<?php

require("../src/Vault.php");

use IDAnalyzer\Vault;

// ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$apikey = "Your API Key";

// API region: US or EU
$api_region = "US";

try{

    // Initialize Vault API with your credentials
    $vault = new Vault($apikey, $api_region);

    // Make API error raise exceptions for API level errors
    $vault->throwAPIException(true);

    // List 5 items created on or after 2021/02/25, sort result by first name in ascending order, starting from first item.
    $vaultItems = $vault->list(array("createtime>=2021/02/25"),"createtime","DESC",10, 0);

    // Print result
    print_r($vaultItems);

    // Or get a single items with vaultid
    // $vaultItem = $vault->get("Vault ID");

}catch(\IDAnalyzer\APIException $ex){
    echo("Error Code: " . $ex->getCode() . ", Error Message: " . $ex->getMessage());
}catch(InvalidArgumentException $ex){
    echo("Argument Error! " . $ex->getMessage());
}catch(Exception $ex){
    echo("Unexpected Error! " . $ex->getMessage());
}

