<?php

class UserAction
{
    public function logger($type, $msg)
    {
        $table = "logs";
        $data_array['type'] = $type;
        $data_array['ip'] = Ip::get();
        if (!empty($_SESSION['username'])) {
            $data_array['user'] = $_SESSION['username'] ;
        }
        $data_array['msg'] = $msg;
        $data_array['time'] = date('Y-m-d H:i:s');
        Database::get()->insert($table, $data_array);
    }

    public function getResetToken($email)
    {
        $data_array['resetComplete'] = 'No';
        $data_array['resetToken'] = md5(rand().time());
        $result = Database::get()->execute('SELECT memberID FROM members WHERE email = :email', array(':email' => $email));
        $memberID = $result[0]['memberID'];
        Database::get()->update('members', $data_array, "memberID", $memberID);
        $resetToken = $data_array['resetToken'];
        return $resetToken;
    }

    public function sendResetEmail($resetToken, $email)
    {
        $body = "<p>Someone requested that the password be reset.</p>
        <p>If this was a mistake, just ignore this email and nothing will happen.</p>
        <p>To reset your password, visit the following address: <a href='".Config::BASE_URL."reset/$resetToken'>".Config::BASE_URL."reset/$resetToken</a></p>";
        $mail = new Mail(Config::MAIL_USER_NAME, Config::MAIL_USER_PASSWROD);
        $mail->setFrom(Config::MAIL_FROM, Config::MAIL_FROM_NAME);
        $mail->addAddress($email);
        $mail->subject("Password Reset");
        $mail->body($body);
        $mail->send();
    }

    public function redir2login()
    {
        $msg = new \Plasticbrain\FlashMessages\FlashMessages();
        $msg->success("Please check your inbox for a reset link.");
        header('Location: '.Config::BASE_URL.'login');
        exit;
    }
}
