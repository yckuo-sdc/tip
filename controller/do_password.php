<?php

if(!$userValidator->isLogin() || !isset($_POST['username'])) {
    header("Location: /logout");
    return;
}

$error = array();

// Sanitizes data and converts strings to UTF-8 (if available), according to the provided field whitelist
$whitelist = array("username", "current_password", "new_password", "confirm_password");
$_POST = $gump->sanitize($_POST, $whitelist);

// set validation rules
$validation_rules_array = array(
    'username'    => 'required',
    //'current_password'   => 'required|min_len,8',
    'new_password'   => 'required|min_len,8',
    'confirm_password'   => 'required|min_len,8',
);
$gump->validation_rules($validation_rules_array);

// set filter rules
$filter_rules_array = array(
    'username' => 'trim|sanitize_string',
    //'current_password' => 'trim',
    'new_password' => 'trim',
    'confirm_password' => 'trim',
);
$gump->filter_rules($filter_rules_array);

$validated_data = $gump->run($_POST);

if($validated_data === false) {
    $error = $gump->get_readable_errors(false);
} else {
    foreach($validated_data as $key => $val) {
        ${$key} = $val; // transfer to local parameters
    }

    $userValidator->loginVerification($username, $current_password);
    $userValidator->isPasswordMatch($new_password, $confirm_password);
    $error = $userValidator->getErrorArray();

    //if no errors have been created carry on
    if(empty($error)) {
        //hash the password
        $passwordObject = new Password();
        $hashedpassword = $passwordObject->password_hash($new_password, PASSWORD_BCRYPT);

        $table = 'users';
        $data_array = array(
            'Password' => $hashedpassword,
        );
        $key_column = "SSOID";
        $user = $username;
        Database::get()->update($table, $data_array, $key_column, $user);

        $db_error = $db->getErrorMessageArray();
        if(!empty($db_error)) {
            $error[] = "Changing Password Error Occur on Database.";
        }

        if(isset($error) and count($error) > 0) {
            foreach($error as $e) {
                $flash->error($e);
            }
        } else {
            $flash->success('Changing Password successful');
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
}

foreach($error as $e) {
    $flash->error($e);
}
header("Location: ".$_SERVER['HTTP_REFERER']);
