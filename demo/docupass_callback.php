<?php
require("../src/DocuPass.php");
require("../src/Vault.php");

use IDAnalyzer\DocuPass;
use IDAnalyzer\Vault;

// ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$apikey = "Your API Key";

// API region: US or EU
$api_region = "US";

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
            // User has completed verification successfully, or has signed legal document

            // Maybe we could email them!
            mail($userEmail, "Identity Verification Success", "Dear {$data['data']['firstName']} {$data['data']['lastName']},\n Thank you for verifying your identity.");


        }else{
            // User did not pass identity verification

            // Save failed reason so we can investigate further
            file_put_contents("failed_verifications.txt", "$userEmail has failed identity verification, reference: {$data['reference']}, reason: {$data['failreason']} code: {$data['failcode']}\n", FILE_APPEND);

        }

        // Save user's document images
        if(is_array($data['documentimage'])){
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
        }

        // Save user's face image
        if(is_array($data['faceimage'])) {
            foreach ($data['faceimage'] as $image) {
                $savePath = $data['reference'] . "_face.jpg";
                if ($image['url'] != "") {
                    // Download image from remote url
                    downloadUrlToFile($image['url'], $savePath);
                } else if ($image['content'] != "") {
                    // Save base64 content as file
                    file_put_contents($savePath, base64_decode($image['content']));
                }
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

        // If you have generated a legal document or have your user signed a contract, the contract file URL will be contained in contract.document_url
        if($data['contract']['document_url'] != ""){
            // Download contract and save it as user_contract.pdf
            downloadUrlToFile($data['contract']['document_url'], "user_contract.pdf");
        }

    }else{
        writeDebugLog("Could not validate the authenticity of this request");
    }

}catch(\IDAnalyzer\APIException $ex){
    writeDebugLog("Error Code: " . $ex->getCode() . ", Error Message: " . $ex->getMessage());
}catch(InvalidArgumentException $ex){
    writeDebugLog("Argument Error! " . $ex->getMessage());
}catch(Exception $ex){
    writeDebugLog("Unexpected Error! " . $ex->getMessage());
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