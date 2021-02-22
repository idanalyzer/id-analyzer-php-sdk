
# ID Analyzer PHP SDK
This is a PHP SDK for [ID Analyzer Identity Verification APIs](https://www.idanalyzer.com), though all the APIs can be called with without the SDK using simple HTTP requests as outlined in the [documentation](https://developer.idanalyzer.com), you can use this SDK to accelerate server-side development.

We strongly discourage users to connect to ID Analyzer API endpoint directly  from client-side applications that will be distributed to end user, such as mobile app, or in-browser JavaScript. Your API key could be easily compromised therefore the best practice is always to call our APIs from the server-side.

## Installation
Install through composer

    composer require idanalyzer/id-analyzer-php-sdk
Alternatively, download this package and manually require the php files under **src** folder.

## Core API
[ID Analyzer Core API](https://www.idanalyzer.com/products/id-analyzer-core-api.html) allows you to perform OCR data extraction, facial biometric verification, identity verification, age verification, document cropping, document authentication (fake ID check) using an ID image (JPG, PNG, PDF accepted) and user selfie photo or video. Core API has great global coverage, supporting over 98% of the passports, driver licenses and identification cards currently being circulated around the world.

![Sample ID](https://www.idanalyzer.com/img/sampleid1.jpg)

The sample code below will extract data from this sample Driver License issued in California, and check whether it is real or fake.

    $coreapi = new \IDAnalyzer\CoreAPI();  
      
    // Initialize Core API US Region with your credentials  
    $coreapi->init("Your API Key", "US"); 
    
    // Enable authentication module v2 to check if ID is authentic
    $coreapi->enableAuthentication(true, 2);
	
	// Analyze the ID image by passing URL of the ID image (you may also use a local file)
    $result = $coreapi->scan("https://www.idanalyzer.com/img/sampleid1.jpg");
    
    // All the information about this ID will be returned in an associative array
	$data_result = $result['result'];   
	$authentication_result = $result['authentication'];  

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
You could also set additional parameters before performing ID scan:
	
    
	$coreapi->enableVault(true,true,false,false);  // enable vault cloud storage to store document information and image
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

To **scan both front and back of ID**:

	$result = $coreapi->scan("path/to/id_front.jpg", "path/to/id_back.jpg");
To perform **biometric photo verification**:

	$result = $coreapi->scan("path/to/id.jpg", "", "path/to/face.jpg");
To perform **biometric video verification**:

	$result = $coreapi->scan("path/to/id.jpg", "", "", "path/to/video.mp4", "1234");
Check out sample response array fields visit [Core API reference](https://developer.idanalyzer.com/coreapi.html##readingresponse).