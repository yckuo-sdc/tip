<?php

function generateRandomToken()
{
    return bin2hex(random_bytes($length = 32));
}

function generateUserCookie($username, $displayname, $level)
{
    $algo = "sha256";
    $key = "security";

    $token = generateRandomToken(); // generate a token, should be 128 - 256 bit
    $userCookie = $username . ':' . $token. ':' . $displayname . ':' . $level;
    $mac = hash_hmac($algo, $userCookie, $key);
    $userCookie .= ':' . $mac;
    return $userCookie;
}

function decryptUserCookie($userCookie)
{
    $algo = "sha256";
    $key = "security";

    list($username, $token, $displayname, $level, $mac) = explode(':', $userCookie);
    $encrypted_data = $username . ':' . $token .':'. $displayname . ':' . $level;

    $data_array = array();
    if (hash_equals(hash_hmac($algo, $encrypted_data, $key), $mac)) {
        $data_array['isValid'] = true;
        $data_array['username'] = $username;
        $data_array['displayname'] = $displayname;
        $data_array['level'] = $level;
    } else {
        $data_array['isValid'] = false;
    }
    return $data_array;
}

//Alert message
function phpAlert($msg)
{
    echo '<script type="text/javascript">alert("' . $msg . '")</script>';
}

// For Windows NT Time convert to UnixTimestamp
function WindowsTime2UnixTime($WindowsTime)
{
    $UnixTime = $WindowsTime / 10000000 - 11644473600;
    return $UnxiTime;
}
// For Windows NT Time convert to UnixTimestamp
function WindowsTime2DateTime($WindowsTime)
{
    $UnixTime = $WindowsTime / 10000000 - 11644473600;
    return date('Y-m-d H:i:s', $UnixTime);
}

// check the disabled ad account
function dateConvert($str)
{
    $GMT = 8;	// Time zones with GMT+8
    if($str == 'NULL' || $str == '') {
        return $str;
    } else {
        return date('Y-m-d H:i:s', strtotime($str) + $GMT * 3600);
    }
}

//處理 Nmap 掃描原始結果，將使用的Port、State、Service取出紀錄
function NmapParser($input)
{
    $rows = explode("\n", $input);
    $rows = array_slice($rows, 6);
    $stack = array();
    foreach($rows as $key => $data) {
        //get row data
        $row_data = explode(" ", preg_replace('/\s+/', ' ', $data));
        if($row_data[0]) {
            $port_data = explode("/", $row_data[0]);
            $portStatus = strtolower(trim($row_data[1]));
            $portDesc = $row_data[2];
            if($portStatus == 'open' || $portStatus == 'filtered' || $portStatus == 'closed') {
                $portNum = $port_data[0];
                $TcpOrUdp = $port_data[1];
                array_push($stack, array($portNum,$TcpOrUdp,$portStatus,$portDesc));
            }
        }
    }
    return $stack;
}

// convert json to csv file
function jsonToCSV($json, $cfilename)
{
    //if (($json = file_get_contents($jfilename)) == false)
    //    die('Error reading json file...');
    $data = json_decode($json, true);
    $fp = fopen($cfilename, 'w');
    $header = false;
    foreach ($data as $row) {
        if (empty($header)) {
            $header = array_keys($row);
            fputcsv($fp, $header);
            $header = array_flip($header);
        }
        fputcsv($fp, array_merge($header, $row));
    }
    fclose($fp);
    return 0;
}

// convert array to csv file
function array2csv($list)
{
    if (count($list) == 0) {
        return null;
    }
    $fp = fopen('file.csv', 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    foreach ($list as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);
    return true;
}

// convert array to csv file and download automatically
function array_to_csv_download($array, $filename, $delimiter)
{
    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    //Clean the output buffer
    ob_clean();
    // open the "output" stream
    $f = fopen('php://output', 'w');
    fputs($f, "\xEF\xBB\xBF"); // UTF-8 BOM !!!!!
    fputcsv($f, array_keys($array[0]));
    foreach ($array as $line) {
        fputcsv($f, $line);
    }
    fclose($f);
}

// check the disabled of AD account
function isDisabled($useraccountcontrol)
{
    $accountdisable = 0x0002 & $useraccountcontrol;

    if ($accountdisable == 2) {
        return true;
    }

    return false;
}

function getUACDescription($useraccountcontrol)
{
    $UAC_flag_array = array(
        array('property' => 'SCRIPT', 'hex_value' => '0x0001', 'dec_value' => '1'),
        array('property' => 'ACCOUNTDISABLE', 'hex_value' => '0x0002', 'dec_value' => '2'),
        array('property' => 'HOMEDIR_REQUIRED', 'hex_value' => '0x0008', 'dec_value' => '8'),
        array('property' => 'LOCKOUT', 'hex_value' => '0x0010', 'dec_value' => '16'),
        array('property' => 'PASSWD_NOTREQD', 'hex_value' => '0x0020', 'dec_value' => '32'),
        array('property' => 'PASSWD_CANT_CHANGE', 'hex_value' => '0x0040', 'dec_value' => '64'),
        array('property' => 'ENCRYPTED_TEXT_PWD_ALLOWED', 'hex_value' => '0x0080', 'dec_value' => '128'),
        array('property' => 'TEMP_DUPLICATE_ACCOUNT', 'hex_value' => '0x0100', 'dec_value' => '256'),
        array('property' => 'NORMAL_ACCOUNT', 'hex_value' => '0x0200', 'dec_value' => '512'),
        array('property' => 'INTERDOMAIN_TRUST_ACCOUNT', 'hex_value' => '0x0800', 'dec_value' => '2048'),
        array('property' => 'WORKSTATION_TRUST_ACCOUNT', 'hex_value' => '0x1000', 'dec_value' => '4096'),
        array('property' => 'SERVER_TRUST_ACCOUNT', 'hex_value' => '0x2000', 'dec_value' => '8192'),
        array('property' => 'DONT_EXPIRE_PASSWORD', 'hex_value' => '0x10000', 'dec_value' => '65536'),
        array('property' => 'MNS_LOGON_ACCOUNT', 'hex_value' => '0x20000', 'dec_value' => '131072'),
        array('property' => 'SMARTCARD_REQUIRED', 'hex_value' => '0x40000', 'dec_value' => '262144'),
        array('property' => 'TRUSTED_FOR_DELEGATION', 'hex_value' => '0x80000', 'dec_value' => '524288'),
        array('property' => 'NOT_DELEGATED', 'hex_value' => '0x100000', 'dec_value' => '1048576'),
        array('property' => 'USE_DES_KEY_ONLY', 'hex_value' => '0x200000', 'dec_value' => '2097152'),
        array('property' => 'DONT_REQ_PREAUTH', 'hex_value' => '0x400000', 'dec_value' => '4194304'),
        array('property' => 'PASSWORD_EXPIRED', 'hex_value' => '0x800000', 'dec_value' => '8388608'),
        array('property' => 'TRUSTED_TO_AUTH_FOR_DELEGATION', 'hex_value' => '0x1000000', 'dec_value' => '16777216'),
        array('property' => 'PARTIAL_SECRETS_ACCOUNT', 'hex_value' => '0x04000000', 'dec_value' => '67108864'),
    );

    // convert string to int base 10
    $useraccountcontrol = intval($useraccountcontrol);
    // convert base 10 to base 16
    $hexValue = intval($useraccountcontrol, 16);

    $filtered_array = array_filter($UAC_flag_array, function ($value) use (&$hexValue) {
        return intval($value['hex_value'], 16) & $hexValue;
    });

    $property_array = array_column($filtered_array, 'property');

    return implode(" | ", $property_array);
}

function formatBytes($bytes, $precision = 1)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = (int) $bytes;
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function formatNumbers($num, $precision = 1)
{
    $units = array('', 'K', 'M', 'G', 'T');
    $num = (int) $num;
    $num = max($num, 0);
    $pow = floor(($num ? log($num) : 0) / log(1000));
    $pow = min($pow, count($units) - 1);
    $num /= pow(1024, $pow);
    return round($num, $precision) . ' ' . $units[$pow];
}

function test_print($item, $key)
{
    echo "<div class='ui black label'>" . $item . "</div>";
}

function filterHtml(&$value)
{
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function breadcrumbs($separator = ' &raquo; ', $home = 'Home')
{
    // This gets the REQUEST_URI (/path/to/file.php), splits the string (using '/') into an array, and then filters out any empty values
    $path = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

    // This will build our "base URL" ... Also accounts for HTTPS :)
    $base = ($_SERVER['HTTPS'] ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

    // Initialize a temporary array with our breadcrumbs. (starting with our home page, which I'm assuming will be the base URL)
    $breadcrumbs = array("<a href=\"$base\">$home</a>");

    // Initialize crumbs to track path for proper link
    $crumbs = '';

    // Find out the index for the last value in our path array
    $last = @end(array_keys($path));

    // Build the rest of the breadcrumbs
    foreach ($path as $x => $crumb) {
        // Our "title" is the text that will be displayed (strip out .php and turn '_' into a space)
        $title = ucwords(str_replace(array('.php', '_', '%20'), array('', ' ', ' '), $crumb));

        // If we are not on the last index, then display an <a> tag
        if ($x != $last) {
            $breadcrumbs[] = "<a href=\"$base$crumbs$crumb\">$title</a>";
            $crumbs .= $crumb . '/';
        }
        // Otherwise, just display the title (minus)
        else {
            $breadcrumbs[] = $title;
        }

    }

    // Build our temporary array (pieces of bread) into one big string :)
    return implode($separator, $breadcrumbs);
}

function createBreadCrumbsWithOu($ou)
{
    $ou_pieces = explode("/", substr_replace($ou, '', 0, 1));

    // remove first piece "雲嘉嘉南"
    unset($ou_pieces[0]);

    foreach($ou_pieces as $key => $val) {
        $ou_pieces[$key] = "<div class='section'>"  . $val . "</div>";
    }

    $separator = "<i class='right angle icon divider'></i>";
    $breadcrumbs = "<div class='ui breadcrumb'>" . implode($separator, $ou_pieces) . "</div>";

    return $breadcrumbs;
}

function createWebadMessageBox($result, $label)
{
    $html = "";
    if ($result == '"1."') {
        $html .= "<div class='ui info message'>";
        $html .= $label . " 執行結果: ". $result;
        $html .= "</div>";
    } else {
        $html .= "<div class='ui negative message'>";
        $html .= $label . " 執行結果: ". $result;
        $html .= "</div>";
    }
    return $html;
}

function xml2array($xmlObject, $out = array())
{
    // Convert into json
    $con = json_encode($xmlObject);
    // Convert into associative array
    $out = json_decode($con, true);

    return $out;
}

/**
 * Filter an array based on a white list of keys
 *
 * @param array $array
 * @param array $keys
 *
 * @return array
 */
function array_keys_whitelist(array $array, array $keys)
{
    return array_intersect_key($array, array_flip($keys));
}

/**
 * Checks if a given IP address matches the specified CIDR subnet/s
 *
 * @param string $ip The IP address to check
 * @param mixed $cidrs The IP subnet (string) or subnets (array) in CIDR notation
 * @param string $match optional If provided, will contain the first matched IP subnet
 * @return boolean TRUE if the IP matches a given subnet or FALSE if it does not
 */
function ipMatch($ip, $cidrs, &$match = null)
{
    foreach((array) $cidrs as $cidr) {
        list($subnet, $mask) = explode('/', $cidr);
        if(((ip2long($ip) & ($mask = ~ ((1 << (32 - $mask)) - 1))) == (ip2long($subnet) & $mask))) {
            $match = $cidr;
            return true;
        }
    }
    return false;
}

function getDaysAfterToday($target_time, $format = 'Y-m-d')
{
    $dtime = DateTime::createFromFormat($format, $target_time);
    $now = new DateTime('now');
    $interval = date_diff($now, $dtime);
    $interval_days = $interval->format('%R%a');
    return $interval_days;
}

// Recursive function to render JSON in HTML with collapsible/expandable structure
function renderJSON($obj)
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
            $colorClass = getColorClass($value);
            $html .= "<div><strong>{$key}:</strong> <span class='$colorClass'>" . htmlspecialchars(json_encode($value)) . "</span></div>";
        }
    }

    return $html;
}

function getColorClass($value)
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
