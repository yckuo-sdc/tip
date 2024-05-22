<?php

class HttpHelper
{
    /**
     * @param string $url
     * @param array $curlopt
     * @param array $getinfo
     *
     * @return array $curl_info
     */
    public function getUrlResponse($url, $curlopt = array(), $getinfo = array())
    {
        $agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36";
        $curl = curl_init();

        $opt_array = $curlopt + array(
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $agent,
            CURLOPT_RETURNTRANSFER => true, //講curl_exec()獲取的信息以文件流的形式返回，而不是直接輸出。
            CURLOPT_VERBOSE => false, // 啟用時會匯報所有的信息，存放在STDERR或指定的CURLOPT_STDERR中
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HEADER => true, // 顯示表頭
            CURLOPT_NOBODY => true, // 忽略內容
            CURLOPT_FOLLOWLOCATION => true, // 遞迴，跟著頁面跳轉
            CURLOPT_MAXREDIRS => 5, //  避免無限遞迴
            CURLOPT_HTTPHEADER => array('Expect:'), //避免data資料過長問題
            CURLOPT_SSL_VERIFYPEER => false, // 略過檢查 SSL 憑證有效性
            CURLOPT_SSL_VERIFYHOST => false, // 略過從證書中檢查SSL加密演算法是否存在
        );

        curl_setopt_array($curl, $opt_array);

        $result = curl_exec($curl);
        $curl_info = array();
        if (empty($getinfo)) {
            $curl_info = curl_getinfo($curl);
        } else {
            $curl_info['header'] = $result;
            foreach ($getinfo as $optKey) {
                $curl_info[$optKey] = curl_getinfo($curl, $optKey);
            }
        }

        curl_close($curl);

        return $curl_info;
    }

    /**
     * @param string $url
     *
     * @return array $ssl_date
     */
    public function getSSLDate($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(
            array(
                "ssl" => array(
                    "capture_peer_cert" => true,
                    "verify_peer"   => false,
                )
            )
        );

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $ssl_date = array();
        $ssl_date['status'] = "Failure";
        $ssl_date['valid_from_time'] = null;
        $ssl_date['valid_to_time'] = null;
        try {
            $timeout = 10; // seconds
            $read = stream_socket_client("ssl://" . $host . ":443", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $get);
        } catch (ErrorException $e) {
            $status = $e->getMessage();
            //$ssl_date['status'] = str_replace("stream_socket_client():", "", $status);
            $ssl_date['status'] = $status;
        }

        if (empty($read)) {
            return $ssl_date;
        }

        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        $valid_from_time = date("Y-m-d H:i:s", $certinfo['validFrom_time_t']);
        $valid_to_time = date("Y-m-d H:i:s", $certinfo['validTo_time_t']);

        $ssl_date['status'] = "Success";
        $ssl_date['valid_from_time'] = $valid_from_time;
        $ssl_date['valid_to_time'] = $valid_to_time;

        return $ssl_date;
    }

    /**
     * @param string $url
     * @param string &$status
     *
     * @return bool
     */
    public function isOnlyOrRedirectHttps($url = "", &$status)
    {
        $status = "NULL";

        if (empty($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $redirect_http_codes = array(301, 302);
        $curlopt = array(
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 15,
        );

        // testing for http
        $response = $this->getUrlResponse("http://" . $host, $curlopt);

        if (!empty($response['header_size'])) {

            if (!in_array($response['http_code'], $redirect_http_codes)) {
                $status = "The http response code is neither 301 nor 302";
                return false;
            }

            $redirect_host = parse_url($response['redirect_url'], PHP_URL_HOST);
            $redirect_scheme = parse_url($response['redirect_url'], PHP_URL_SCHEME);

            if (strcasecmp($redirect_scheme, "https") != 0  || strcasecmp($redirect_host, $host) != 0) {
                $status = "The http response will redirect to http or different orgin";
                return false;
            }

            $status = "Http2https";
            return true;
        }

        // testing for https
        $response = $this->getUrlResponse("https://" . $host, $curlopt);

        if (!empty($response['header_size'])) {
            $status = "Onlyhttps";
            return true;
        }

        $status = "No http or https";
        return false;
    }

} // End of class
