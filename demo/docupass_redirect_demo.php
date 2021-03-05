<?php
require("../src/DocuPass.php");

use IDAnalyzer\DocuPass;

// ID Analyzer API key available under your web portal https://portal.idanalyzer.com
$apikey = "Your API Key";

// API region: US or EU
$api_region = "US";


if ($_POST['email'] != "") {

    try {
        // Initialize DocuPass with your credentials and company name
        $docupass = new DocuPass($apikey, "My Company Inc.", $api_region);

        // Make API error raise exceptions for API level errors
        $docupass->throwAPIException(true);

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
        $docupass->enableFaceVerification(true, 1, 0.5);

        // Users will have only 1 attempt for verification
        $docupass->setMaxAttempt(1);

        // We want to redirect user back to your website when they are done with verification
        $docupass->setRedirectionURL("https://www.your-website.com/verification_succeeded.html", "https://www.your-website.com/verification_failed.html");
        /*
         * more settings
        $docupass->setReusable(true); // allow DocuPass URL/QR Code to be used by multiple users
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
        $result = $docupass->createRedirection();

        if ($result['error']) {
            // Something went wrong
            die("Error Code: {$result['error']['code']}<br/>Error Message: {$result['error']['message']}");
        } else {
            // Redirect browser to DocuPass URL, the URL will work on both Desktop and Mobile
            header("Location: " . $result['url']);
            die();
        }

    }catch(\IDAnalyzer\APIException $ex){
        echo("Error Code: " . $ex->getCode() . ", Error Message: " . $ex->getMessage());
    }catch(InvalidArgumentException $ex){
        echo("Argument Error! " . $ex->getMessage());
    }catch(Exception $ex){
        echo("Unexpected Error! " . $ex->getMessage());
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DocuPass Redirect Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

</head>
<body>
<div class="container mt-5 mb-5">
    <h1>DocuPass Redirect Demo</h1>
    <p>Enter your email to begin identity verification.</p>
    <form method="post">

        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <button type="submit" class="btn btn-primary">Start Identity Verification</button>
    </form>


</div>
</body>
</html>