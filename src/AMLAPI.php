<?php

namespace IDAnalyzer;

use Exception;
use InvalidArgumentException;

class AMLAPI
{
    private $apikey;
    private $apiendpoint = "";
    private $AMLDatabases = "";
    private $AMLEntityType = "";
    private $throwError = false;
    /**
     * Initialize AML API with an API key, and optional region (US, EU)
     * @param string $apikey You API key
     * @param string $region US/EU
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct($apikey, $region = "US")
    {
        if($apikey == "") throw new InvalidArgumentException("Please provide an API key");
        $this->apikey = $apikey;
        if($region === 'eu' || $region === "EU"){
            $this->apiendpoint = "https://api-eu.idanalyzer.com/aml";
        }else if($region === 'us' || $region === "US"){
            $this->apiendpoint = "https://api.idanalyzer.com/aml";
        }else{
            $this->apiendpoint = $region;
        }
    }

    /**
     * Whether an exception should be thrown if API response contains an error message
     * @param bool $throwException Enable or Disable APIException being thrown
     * @return void
     */
    public function throwAPIException($throwException = false)
    {
        $this->throwError = $throwException == true;
    }

    /**
     * Specify the source databases to perform AML search, if left blank, all source databases will be checked. Separate each database code with comma, for example: un_sc,us_ofac. For full list of source databases and corresponding code visit AML API Overview.
     * @param string $databases Database codes separated by comma
     * @return void
     */
    public function setAMLDatabase($databases = "au_dfat,ca_dfatd,ch_seco,eu_fsf,fr_tresor_gels_avoir,gb_hmt,ua_sfms,un_sc,us_ofac,eu_cor,eu_meps,global_politicians,interpol_red")
    {
        $this->AMLDatabases = $databases;
    }

    /**
     * Return only entities with specified entity type, leave blank to return both person and legal entity.
     * @param string $entityType 'person' or 'legalentity'
     * @return void
     * @throws InvalidArgumentException
     */
    public function setEntityType($entityType = "")
    {
        if ($entityType!=="person" && $entityType!=="legalentity" && $entityType!=="")
        {
            throw new InvalidArgumentException("Entity Type should be either empty, 'person' or 'legalentity'");
        }
        $this->AMLEntityType = $entityType;
    }



    /**
     * Search AML Database using a person or company's name or alias
     * @param string $name Name or alias to search AML Database
     * @param string $country ISO 2 Country Code
     * @param string $dob Date of birth in YYYY-MM-DD or YYYY-MM or YYYY format
     * @return array AML match list
     * @throws APIException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function searchByName($name = "", $country = "", $dob = "")
    {

        if (strlen($name) <3)
        {
            throw new InvalidArgumentException("Name should contain at least 3 characters.");
        }
        return $this->callAPI( array(
                "name"=>$name,
                "country"=>$country,
                "dob"=>$dob)
        );
    }

    /**
     * Search AML Database using a document number (Passport, ID Card or any identification documents)
     * @param string $documentNumber Document ID Number to perform search
     * @param string $country ISO 2 Country Code
     * @param string $dob Date of birth in YYYY-MM-DD or YYYY-MM or YYYY format
     * @return array AML match list
     * @throws APIException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function searchByIDNumber($documentNumber = "", $country = "", $dob = "")
    {

        if (strlen($documentNumber) < 5)
        {
            throw new InvalidArgumentException("Document number should contain at least 5 characters.");
        }
        return $this->callAPI( array(
                "documentnumber"=>$documentNumber,
                "country"=>$country,
                "dob"=>$dob)
        );
    }



    /**
     * @param array $payload
     * @return array
     * @throws APIException
     * @throws Exception
     */
    private function callAPI($payload = array()){

        $payload["apikey"] = $this->apikey;
        $payload["database"] = $this->AMLDatabases;
        $payload["entity"] = $this->AMLEntityType;
        $payload["apikey"] = $this->apikey;
        $payload["client"] = "php-sdk";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint );
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




}