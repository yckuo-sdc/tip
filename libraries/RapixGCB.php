<?php

namespace gcb\api;

class RapixGCB
{
    private $host;
    private $token;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->host = Rapix::HOST;
        $this->token = $this->getAccessToken(Rapix::APIKEY);
        if (is_null($this->token)) {
            exit;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->token = null;
    }

    /**
     * @param integer $limit
     *
     * @return array $clients
     */
    public function getClientList($limit = 20)
    {
        $url = $this->host . "/api/v1/client/list";
        $postField = json_encode(array(
               "kind" => "list&count",
               "page" => 1,
               "limit" => $limit,
               "incl_exc" => true,
               "sorts" => [[
                    "field" => "ID",
                    "type" => "ASC"
               ]],
               "select" => [
                   "ID",
                   "Name",
                   "UserName",
                   "AssocOwner",
                   "OrgName",
                   "OSEnvID",
                   "OSArch",
                   "IEEnvID",
                   "InternalIP",
                   "ExternalIP",
                   "IsOnline",
                   "GsID",
                   "GsSetDeployID",
                   "GsStat",
                   "GsExcTot",
                   "GsAll",
                   "GsUpdatedAt"
               ],
               "filter" => []
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    /**
     * @param integer $client_id
     *
     * @return array $client_detail
     */
    public function getClientDetail($client_id)
    {
        $url = $this->host . "/api/v1/client/detail/" . $client_id;
        $postField = json_encode(array("client_id" => $client_id));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    /**
     * @param integer $gs_id
     *
     * @return array $gcb_scan_results
     */
    public function getGscanResult($gs_id)
    {
        $url = $this->host . "/api/v1/gscan/result/" . $gs_id;
        $postField = json_encode(array("gs_id" => $gs_id));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    /**
     * @param string $apikey
     *
     * @return string $token
     */
    private function getAccessToken($apikey)
    {
        $url = $this->host . "/api/v1/token";
        $httpHeader = array("Content-Type: application/json");
        $postField = json_encode(array("key" => $apikey));
        $response = $this->sendHttpRequest($url, $postField, $httpHeader);

        if(($data = json_decode($response, true)) == true) {
            $token = $data['token'];
        }

        if (isset($token)) {
            return $token;
        }

        return null;
    }

    /**
     * @param string $url
     * @param array $postField
     * @param array $httpHeader
     *
     * @return string $token
     */
    // send http request with bearer token
    private function sendHttpRequest($url, $postField, $httpHeader = array())
    {
        if (empty($httpHeader)) {
            $httpHeader = array("Content-Type: application/json", "Authorization: Bearer ".$this->token);
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postField,
            CURLOPT_HTTPHEADER => $httpHeader
        ));
        $res = curl_exec($curl);

        // Check if any error occurred
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);
        return $res;
    }

}
