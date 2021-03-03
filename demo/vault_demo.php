<?php

require("../src/Vault.php");

use IDAnalyzer\Vault;

$apikey = "Your API Key"; // ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$api_region = "US"; // or EU if you are from Europe

try{

    // Initialize Vault API with your credentials
    $vault = new Vault($apikey, $api_region);

    // List 5 items created on or after 2021/02/25, sort result by first name in ascending order, starting from first item.
    $vaultItems = $vault->list(array("createtime>=2021/02/25"),"createtime","DESC",10, 0);

    // Print result
    print_r($vaultItems);

    // Or get a single items with vaultid
    // $vaultItems = $vault->get("Vault ID");

}catch(Exception $ex){
    die("Exception: ".$ex->getMessage());
}

