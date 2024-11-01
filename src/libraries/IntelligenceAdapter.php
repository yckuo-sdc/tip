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
    public function search($query, $source="shodan")
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
     * @param array $jsonObj
     *
     * @return string $html
     */
    // Extract key data from JSON object
    public function extractKeyData($obj, $source="shodan" )
    {
        $keyData = [];
        if (empty($obj)) {
            return $keyData;
        }

        $keyMap = [
            "shodan" => [ 
                ["field" => "ip", "pair_displayed" => False],
                ["field" => "hostnames", "pair_displayed" => False],
                ["field" => "country_code", "pair_displayed" => False],
                ["field" => "org", "pair_displayed" => False],
                ["field" => "data", "pair_displayed" => False],
                ["field" => "tags", "pair_displayed" => False],
            ],
            "vt" => [
                ["field" => "last_analysis_stats", "pair_displayed" => False],
                ["field" => "total_votes", "pair_displayed" => False],
                ["field" => "resolutions_count", "pair_displayed" => True],
                ["field" => "collections_count", "pair_displayed" => True],
            ],
            "censys" => [],
            "mandiant" => [],
        ];


        $sourceKeys = getArrayValue($keyMap, $source);
        foreach ($sourceKeys as $sourceKey) {
           $item = [     
               "name" => $sourceKey["field"],
               "value" => getArrayString($obj, $sourceKey["field"]),
               "pair_displayed" => $sourceKey["pair_displayed"],
           ];
           $keyData[] = $item;
        }

        return $keyData;
    }



    /**
     * @param array $jsonObj
     *
     * @return string $html
     */
    // Recursive function to render JSON in HTML with collapsible/expandable structure
    public function renderJSON($obj)
    {
        $html = "";

        if (empty($obj)) {
            $html .= "No Information.";
            return $html;
        }

        foreach ($obj as $key => $value) {
            if (is_array($value)) {

                // Handle Arrays
                $isAssociative = count(array_filter(array_keys($value), 'is_string')) > 0;

                if ($isAssociative) {
                    // Handle Associative Arrays
                    $fieldNumber = count(array_keys($value));
                    $html .= "<div>
                                <span class='toggle-button'><i class='plus square outline icon'></i></span>
                                <strong>{$key}: <span class='curly bracket show'>{ /* {$fieldNumber} fileds */ }</span></strong>
                                <div class='nested' style='display:none;'>" . renderJSON($value) . "</div>
                             </div>";
                } else {
                    // Handle Indexed Arrays
                    $itemNumber = count($value);

                    if ($itemNumber == 0) {
                        $html .= "<div>
                                    <strong>{$key}: <span class='square bracket show'>[ ]</span></strong>
                                  </div>";
                    } else {
                        $html .= "<div>
                                    <span class='toggle-button'><i class='plus square outline icon'></i></span>
                                    <strong>{$key}: <span class='square bracket show'>[ /* {$itemNumber} items */ ]</span></strong>
                                    <div class='nested' style='display:none;'>" . renderJSON($value) . "</div>
                                  </div>";
                    }
                }

            } else {
                // Handle Primitive Values (string, number, etc.)
                $colorClass = $this->getColorClass($value);
                $html .= "<div><strong>{$key}:</strong> <span class='$colorClass'>" . htmlspecialchars(json_encode($value)) . "</span></div>";
            }
        }

        return $html;
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
            echo "<div class='ui error message'>" . "Curl error: " . curl_error($curl) . "</div>";
            //exit;
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
        $data = json_decode($jsonString, True);
    

        // Check if decoded data is an array
        if (is_array($data)) {
            // Return the count of elements in the array
            if (array_key_exists("data_count", $data)) {
                return $data["data_count"];
            } 
            return count($data);
        } elseif (is_object($data)) {
            // Return 1 if the decoded data is an object
            return 0;
        } else {
            // Handle invalid JSON string
            return 0;
        }
    }


    /**
     * @param string $value
     *
     * @return string $colorClass
     */
    // count the number of objects in JSON
    private function getColorClass($value)
    {
        switch (gettype($value)) {
            case "string":
                return "string-value color";
            case "integer":
            case "double":
                return "number-value color";
            case "boolean":
                return "boolean-value color";
            case "NULL":
                return "null-value color";
            default:
                return "default-value color";
        }
    }

}
