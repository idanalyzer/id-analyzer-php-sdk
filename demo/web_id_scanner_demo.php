<?php
require("../src/CoreAPI.php");
require("../src/Vault.php");


use IDAnalyzer\CoreAPI;
use IDAnalyzer\Vault;

// ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$apikey = "Your API Key";

// API region: US or EU
$api_region = "US";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Analyzer ID Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

</head>
<body>
<div class="container mt-5">
    <h1>ID Scanner Demo using Core API</h1>
    <p>Please upload your identification document for verification (front of ID is mandatory).</p>
    <form enctype="multipart/form-data" method="post">

        <div class="mb-3">
            <label class="form-label">Document Image (Front) *</label>
            <input type="file" class="form-control" name="DocumentFront" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Document Image (Back)</label>
            <input type="file" class="form-control" name="DocumentBack">
        </div>
        <div class="mb-3">
            <label class="form-label">Face Photo</label>
            <input type="file" class="form-control" name="FacePhoto">
        </div>
        <button type="submit" class="btn btn-primary">Check My ID</button>
    </form>

    <div class="card mt-5 mb-5">
        <div class="card-header">
            Result
        </div>
        <div class="card-body">
            <p>
                <?php
                // We need at very least one document image to perform ID scan
                if($_FILES['DocumentFront']['tmp_name'] != ""){

                    try{
                        // Initialize Core API with your credentials
                        $coreapi = new CoreAPI($apikey, $api_region);

                        // Make API error raise exceptions for API level errors (such as out of quota, document not recognized)
                        $coreapi->throwAPIException(true);

                        // enable vault cloud storage to store document information and image
                        $coreapi->enableVault(true,false,false,false);

                        // quick fake id check
                        $coreapi->enableAuthentication(true, 'quick'); // check if document is real using 'quick' module


                        /*
                         * more settings
                        $coreapi->setAccuracy(2); // set OCR accuracy to highest
                        $coreapi->setBiometricThreshold(0.6); // make face verification more strict
                        $coreapi->enableBarcodeMode(false); // disable OCR and scan for AAMVA barcodes only
                        $coreapi->enableImageOutput(true,true,"url"); // output cropped document and face region in URL format
                        $coreapi->enableDualsideCheck(true); // check if data on front and back of ID matches
                        $coreapi->setVaultData("user@example.com",12345,"AABBCC"); // store custom data into vault
                        $coreapi->restrictCountry("US,CA,AU"); // accept documents from United States, Canada and Australia
                        $coreapi->restrictState("CA,TX,WA"); // accept documents from california, texas and washington
                        $coreapi->restrictType("DI"); // accept only driver license and identification card
                        $coreapi->setOCRImageResize(0); // disable OCR resizing
                        $coreapi->verifyExpiry(true); // check document expiry
                        $coreapi->verifyAge("18-120"); // check if person is above 18
                        $coreapi->verifyDOB("1990/01/01"); // check if person's birthday is 1990/01/01
                        $coreapi->verifyDocumentNumber("X1234567"); // check if the person's ID number is X1234567
                        $coreapi->verifyName("Elon Musk"); // check if the person is named Elon Musk
                        $coreapi->verifyAddress("123 Sunny Rd, California"); // Check if address on ID matches with provided address
                        $coreapi->verifyPostcode("90001"); // check if postcode on ID matches with provided postcode
                        $coreapi->enableAMLCheck(true); // enable AML/PEP compliance check
                        $coreapi->setAMLDatabase("global_politicians,eu_meps,eu_cors"); // limit AML check to only PEPs
                        $coreapi->enableAMLStrictMatch(true); // make AML matching more strict to prevent false positives
                        $coreapi->generateContract("Template ID", "PDF", array("email"=>"user@example.com")); // generate a PDF document autofilled with data from user ID
                        */

                        // perform a scan using uploaded image
                        $result = $coreapi->scan($_FILES['DocumentFront']['tmp_name'], $_FILES['DocumentBack']['tmp_name'], $_FILES['FacePhoto']['tmp_name']);

                        // or perform a scan using remote image url
                        // $result = $coreapi->scan("https://www.idanalyzer.com/img/sampleid1.jpg");


                        // We gotten the result array

                        $data_result = $result['result'];
                        $face_result = $result['face'];
                        $authentication_result = $result['authentication'];
                        $verification_result = $result['verification'];
                        $vaultid = $result['vaultid'];
                        $matchrate = $result['matchrate'];


                        // Print some data from OCR results
                        if($data_result['firstName'] != ""){
                            echo("Hello your name is {$data_result['firstName']} {$data_result['lastName']}<br>");
                        }
                        if($data_result['dob']!=""){
                            echo("You were born on {$data_result['dob']}<br>");
                        }
                        if($data_result['documentType'] != ""){
                            switch($data_result['documentType']){
                                case "P":
                                    $documentType = "Passport";
                                    break;
                                case "I":
                                    $documentType = "Identification Card";
                                    break;
                                case "D":
                                    $documentType = "Driver License";
                                    break;
                                case "V":
                                    $documentType = "Visa";
                                    break;
                                default:
                                    $documentType = "Other";
                            }
                            echo("Thank you for uploading your {$documentType} issued by {$data_result['issuerOrg_region_full']} {$data_result['issuerOrg_full']}<br>");
                        }


                        // Parse face verification results
                        if($face_result){
                            if($face_result['error']){
                                // View complete error codes under API reference: https://developer.idanalyzer.com/coreapi.html
                                echo("Face verification failed! Code: {$face_result['error']}, Reason: {$face_result['error_message']}<br>");
                            }else{
                                if($face_result['isIdentical'] === true){
                                    echo("Great! Your photo looks identical to the photo on document<br>");
                                }else{
                                    echo("Oh no! Your photo looks different to the photo on document<br>");
                                }
                                echo("Similarity score: {$face_result['confidence']}<br>");
                            }
                        }

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

                        // Check if document is blurry
                        if($matchrate<0.4) {
                            echo("The document uploaded is too blurry, we couldn't capture some of the data<br>");
                        }

                        // print Core API Results
                        echo("<br><br>Core API Results:<br>");
                        print_r($result);

                        // Retrieve the identity information from Vault
                        if($vaultid != ""){
                            echo("<br><br>Data from Vault:<br>");

                            // Initialize Vault API with your credentials
                            $vault = new Vault($apikey, $api_region);

                            // Get the vault entry using Vault Entry ID received from Core API
                            $vaultdata = $vault->get($vaultid);

                            print_r($vaultdata);
                        }

                    }catch(\IDAnalyzer\APIException $ex){
                        // We can catch API level exceptions if we call throwAPIException(true);
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
                        echo("Error! " . $ex->getMessage());
                    }

                }

                ?>
            </p>
        </div>
    </div>
</div>


</body>
</html>