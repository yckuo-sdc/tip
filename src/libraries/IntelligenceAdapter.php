<?php

class IntelligenceAdapter
{
    private $host;
    private $token;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->host = $_ENV['Intelligence_HOST'];
        $this->token = $_ENV['Intelligence_TOKEN'];
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
     * @param string $query
     *
     * @return string $results
     */
    public function search($query, $source = "shodan")
    {
        $url = $this->host . "/api/" . $source;
        $postField = array(
            "token" => $this->token,
            "query" => $query,
        );
        $response = $this->sendHttpRequest($url, $postField);
        $objectNumber = $this->countObjectsInJson($response);

        $results = array(
            "count" => $objectNumber,
            "data" => json_decode($response, true),
        );
        return $results;
    }


    /**
     * @param string $url
     * @param array $postField
     * @param array $httpHeader
     *
     * @return string $token
     */
    // send http request
    private function sendHttpRequest($url, $postField, $httpHeader = array())
    {
        if (empty($httpHeader)) {
            $httpHeader = array("Content-Type: multipart/form-data;");
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


    /**
     * @param string $jsonString
     *
     * @return integer $objectNumber
     */
    // count the number of objects in JSON
    private function countObjectsInJson($jsonString)
    {
        // Decode JSON string
        $data = json_decode($jsonString);

        // Check if decoded data is an array
        if (is_array($data)) {
            // Return the count of elements in the array
            return count($data);
        } elseif (is_object($data)) {
            // Return 1 if the decoded data is an object
            return 1;
        } else {
            // Handle invalid JSON string
            return -1;
        }
    }

}
