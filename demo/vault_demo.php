<?php

require("../src/Vault.php");

use IDAnalyzer\Vault;

$apikey = "Your API Key"; // ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$api_region = "US"; // or EU if you are from Europe

try{

    // Initialize Vault API with your credentials
    $vault = new Vault($apikey, $api_region);

    // Get 10 newest items from vault
    $vaultItems = $vault->list("","createtime","DESC",10);

    // Print result
    print_r($vaultItems);

    // Or get a single items with vaultid
    // $vaultItems = $vault->get("Vault ID");

}catch(Exception $ex){
    die("Exception: ".$ex->getMessage());
}

