<?php

namespace IDAnalyzer;

use Exception;
use InvalidArgumentException;

class Vault
{
    private $apikey;
    private $apiendpoint = "";
    private $throwError = false;
    /**
     * Initialize Vault API with an API key, and optional region (US, EU)
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
            $this->apiendpoint = "https://api-eu.idanalyzer.com/";
        }else if($region === 'us' || $region === "US"){
            $this->apiendpoint = "https://api.idanalyzer.com/";
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
     * Get a single vault entry
     * @param string $vault_id Vault entry ID
     * @return array
     * @throws InvalidArgumentException
     * @throws APIException
     */
    public function get($vault_id)
    {
        if($vault_id == ""){
            throw new InvalidArgumentException("Vault entry ID required.");
        }
        return $this->callAPI("get", array("id"=>$vault_id));

    }


    /**
     * List multiple vault entries with optional filter, sorting and paging arguments
     * @param array $filter Array of filter statements, refer to https://developer.idanalyzer.com/vaultapi.html for filter construction
     * @param string $orderby Field name used to order the results, refer to https://developer.idanalyzer.com/vaultapi.html for available fields
     * @param string $sort Sort results by ASC = Ascending, DESC = DESCENDING
     * @param int $limit Number of results to return
     * @param int $offset Offset the first result using specified index
     * @return array
     * @throws APIException
     */
    public function list($filter = array(), $orderby = "createtime", $sort = "DESC", $limit = 10, $offset = 0)
    {
        if($filter == "" || $filter == null ) $filter = array();
        if(!is_array($filter) || count($filter)>5 ){
            throw new InvalidArgumentException("Filter should be an array containing maximum of 5 filter statements.");
        }
        return $this->callAPI("list", array(
            "filter"=>$filter,
            "orderby"=>$orderby,
            "sort"=>$sort,
            "limit"=>$limit,
            "offset"=>$offset)
        );


    }


    /**
     * Update vault entry with new data
     * @param string $vault_id Vault entry ID
     * @param array $data Key-value associative array of the field name and its value
     * @return bool
     * @throws InvalidArgumentException
     * @throws APIException
     */
    public function update($vault_id, $data = array())
    {
        if($vault_id == ""){
            throw new InvalidArgumentException("Vault entry ID required.");
        }
        if(count($data)<1){
            throw new InvalidArgumentException("Data required.");
        }
        $data['id'] = $vault_id;
        $this->callAPI("update", $data);
        return true;

    }

    /**
     * Delete a single or multiple vault entries
     * @param mixed $vault_id Vault entry ID or array of IDs
     * @return bool
     * @throws InvalidArgumentException
     * @throws APIException
     */
    public function delete($vault_id)
    {
        if($vault_id == ""){
            throw new InvalidArgumentException("Vault entry ID required.");
        }
        $result = $this->callAPI("delete", array("id"=>$vault_id));
        return true;


    }


    /**
     * Add a document or face image into an existing vault entry
     * @param string $vault_id Vault entry ID
     * @param string $image Image file path, base64 content or URL
     * @param int $type Type of image: 0 = document, 1 = person
     * @return array New image information array
     * @throws InvalidArgumentException
     * @throws APIException
     */
    public function addImage($vault_id, $image, $type = 0)
    {
        if($vault_id == ""){
            throw new InvalidArgumentException("Vault entry ID required.");
        }
        if($type !== 0 && $type!==1){
            throw new InvalidArgumentException("Invalid image type, 0 or 1 accepted.");
        }
        $payload = array("id"=>$vault_id, "type"=>$type);
        if(filter_var($image, FILTER_VALIDATE_URL)){
            $payload['imageurl'] = $image;
        }else if(file_exists($image)){
            $payload['image'] = base64_encode(file_get_contents($image));
        }else if(strlen($image)>100){
            $payload['image'] = $image;
        }else{
            throw new InvalidArgumentException("Invalid image, file not found or malformed URL.");
        }

        return $this->callAPI("addimage", $payload);

    }


    /**
     * Delete an image from vault
     * @param string $vault_id Vault entry ID
     * @param string $image_id Image ID
     * @return bool
     * @throws InvalidArgumentException
     * @throws APIException
     */
    public function deleteImage($vault_id, $image_id)
    {
        if($vault_id == ""){
            throw new InvalidArgumentException("Vault entry ID required.");
        }
        if($image_id == ""){
            throw new InvalidArgumentException("Image ID required.");
        }

        $this->callAPI("deleteimage", array("id"=>$vault_id,"imageid"=>$image_id));
        return true;


    }


    /**
     * Search vault using a person's face image
     * @param string $image Face image file path, base64 content or URL
     * @param int $maxEntry Number of entries to return, 1 to 10.
     * @param float $threshold Minimum confidence score required for face matching
     * @return array List of vault entries
     * @throws InvalidArgumentException
     * @throws APIException
     */
    public function searchFace($image, $maxEntry = 10, $threshold = 0.5)
    {

        $payload = array("maxentry"=>$maxEntry, "threshold"=>$threshold);
        if(filter_var($image, FILTER_VALIDATE_URL)){
            $payload['imageurl'] = $image;
        }else if(file_exists($image)){
            $payload['image'] = base64_encode(file_get_contents($image));
        }else if(strlen($image)>100){
            $payload['image'] = $image;
        }else{
            throw new InvalidArgumentException("Invalid image, file not found or malformed URL.");
        }

        return $this->callAPI("searchface", $payload);

    }

    /**
     * Train vault for face search
     * @return array
     * @throws APIException
     */
    public function trainFace()
    {
        return $this->callAPI("train");
    }

    /**
     * Get vault training status
     * @return array
     * @throws APIException
     */
    public function trainingStatus()
    {
        return $this->callAPI("trainstatus");

    }


    /**
     * @param string $action
     * @param array $payload
     * @return array
     * @throws APIException
     * @throws Exception
     */
    private function callAPI($action, $payload = array()){

        $payload["apikey"] = $this->apikey;
        $payload["client"] = "php-sdk";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint . "vault/" . $action);
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