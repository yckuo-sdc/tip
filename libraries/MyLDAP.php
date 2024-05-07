<?php

class MyLDAP
{
    private $ldapconn ;

    public function __construct()
    {
        $ldapconn = ldap_connect(LDAP::HOST) or die("Could not connect to LDAP server.");
        $set = ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $ldap_bd = ldap_bind($ldapconn, LDAP::USERNAME . "@" . LDAP::DOMAIN, LDAP::PASSWORD);
        $this->ldapconn = $ldapconn;
    }

    public function __destruct()
    {
        ldap_unbind($this->ldapconn);
    }

    /**
     * Get all result entries.
     *
     * @param array $data_array.
     * @return array|false result array.
     */
    public function getData($data_array = array())
    {
        if(empty($data_array)) {
            return false;
        }
        $base = $data_array['base'];
        $filter = $data_array['filter'];
        $attributes = empty($data_array['attributes']) ? array() : $data_array['attributes'];
        $sizelimit = empty($data_array['sizelimit']) ? -1 : $data_array['sizelimit'];

        $result = @ldap_search($this->ldapconn, $base, $filter, $attributes, $attributes_only = 0, $sizelimit);
        if (!$result) {
            die("Error in search query: " . ldap_error($this->ldapconn));
        }
        $entries = ldap_get_entries($this->ldapconn, $result);

        $data = array();
        for($i = 0; $i < $entries["count"]; $i++) {
            for($j = 0; $j < $entries[$i]["count"]; $j++) {
                if(empty($entries[$i][$entries[$i][$j]][0])) {
                    continue;
                }
                $data[$i][$entries[$i][$j]] = $entries[$i][$entries[$i][$j]][0];
            }
        }

        return $data;
    }

    public function getList($data_array = array())
    {
        if(empty($data_array)) {
            return false;
        }
        $base = $data_array['base'];
        $filter = $data_array['filter'];
        $attributes = empty($data_array['attributes']) ? array() : $data_array['attributes'];

        $result = ldap_list($this->ldapconn, $base, $filter, $attributes);
        $entries = ldap_get_entries($this->ldapconn, $result);

        $data = array();
        for($i = 0; $i < $entries["count"]; $i++) {
            for($j = 0; $j < $entries[$i]["count"]; $j++) {
                if(empty($entries[$i][$entries[$i][$j]][0])) {
                    continue;
                }
                $data[$i][$entries[$i][$j]] = $entries[$i][$entries[$i][$j]][0];
            }
        }

        return $data;
    }

    public function createComputerTree($base, $ou, $ou_description)
    {
        $html = "";
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=*)";
        $data_array['attributes'] = array("distinguishedname");
        $lists = $this->getList($data_array);

        if (empty($lists)) {
            $html .= "<div class='item'>";
            $html .= "<i class='folder icon'></i>";
            $html .= "<div class='content'>";
            $html .= "<div class='header'>".$ou."(".$ou_description.")</div>";
            $html .= "</div>";
            $html .= "</div>";
            return $html;
        }

        $html .= "<div class='item hide'>";
        $html .= "<i class='plus square outline icon'></i>";
        $html .= "<i class='folder icon'></i>";
        $html .= "<div class='content'>";
        $html .= "<div class='header'>".$ou."(".$ou_description.")</div>";

        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=computer)";
        $data_array['attributes'] = array("cn", "useraccountcontrol");
        $lists = $this->getList($data_array);

        if(!empty($lists)) {
            $html .= "<div class='list'>";
            foreach($lists as $list) {
                if(isDisabled($list['useraccountcontrol'])) {
                    $uac = false;
                    $uac_status = "__已停用";
                    $computer_icon = "<i class='desktop icon'></i>";
                } else {
                    $uac = true;
                    $uac_status = "";
                    $computer_icon = "<i class='blue desktop icon'></i>";
                }
                $html .= "<div class='computer item' cn='".$list['cn']."' uac='".$uac."'>";
                $html .= $computer_icon . "&nbsp;";
                $html .= $list['cn'];
                $html .= $uac_status;
                $html .= "</div>";
            }
            $html .= "</div>";
        }

        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=organizationalUnit)";
        $data_array['attributes'] = array("ou", "distinguishedname", "description");
        $lists = $this->getList($data_array);

        if(!empty($lists)) {
            $html .= "<div class='list'>";
            foreach($lists as $list) {
                $sub_base = $list["distinguishedname"];
                $sub_ou = $list["ou"];
                $sub_ou_description = empty($list["description"]) ? "" : $list["description"];
                $html .= $this->createComputerTree($sub_base, $sub_ou, $sub_ou_description);
            }
            $html .= "</div>";
        }

        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    public function createSingleLevelComputerTree($base, $ou, $description)
    {
        $html = "";
        $computer_show_records = 3;

        // computer
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=computer)";
        $data_array['attributes'] = array("cn", "useraccountcontrol", "description");
        $computer_list = $this->getList($data_array);

        if (!empty($computer_list)) {
            $computer_count = count($computer_list);
            $html .= "<div class='list'>";
            $html .= "<div class='item'><i class='icon caret right'></i>共 " . $computer_count . " 筆資料 !</div>";
            foreach($computer_list as $computer_index => $computer) {
                if(isDisabled($computer['useraccountcontrol'])) {
                    $uac = false;
                    $uac_status = "__已停用";
                    $computer_icon = "<i class='desktop icon'></i>";
                } else {
                    $uac = true;
                    $uac_status = "";
                    $computer_icon = "<i class='blue desktop icon'></i>";
                }
                $computer_description = empty($computer['description']) ? "" : "(" . $computer['description'] . ")";

                if($computer_index < $computer_show_records) {
                    $html .= "<div class='computer item' cn='" . $computer['cn'] . "' uac='" . $uac . "'>";
                } elseif($computer_index == $computer_show_records) {
                    $html .= "<i class='ellipsis horizontal icon'></i>";
                    $html .= "<div class='foldable computer item' cn='" . $computer['cn'] . "' uac='" . $uac . "'>";
                } else {
                    $html .= "<div class='foldable computer item' cn='" . $computer['cn'] . "' uac='" . $uac . "'>";
                }
                $html .= $computer_icon . "&nbsp;";
                $html .= $computer['cn'];
                $html .= $computer_description;
                $html .= $uac_status;
                $html .= "</div>";
            }
            $html .= "</div>";
        }

        // ou
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=organizationalUnit)";
        $data_array['attributes'] = array("ou", "distinguishedname", "description");
        $ou_list = $this->getList($data_array);

        if (!empty($ou_list)) {
            $html .= "<div class='list'>";
            foreach($ou_list as $entry) {
                $base = $entry['distinguishedname'];
                $ou = $entry['ou'];
                $description = empty($entry["description"]) ? "" : $entry["description"];
                $text_description = empty($entry["description"]) ? "" : "(" . $entry["description"] . ")";

                $data_array = array();
                $data_array['base'] = $base;
                $data_array['filter'] = "(objectCategory=*)";
                $data_array['attributes'] = array("distinguishedname");
                $list = $this->getList($data_array);

                if (empty($list)) {
                    $html .= "<div class='item'>";
                    $html .= "<i class='folder icon'></i>";
                    $html .= "<div class='content'>";
                    $html .= "<div class='ou header' ou='computer " . $ou . "'>" . $ou . $text_description . "</div>";
                    $html .= "</div>";
                    $html .= "</div>";
                } else {
                    $html .= "<div class='item'>";
                    $html .= "<i class='plus square outline icon' base='" . $base . "' ou='" . $ou . "' description='" . $description . "'></i>";
                    $html .= "<i class='folder icon'></i>";
                    $html .= "<div class='content'>";
                    $html .= "<div class='ou header' ou='computer " . $ou . "'>" . $ou . $text_description . "</div>";
                    $html .= "</div>";
                    $html .= "</div>";
                }
            }
            $html .= "</div>";
        }

        return $html;
    }

    public function createSingleLevelUserTree($base, $ou, $description)
    {
        $html = "";
        $user_show_records = 3;

        // user
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=person)";
        $data_array['attributes'] = array("cn", "useraccountcontrol", "displayname");
        $user_list = $this->getList($data_array);

        if (!empty($user_list)) {
            $user_count = count($user_list);
            $html .= "<div class='list'>";
            $html .= "<div class='item'><i class='icon caret right'></i>共 " . $user_count . " 筆資料 !</div>";
            foreach($user_list as $user_index => $user) {
                if (isDisabled($user['useraccountcontrol'])) {
                    $uac = false;
                    $uac_status = "__已停用";
                    $user_icon = "<i class='user icon'></i>";
                } else {
                    $uac = true;
                    $uac_status = "";
                    $user_icon = "<i class='blue user icon'></i>";
                }
                $displayname = empty($user['displayname']) ? "" : "(" . $user['displayname'] . ")";

                if($user_index < $user_show_records) {
                    $html .= "<div class='user item' cn='" . $user['cn'] . "' uac='" . $uac . "'>";
                } elseif($user_index == $user_show_records) {
                    $html .= "<i class='ellipsis horizontal icon'></i>";
                    $html .= "<div class='foldable user item' cn='" . $user['cn'] . "' uac='" . $uac . "'>";
                } else {
                    $html .= "<div class='foldable user item' cn='" . $user['cn'] . "' uac='" . $uac . "'>";
                }
                $html .= $user_icon . "&nbsp;";
                $html .= $user['cn'];
                $html .= $displayname;
                $html .= $uac_status;
                $html .= "</div>";
            }
            $html .= "</div>";
        }

        // ou
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=organizationalUnit)";
        $data_array['attributes'] = array("ou", "distinguishedname", "description");
        $ou_list = $this->getList($data_array);

        if (!empty($ou_list)) {
            $html .= "<div class='list'>";
            foreach($ou_list as $entry) {
                $base = $entry['distinguishedname'];
                $ou = $entry['ou'];
                $description = empty($entry["description"]) ? "" : $entry["description"];
                $text_description = empty($entry["description"]) ? "" : "(" . $entry["description"] . ")";

                $data_array = array();
                $data_array['base'] = $base;
                $data_array['filter'] = "(objectCategory=*)";
                $data_array['attributes'] = array("distinguishedname");
                $list = $this->getList($data_array);

                if (empty($list)) {
                    $html .= "<div class='item'>";
                    $html .= "<i class='folder icon'></i>";
                    $html .= "<div class='content'>";
                    $html .= "<div class='ou header' ou='user " . $ou . "'>" . $ou . $text_description . "</div>";
                    $html .= "</div>";
                    $html .= "</div>";
                } else {
                    $html .= "<div class='item'>";
                    $html .= "<i class='plus square outline icon' base='" . $base . "' ou='" . $ou . "' description='" . $description . "'></i>";
                    $html .= "<i class='folder icon'></i>";
                    $html .= "<div class='content'>";
                    $html .= "<div class='ou header' ou='user " . $ou . "'>" . $ou . $text_description . "</div>";
                    $html .= "</div>";
                    $html .= "</div>";
                }
            }
            $html .= "</div>";
        }

        return $html;
    }

    public function getAllComputersByRecursion($base, $ou, $description)
    {

        $computer_array = array();
        // computer
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=computer)";
        $data_array['attributes'] =  array("cn", "dnshostname", "operatingsystem", "operatingsystemversion", "distinguishedname", "lastlogon", "pwdlastset", "useraccountcontrol");
        $computer_list = $this->getList($data_array);

        if (!empty($computer_list)) {
            $computer_array = array_merge($computer_array, $computer_list);
        }

        // ou
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=organizationalUnit)";
        $data_array['attributes'] = array("ou", "distinguishedname", "description");
        $ou_list = $this->getList($data_array);

        if (!empty($ou_list)) {
            foreach($ou_list as $entry) {
                $sub_base = $entry['distinguishedname'];
                $sub_ou = $entry['ou'];
                $sub_description = empty($entry["description"]) ? "" : $entry["description"];
                $sub_computer_array = $this->getAllComputersByRecursion($sub_base, $sub_ou, $sub_description);

                if (empty($sub_computer_array)) {
                    continue;
                }
                $computer_array = array_merge($computer_array, $sub_computer_array);
            }
        }

        return $computer_array;

    }

    public function getAllUsersByRecursion($base, $ou, $description)
    {

        $user_array = array();

        // user
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=person)";
        //$data_array['attributes'] = array("cn", "title", "physicaldeliveryofficename", "telephonenumber", "distinguishedname", "displayname", "useraccountcontrol", "lastlogon", "pwdlastset", "mail");
        $data_array['attributes'] = array("cn", "title", "physicaldeliveryofficename", "telephonenumber", "distinguishedname", "displayname", "useraccountcontrol", "lastlogon", "pwdlastset", "mail", "description");
        $user_list = $this->getList($data_array);

        if (!empty($user_list)) {
            $user_array = array_merge($user_array, $user_list);
        }

        // ou
        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=organizationalUnit)";
        $data_array['attributes'] = array("ou", "distinguishedname", "description");
        $ou_list = $this->getList($data_array);

        if (!empty($ou_list)) {
            foreach($ou_list as $entry) {
                $sub_base = $entry['distinguishedname'];
                $sub_ou = $entry['ou'];
                $sub_description = empty($entry["description"]) ? "" : $entry["description"];
                $sub_user_array = $this->getAllUsersByRecursion($sub_base, $sub_ou, $sub_description);

                if(empty($sub_user_array)) {
                    continue;
                }
                $user_array = array_merge($user_array, $sub_user_array);
            }
        }

        return $user_array;

    }

    public function getAllOUsByRecursion($base, $ou, $description, $parent_ou, $level)
    {

        $ou_array = array(
            0 => array(
                'ou' => $ou,
                'description' => $description,
                'parent_ou' => $parent_ou,
                'level' => $level,
            )
        );

        //var_dump($ou_array);

        $data_array = array();
        $data_array['base'] = $base;
        $data_array['filter'] = "(objectCategory=organizationalUnit)";
        $data_array['attributes'] = array("ou", "distinguishedname", "description");
        $ou_list = $this->getList($data_array);

        if (!empty($ou_list)) {
            foreach($ou_list as $entry) {
                $sub_base = $entry['distinguishedname'];
                $sub_ou = $entry['ou'];
                $sub_description = empty($entry["description"]) ? "" : $entry["description"];
                $sub_level = $level + 1;
                $sub_ou_array = $this->getAllOUsByRecursion($sub_base, $sub_ou, $sub_description, $ou, $sub_level);

                if (empty($sub_ou_array)) {
                    continue;
                }
                $ou_array = array_merge($ou_array, $sub_ou_array);
                //array_push($ou_array, $sub_ou_array);
            }
        }

        return $ou_array;

    }

    public function getSingleOUDescription($base, $distinguishedname)
    {
        $description = "";
        $sections = explode(",", $distinguishedname);
        for($i = 0; $i < count($sections); $i++) {
            if(substr_compare($sections[$i], "OU", 0, 2) == 0) {
                $data_array = array();
                $data_array['base'] = $base;
                $data_array['filter'] = "(" . $sections[$i] . ")";
                $data_array['attributes'] = array("description");
                $data = $this->getData($data_array);
                $description .= empty($data[0]['description']) ? "" : $data[0]['description'];
                break;
            }
        }
        return $description;
    }

    public function getAllOUDescription($base, $distinguishedname)
    {
        $description = "";
        $sections = explode(",", $distinguishedname);
        for($i = 0; $i < count($sections); $i++) {
            if(substr_compare($sections[$i], "OU", 0, 2) == 0) {
                $data_array = array();
                $data_array['base'] = $base;
                $data_array['filter'] = "(" . $sections[$i] . ")";
                $data_array['attributes'] = array("description");
                $data = $this->getData($data_array);
                $description .= empty($data[0]['description']) ? "" : $data[0]['description'];
                $description .= "/";
            }
        }
        $description = substr($description, 0, -1);
        return $description;
    }

    public function verifyUser($data_array = array(), &$user_attributes)
    {
        if(empty($data_array)) {
            return false;
        }
        $base = $data_array['base'];
        $username = $data_array['username'];
        $password = $data_array['password'];
        $ldaprdn = $username . "@" . LDAP::DOMAIN;

        try {
            $ldapbind = @ldap_bind($this->ldapconn, $ldaprdn, $password);
            if ($ldapbind) {
                $data_array = array();
                $data_array['base'] = $base;
                $data_array['filter'] = "(cn=" . $username . ")";
                $data_array['attributes'] = array("cn", "displayname");
                $data = $this->getData($data_array)[0];
                $user_attributes = array('displayname' => $data['displayname']);

                return true;
            }
        } catch(Exception $e) {

        }
        echo "<div class='ui error message'>" . ldap_error($this->ldapconn) . "</div>";

        return false;
    }


    public function replaceUserAttributes($user_dn, $user_attributes)
    {
        $result = ldap_mod_replace($this->ldapconn, $user_dn, $user_attributes);
        if ($result) {
            echo "User modified!" . PHP_EOL;
        } else {
            echo "There was a problem!" . PHP_EOL;
        }
    }

    public function deleteUserAttributes($user_dn, $user_attributes)
    {
        $result = ldap_mod_del($this->ldapconn, $user_dn, $user_attributes);
        if ($result) {
            echo "User modified!" . PHP_EOL;
        } else {
            echo "There was a problem!" . PHP_EOL;
        }
    }

}

/** Usage

// init
$ldap = new MyLDAP();

// get all OUs on LDAP_SCOPE_SUBTREE
$data_array = array();
$data_array['base'] =  "ou=TainanLocalUser, dc=tainan, dc=gov, dc=tw";
$data_array['filter'] = "(objectClass=organizationalUnit)";
$data_array['attributes'] = array("name", "description");
$OUs = $ldap->getData($data_array);

// get 1-level users on LDAP_SCOPE_ONELEVEL
$data_array = array();
$data_array['base'] =  "ou=TainanLocalUser, dc=tainan, dc=gov, dc=tw";
$data_array['filter'] = "(objectCategory=person)";
$data_array['attributes'] = array("cn", "useraccountcontrol", "displayname");
$user_list = $this->getList($data_array);

// create UserTree on search base
$base = "ou=TainanLocalUser, dc=tainan, dc=gov, dc=tw";
$ou = "TainanLocalUser";
$description = "永華及民治使用者AD帳號";
echo $ldap->createSingleLevelUserTree($base, $ou, $description);

// replace user attributes
$user_attributes["description"] = "tainan user";
$ld->replaceUserAttributes($user_dn, $user_attributes);

// delete user attributes
$user_attributes["description"] = array();
$ld->deleteUserAttributes($user_dn, $user_attributes);

**/
