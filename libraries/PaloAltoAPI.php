<?php

/*** manual： https:// {{ host }} /php/rest/browse.php ***/

class PaloAltoAPI
{
    private $host;
    private $apikey;

    /**
     * Constructor
     */
    public function __construct($host = null)
    {
        if ($host == null) {
            $this->host = PaloAlto::HOST_ADDRESS['yonghua'];
        } else {
            $this->host = PaloAlto::HOST_ADDRESS[$host];
        }

        $this->apikey = $this->getAPIKey("keygen", PaloAlto::USERNAME, PaloAlto::PASSWORD);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->host = null;
        $this->apikey = null;
    }

    /**
     * @return array $property
     */
    public function getProperty()
    {
        $property['host'] = $this->host;
        $property['apikey'] = $this->apikey;
        return $property;
    }

    /**
     * @param string $host
     * @param string $apikey
     */
    public function setProperty($host, $apikey)
    {
        $this->host = $host;
        $this->apikey = $apikey;
    }

    // ================
    // === xml api ====
    // ================

    /**
     * @return array $security_rules
     */
    public function getXmlSecurityRules()
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $args = array('type' => 'config', 'action' => 'show', 'xpath' => '/config/devices/entry/vsys/entry/rulebase/security');
        $url = "https://" . $host . "/api/?" . http_build_query($args) . "&key=" . $apikey;
        $xmlstr = $this->sendHttpRequest($url);
        $xml = simplexml_load_string($xmlstr) or die("Error: Cannot create object");
        $security_rules = xml2array($xml);
        return $security_rules;
    }

    /**
     * @param string $log_type
     * @param string $dir
     * @param integer $nlogs
     * @param integer $skip
     * @param string $query_criteria
     *
     * @return array $logs
     */
    public function retrieveLogs($log_type, $dir, $nlogs, $skip, $query_criteria)
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $args = array('type' => 'log', 'log-type' => $log_type, 'dir' => $dir, 'nlogs' => $nlogs, 'skip' => $skip, 'query' => $query_criteria, 'async' => 'yes', 'uniq' => 'yes');
        $url = "https://$host/api/?" . http_build_query($args) . "&key=$apikey";
        $xmlstr = $this->sendHttpRequest($url);
        $xml = simplexml_load_string($xmlstr) or die("Error: Cannot create object");
        $job_id = $xml->result->job;

        $max_query_count = 30;
        $query_count = 0;
        $timeout = true;
        do {
            $queried_log = $this->retrieveLogsByJobId($job_id);
            $query_count = $query_count + 1;
            if ($queried_log['@attributes']['status'] == 'success') {
                if ($queried_log['result']['job']['status'] == 'FIN') {
                    $timeout = false;
                }
            }
        } while ($timeout & $query_count < $max_query_count);

        if ($timeout) {
            echo "Timeout" . "<br>";
        }

        $data_array = array();
        $data_array['log_count'] = 0;
        $data_array['logs'] = array();

        if ($queried_log['@attributes']['status'] != 'success') {
            return $data_array;
        }

        $logs = $queried_log['result']['log']['logs'];
        $data_array['log_count'] = $logs['@attributes']['count'];

        if (empty($data_array['log_count'])) {
            return $data_array;
        }

        // if log_count euqal 1, PaloAlto will return object not array of object
        if ($data_array['log_count'] == 1) {
            $data_array['logs'] = array($logs['entry']);
            return $data_array;
        }

        $data_array['logs'] = $logs['entry'];
        return $data_array;
    }

    /**
     * @param string $report_type
     * @param string $report_name
     *
     * @return array $report
     */
    public function getAsyncReport($report_type, $report_name)
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $args = array('type' => 'report', 'reporttype' => $report_type, 'reportname' => $report_name, 'async' => 'yes', 'uniq' => 'yes');
        $url = "https://$host/api/?" . http_build_query($args) . "&key=$apikey";
        $xmlstr = $this->sendHttpRequest($url);
        $xml = simplexml_load_string($xmlstr) or die("Error: Cannot create object");
        $job_id = $xml->result->job;

        $report = array();
        $report['log_count'] = 0;
        $report['logs'] = array();

        if (empty($job_id)) {
            echo "no job_id" . PHP_EOL;
            return $report;
        }

        echo "job_id: " . $job_id . PHP_EOL;

        $max_query_count = 5;
        $query_count = 0;
        $timeout = true;
        do {
            sleep(5);
            $queried_log = $this->retrieveLogsByJobId($job_id, $type = "report", $action = "get");
            echo "query" . $query_count + 1 . ". " . $queried_log['@attributes']['status'] . PHP_EOL;
            $query_count = $query_count + 1;
            if ($queried_log['@attributes']['status'] == 'success') {
                if (empty($queried_log['result']['report']['entry'])) {
                    echo "get 0 entries" . PHP_EOL;
                } else {
                    $timeout = false;
                    echo "get " . count($queried_log['result']['report']['entry']) . " entries" . PHP_EOL;
                }
            }
        } while ($timeout & $query_count < $max_query_count);

        if ($timeout) {
            echo "Timeout" . PHP_EOL;
            return $report;
        }

        $logs = array();
        $log_count = 0;
        //foreach ($queried_log->result->report->entry as $entry) {
        foreach ($queried_log['result']['report']['entry'] as $entry) {
            foreach ($entry as $key => $val) {
                $logs[$log_count][$key] = $val;
            }
            $log_count = $log_count + 1;
        }
        $report['log_count'] = $log_count;
        $report['logs'] = $logs;

        return $report;
    }

    /**
     * @param string $report_type
     * @param string $report_name
     *
     * @return array $report
     */
    public function getSyncReport($report_type, $report_name)
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $args = array('type' => 'report', 'reporttype' => $report_type, 'reportname' => $report_name, 'async' => 'yes', 'uniq' => 'yes');
        $url = "https://$host/api/?" . http_build_query($args) . "&key=$apikey";
        $xmlstr = $this->sendHttpRequest($url);
        $xml = simplexml_load_string($xmlstr) or die("Error: Cannot create object");

        $log_count = 0;
        $logs = array();
        foreach($xml->result->entry as $entry) {
            foreach($entry as $key => $val) {
                $logs[$log_count][$key] = $val;
            }
            $log_count = $log_count + 1;
        }

        $report = array();
        $report['log_count'] = $log_count;
        $report['logs'] = $logs;
        return $report;
    }

    /**
     * @param string $type
     * @param string $cmd
     *
     * @return string $response
     */
    public function getXmlCmdResponse($type, $cmd)
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $url = "https://$host/api/?type=$type&cmd=$cmd&key=$apikey";
        $response = $this->sendHttpRequest($url);
        return $response;
    }

    // ====================
    // === restful api ====
    // ====================

    /**
     * @param string $object_type
     * @param string $name
     *
     * @return array
     */
    public function getObjectList($object_type, $name)
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $url = "https://$host/restapi/9.0/Objects/$object_type?name=$name&location=vsys&vsys=vsys1&output-format=json&key=".$apikey;
        $result = $this->sendHttpRequest($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * @param string $policy_type
     * @param string $name
     *
     * @return array
     */
    public function getPoliciesList($policy_type, $name)
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $url = "https://$host/restapi/9.0/Policies/$policy_type?name=$name&location=vsys&vsys=vsys1&output-format=json&key=".$apikey;
        $response = $this->sendHttpRequest($url);
        $response = json_decode($response, true);
        return $response;
    }

    /**
     * @param string $job_id
     * @param string $type
     * @param string $action
     *
     * @return array $queried_log
     */
    private function retrieveLogsByJobId($job_id, $type = "log", $action = "get")
    {
        $host = $this->host;
        $apikey = $this->apikey;
        $args = array('type' => $type, 'action' => $action, );
        $url = "https://$host/api/?".http_build_query($args)."&job-id=$job_id&key=$apikey";
        $xmlstr = $this->sendHttpRequest($url);
        $queried_log = simplexml_load_string($xmlstr) or die("Error: Cannot create object");

        return xml2array($queried_log);
    }

    /**
     * @param string $type
     * @param string $username
     * @param string $password
     *
     * @return string $apikey
     */
    private function getAPIKey($type, $username, $password)
    {
        $host = $this->host;
        $url = "https://".$host."/api/?type=$type&user=$username&password=$password";
        $xmlstr = $this->sendHttpRequest($url);
        $xml = simplexml_load_string($xmlstr) or die("Error: Cannot create object");
        $apikey = $xml->result->key;
        return $apikey;
    }

    /**
     * @param string $url
     *
     * @return string $response
     */
    private function sendHttpRequest($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 20,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_HTTPHEADER => array("Content-Type: application/json")
        ));
        $response = curl_exec($curl);

        // Check if any error occurred
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);
        return $response;
    }

}

/** Usage
require 'vendor/autoload.php';

$hosts = ['yonghua', 'minjhih', 'idc', 'intrayonghua'];

foreach($hosts as $key => $host){
    $pa = new PaloAltoAPI($host);
    $xml_type = "op";
    $cmd = "<show><system><info></info></system></show>";
    $xmlstr = $pa->getXmlCmdResponse($xml_type, $cmd);
    print_r($xmlstr);
}

**/
