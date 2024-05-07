<?php

namespace gcb\api;

/**
 * 從瑞思Rapix GCB網站分離出來的Web API功能
 * @since 1.0.0 完成登入、Client、CPE資產取得
 * @author 阿舜 <wewe987001@email.com>
 */
class RapixWebAPIAdapter
{
    private $host = "";
    private $access_token = null;
    private $refresh_token = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->host = Rapix::HOST;
        $tokens = $this->getTokens(Rapix::USERNAME, Rapix::PASSWORD);

        if (empty($tokens)) {
            exit;
        }

        $this->access_token = $tokens['access_token'];
        $this->refresh_token = $tokens['refresh_token'];
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

    //function fetchClientListByCPEAsset($cpeAssetId) {
    //    if (empty($cpeAssetId)) return [];
    //    $postData = [
    //        "kind" => "list&count",
    //        "filter" => [
    //            ["operator" => "AND", "type" => "column", "name" => "CPEAssetsID", "comparison_op" => "=", "content" => "$cpeAssetId"]
    //        ],
    //        "between" => true,
    //        "sorts" => [["field" => "ID", "type" => "ASC"]],
    //        "select" => ["ID", "OrgID", "Name", "IsOnline", "OrgName", "OSEnvID", "OSArch", "IEEnvID", "InternalIP", "ExternalIP", "DeployEndedAt", "LastActiveAt", "UpdatedAt", "OwnerAssoc", "VANSConfigOID", "VANSConfigURL", "VANSConfigAPIKey", "VANSConfigUnitName", "VANSConfigTag", "VANSReturnCode", "VANSReportedAt"]
    //    ];

    //    return $this->fetchClientList($postData);
    //}

    /**
     * @param array $data_array
     *
     * @return array $cpe_assets
     */
    public function fetchCPEAssets($data_array = array())
    {
        $this->refreshTokens();

        $defaultFilterType = 1; // 0:全部 1:僅顯示CPE比對成功結果 3:僅顯示有CVE弱點的項目

        if (empty($data_array)) {
            $data_array = array(
                "page" => 1,
                "limit" => 0,
                "filter_ctrl" => $defaultFilterType,
            );
        }

        $url = $this->host . "/api/web/client/cpe-assets";
        $postField = json_encode($data_array);
        $response = $this->sendHttpRequest($url, $postField);

        $cpe_assets = array();
        $data = json_decode($response, true);
        if (!empty($data)) {
            if (isset($data['data']['Items'])) {
                $cpe_assets = $data['data']['Items'];

                foreach ($cpe_assets as $key => $cpe) {
                    $cpe_assets[$key] = $cpe['Assets'];

                    if (!isset($cpe_assets[$key]['Dict'])) {
                        $cpe_assets[$key]['Dict'] = [];
                    }

                    $cpe_assets[$key] = $cpe_assets[$key] +
                        $cpe_assets[$key]['Dict'] +
                        array('Name' => '', 'Title' => '', 'UpdatedAt' => '');
                    unset($cpe_assets[$key]['Dict']);
                }

            }
        }
        return $cpe_assets;
    }

    //function fetchCPEAssetsByIp($ip = '') {
    //    // 找出 Client ID
    //    $data = current($this->fetchClientList([
    //        'filter' => [
    //            ['comparison_op' => "=", 'content' => $ip, 'name' => "InternalIP", 'operator' => "OR", 'type' => "column"],
    //            ['comparison_op' => "=", 'content' => $ip, 'name' => "ExternalIP", 'operator' => "OR", 'type' => "column"],
    //        ],
    //        'select' => ['ID'],
    //        'limit' => 1
    //    ]));
    //    if (empty($data)) return [];
    //    $clientId = $data['ID'];

    //    // 找出Client安裝軟體ID清單
    //    $data = $this->fetchClientDetail($clientId);
    //    if (empty($data)) return [];
    //    $idList = array_map(fn ($d) => isset($d['SoftWareData']['ID']) ? $d['SoftWareData']['ID'] : 0,  $data['ClientSoftwares']);
    //    $idList = array_combine($idList, $idList);

    //    // 找出有CVE漏洞的CPE資產
    //    $cpeList = $this->fetchCPEAssets();
    //    $cpeList = array_combine(
    //        array_map(fn ($c) => $c['Assets']['ID'], $cpeList),
    //        array_map(fn ($c) => $c['Assets'], $cpeList)
    //    );

    //    // 取交集取得該IP的
    //    $cpeList = array_intersect_key($cpeList, $idList);

    //    return $cpeList;
    //}

    /**
     * @return array $cpe_assets
     */
    public function fetchCPEIDAndClientIDMap()
    {
        $cpe_assets = $this->fetchCPEAssets();

        $cpe_asset_array = array();
        foreach ($cpe_assets as $key => $cpe_asset) {
            $cpe = array();

            $postField = array(
                "filter" => array(
                    array('operator' => 'AND', 'type' => 'column', 'name' => 'CPEAssetsID', 'comparison_op' => '=', 'content' => "{$cpe_asset['ID']}"),
                ),
                "kind" => "list&count",
                "select" => array("ID"),
                "limit" => 0,
                'sorts' => array(
                    array('field' => 'ID', 'type' => 'ASC'),
                ),
            );

            $cpe['CPEID'] = $cpe_asset['ID'];
            $cpe['ClientID'] = array_map(fn ($cid) => $cid['ID'], $this->fetchClientList($postField));
            $cpe_asset_array[] = $cpe;
        }

        return $cpe_asset_array;
    }

    /*
    * @param array data_array
    * @return array client_list
    */
    public function fetchClientList($data_array = array())
    {
        $this->refreshTokens();

        if (empty($data_array)) {
            $data_array = array(
                'filter' => array(),
                "kind" => "list&count",
                'limit' => 0,
                'page' => 1,
                'select' => array("ID", "OwnerAssoc", "UserName", "OrgName", "OrgID", "Name", "OSEnvID", "OSArch", "IEEnvID", "IsOnline", "InternalIP", "ExternalIP", "LastActiveAt", "UpdatedAt"),
                'sorts' => array(
                    array('field' => 'ID', 'type' => 'ASC'),
                ),
            );
        }

        $url = $this->host . "/api/web/client/list";
        $postField = json_encode($data_array);
        $response = $this->sendHttpRequest($url, $postField);

        $client_list = array();
        $data = json_decode($response, true);
        if (!empty($data)) {
            if (isset($data['List'])) {
                $client_list = $data['List'];
            }
        }
        return $client_list;
    }


    /** Refresh tokens
     */
    private function refreshTokens()
    {
        $url = $this->host . '/api/web/token';
        $httpHeader = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token,
            "Cookie: refresh_token=" . $this->refresh_token
        );

        $postField = '{}';
        $response = $this->sendHttpRequest($url, $postField, $httpHeader);

        if(($data = json_decode($response, true)) == true) {
            if ($data['status'] == 'success') {
                $this->refresh_token = $data['data']['refresh_token'];
                $this->access_token = $data['data']['access_token'];
            }
        }
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array $tokens
     */
    private function getTokens($username, $password)
    {
        $url = $this->host . '/api/web/login';
        $httpHeader = array("Content-Type: application/json");
        $postField = json_encode(array("username" => $username, "password" => $password));
        $response = $this->sendHttpRequest($url, $postField, $httpHeader);

        $tokens = array();
        if(($data = json_decode($response, true)) == true) {
            if ($data['status'] == 'success') {
                $tokens = $data['data'];
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
                "Content-Type: application/json",
                "Authorization: Bearer ".$this->access_token
            );
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
        $response = curl_exec($curl);

        // Check if any error occurred
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }
}
