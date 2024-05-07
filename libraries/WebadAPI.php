<?php

namespace ad\api;

class WebadAPI
{
    private $host;

    public function __construct()
    {
        $this->host = Webad::HOST;
    }

    // edit domain user
    public function editUser($cn, $newpass, $displayname, $title, $telephonenumber, $physicaldeliveryofficename, $mail, $isActive)
    {
        $url = $this->host . "/api/EditUser";
        $postField = json_encode(array(
            "Username" => $cn,
            //"Password" => $newpass,
            "NewPassword" => $newpass,
            "Name" => $displayname,
            "JobTitle" => $title,
            "Tel" => $telephonenumber,
            "Tel_Extension" => $physicaldeliveryofficename,
            "Email" => $mail,
            "IsActive" => $isActive
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // insert domain user
    public function insertUser($cn, $pass, $displayname, $title, $telephonenumber, $physicaldeliveryofficename, $mail, $ou)
    {
        $url = $this->host . "/api/NewUser";
        $postField = json_encode(array(
            "Username" => $cn,
            "Password" => $pass,
            //"NewPassword" => $newpass,
            "Name" => $displayname,
            "JobTitle" => $title,
            "Tel" => $telephonenumber,
            "Tel_Extension" => $physicaldeliveryofficename,
            "Email" => $mail,
            "OU" => $ou
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // change user's or computer's state
    public function changeState($cn, $PasswordChangeNextTime, $isActive, $isLocked)
    {
        $url = $this->host . "/api/ChangeUserState";
        $postField = json_encode(array(
            "Username" => $cn,
            "ComputerName" => $cn,
            "PasswordChangeNextTime" => $PasswordChangeNextTime,
            "isActive" => $isActive,
            "isLocked" => $isLocked
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // change computer's OU
    public function changeComputerOU($cn, $ou, $TopOU)
    {
        $url = $this->host . "/api/ChangeComputerOU";
        $postField = json_encode(array(
            "ComputerName" => $cn,
            "UpperOU" => $ou,
            "TopOU" => $TopOU
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // change user's OU
    public function changeUserOU($cn, $ou)
    {
        $url = $this->host . "/api/ChangeOU";
        $postField = json_encode(array(
            "Username" => $cn,
            "OU" => $ou
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // edit OU
    public function editOU($upperou, $ou, $description)
    {
        $url = $this->host . "/api/ChangeUpperOU";
        $postField = json_encode(array(
            "UpperOU" => $upperou,
            "OU" => $ou,
            "OUName" => $description
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // edit User OU
    public function editUserOU($upperou, $ou, $description)
    {
        $url = $this->host . "/api/ChangeUpperOU";
        $postField = json_encode(array(
            "UpperOU" => $upperou,
            "OU" => $ou,
            "OUName" => $description
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // edit Computer OU
    public function editComputerOU($upperou, $ou, $description)
    {
        //$url = $this->host . "/api/ChangeComputerOU";
        //$postField = json_encode(array(
        //    "UpperOU" => $upperou,
        //    "OU" => $ou,
        //    "OUName" => $description
        //));
        //$response = $this->sendHttpRequest($url, $postField);
        //return $response;
        return "0.尚未實作";
    }

    // insert OU
    public function insertOU($upperou, $ou, $description)
    {
        $url = $this->host . "/api/NewOU";
        $postField = json_encode(array(
            "UpperOU" => $upperou,
            "OU" => $ou,
            "OUName" => $description
        ));
        $response = $this->sendHttpRequest($url, $postField);
        return $response;
    }

    // send curl request with bearer token
    private function sendHttpRequest($url, $postField)
    {
        $httpHeader = array("Content-Type: application/json");
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
