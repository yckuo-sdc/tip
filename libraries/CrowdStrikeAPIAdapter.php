<?php

class CrowdStrikeAPIAdapter
{
    private $host = "";
    private $access_token = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->host = CrowdStrike::HOST;
        $this->access_token = $this->getTokens(CrowdStrike::CLIENT_ID, CrowdStrike::CLIENT_SECRET);

        if (empty($this->access_token)) {
            exit;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->host = "";
        $this->access_token = null;
        $this->refresh_token = null;
    }


    /*
    * @param array data_array
    * @return array client_list
    */
    public function getDevicesQueries($data_array = array())
    {
        $query = "?" . http_build_query($data_array);
        $url = $this->host . "/devices/queries/devices/v1" .  $query;

        $response = $this->sendHttpRequest($url, $postField = array());
        $response = json_decode($response, true);

        $resource_ids = array();
        if (!empty($response['resources'])) {
            $resource_ids = $response['resources'];
        }

        return $resource_ids;
    }


    /*
    * @param array data_array
    * @return array client_list
    */
    public function getDevicesEntities($data_array = array())
    {
        $query = "?" . http_build_query($data_array);
        $url = $this->host . "/devices/entities/devices/v1" .  $query;

        $response = $this->sendHttpRequest($url, $postField = array());
        $response = json_decode($response, true);

        $resources = array();
        if (!empty($response['resources'])) {
            $resources = $response['resources'][0];
            $resources['internal_ip'] = "null";
            if (!empty($resources['connection_ip'])) {
                $resources['internal_ip'] = $resources['connection_ip'];
            }
        }

        $keys = ['device_id', 'hostname', 'last_seen', 'status', 'external_ip', 'internal_ip'];
        $resources = array_keys_whitelist($resources, $keys);

        return $resources;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array $tokens
     */
    private function getTokens($client_id, $client_secret)
    {
        $url = $this->host . '/oauth2/token';
        $httpHeader = array(
            "Accept: application/json",
            "Content-Type: application/x-www-form-urlencoded"
         );
        $postField = http_build_query(array("client_id" => $client_id, "client_secret" => $client_secret));
        $response = $this->sendHttpRequest($url, $postField, $httpHeader);

        $tokens = "";
        if(($data = json_decode($response, true)) == true) {
            if (!empty($data['access_token'])) {
                $tokens = $data['access_token'];
            }
        }

        return $tokens;
    }

    /**
     * @param string $url
     * @param array $postField
     * @param array $httpHeader
     *
     * @return string $response
     */
    // send http request with bearer token
    private function SendHttpRequest($url, $postField, $httpHeader = array())
    {

        if (empty($httpHeader)) {
            $httpHeader = array(
                "Accept: application/json",
                "Authorization: Bearer " . $this->access_token
            );
        }

        $curl_opt_array = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $httpHeader,
        );

        if (empty($postField)) {
            $curl_opt_array[CURLOPT_CUSTOMREQUEST] = "GET";
        } else {
            $curl_opt_array[CURLOPT_CUSTOMREQUEST] = "POST";
            $curl_opt_array[CURLOPT_POSTFIELDS] = $postField;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curl_opt_array);
        $response = curl_exec($curl);

        // Check if any error occurred
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }
}
