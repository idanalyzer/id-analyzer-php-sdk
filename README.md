
# ID Analyzer PHP SDK
This is a PHP SDK for [ID Analyzer Identity Verification APIs](https://www.idanalyzer.com), though all the APIs can be called with without the SDK using simple HTTP requests as outlined in the [documentation](https://developer.idanalyzer.com), you can use this SDK to accelerate server-side development.

We strongly discourage users to connect to ID Analyzer API endpoint directly  from client-side applications that will be distributed to end user, such as mobile app, or in-browser JavaScript. Your API key could be easily compromised, and if you are storing your customer's information inside Vault they could use your API key to fetch all your user details. Therefore, the best practice is always to implement a client side connection to your server, and call our APIs from the server-side.

## Installation
Install through composer

```shell
composer require idanalyzer/id-analyzer-php-sdk
```
Alternatively, download this package and manually require the PHP files under **src** folder.

## Core API
[ID Analyzer Core API](https://www.idanalyzer.com/products/id-analyzer-core-api.html) allows you to perform OCR data extraction, facial biometric verification, identity verification, age verification, document cropping, document authentication (fake ID check), AML/PEP compliance check, and paperwork automation using an ID image (JPG, PNG, PDF accepted) and user selfie photo or video. Core API has great global coverage, supporting over 98% of the passports, driver licenses and identification cards currently being circulated around the world.

![Sample ID](https://www.idanalyzer.com/img/sampleid1.jpg)

The sample code below will extract data from this sample Driver License issued in California, compare it with a [photo of Lena](https://upload.wikimedia.org/wikipedia/en/7/7d/Lenna_%28test_image%29.png), and check whether the ID is real or fake.

```php
// use composer autoload
require("vendor/autoload.php");

// or manually load CoreAPI class
//require("../src/CoreAPI.php");  
  
use IDAnalyzer\CoreAPI; 

// Initialize Core API US Region with your credentials  
$coreapi = new CoreAPI("Your API Key", "US");  

// Enable authentication and use 'quick' module to check if ID is authentic
$coreapi->enableAuthentication(true, 'quick');

// Analyze ID image by passing URL of the ID image and a face photo (you may also use a local file)
$result = $coreapi->scan("https://www.idanalyzer.com/img/sampleid1.jpg","","https://upload.wikimedia.org/wikipedia/en/7/7d/Lenna_%28test_image%29.png");

// All information about this ID will be returned in an associative array
$data_result = $result['result'];   
$authentication_result = $result['authentication'];  
$face_result = $result['face'];  

// Print result
echo("Hello your name is {$data_result['firstName']} {$data_result['lastName']}<br>");

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
```
You could also set additional parameters before performing ID scan:  
```php
$coreapi->enableVault(true,false,false,false);  // enable vault cloud storage to store document information and image
$coreapi->setBiometricThreshold(0.6); // make face verification more strict  
$coreapi->enableAuthentication(true, 'quick'); // check if document is real using 'quick' module  
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
```

To **scan both front and back of ID**:

```php
$result = $coreapi->scan("path/to/id_front.jpg", "path/to/id_back.jpg");
```
To perform **biometric photo verification**:

```php
$result = $coreapi->scan("path/to/id.jpg", "", "path/to/face.jpg");
```
To perform **biometric video verification**:

```php
$result = $coreapi->scan("path/to/id.jpg", "", "", "path/to/video.mp4", "1234");
```
Check out sample response array fields visit [Core API reference](https://developer.idanalyzer.com/coreapi.html##readingresponse).

## DocuPass Identity Verification API
[DocuPass](https://www.idanalyzer.com/products/docupass.html) allows you to verify your users without designing your own web page or mobile UI. A unique DocuPass URL can be generated for each of your users and your users can verify their own identity by simply opening the URL in their browser. DocuPass URLs can be directly opened using any browser,  you can also embed the URL inside an iframe on your website, or within a WebView inside your iOS/Android/Cordova mobile app.

![DocuPass Screen](https://www.idanalyzer.com/img/docupassliveflow.jpg)

DocuPass comes with 4 modules and you need to [choose an appropriate DocuPass module](https://www.idanalyzer.com/products/docupass.html) for integration.

To start, we will assume you are trying to **verify one of your user that has an ID of "5678"** in your own database, we need to **generate a DocuPass verification request for this user**. A unique **DocuPass reference code** and **URL** will be generated.

```php
// use composer autoload
require("vendor/autoload.php");

// or manually load DocuPass class
//require("../src/DocuPass.php");  

use IDAnalyzer\DocuPass;

// Initialize DocuPass with your credential, company name and API region
$docupass = new DocuPass("API Key", "My Company Inc.", "US");  

// We need to set an identifier so that we know internally who we are verifying, this string will be returned in the callback. You can use your own user/customer id.  
$docupass->setCustomID("5678");  

// Enable vault cloud storage to store verification results, so we can look up the results  
$docupass->enableVault(true);  

// Set a callback URL where verification results will be sent, you can use docupass_callback.php in demo folder as a template  
$docupass->setCallbackURL("https://www.your-website.com/docupass_callback.php"); 

// We want DocuPass to return document image and user face image in URL format so we can store them on our own server later.  
$docupass->setCallbackImage(true, true, 1);  

// We will do a quick check on whether user have uploaded a fake ID  
$docupass->enableAuthentication(true, "quick", 0.3);  

// Enable photo facial biometric verification with threshold of 0.5  
$docupass->enableFaceVerification(true, 1, 0.5);  

// Users will have only 1 attempt at verification  
$docupass->setMaxAttempt(1);  

// We want to redirect user back to your website when they are done with verification  
$docupass->setRedirectionURL("https://www.your-website.com/verification_succeeded.php", "https://www.your-website.com/verification_failed.php");

// Get user to review and sign legal document (such as rental contract) prefilled with ID data
$docupass->signContract("Template ID", "PDF"); 

// Create a session using DocuPass Standard Mobile module
$result = $docupass->createMobile();
  
if($result['error']){  
    // Something went wrong  
    echo("Error Code: {$result['error']['code']}<br/>Error Message: {$result['error']['message']}");  
}else{  
    echo("Scan the QR Code below to verify your identity: <br/>");  
    echo("<img src=\"{$result['qrcode']}\"><br/>");  
    echo("Or open your mobile browser and type in: ");  
    echo("<a href=\"{$result['url']}\">{$result['url']}</a>");  

}
```
If you are looking to embed DocuPass into your mobile application, simply embed `$result['url']` inside a WebView. To tell if verification has been completed monitor the WebView URL and check if it matches the URLs set in `setRedirectionURL`. (DocuPass Live Mobile currently cannot be embedded into native iOS App due to OS restrictions, you will need to open it with Safari)

Check out additional DocuPass settings:

```php
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
$docupass->setCustomHTML("https://www.yourwebsite.com/docupass_template.html"); // use your own HTML/CSS for DocuPass page
$docupass->smsVerificationLink("+1333444555"); // Send verification link to user's mobile phone
$docupass->enablePhoneVerification(true); // get user to input their own phone number for verification
$docupass->verifyPhone("+1333444555"); // verify user's phone number you already have in your database
$docupass->enableAMLCheck(true); // enable AML/PEP compliance check
$docupass->setAMLDatabase("global_politicians,eu_meps,eu_cors"); // limit AML check to only PEPs
$docupass->enableAMLStrictMatch(true); // make AML matching more strict to prevent false positives
$docupass->generateContract("Template ID", "PDF", array("somevariable"=>"somevalue")); // automate paperwork by generating a document autofilled with ID data 
```

Now we need to write a **callback script** or if you prefer to call it a **webhook**, to receive the verification results. This script will be called as soon as user finishes identity verification. In this guide, we will name it **docupass_callback.php**:

```php
// use composer autoload
require("vendor/autoload.php");

// or manually load DocuPass class
//require("../src/DocuPass.php");  

use IDAnalyzer\DocuPass;


try{  
    // Get raw post body  
    $input_raw = file_get_contents('php://input');  

    // Parse JSON into associative array  
    $data = json_decode($input_raw, true);  

    // If we didn't get an array abort the script  
    if(!is_array($data)) die();  

    // Check if we've gotten required data for validation against DocuPass server, we do this to prevent someone spoofing a callback  
    if($data['reference'] =="" || $data['hash'] == "") die();  
    
    // Initialize DocuPass with your credentials and company name  
    $docupass = new DocuPass("Your API Key", "My Company Inc.", "US");  

    // Validate result with DocuPass API Server  
    $validation = $docupass->validate($data['reference'], $data['hash']);  
  
    if($validation){  
        $userID = $data['customid']; // This value should be "5678" matching the User ID in your database
      
        if($data['success'] === true){  
            // User has completed identity verification successfully, or has signed legal document

            // Get some information about your user
            $firstName = $data['data']['firstName'];
            $lastName = $data['data']['lastName'];
            $dob = $data['data']['dob'];

            // Additional steps to store identity data into your own database
          
         }else{  
            // User did not pass identity verification  
            $fail_code = $data['failcode'];
            $fail_reason = $data['failreason'];
             
            // Save failed reason so we can investigate further  
            file_put_contents("failed_verifications.txt", "$userID has failed identity verification, DocuPass Reference: {$data['reference']}, Fail Reason: {$fail_reason} Fail Code: {$fail_code}\n", FILE_APPEND);  
            
            // Additional steps to store why identity verification failed into your own database
          
         }  
    }else{
        throw new Exception("Failed to validate DocuPass results against DocuPass server");
    }
}catch(Exception $ex){  
    file_put_contents("docupass_exception.txt", $ex->getMessage()."\n", FILE_APPEND);  
}        
```

Visit [DocuPass Callback reference](https://developer.idanalyzer.com/docupass_callback.html) to check out the full payload returned by DocuPass.

For the final step, you could create two web pages (URLS set via `setRedirectionURL`) that display the results to your user. DocuPass reference will be passed as a GET parameter when users are redirected, for example: https://www.your-website.com/verification_succeeded.php?reference=XXXXXXXXX, you could use the reference code to fetch the results from your database. P.S. We will always send callbacks to your server before redirecting your user to the set URL.

## DocuPass Signature API

You can get user to solely review and sign document in DocuPass without identity verification, to do so you need to create a DocuPass Signature session.

```php
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
```

Once user has reviewed and signed the document, the signed document will be sent back to your server using callback under the `contract.document_url` field, the contract will also be saved to vault if you have enabled vault.

## Vault API

ID Analyzer provides free cloud database storage (Vault) for you to store data obtained through Core API and DocuPass. You can set whether you want to store your user data into Vault through `enableVault` while making an API request with PHP SDK. Data stored in Vault can be looked up through [Web Portal](https://portal.idanalyzer.com) or via Vault API.

If you have enabled Vault, Core API and DocuPass will both return a vault entry identifier string called `vaultid`,  you can use the identifier to look up your user data:

```php
// use composer autoload
require("vendor/autoload.php");

// or manually load DocuPass class
//require("../src/Vault.php");  

use IDAnalyzer\Vault;

// Initialize Vault API with your credentials  
$vault = new Vault("API Key", "US");  
  
// Get the vault entry using Vault Entry Identifier received from Core API/DocuPass 
$vaultdata = $vault->get("VAULT_ID");
```
You can also list the items in your vault, the following example list 5 items created on or after 2021/02/25, sorted by first name in ascending order, starting from first item:

```php
$vaultItems = $vault->list(array("createtime>=2021/02/25"), "firstName","ASC", 5, 0);
```

Alternatively, you may have a DocuPass reference code which you want to search through vault to check whether user has completed identity verification:

```php
$vaultItems = $vault->list(["docupass_reference=XXXXXXXXXXXXX"]);
```
Learn more about [Vault API](https://developer.idanalyzer.com/vaultapi.html).

## AML API

ID Analyzer provides Anti-Money Laundering AML database consolidated from worldwide authorities,  AML API allows our subscribers to lookup the database using either a name or document number. It allows you to instantly check if a person or company is listed under **any kind of sanction, criminal record or is a politically exposed person(PEP)**, as part of your **AML compliance KYC**. You may also use automated check built within Core API and DocuPass.

```php
// use composer autoload
require("vendor/autoload.php");

// or manually load DocuPass class
//require("../src/AMLAPI.php");  

use IDAnalyzer\AMLAPI;

// Initialize AML API with your credentials
$aml = new AMLAPI($apikey, $api_region);

// Make API error raise exceptions for API level errors
$aml->throwAPIException(true);

// Set AML database to only search the PEP category
$aml->setAMLDatabase("global_politicians,eu_cors,eu_meps");

// Search for a politician
$result = $aml->searchByName("Joe Biden");
print_r($result);

// Set AML database to all databases
$aml->setAMLDatabase("");

// Search for a sanctioned ID number
$result = $aml->searchByIDNumber("AALH750218HBCLPC02");
print_r($result);
```

Learn more about [AML API](https://developer.idanalyzer.com/amlapi.html).

## Error Catching

The API server may return error responses such as when document cannot be recognized. You can either manually inspect the response returned by API, or you may set `$api->throwAPIException(true);` to raise an `APIException` when API error is encountered. You can then use try/catch block on functions that calls the API such as `scan`, `createMobile`, `createLiveMobile`, `createIframe`, `createRedirection`, and all the functions for Vault to catch the errors. `InvalidArgumentException` is thrown when you pass incorrect arguments to any of the functions.

```php

try{
	...    
}catch(\IDAnalyzer\APIException $ex){
    echo("Error Code: " . $ex->getCode() . ", Error Message: " . $ex->getMessage());
    // View complete list of error codes under API reference: https://developer.idanalyzer.com/
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
    echo("Unexpected Error! " . $ex->getMessage());
}
```

## Demo
Check out **/demo** folder for more PHP demo codes.

## SDK Reference
Check out [ID Analyzer PHP SDK Reference](https://idanalyzer.github.io/id-analyzer-php-sdk/)