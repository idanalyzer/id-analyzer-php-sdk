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

        // Raise exceptions for API level errors
        $docupass->throwAPIException(true);

        // We need to set an identifier so that we know internally who is signing the document, this string will be returned in the callback. You can use your own user/customer id.
        $docupass->setCustomID($_POST['email']);

        // Enable vault cloud storage to store signed document
        $docupass->enableVault(true);

        // Set a callback URL where signed document will be sent, you can use docupass_callback.php under this folder as a template to receive the result
        $docupass->setCallbackURL("https://www.your-website.com/docupass_callback.php");

        // We want to redirect user back to your website when they are done with document signing, there will be no fail URL unlike identity verification
        $docupass->setRedirectionURL("https://www.your-website.com/document_signed.html", "");

        /*
         * more settings
        $docupass->setReusable(true); // allow DocuPass URL/QR Code to be used by multiple users
        $docupass->setLanguage("en"); // override auto language detection
        $docupass->setQRCodeFormat("000000","FFFFFF",5,1); // generate a QR code using custom colors and size
        $docupass->hideBrandingLogo(true); // hide default branding footer
        $docupass->setCustomHTML("https://www.yourwebsite.com/docupass_template.html"); // use your own HTML/CSS for DocuPass page
        $docupass->smsContractLink("+1333444555"); // Send signing link to user's mobile phone
        */

        // Assuming in your contract template you have a dynamic field %{email} and you want to fill it with user email
        $prefill = array(
          "email" => $_POST['email']
        );

        // Create a signature session
        $result = $docupass->createSignature("Template ID", "PDF", $prefill);

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
    <h1>DocuPass Signature Demo</h1>
    <p>Enter your email to sign legal document.</p>
    <form method="post">

        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <button type="submit" class="btn btn-primary">Sign Document</button>
    </form>


</div>
</body>
</html>