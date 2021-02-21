<?php

namespace IDAnalyzer;

use Exception;

class DocuPass
{
    private $apikey;
    private $apiendpoint = "";
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
        "vault_save" => "",
        "return_documentimage" => "",
        "return_faceimage" => "",
        "return_type" => "",
        "qr_color" => "",
        "qr_bgcolor" => "",
        "qr_size" => "",
        "qr_margin" => "",
        "welcomemessage" => "",
        "nobranding" => "",
        "logo" => "",
        "language" => "",
        "biometric_threshold" => 0.4
    );

    private $urlRegex = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))#iS";

    /**
     * Reset all API configurations except API key and region.
     * @return null
     */
    public function resetConfig()
    {
        $this->config = $this->defaultconfig;
    }

    /**
     * Set max verification attempt per user
     * @param int $max_attempt 1 to 10
     * @return null
     * @throws Exception
     */
    public function setMaxAttempt($max_attempt = 1)
    {
        if(!is_numeric($max_attempt) || $max_attempt<1 || $max_attempt>10){
            throw new Exception("Invalid max attempt, please specify integer between 1 to 10.");

        }
        $this->config['maxattempt'] = $max_attempt;
    }

    /**
     * Set a custom user identifier string, this will be returned in the callback
     * @param string $customID A string used to identify your customer internally
     * @return null
     */
    public function setCustomID($customID = "12345")
    {
        $this->config['customid'] = $customID;
    }

    /**
     * Display a custom message to the user in the beginning of verification
     * @param string $message Plain text string
     * @return null
     */
    public function setWelcomeMessage($message)
    {
        $this->config['welcomemessage'] = $message;
    }


    /**
     * Replace footer logo with your own logo
     * @param string $url Logo URL
     * @return null
     */
    public function setLogo($url = "https://docupass.app/asset/logo1.png")
    {
        $this->config['logo'] = $url;
    }


    /**
     * Hide all branding logo
     * @param bool $hide
     * @return null
     */
    public function hideBrandingLogo($hide = false)
    {
        $this->config['nobranding'] = $hide == true;
    }

    /**
     * DocuPass automatically detects user device language and display corresponding language. Set this parameter to override automatic language detection.
     * @param string $language Language Code: en fr nl de es zh-TW zh-CN
     * @return null
     */
    public function setLanguage($language)
    {
        $this->config['language'] = $language;
    }



    /**
     * Set server-side callback URL to receive verification results
     * @param string $url Callback URL
     * @return null
     * @throws Exception
     */
    public function setCallbackURL($url = "https://www.example.com/docupass_callback.php")
    {
        if (!preg_match($this->urlRegex, $url)) {
            throw new Exception("Invalid URL format");
        }

        $urlinfo = parse_url($url);

        if(filter_var($urlinfo['host'], FILTER_VALIDATE_IP)) {
            if(!filter_var(
                $urlinfo['host'],
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
            )){
                throw new Exception("Invalid URL, the host does not appear to be a remote host.");
            }
        }
        if(strtolower($urlinfo['host'])=='localhost'){
            throw new Exception("Invalid URL, the host does not appear to be a remote host.");
        }
        if($urlinfo['scheme']!='http' && $urlinfo['scheme']!='https'){
            throw new Exception("Invalid URL, only http and https protocols are allowed.");
        }
        $this->config['callbackurl'] = $url;
    }


    /**
     * Redirect client browser to set URLs after verification
     * @param string $successURL Redirection URL when verification succeed
     * @param string $failURL Redirection URL when verification failed
     * @return null
     * @throws Exception
     */
    public function setRedirectionURL($successURL = "https://www.example.com/success.php", $failURL = "https://www.example.com/failed.php")
    {
        if (!preg_match($this->urlRegex,$successURL)) {
            throw new Exception("Invalid URL format for success URL");
        }
        if (!preg_match($this->urlRegex,$failURL)) {
            throw new Exception("Invalid URL format for fail URL");
        }

        $this->config['successredir'] = $successURL;
        $this->config['failredir'] = $failURL;
    }


    /**
     * Validate the document to check whether the document is authentic and has not been tampered
     * @param boolean $enabled Enable/Disable Document Authentication
     * @param mixed $module Module: 1, 2 or quick
     * @param float $minimum_score Minimum score to pass verification
     * @return null
     * @throws Exception
     */
    public function enableAuthentication($enabled = false, $module = 2, $minimum_score = 0.3)
    {
        if($enabled == false){
            $this->config['authenticate_minscore'] = 0;
        }else{
            if(!is_numeric($minimum_score)){
                throw new Exception("Invalid minimum score, please specify float between 0 to 1.");
            }else{
                if($minimum_score<0 || $minimum_score>1){
                    throw new Exception("Invalid minimum score, please specify float between 0 to 1.");
                }
            }
            if($enabled && $module != 1 && $module != 2 && $module != 'quick'){
                throw new Exception("Invalid authentication module, 1, 2 or 'quick' accepted.");
            }
            $this->config['authenticate_module'] = $module;
            $this->config['authenticate_minscore'] = $minimum_score;
        }
    }



    /**
     * Whether users will be required to submit a selfie photo or record selfie video for facial verification.
     * @param boolean $enabled Enable/Disable Facial Biometric Verification
     * @param int $verification_type 1 for photo verification, 2 for video verification
     * @param float $threshold Minimum confidence score required to pass verification, value between 0 to 1
     * @return null
     * @throws Exception
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
                throw new Exception("Invalid verification type, 1 for photo verification, 2 for video verification.");
            }
        }
    }




    /**
     * Enable/Disable returning user uploaded document and face image in callback, and image data format.
     * @param bool $return_documentimage Return document image in callback data
     * @param bool $return_faceimage Return face image in callback data
     * @param int $return_type Image type: 0=base64, 1=url
     * @return null
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
     * @return null
     * @throws Exception
     */
    public function setQRCodeFormat($foregroundColor = "000000", $backgroundColor = "FFFFFF", $size = 5, $margin = 1)
    {
        if(!ctype_xdigit($foregroundColor) || strlen($foregroundColor)!==6){
            throw new Exception("Invalid foreground color HEX code");
        }
        if(!ctype_xdigit($foregroundColor) || strlen($foregroundColor)!==6){
            throw new Exception("Invalid background color HEX code");
        }

        if(!is_numeric($size)){
            throw new Exception("Invalid image size");
        }
        if(!is_numeric($margin)){
            throw new Exception("Invalid margin");
        }


        $this->config['qr_color'] = $foregroundColor;
        $this->config['qr_bgcolor'] = $backgroundColor;
        $this->config['qr_size'] = $size;
        $this->config['qr_margin'] = $margin;
    }

    /**
     * Check if the names, document number and document type matches between the front and the back of the document when performing dual-side scan. If any information mismatches error 14 will be thrown.
     * @param boolean $enabled
     * @return null
     */
    public function enableDualsideCheck($enabled = false)
    {
        $this->config['dualsidecheck'] = $enabled == true;

    }

    /**
     * Check if the document is still valid based on its expiry date.
     * @param boolean $enabled Enable/Disable expiry check
     * @return null
     */
    public function verifyExpiry($enabled = false)
    {
        $this->config['verify_expiry'] = $enabled == true;
    }

    /**
     * Check if supplied document or personal number matches with document.
     * @param string $documentNumber Document or personal number requiring validation
     * @return null
     * @throws Exception
     */
    public function verifyDocumentNumber($documentNumber = "X1234567")
    {
        if($documentNumber === false || $documentNumber == ""){
            $this->config['verify_documentno'] = "";
        }else{
            if($documentNumber == ""){
                throw new Exception("You must set a document or personal ID number you want to verify against.");
            }
            $this->config['verify_documentno'] = $documentNumber;
        }



    }

    /**
     * Check if supplied name matches with document.
     * @param string $fullName Full name requiring validation
     * @return null
     * @throws Exception
     */
    public function verifyName($fullName = "ELON MUSK")
    {

        if($fullName === false || $fullName == ""){
            $this->config['verify_name'] = "";
        }else{
            if($fullName == ""){
                throw new Exception("You must set the full name you want to verify against.");
            }
            $this->config['verify_name'] = $fullName;
        }



    }


    /**
     * Check if supplied date of birth matches with document.
     * @param string $dob Date of birth in YYYY/MM/DD
     * @return null
     * @throws Exception
     */
    public function verifyDOB($dob = "1990/01/01")
    {
        if($dob === false || $dob == ""){
            $this->config['verify_dob'] = "";
        }else{
            if(DateTime::createFromFormat('!Y/m/d', $dob) === false){
                throw new Exception("Invalid birthday format (YYYY/MM/DD)");
            }

            $this->config['verify_dob'] = $dob;
        }


    }

    /**
     * Check if the document holder is aged between the given range.
     * @param string $ageRange Age range, example: 18-40
     * @return null
     * @throws Exception
     */
    public function verifyAge($ageRange = "18-99")
    {
        if($ageRange === false || $ageRange == ""){
            $this->config['verify_age'] = "";
        }else{
            if (!preg_match('/^\d+-\d+$/', $ageRange)) {
                throw new Exception("Invalid age range format (minAge-maxAge)");
            }

            $this->config['verify_age'] = $ageRange;
        }


    }

    /**
     * Check if supplied address matches with document.
     * @param string $address Address requiring validation
     * @return null
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
     * @return null
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
     * @return null
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
     * @return null
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
     * Only accept document of specified types. For example, "PD" would accept both passport and drivers license.
     * @param string $documentType P: Passport, D: Driver's License, I: Identity Card
     * @return null
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
     * Disable Visual OCR and read data from AAMVA Barcodes only
     * @param boolean $enabled Enable/Disable Barcode Mode
     * @return null
     */
    public function enableBarcodeMode($enabled = false)
    {
        $this->config['barcodemode'] = $enabled == true;

    }


    /**
     * Save document image and parsed information in your secured vault. You can list, search and update document entries in your vault through Vault API or web portal.
     * @param boolean $enabled Enable/Disable Vault
     * @return null
     */
    public function enableVault($enabled = true)
    {
        $this->config['vault_save'] = $enabled == true;
    }


    /**
     * Add up to 5 custom strings that will be associated with the vault entry, this can be useful for filtering and searching entries.
     * @param string $data1 Custom data field 1
     * @param string $data2 Custom data field 2
     * @param string $data3 Custom data field 3
     * @param string $data4 Custom data field 4
     * @param string $data5 Custom data field 5
     * @return null
     */
    public function setVaultData($data1 = "", $data2 = "", $data3 = "", $data4 = "", $data5 = "" )
    {
        $this->config['vault_customdata1'] = $data1;
        $this->config['vault_customdata2'] = $data2;
        $this->config['vault_customdata3'] = $data3;
        $this->config['vault_customdata4'] = $data4;
        $this->config['vault_customdata5'] = $data5;

    }


    /**
     * Initialize DocuPass API with an API key, company name and optional region (US, EU)
     * @param string $apikey You API key
     * @param string $companyName Your company name
     * @param string $region US/EU
     * @return null
     */
    public function init($apikey, $companyName = "My Company Name", $region = "US")
    {
        $this->apikey = $apikey;
        if($region === 'eu' || $region === "EU"){
            $this->apiendpoint = "https://api-eu.idanalyzer.com/";
        }else if($region === 'us' || $region === "US"){
            $this->apiendpoint = "https://api.idanalyzer.com/";
        }else{
            $this->apiendpoint = $region;
        }

    }

    /**
     * Create a DocuPass session for embedding in web page as iframe
     * @return array
     * @throws Exception
     */
    public function createIframe(){
        return $this->create(0);
    }

    /**
     * Create a DocuPass session for users to open on mobile phone, or embedding in mobile app
     * @return array
     * @throws Exception
     */
    public function createMobile(){
        return $this->create(1);
    }

    /**
     * Create a DocuPass session for users to open in any browser
     * @return array
     * @throws Exception
     */
    public function createRedirection(){
        return $this->create(2);
    }

    /**
     * Create a DocuPass Live Mobile verification session for users to open on mobile phone
     * @return array
     * @throws Exception
     */
    public function createLiveMobile(){
        return $this->create(3);
    }


    private function create($docupass_module){

        if($this->apiendpoint=="" || $this->apikey==""){
            throw new Exception("Please call init() with your API key.");
        }

        $payload = $this->config;
        $payload["apikey"] = $this->apikey;
        $payload["type"] = $docupass_module;



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint . "docupass/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);

        if(curl_error($ch)){
            throw new Exception("Connecting to API Server failed: ".curl_error($ch));
        }else{
            $result = json_decode($response, true);

            return $result;
        }

    }




}