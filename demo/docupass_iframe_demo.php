<?php
require("../src/DocuPass.php");



$apikey = "Your API Key"; // ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$api_region = "US"; // or EU if you are from Europe


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DocuPass Iframe Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

</head>
<body>
<div class="container">
    <h1>DocuPass Iframe Demo</h1>
    <p>Enter your email to begin identity verification, please have your mobile phone ready with you.</p>
    <form method="post">

        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="file" class="form-control" name="email" required>
        </div>

        <button type="submit" class="btn btn-primary">Start Identity Verification</button>
    </form>

    <div class="card">
        <div class="card-header">
            Identity Verification
        </div>
        <div class="card-body">
            <p>
                <?php

                if($_POST['email'] != ""){

                    try{

                        $docupass = new \IDAnalyzer\DocuPass();

                        // Initialize DocuPass with your credentials and company name
                        $docupass->init($apikey, "My Company Inc.", $api_region);

                        // We need to set an identifier so that we know internally who we are verifying, this string will be returned in the callback. You can use your own user/customer id.
                        $docupass->setCustomID($_POST['email']);

                        // Enable vault cloud storage to store verification results
                        $docupass->enableVault(true);

                        // Set a callback URL where verification results will be sent, you can use docupass_callback.php under this folder as a template
                        $docupass->setCallbackURL("https://www.your-website.com/docupass_callback.php");

                        // We want DocuPass to return document image and user face image in URL format.
                        $docupass->setCallbackImage(true, true, 1);

                        // We will do a quick check on whether user have uploaded a fake ID
                        $docupass->enableAuthentication(true, "quick", 0.3);

                        // Enable photo facial biometric verification with threshold of 0.5
                        $docupass->enableFaceVerification(true,1,0.5);

                        // Users will have only 1 attempt for verification
                        $docupass->setMaxAttempt(1);

                        // We want to redirect user back to your website when they are done with verification
                        $docupass->setRedirectionURL("https://www.your-website.com/verification_succeeded.html","https://www.your-website.com/verification_failed.html");



                        /*
                         * more settings
                        $docupass->setLanguage("en"); // override auto language detection
                        $docupass->setQRCodeFormat("000000","FFFFFF",5,1); // generate a QR code using custom colors and size
                        $docupass->setWelcomeMessage("We need to verify your driver license before you make a rental booking with our company."); // Display your own greeting message
                        $docupass->setLogo("https://www.your-website.com/logo.png"); // change default logo to your own
                        $docupass->hideBrandingLogo(true); // hide footer logo
                        $docupass->restrictCountry("US,CA,AU"); // accept documents from United States, Canada and Australia
                        $docupass->restrictState("CA,TX,WA"); // accept documents from california, texas and washington
                        $docupass->restrictType("DI"); // accept only driver license and identification card
                        $docupass->verifyExpiry(true); // check document expiry
                        $docupass->verifyAge("18-120"); // check if person is above 18
                        $docupass->verifyDOB("1990/01/01"); // check if person's birthday is 1990/01/01
                        $docupass->verifyDocumentNumber("X1234567"); // check if the person's ID number is X1234567
                        $docupass->verifyName("Elon Musk"); // check if the person is named Elon Musk
                        $docupass->verifyAddress("123 Sunny Rd, California"); // Check if address on ID matches with provided address
                        $docupass->verifyPostcode("90001"); // check if postcode on ID matches with provided postcode
                        */


                        // Create a verification session for this user
                        $result = $docupass->createIframe();


                        if($result['error']){
                            // Something went wrong
                            echo("Error Code: {$result['error']['code']}<br/>Error Message: {$result['error']['message']}");
                        }else{

                            echo("<iframe src=\"{$result['url']}\" frameborder=\"0\" allowtransparency=\"true\" style=\"width: 800px; height: 600px;\"></iframe>");


                        }

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