<?php
require("../src/DocuPass.php");
require("../src/Vault.php");

use IDAnalyzer\DocuPass;
use IDAnalyzer\Vault;

$apikey = "Your API Key"; // ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$api_region = "US"; // or EU if you are from Europe

try{
    // Get raw post body
    $input_raw = file_get_contents('php://input');

    // Parse JSON into associative array
    $data = json_decode($input_raw, true);

    // If we didn't get an array abort the script
    if(!is_array($data)) die();

    // Check if we've gotten required data for validation against DocuPass server, we do this to prevent someone spoofing a callback
    if($data['reference'] =="" || $data['hash'] == "") die();

    // Store the callback payload for debugging
    writeDebugLog($input_raw);

    // Initialize DocuPass with your credentials and company name
    $docupass = new DocuPass($apikey, "My Company Inc.", $api_region);

    // Validate result with DocuPass API Server
    $validation = $docupass->validate($data['reference'],  $data['hash']);


    if($validation){
        $userEmail = $data['customid']; // We've asked user's email in the demo, now we can get the emails they have entered.

        if($data['success'] === true){
            // User has completed verification successfully

            // Maybe we could email them!
            mail($userEmail, "Identity Verification Success", "Dear {$data['data']['firstName']} {$data['data']['lastName']},\n Thank you for verifying your identity.");


        }else{
            // User did not pass identity verification

            // Save failed reason so we can investigate further
            file_put_contents("failed_verifications.txt", "$userEmail has failed identity verification, reference: {$data['reference']}, reason: {$data['failreason']} code: {$data['failcode']}\n", FILE_APPEND);

        }
        // Save user's document images
        foreach($data['documentimage'] as $image){
            $savePath = $data['reference'] . "_" . $image['side']. ".jpg";
            if($image['url']!=""){
                // Download image from remote url
                downloadUrlToFile($image['url'],$savePath);
            }else if($image['content']!=""){
                // Save base64 content as file
                file_put_contents($savePath, base64_decode($image['content']));
            }
        }

        // Save user's face image
        foreach($data['faceimage'] as $image){
            $savePath = $data['reference'] . "_face.jpg";
            if($image['url']!=""){
                // Download image from remote url
                downloadUrlToFile($image['url'],$savePath);
            }else if($image['content']!=""){
                // Save base64 content as file
                file_put_contents($savePath, base64_decode($image['content']));
            }
        }

        // We could use the Vault ID and get verification results from Vault
        if($data['vaultid'] != ""){
            // Initialize Vault API with your credentials
            $vault = new Vault($apikey, $api_region);

            // Get the vault entry using Vault Entry ID received from Core API
            $vaultdata = $vault->get($data['vaultid']);


            file_put_contents("docupass_from_vault.txt",  print_r($vaultdata, true), FILE_APPEND);

        }

        // We could also query the vault with DocuPass reference
        if($data['vaultid'] != ""){
            // Initialize Vault API with your credentials
            $vault = new Vault($apikey, $api_region);

            // Get the vault entry using Vault Entry ID received from Core API
            $vaultItems = $vault->list(["docupass_reference={$data['reference']}"]);

            if(count($vaultItems['items'])>0){
                file_put_contents("docupass_from_vault.txt",  print_r($vaultItems['items'][0], true), FILE_APPEND);
            }


        }
    }else{
        writeDebugLog("Could not validate the authenticity of this request");
    }


}catch(Exception $ex){
    writeDebugLog("Exception: ".$ex->getMessage());
}

function writeDebugLog($message){
    file_put_contents("callback_debug.txt",  $message . "\n", FILE_APPEND);
}

function downloadUrlToFile($url, $outFileName)
{
    if(is_file($url)) {
        copy($url, $outFileName);
    } else {
        $options = array(
            CURLOPT_FILE    => fopen($outFileName, 'w'),
            CURLOPT_TIMEOUT =>  30,
            CURLOPT_URL     => $url
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        curl_close($ch);
    }
}