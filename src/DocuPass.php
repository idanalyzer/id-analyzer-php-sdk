<?php

namespace IDAnalyzer;

use Exception;
use InvalidArgumentException;



class DocuPass
{
    private $apikey;
    private $apiendpoint = "";
    private $throwError = false;
    private $config, $defaultconfig = array(
        "companyname" => "",
        "callbackurl" => "",
        "biometric" => 0,
        "authenticate_minscore" => 0,
        "authenticate_module" => 2,
        "maxattempt" => 1,
        "documenttype" => "",
        "documentcountry" => "",
        "documentregion" => "",
        "dualsidecheck" => false,
        "verify_expiry" => false,
        "verify_documentno" => "",
        "verify_name" => "",
        "verify_dob" => "",
        "verify_age" => "",
        "verify_address" => "",
        "verify_postcode" => "",
        "successredir" => "",
        "failredir" => "",
        "customid" => "",
        "vault_save" => true,
        "return_documentimage" => true,
        "return_faceimage" => true,
        "return_type" => 1,
        "qr_color" => "",
        "qr_bgcolor" => "",
        "qr_size" => "",
        "qr_margin" => "",
        "welcomemessage" => "",
        "nobranding" => "",
        "logo" => "",
        "language" => "",
        "biometric_threshold" => 0.4,
        "reusable" => false,
        "aml_check" => false,
        "aml_strict_match" => false,
        "aml_database" => "",
        "phoneverification" => false,
        "verify_phone" => "",
        "sms_verification_link" => "",
        "customhtmlurl" => "",
        "contract_generate" => "",
        "contract_sign" => "",
        "contract_format" => "",
        "contract_prefill_data" => "",
        "sms_contract_link" => "",
        "client" => 'php-sdk'

    );

    private $urlRegex = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))#iS";


    /**
     * Initialize DocuPass API with an API key, company name and optional region (US, EU)
     * @param string $apikey You API key
     * @param string $companyName Your company name
     * @param string $region US/EU
     * @throws InvalidArgumentException
     * @return void
     */
    public function __construct($apikey, $companyName = "My Company Name", $region = "US")
    {
        if($apikey == "") throw new InvalidArgumentException("Please provide an API key");
        if($companyName == "") throw new InvalidArgumentException("Please provide your company name");
        $this->apikey = $apikey;
        $this->config['companyname'] = $companyName;
        if($region === 'eu' || $region === "EU"){
            $this->apiendpoint = "https://api-eu.idanalyzer.com/";
        }else if($region === 'us' || $region === "US"){
            $this->apiendpoint = "https://api.idanalyzer.com/";
        }else{
            $this->apiendpoint = $region;
        }

    }


    /**
     * Whether an exception should be thrown if API response contains an error message
     * @param bool $throwException
     * @return void
     */
    public function throwAPIException($throwException = false)
    {
        $this->throwError = $throwException == true;
    }

    /**
     * Reset all API configurations except API key and region.
     * @return void
     */
    public function resetConfig()
    {
        $this->config = $this->defaultconfig;
    }

    /**
     * Set max verification attempt per user
     * @param int $max_attempt 1 to 10
     * @return void
     * @throws InvalidArgumentException
     */
    public function setMaxAttempt($max_attempt = 1)
    {
        if(!is_numeric($max_attempt) || $max_attempt<1 || $max_attempt>10){
            throw new InvalidArgumentException("Invalid max attempt, please specify integer between 1 to 10.");

        }
        $this->config['maxattempt'] = $max_attempt;
    }

    /**
     * Set a custom string that will be sent back to your server's callback URL, and appended to redirection URLs as a query string. It is useful for identifying your user within your database. This value will be stored under docupass_customid under Vault.
     * @param string $customID A string used to identify your customer internally
     * @return void
     */
    public function setCustomID($customID = "12345")
    {
        $this->config['customid'] = $customID;
    }

    /**
     * Display a custom message to the user in the beginning of verification
     * @param string $message Plain text string
     * @return void
     */
    public function setWelcomeMessage($message)
    {
        $this->config['welcomemessage'] = $message;
    }


    /**
     * Replace footer logo with your own logo
     * @param string $url Logo URL
     * @return void
     */
    public function setLogo($url = "https://docupass.app/asset/logo1.png")
    {
        $this->config['logo'] = $url;
    }


    /**
     * Hide all branding logo
     * @param bool $hide
     * @return void
     */
    public function hideBrandingLogo($hide = false)
    {
        $this->config['nobranding'] = $hide == true;
    }

    /**
     * Replace DocuPass page content with your own HTML and CSS, you can download the HTML/CSS template from DocuPass API Reference page
     * @param string $url URL pointing to your own HTML page
     * @return void
     */
    public function setCustomHTML($url)
    {
        $this->config['customhtmlurl'] = $url;
    }

    /**
     * Set an API parameter and its value, this function allows you to set any API parameter without using the built-in functions
     * @param string $parameterKey Parameter key
     * @param string $parameterValue Parameter value
     * @return void
     */
    public function setParameter($parameterKey, $parameterValue)
    {
        $this->config[$parameterKey] = $parameterValue;
    }



    /**
     * DocuPass automatically detects user device language and display corresponding language. Set this parameter to override automatic language detection.
     * @param string $language Check DocuPass API reference for language code
     * @return void
     */
    public function setLanguage($language)
    {
        $this->config['language'] = $language;
    }



    /**
     * Set server-side callback/webhook URL to receive verification results
     * @param string $url Callback URL
     * @return void
     * @throws InvalidArgumentException
     */
    public function setCallbackURL($url = "https://www.example.com/docupass_callback.php")
    {
        if (!preg_match($this->urlRegex, $url)) {
            throw new InvalidArgumentException("Invalid URL format");
        }

        $urlinfo = parse_url($url);

        if(filter_var($urlinfo['host'], FILTER_VALIDATE_IP)) {
            if(!filter_var(
                $urlinfo['host'],
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
            )){
                throw new InvalidArgumentException("Invalid URL, the host does not appear to be a remote host.");
            }
        }
        if(strtolower($urlinfo['host'])=='localhost'){
            throw new InvalidArgumentException("Invalid URL, the host does not appear to be a remote host.");
        }
        if($urlinfo['scheme']!='http' && $urlinfo['scheme']!='https'){
            throw new InvalidArgumentException("Invalid URL, only http and https protocols are allowed.");
        }
        $this->config['callbackurl'] = $url;
    }


    /**
     * Redirect client browser to set URLs after verification. DocuPass reference code and customid will be appended to the end of URL, e.g. https://www.example.com/success.php?reference=XXXXXXXX&customid=XXXXXXXX
     * @param string $successURL Redirection URL after verification succeeded
     * @param string $failURL Redirection URL after verification failed
     * @return void
     * @throws InvalidArgumentException
     */
    public function setRedirectionURL($successURL = "https://www.example.com/success.php", $failURL = "https://www.example.com/failed.php")
    {
        if ($successURL!="" && !preg_match($this->urlRegex,$successURL)) {
            throw new InvalidArgumentException("Invalid URL format for success URL");
        }
        if ($failURL!="" && !preg_match($this->urlRegex,$failURL)) {
            throw new InvalidArgumentException("Invalid URL format for fail URL");
        }

        $this->config['successredir'] = $successURL;
        $this->config['failredir'] = $failURL;
    }


    /**
     * Validate the document to check whether the document is authentic and has not been tampered
     * @param boolean $enabled Enable or disable Document Authentication
     * @param mixed $module Authentication Module: "1", "2" or "quick"
     * @param float $minimum_score Minimum score to pass verification
     * @return void
     * @throws InvalidArgumentException
     */
    public function enableAuthentication($enabled = false, $module = 2, $minimum_score = 0.3)
    {
        if($enabled == false){
            $this->config['authenticate_minscore'] = 0;
        }else{
            if(!is_numeric($minimum_score)){
                throw new InvalidArgumentException("Invalid minimum score, please specify float between 0 to 1.");
            }else{
                if($minimum_score<0 || $minimum_score>1){
                    throw new InvalidArgumentException("Invalid minimum score, please specify float between 0 to 1.");
                }
            }
            if($enabled && $module != 1 && $module != 2 && $module != 'quick'){
                throw new InvalidArgumentException("Invalid authentication module, 1, 2 or 'quick' accepted.");
            }
            $this->config['authenticate_module'] = $module;
            $this->config['authenticate_minscore'] = $minimum_score;
        }
    }



    /**
     * Whether users will be required to submit a selfie photo or record selfie video for facial verification.
     * @param boolean $enabled Enable or disable Facial Biometric Verification
     * @param int $verification_type 1 for photo verification, 2 for video verification
     * @param float $threshold Minimum confidence score required to pass verification, value between 0 to 1
     * @return void
     * @throws InvalidArgumentException
     */
    public function enableFaceVerification($enabled = false, $verification_type = 1, $threshold = 0.4)
    {
        if($enabled == false){
            $this->config['biometric'] = 0;
        }else{
            if($verification_type === 1 || $verification_type === 2 ){
                $this->config['biometric'] = $verification_type;
                $this->config['biometric_threshold'] = $threshold;
            }else{
                throw new InvalidArgumentException("Invalid verification type, 1 for photo verification, 2 for video verification.");
            }
        }
    }


    /**
     * Enabling this parameter will allow multiple users to verify their identity through the same URL, a new DocuPass reference code will be generated for each user automatically.
     * @param bool $reusable Set true to allow unlimited verification for a single DocuPass session
     * @return void
     */
    public function setReusable($reusable = false)
    {
        $this->config['reusable'] = $reusable == true;
    }


    /**
     * Enable or disable returning user uploaded document and face image in callback, and image data format.
     * @param bool $return_documentimage Return document image in callback data
     * @param bool $return_faceimage Return face image in callback data
     * @param int $return_type Image type: 0=base64, 1=url
     * @return void
     */
    public function setCallbackImage($return_documentimage = true, $return_faceimage = true, $return_type = 1)
    {
        $this->config['return_documentimage'] = $return_documentimage == true;
        $this->config['return_faceimage'] = $return_faceimage == true;
        $this->config['return_type'] = $return_type == 0? 0:1;
    }


    /**
     * Configure QR code generated for DocuPass Mobile and Live Mobile
     * @param string $foregroundColor Image foreground color HEX code
     * @param string $backgroundColor Image background color HEX code
     * @param int $size Image size: 1 to 50
     * @param int $margin Image margin: 1 to 50
     * @return void
     * @throws InvalidArgumentException
     */
    public function setQRCodeFormat($foregroundColor = "000000", $backgroundColor = "FFFFFF", $size = 5, $margin = 1)
    {
        if(!ctype_xdigit($foregroundColor) || strlen($foregroundColor)!==6){
            throw new InvalidArgumentException("Invalid foreground color HEX code");
        }
        if(!ctype_xdigit($foregroundColor) || strlen($foregroundColor)!==6){
            throw new InvalidArgumentException("Invalid background color HEX code");
        }

        if(!is_numeric($size)){
            throw new InvalidArgumentException("Invalid image size");
        }
        if(!is_numeric($margin)){
            throw new InvalidArgumentException("Invalid margin");
        }


        $this->config['qr_color'] = $foregroundColor;
        $this->config['qr_bgcolor'] = $backgroundColor;
        $this->config['qr_size'] = $size;
        $this->config['qr_margin'] = $margin;
    }

    /**
     * Check if the names, document number and document type matches between the front and the back of the document when performing dual-side scan. If any information mismatches error 14 will be thrown.
     * @param boolean $enabled Enable or disable dual-side information check
     * @return void
     */
    public function enableDualsideCheck($enabled = false)
    {
        $this->config['dualsidecheck'] = $enabled == true;

    }

    /**
     * Check document holder's name and document number against ID Analyzer AML Database for sanctions, crimes and PEPs.
     * @param boolean $enabled Enable or disable AML/PEP check
     * @return void
     */
    public function enableAMLCheck($enabled = false)
    {
        $this->config["aml_check"] = $enabled == true;
    }

    /**
     * Specify the source databases to perform AML check, if left blank, all source databases will be checked. Separate each database code with comma, for example: un_sc,us_ofac. For full list of source databases and corresponding code visit AML API Overview.
     * @param string $databases Database codes separated by comma
     * @return void
     */
    public function setAMLDatabase($databases = "au_dfat,ca_dfatd,ch_seco,eu_fsf,fr_tresor_gels_avoir,gb_hmt,ua_sfms,un_sc,us_ofac,eu_cor,eu_meps,global_politicians,interpol_red")
    {
        $this->config["aml_database"] = $databases;
    }

    /**
     * By default, entities with identical name or document number will be considered a match even though their birthday or nationality may be unknown. Enable this parameter to reduce false-positives by only matching entities with exact same nationality and birthday.
     * @param boolean $enabled Enable or disable AML strict match mode
     * @return void
     */
    public function enableAMLStrictMatch($enabled = false)
    {
        $this->config["aml_strict_match"] = $enabled == true;
    }




    /**
     * Whether to ask user to enter a phone number for verification, DocuPass supports both mobile or landline number verification. Verified phone number will be returned in callback JSON.
     * @param boolean $enabled Enable or disable user phone verification
     * @return void
     */
    public function enablePhoneVerification($enabled = false)
    {
        $this->config["phoneverification"] = $enabled;
    }


    /**
    * DocuPass will send SMS to this number containing DocuPass link to perform identity verification, the number provided will be automatically considered as verified if user completes identity verification. If an invalid or unreachable number is provided error 1050 will be thrown. You should add your own thresholding mechanism to prevent abuse as you will be charged 1 quota to send the SMS.
    * @param string $mobileNumber Mobile number should be provided in international format such as +1333444555
    * @return void
    */
    public function smsVerificationLink($mobileNumber = "+1333444555")
    {
        $this->config["sms_verification_link"] = $mobileNumber;
    }

    /**
     * DocuPass will send SMS to this number containing DocuPass link to review and sign legal document.
     * @param string $mobileNumber Mobile number should be provided in international format such as +1333444555
     * @return void
     */
    public function smsContractLink($mobileNumber = "+1333444555")
    {
        $this->config["sms_contract_link"] = $mobileNumber;
    }

    /**
     * DocuPass will attempt to verify this phone number as part of the identity verification process, both mobile or landline are supported, users will not be able to enter their own numbers or change the provided number.
     * @param string $phoneNumber Mobile or landline number should be provided in international format such as +1333444555
     * @return void
     */
    public function verifyPhone($phoneNumber = "+1333444555")
    {
        $this->config["verify_phone"] = $phoneNumber;
    }

    /**
     * Check if the document is still valid based on its expiry date.
     * @param boolean $enabled Enable or disable expiry check
     * @return void
     */
    public function verifyExpiry($enabled = false)
    {
        $this->config['verify_expiry'] = $enabled == true;
    }

    /**
     * Check if supplied document or personal number matches with document.
     * @param string $documentNumber Document or personal number requiring validation
     * @return void
     */
    public function verifyDocumentNumber($documentNumber = "X1234567")
    {
        if($documentNumber === false || $documentNumber == ""){
            $this->config['verify_documentno'] = "";
        }else{
            $this->config['verify_documentno'] = $documentNumber;
        }



    }

    /**
     * Check if supplied name matches with document.
     * @param string $fullName Full name requiring validation
     * @return void
     */
    public function verifyName($fullName = "ELON MUSK")
    {

        if($fullName === false || $fullName == ""){
            $this->config['verify_name'] = "";
        }else{
            $this->config['verify_name'] = $fullName;
        }


    }


    /**
     * Check if supplied date of birth matches with document.
     * @param string $dob Date of birth in YYYY/MM/DD
     * @return void
     * @throws InvalidArgumentException
     */
    public function verifyDOB($dob = "1990/01/01")
    {
        if($dob === false || $dob == ""){
            $this->config['verify_dob'] = "";
        }else{
            if(DateTime::createFromFormat('!Y/m/d', $dob) === false){
                throw new InvalidArgumentException("Invalid birthday format (YYYY/MM/DD)");
            }

            $this->config['verify_dob'] = $dob;
        }


    }

    /**
     * Check if the document holder is aged between the given range.
     * @param string $ageRange Age range, example: 18-40
     * @return void
     * @throws InvalidArgumentException
     */
    public function verifyAge($ageRange = "18-99")
    {
        if($ageRange === false || $ageRange == ""){
            $this->config['verify_age'] = "";
        }else{
            if (!preg_match('/^\d+-\d+$/', $ageRange)) {
                throw new InvalidArgumentException("Invalid age range format (minAge-maxAge)");
            }

            $this->config['verify_age'] = $ageRange;
        }


    }

    /**
     * Check if supplied address matches with document.
     * @param string $address Address requiring validation
     * @return void
     */
    public function verifyAddress($address = "123 Sample St, California, US")
    {
        if($address === false || $address == ""){
            $this->config['verify_address'] = "";
        }else{
            $this->config['verify_address'] = $address;
        }

    }

    /**
     * Check if supplied postcode matches with document.
     * @param string $postcode Postcode requiring validation
     * @return void
     */
    public function verifyPostcode($postcode = "90001")
    {
        if($postcode === false || $postcode == ""){
            $this->config['verify_postcode'] = "";
        }else{
            $this->config['verify_postcode'] = $postcode;
        }

    }

    /**
     * Check if the document was issued by specified countries, if not error code 10 will be thrown. Separate multiple values with comma. For example "US,CA" would accept documents from United States and Canada.
     * @param string $countryCodes ISO ALPHA-2 Country Code separated by comma
     * @return void
     */
    public function restrictCountry($countryCodes = "US,CA,UK")
    {
        if($countryCodes === false || $countryCodes == ""){
            $this->config['documentcountry'] = "";
        }else{
            $this->config['documentcountry'] = $countryCodes;
        }

    }

    /**
     * Check if the document was issued by specified state, if not error code 11 will be thrown. Separate multiple values with comma. For example "CA,TX" would accept documents from California and Texas.
     * @param string $states State full name or abbreviation separated by comma
     * @return void
     */
    public function restrictState($states = "CA,TX")
    {
        if($states === false || $states == ""){
            $this->config['documentregion'] = "";
        }else{
            $this->config['documentregion'] = $states;
        }

    }

    /**
     * Only accept document of specified types.
     * @param string $documentType P: Passport, D: Driver's License, I: Identity Card. For example, "PD" would only accept passport and drivers license but not ID card.
     * @return void
     */
    public function restrictType($documentType = "DIP")
    {
        if($documentType === false || $documentType == ""){
            $this->config['documenttype'] = "";
        }else{
            $this->config['documenttype'] = $documentType;
        }

    }

    /**
     * Save document image and parsed information in your secured vault. You can list, search and update document entries in your vault through Vault API or web portal.
     * @param boolean $enabled Enable or disable Vault
     * @return void
     */
    public function enableVault($enabled = true)
    {
        $this->config['vault_save'] = $enabled == true;
    }

    /**
     * Generate legal document using data from user uploaded ID
     * @param string $templateId Contract Template ID displayed under web portal
     * @param string $format Output file format: PDF, DOCX or HTML
     * @param mixed $prefillData Associative array or JSON string, to autofill dynamic fields in contract template.
     * @return void
     * @throws InvalidArgumentException
     */
    public function generateContract($templateId, $format = "PDF", $prefillData = array())
    {
        if ($templateId == "") {
            throw new InvalidArgumentException("Invalid template ID");
        }
        $this->config['contract_sign'] = "";
        $this->config['contract_generate'] = $templateId;
        $this->config['contract_format'] = $format;
        $this->config['contract_prefill_data'] = $prefillData;
    }

    /**
     * Have user review and sign autofilled legal document after successful identity verification
     * @param string $templateId Contract Template ID displayed under web portal
     * @param string $format Output file format: PDF, DOCX or HTML
     * @param mixed $prefillData Associative array or JSON string, to autofill dynamic fields in contract template.
     * @return void
     * @throws InvalidArgumentException
     */
    public function signContract($templateId, $format = "PDF", $prefillData = array())
    {
        if ($templateId == "") {
            throw new InvalidArgumentException("Invalid template ID");
        }
        $this->config['contract_generate'] = "";
        $this->config['contract_sign'] = $templateId;
        $this->config['contract_format'] = $format;
        $this->config['contract_prefill_data'] = $prefillData;
    }

    /**
     * Create a DocuPass identity verification session for embedding in web page as iframe
     * @return array
     * @throws APIException
     */
    public function createIframe(){
        return $this->create(0);
    }

    /**
     * Create a DocuPass identity verification session for users to open on mobile phone, or embedding in mobile app
     * @return array
     * @throws APIException
     */
    public function createMobile(){
        return $this->create(1);
    }

    /**
     * Create a DocuPass identity verification session for users to open in any browser
     * @return array
     * @throws APIException
     */
    public function createRedirection(){
        return $this->create(2);
    }

    /**
     * Create a DocuPass Live Mobile identity verification session for users to open on mobile phone
     * @return array
     * @throws APIException
     */
    public function createLiveMobile(){
        return $this->create(3);
    }

    /**
     * Create a DocuPass signature session for user to review and sign legal document without identity verification
     * @param string $templateId Contract Template ID displayed under web portal
     * @param string $format Output file format: PDF, DOCX or HTML
     * @param mixed $prefillData Associative array or JSON string, to autofill dynamic fields in contract template.
     * @return array
     * @throws APIException
     */
    public function createSignature($templateId, $format = "PDF", $prefillData = array()){
        $payload = $this->config;
        $payload["apikey"] = $this->apikey;
        $payload["template_id"] = $templateId;
        $payload['contract_format'] = $format;
        $payload['contract_prefill_data'] = $prefillData;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint . "docupass/sign");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);

        if(curl_error($ch) || curl_errno($ch)){
            throw new Exception("Failed to connect to API server: ". curl_error($ch) . " (" . curl_errno($ch) . ")" );
        }else{
            $result = json_decode($response,true);

            if($this->throwError){
                if(is_array($result['error'])){

                    throw new APIException($result['error']['message'], $result['error']['code'] );

                }else{
                    return $result;
                }
            }else{
                return $result;
            }


        }
    }

    /**
     * @return array
     * @throws APIException
     * @throws Exception
     */
    private function create($docupass_module){

        $payload = $this->config;
        $payload["apikey"] = $this->apikey;
        $payload["type"] = $docupass_module;



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint . "docupass/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);

        if(curl_error($ch) || curl_errno($ch)){
            throw new Exception("Failed to connect to API server: ". curl_error($ch) . " (" . curl_errno($ch) . ")" );
        }else{
            $result = json_decode($response,true);

            if($this->throwError){
                if(is_array($result['error'])){

                    throw new APIException($result['error']['message'], $result['error']['code'] );

                }else{
                    return $result;
                }
            }else{
                return $result;
            }


        }



    }


    /**
     * Validate a data received through DocuPass Callback against DocuPass Server to prevent request spoofing
     * @param string $reference DocuPass reference
     * @param string $hash DocuPass callback hash
     * @return bool
     * @throws Exception
     */
    public function validate($reference, $hash){

        $payload = array(
            "apikey"=>$this->apikey,
            "reference"=>$reference,
            "hash"=>$hash
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint . "docupass/validate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);

        if(curl_error($ch)){
            throw new Exception("Failed to connect to API server: ".curl_error($ch), curl_errno($ch) );
        }else{
            $result = json_decode($response, true);

            return $result['success'] === true;
        }

    }


}