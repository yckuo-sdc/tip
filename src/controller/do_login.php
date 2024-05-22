<?php

use PHPMailer\PHPMailer\POP3;

if(!isset($_POST['submit'])) {
    header("Location: /logout");
    exit();
}

$error = array();

// Sanitizes data and converts strings to UTF-8 (if available), according to the provided field whitelist
$whitelist = array("authentication", "username", "password");
$_POST = $gump->sanitize($_POST, $whitelist);

// set validation rules
$validation_rules_array = array(
    'username'    => 'required|alpha_numeric_dash',
    'password'   => 'required|min_len,8'
);
$gump->validation_rules($validation_rules_array);

// set filter rules
$filter_rules_array = array(
    'username' => 'trim|sanitize_string',
    'password' => 'trim',
);
$gump->filter_rules($filter_rules_array);

$validated_data = $gump->run($_POST);

if($validated_data === false) {
    $error = $gump->get_readable_errors(false);
} else {
    foreach($validated_data as $key => $val) {
        ${$key} = $val; // transfer to local parameters
    }

    $table = "users";
    $condition = "SSOID = :SSOID";
    $user = $db->query($table, $condition, $order_by = 1, $fields = "*", $limit = "", [':SSOID' => $username]);

    $expire_time = 3600 * 24 * 30; //3600sec * 24hour * 30day

    switch($authentication) {
        case "ad":
            $ldap = new MyLDAP();
            //Bind Smart Developement Center OU
            $data_array = array();
            $data_array['base'] = "ou=395000300A, ou=395002900-, ou=395000000A, ou=TainanLocalUser, dc=tainan, dc=gov, dc=tw";
            $data_array['username'] = $username;
            $data_array['password'] = $password;
            $result = $ldap->verifyUser($data_array, $user_attributes);

            if($result && !empty($user[0]['SSOID'])) {

                session_regenerate_id(); //Prevent Session Fixation with changing session id

                $displayname = empty($user_attributes) ? $user[0]['DisplayName'] : $user_attributes['displayname'];
                $level = $user[0]['Level'];

                $_SESSION['username'] = $username;
                $_SESSION['displayname'] = $displayname;
                $_SESSION['level'] = $level;

                $userAction->logger('login', $_SERVER['REQUEST_URI']);

                if(isset($remember) && !empty($remember)) {
                    $cookie = generateUserCookie($username, $displayname, $level);
                    setcookie('rememberme', $cookie, time() + $expire_time, '/');
                } else {
                    setcookie('rememberme', "", time() - $expire_time, '/');
                }
                header("Location: /");
                exit();
            }
            break;
        case "local":
            //$pop = POP3::popBeforeSmtp('pop3.tainan.gov.tw', 110, 30, $username, $password, 1);
            $local = $userValidator->loginVerification($username, $password);
            if($local && isset($user[0]['SSOID']) && !empty($user[0]['SSOID'])) {
                session_regenerate_id(); //Prevent Session Fixation with changing session id

                $displayname = $user[0]['DisplayName'];
                $level = $user[0]['Level'];

                $_SESSION['username'] = $username;
                $_SESSION['displayname'] = $displayname;
                $_SESSION['level'] = $level;

                $userAction->logger('login', $_SERVER['REQUEST_URI']);

                if (!empty($remember)) {
                    $cookie = generateUserCookie($username, $displayname, $level);
                    setcookie('rememberme', $cookie, time() + $expire_time, '/');
                } else {
                    setcookie('rememberme', "", time() - $expire_time, '/');
                }
                header("Location: /");
                exit();
            }
            break;
    }

    $error[] = "invalid username or password";

}

if(!empty($error)) {
    foreach($error as $e) {
        $flash->error($e);
    }
}

header("Location: ".$_SERVER['HTTP_REFERER']);
