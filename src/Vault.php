<?php

namespace IDAnalyzer;

use Exception;

class Vault
{
    private $apikey;
    private $apiendpoint = "";


    /**
     * Get a single vault entry
     * @param string $id Vault entry ID
     * @return array
     * @throws Exception
     */
    public function get($id)
    {
        if($id == ""){
            throw new Exception("Vault entry ID required.");
        }
        $result = $this->callAPI("get", array("id"=>$id));
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return $result['data'];
        }

    }


    /**
     * List multiple vault entries with optional filter, sorting and paging arguments
     * @param array $filter Array of filter statements, refer to https://developer.idanalyzer.com/vaultapi.html for filter construction
     * @param string $orderby Field name used to order the results, refer to https://developer.idanalyzer.com/vaultapi.html for available fields
     * @param string $sort Sort results by ASC = Ascending, DESC = DESCENDING
     * @param int $limit Number of results to return
     * @param int $offset Offset the first result using specified index
     * @return array
     * @throws Exception
     */
    public function list($filter = array(), $orderby = "createtime", $sort = "DESC", $limit = 10, $offset = 0)
    {
        $result = $this->callAPI("list", array("filter"=>$filter,"orderby"=>$orderby,"sort"=>$sort,"limit"=>$limit,"offset"=>$offset));
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return $result;
        }

    }


    /**
     * List multiple vault entries with optional filter, sorting and paging arguments
     * @param string $id Vault entry ID
     * @param array $data Key-value pairs of the field name and its value
     * @return bool
     * @throws Exception
     */
    public function update($id, $data = array())
    {
        if($id == ""){
            throw new Exception("Vault entry ID required.");
        }
        if(count($data)<1){
            throw new Exception("Data required.");
        }
        $data['id'] = $id;
        $result = $this->callAPI("update", $data);
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return true;
        }

    }

    /**
     * Delete a single or multiple vault entries
     * @param mixed $id Vault entry ID or array of IDs
     * @return bool
     * @throws Exception
     */
    public function delete($id)
    {
        if($id == ""){
            throw new Exception("Vault entry ID required.");
        }
        if(is_array($id)){
            foreach($id as $i){
                $result = $this->callAPI("delete", array("id"=>$i));
            }
            return true;
        }else{
            $result = $this->callAPI("delete", array("id"=>$id));
            if($result['error']){
                throw new Exception($result['error']['message'],$result['error']['code']);
            }else{
                return true;
            }
        }
    }


    /**
     * Delete a single or multiple vault entries
     * @param string $id Vault entry ID or array of IDs
     * @param string $image Image file path or URL
     * @param int $type Type of image: 0 = document, 1 = person
     * @return array New image information array
     * @throws Exception
     */
    public function addImage($id, $image, $type = 0)
    {
        if($id == ""){
            throw new Exception("Vault entry ID required.");
        }
        if($type !== 0 && $type!==1){
            throw new Exception("Invalid image type, 0 or 1 accepted.");
        }
        $payload = array("id"=>$id, "type"=>$type);
        if(filter_var($image, FILTER_VALIDATE_URL)){
            $payload['imageurl'] = $image;
        }else if(file_exists($image)){
            $payload['image'] = base64_encode(file_get_contents($image));
        }else{
            throw new Exception("Invalid image, file not found or malformed URL.");
        }

        $result = $this->callAPI("addimage", $payload);
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return $result['image'];
        }
    }


    /**
     * Delete an image from vault
     * @param string $vaultId Vault entry ID
     * @param string $imageId Image ID
     * @return bool
     * @throws Exception
     */
    public function deleteImage($vaultId,$imageId)
    {
        if($vaultId == ""){
            throw new Exception("Vault entry ID required.");
        }
        if($imageId == ""){
            throw new Exception("Image ID required.");
        }

        $result = $this->callAPI("deleteimage", array("id"=>$vaultId,"imageid"=>$imageId));
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return true;
        }

    }


    /**
     * Search vault using a person's face image
     * @param string $image Face image file path or URL
     * @param int $maxEntry Number of entries to return, 1 to 10.
     * @param float $threshold Minimum confidence score required for face matching
     * @return array List of vault entries
     * @throws Exception
     */
    public function searchFace($image, $maxEntry = 10, $threshold = 0.5)
    {

        $payload = array("maxentry"=>$maxEntry, "$threshold"=>$threshold);
        if(filter_var($image, FILTER_VALIDATE_URL)){
            $payload['imageurl'] = $image;
        }else if(file_exists($image)){
            $payload['image'] = base64_encode(file_get_contents($image));
        }else{
            throw new Exception("Invalid image, file not found or malformed URL.");
        }

        $result = $this->callAPI("searchface", $payload);
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return $result['items'];
        }
    }

    /**
     * Train vault for face search
     * @return bool
     * @throws Exception
     */
    public function trainFace()
    {
        $result = $this->callAPI("train");
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return true;
        }
    }

    /**
     * Get vault training status
     * @return array
     * @throws Exception
     */
    public function trainingStatus()
    {
        $result = $this->callAPI("trainstatus");
        if($result['error']){
            throw new Exception($result['error']['message'],$result['error']['code']);
        }else{
            return $result;
        }
    }


    /**
     * Initialize Vault API with an API key, and optional region (US, EU)
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

    private function callAPI($action, $payload = array()){

        if($this->apiendpoint=="" || $this->apikey==""){
            throw new Exception("Please call init() with your API key.");
        }


        $payload["apikey"] = $this->apikey;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiendpoint . "vault/" . $action);
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