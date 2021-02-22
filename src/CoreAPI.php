<?php

namespace IDAnalyzer;

use Exception;

class CoreAPI
{
    private $apikey;
    private $apiendpoint = "";
    private $config, $defaultconfig = array(
        "accuracy" => 2,
        "authenticate" => false,
        "authenticate_module" => 1,
        "ocr_scaledown" => 1500,
        "outputimage" => false,
        "outputface" => false,
        "outputmode" => "url",
        "dualsidecheck" => false,
        "verify_expiry" => true,
        "verify_documentno" => "",
        "verify_name" => "",
        "verify_dob" => "",
        "verify_age" => "",
        "verify_address" => "",
        "verify_postcode" => "",
        "country" => "",
        "region" => "",
        "type" => "",
        "checkblocklist" => "",
        "vault_save" => "",
        "vault_saveunrecognized" => "",
        "vault_noduplicate" => "",
        "vault_automerge" => "",
        "vault_customdata1" => "",
        "vault_customdata2" => "",
        "vault_customdata3" => "",
        "vault_customdata4" => "",
        "vault_customdata5" => "",
        "barcodemode" => false,
        "biometric_threshold" => 0.4
    );



    /**
     * Reset all API configurations except API key and region.
     * @return null
     */
    public function resetConfig()
    {
        $this->config = $this->defaultconfig;
    }

    /**
     * Set OCR Accuracy
     * @param integer $accuracy 0 = Fast, 1 = Balanced, 2 = Accurate
     * @return null
     */
    public function setAccuracy($accuracy = 2)
    {
        $this->config['accuracy'] = $accuracy;
    }



    /**
     * Validate the document to check whether the document is authentic and has not been tampered, and set authentication module
     * @param boolean $enabled Enable/Disable Document Authentication
     * @param mixed $module Module: 1, 2 or quick
     * @return null
     * @throws Exception
     */
    public function enableAuthentication($enabled = false, $module = 2)
    {
        $this->config['authenticate'] = $enabled == true;

        if($enabled && $module != 1 && $module != 2 && $module != 'quick'){
            throw new Exception("Invalid authentication module, 1, 2 or 'quick' accepted.");
        }

        $this->config['authenticate_module'] = $module;
    }

    /**
     * Scale down the uploaded image before sending to OCR engine. Adjust this value to fine tune recognition accuracy on large full-resolution images. Set 0 to disable image resizing.
     * @param int $maxScale 0 or 500~4000
     * @return null
     * @throws Exception
     */
    public function setOCRImageResize($maxScale = 1500)
    {
        if($maxScale!=0 && ($maxScale<500 || $maxScale>4000)){
            throw new Exception("Invalid scale value, 0, or 500 to 4000 accepted.");
        }
        $this->config['ocr_scaledown'] = $maxScale;

    }

    /**
     * Set the minimum confidence score to consider faces being identical
     * @param float $threshold float between 0 to 1
     * @return null
     * @throws Exception
     */
    public function setBiometricThreshold($threshold = 0.4)
    {
        if($threshold<=0 || $threshold>1){
            throw new Exception("Invalid threshold value, float between 0 to 1 accepted.");
        }

        $this->config['biometric_threshold'] = $threshold;

    }

    /**
     * Generate cropped image of document and/or face, and set output format [url, base64]
     * @param bool $cropDocument Crop document
     * @param bool $cropFace Crop face
     * @param string $outputFormat url/base64
     * @return null
     * @throws Exception
     */
    public function enableImageOutput($cropDocument = false, $cropFace = false, $outputFormat = "url")
    {
        if($outputFormat !== 'url' && $outputFormat !== 'base64'){
            throw new Exception("Invalid output format, 'url' or 'base64' accepted.");
        }
        $this->config['outputimage'] = $cropDocument == true;
        $this->config['outputface'] = $cropFace == true;
        $this->config['outputmode'] = $outputFormat;

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
            $this->config['country'] = "";
        }else{
            $this->config['country'] = $countryCodes;
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
            $this->config['region'] = "";
        }else{
            $this->config['region'] = $states;
        }

    }

    /**
     * Check if the document was one of the specified types, if not error code 12 will be thrown. For example, "PD" would accept both passport and drivers license.
     * @param string $documentType P: Passport, D: Driver's License, I: Identity Card
     * @return null
     */
    public function restrictType($documentType = "DIP")
    {
        if($documentType === false || $documentType == ""){
            $this->config['type'] = "";
        }else{
            $this->config['type'] = $documentType;
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
     * @param boolean $saveUnrecognized Save document image in your vault even if the document cannot be recognized.
     * @param boolean $noDuplicateImage Prevent duplicated images from being saved.
     * @param boolean $autoMergeDocument Automatically merge images with same document number into a single entry inside vault.
     * @return null
     */
    public function enableVault($enabled = true, $saveUnrecognized = false, $noDuplicateImage = false, $autoMergeDocument = false)
    {
        $this->config['vault_save'] = $enabled == true;
        $this->config['vault_saveunrecognized'] = $saveUnrecognized == true;
        $this->config['vault_noduplicate'] = $noDuplicateImage == true;
        $this->config['vault_automerge'] = $autoMergeDocument == true;
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
     * Initialize Core API with an API key and optional region (US, EU)
     * @param string $apikey You API key
     * @param string $region US/EU
     * @return null
     */
    public function init($apikey, $region = "US")
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
     * Scan an ID document with Core API, optionally specify document back image, face verification image, face verification video and video passcode
     * @param string $document_primary Front of Document (File path or URL)
     * @param string $document_secondary Back of Document (File path or URL)
     * @param string $biometric_photo Face Photo (File path or URL)
     * @param string $biometric_video Face Video (File path or URL)
     * @param string $biometric_video_passcode Face Video Passcode (4 Digit Number)
     * @return array
     * @throws Exception
     */
    public function scan($document_primary, $document_secondary = "", $biometric_photo = "", $biometric_video = "", $biometric_video_passcode = ""){

        if($this->apiendpoint=="" || $this->apikey==""){
            throw new Exception("Please call init() with your API key.");
        }

        $payload = $this->config;
        $payload["apikey"] = $this->apikey;


        if($document_primary == ""){
            throw new Exception("Primary document image required.");
        }
        if(filter_var($document_primary, FILTER_VALIDATE_URL)){
            $payload['url'] = $document_primary;
        }else if(file_exists($document_primary)){
            $payload['file_base64'] = base64_encode(file_get_contents($document_primary));
        }else{
            throw new Exception("Invalid primary document image, file not found or malformed URL.");
        }
        if($document_secondary != ""){
            if(filter_var($document_secondary, FILTER_VALIDATE_URL)){
                $payload['url_back'] = $document_secondary;
            }else if(file_exists($document_secondary)){
                $payload['file_back_base64'] = base64_encode(file_get_contents($document_secondary));
            }else {
                throw new Exception("Invalid secondary document image, file not found or malformed URL.");
            }
        }
        if($biometric_photo != ""){
            if(filter_var($biometric_photo, FILTER_VALIDATE_URL)){
                $payload['faceurl'] = $biometric_photo;
            }else if(file_exists($biometric_photo)){
                $payload['face_base64'] = base64_encode(file_get_contents($biometric_photo));
            }else {
                throw new Exception("Invalid face image, file not found or malformed URL.");
            }
        }
        if($biometric_video != ""){
            if(filter_var($biometric_video, FILTER_VALIDATE_URL)){
                $payload['videourl'] = $biometric_video;
            }else if(file_exists($biometric_video)){
                $payload['video_base64'] = base64_encode(file_get_contents($biometric_video));
            }else {
                throw new Exception("Invalid face video, file not found or malformed URL.");
            }
            if (!preg_match('/^[0-9]{4}$/', $biometric_video_passcode)) {
                throw new Exception("Please provide a 4 digit passcode for video biometric verification.");
            }
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);



        if(curl_error($ch) || curl_errno($ch)){
            throw new Exception("Connecting to API Server failed: ".curl_error($ch));
        }else{
            $result = json_decode($response,true);

            return $result;
        }

    }




}