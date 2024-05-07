<?php
/**
 * 耦合使用 Database 物件進行資料庫驗證 username 與 email 是否已存在於資料庫
 */
class UserValidator
{
    private $error;

    /**
     * 驗證是否已登入
     */
    public function isLogin()
    {
        if (isset($_SESSION['username'])) { 	//檢查session是否有值
            return true;
        }

        if (isset($_COOKIE['rememberme'])) {	//使用者選擇記住登入狀態
            $user = decryptUserCookie($_COOKIE['rememberme']);
            if ($user['isValid']) {	//若為有效的使用者，把使用者的個人資料放到session裡面
                $_SESSION['username'] = $user['username'];
                $_SESSION['displayname'] = $user['displayname'];
                $_SESSION['level'] = $user['level'];
                $userAction = new UserAction();
                $userAction->logger('rememberLogin', $_SERVER['REQUEST_URI']);
                return true;
            }
        }

        return false;
    }

    /**
     * 驗證是否為admin
     */
    public function isAdmin()
    {
        if (isset($_SESSION['level'])) { 	//檢查session是否有值
            if($_SESSION['level'] == 2) {
                return true;
            }
        }
        return false;
    }

    /**
     * 可取出錯誤訊息字串陣列
     */
    public function getErrorArray()
    {
        return $this->error;
    }

    /**
     * 驗證二次密碼輸入是否相符
     */
    public function isPasswordMatch($password, $passwrodConfirm)
    {
        if ($password != $passwrodConfirm) {
            $this->error[] = 'Passwords do not match.';
            return false;
        }
        return true;
    }

    /**
     * 驗證帳號密碼是否正確可登入
     */
    public function loginVerification($username, $password)
    {
        $result = Database::get()->execute('SELECT * FROM users WHERE SSOID  = :SSOID', array(':SSOID' => $username));
        if(isset($result[0]['SSOID']) and !empty($result[0]['SSOID'])) {
            $passwordObject = new Password();
            if($passwordObject->password_verify($password, $result[0]['Password'])) {
                return true;
            }
        }
        $this->error[] = 'Wrong username or password.';
        return false;
    }

    /**
     * 驗證帳號是否已存在於資料庫中
     */
    public function isUsernameDuplicate($username)
    {
        $result = Database::get()->execute('SELECT username FROM members WHERE username = :username', array(':username' => $username));
        if(isset($result[0]['username']) and !empty($result[0]['username'])) {
            $this->error[] = 'Username provided is already in use.';
            return false;
        }
        return true;
    }

    /**
     * 驗證此帳號 ID 跟 開通碼 hash 是否已存在於資料庫中
     */
    public function isReady2Active($id, $active)
    {
        $result = Database::get()->execute('SELECT username FROM members WHERE memberID = :memberID AND active = :active', array(':memberID' => $id, ':active' => $active));
        if(isset($result[0]['username']) and !empty($result[0]['username'])) {
            return true;
        } else {
            $this->error[] = 'Username provided is already in use.';
            return false;
        }
    }

    /**
     * 驗證信箱是否已存在於資料庫中
     */
    public function isEmailDuplicate($email)
    {
        $result = Database::get()->execute('SELECT email FROM members WHERE email = :email', array(':email' => $email));
        if(isset($result[0]['email']) and !empty($result[0]['email'])) {
            $this->error[] = 'Email provided is already in use.';
            return true;
        }
        return false;
    }

    /**
     * 驗證臉書帳號是否已存在於資料庫中
     */
    public function fbLoginVerification($email, $fbUserId)
    {
        $result = Database::get()->execute(
            'SELECT memberID FROM members WHERE email = :email AND fbUserId = :fbUserId',
            array(':email' => $email, ':fbUserId' => $fbUserId)
        );
        if(isset($result[0]['memberID']) and !empty($result[0]['memberID'])) {
            return true;
        }
        return false;
    }

    /**
     * 用臉書 ID 取得 memberID
     */
    public function getMemberIdByFb($fbUserId)
    {
        $result = Database::get()->execute(
            'SELECT memberID FROM members WHERE fbUserId = :fbUserId',
            array(':fbUserId' => $fbUserId)
        );
        if(isset($result[0]['memberID']) and !empty($result[0]['memberID'])) {
            return $result[0]['memberID'];
        }
        return false;
    }


}
