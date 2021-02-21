<?php
require("../src/CoreAPI.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Analyzer ID Scanner</title>
</head>
<body>
<h1>ID Scanner Demo using Core API</h1>
<form enctype="multipart/form-data" method="post">
    <p>Please upload a document image, front of ID is mandatory.</p>
    *Document Image (Front): <input type="file" required name="DocumentFront"><br/>
    Document Image (Back):<input type="file" name="DocumentBack"><br/>
    Face Photo: <input type="file" name="FacePhoto">
</form>
<div>
    <h2>Result</h2>
    <?php
    if($_FILES['DocumentFront']['tmp_name'] != ""){

        try{

            $coreapi = new \IDAnalyzer\CoreAPI();
            $coreapi->init("Your API Key", "US");

            /*
             * optional parameters
            $coreapi->setAccuracy(2); // Set OCR accuracy to highest
            $coreapi->enableAuthentication(true, 'quick'); // Check if document is real using 'quick' module
            $coreapi->enableBarcodeMode(false); // Disable OCR and scan for AAMVA barcodes only
            $coreapi->enableImageOutput(true,true,"url"); // output cropped document and face region in URL format
            $coreapi->enableDualsideCheck(true); // check if data on front and back of ID matches
            $coreapi->enableVault(true,true,false,false); // enable vault cloud storage to store document information and image
            $coreapi->setVaultData("user@example.com",12345); // store customer information into vault
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
            */


            // perform a scan using uploaded image
            $result = $coreapi->scan($_FILES['DocumentFront']['tmp_name'],$_FILES['DocumentBack']['tmp_name'],$_FILES['FacePhoto']['tmp_name']);

            if($result['error']){
                echo("Error Code: " . $result['error']['code'] . "<br/>Error Message: ". $result['error']['message']);
            }else{
                $data_result = $result['result'];
                $face_result = $result['face'];
                $authentication_result = $result['authentication'];
                $verification_result = $result['verification'];
                $vaultid = $result['vaultid'];
                $matchrate = $result['vaultid'];

                if($data_result['firstName']!=""){
                    echo("Hello your name is {$data_result['firstName']} {$data_result['lastName']}<br>");
                }
                if($data_result['dob']!=""){
                    echo("You were born on {$data_result['dob']}<br>");
                }
                if($face_result){
                    if($face_result['isIdentical']===true){
                        echo("Your photo is looks identical to the photo on document<br>");
                    }else{
                        echo("Your photo looks different to the photo on document<br>");
                    }
                }
                if($authentication_result){
                    if($authentication_result['score']>0.5) {
                        echo("The ID uploaded is authentic<br>");
                    }else if($authentication_result['score']>0.3){
                        echo("The ID uploaded looks little bit suspicious<br>");
                    }else{
                        echo("The ID uploaded is fake<br>");
                    }
                }

                if($matchrate<0.4) {
                    echo("The ID uploaded is too blurry, we couldn't capture most of the data<br>");
                }



            }



        }catch(Exception $ex){
            echo("Error! " . $ex->getMessage());

        }

    }

    ?>
</div>

</body>
</html>
