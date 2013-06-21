<?php
namespace tpp\user;

//For security reasons, don't display any errors or warnings.
//error_reporting(0);

class User {

    private $_userfile = '';     // storage of login data
    private $_users = array();   // array of all valid users
    private $_encrypt = true;    // set to true to use sha1 encryption for the password

    
    function __construct($userfile, $encrypt = true) {
        $this->_userfile = $userfile;
        $this->_encrypt = $encrypt;
        $this->_load_users();

        log&&msg('Loaded user authenication');
    }

    
    function login($username, $password) {
        if ($this->_get_user($username, $this->_encrypted($password))) {
            $_SESSION['user'] = $username;
            return true;
        } else {
            $this->logout();
            return false;
        }
    }

    
    function logout(){
        session_destroy();
    }

    
    // is user logged in?
    function logged_in() {
        //return isset($_SESSION['user']) && $this->_get_user($_SESSION['user']) !== false;
    
        return true;
    }

    
    function new_user($username, $password, $nickname) {
        if ($this->_valid_username($username) && $this->_valid_password($password)) {
            $this->_set_user($username, $this->_encrypted($password), $nickname);
            return true;
        } else {
            return false;
        }
    }

    
    function change_password($username, $old_password, $new_password) {
        if ($this->_valid_password($new_password) && $this->_user_exists($username, $this->_encrypted($old_password))) {
            $this->_set_user($username, $this->_encrypted($new_password));
            return true;
        } else {
            return false;
        }
    }

    
    function change_name($username, $nickname) {
        if ($this->_user_exists($username)) {
            $this->_set_user($username, null, $nickname);
            return true;
        } else {
            return false;
        }
    }

    
    //create random password with 8 alphanumerical characters
    function create_password() {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }


    /**
    * functions to load and save user data storage
    */
    private function _load_users() {
        if (file_exists($this->_userfile)) {
            $this->_users = unserialize($this->_userfile);
        } else {
            $this->_users = array();
        }
        return $this;
    }
    
    
    private function _save_users() {
        file_put_contents($this->_userfile, serialize($this->_users));
        return $this;
    }
    
    
    private function _set_user($username, $password = null, $nickname = null) {
        $user = $this->_get_user($username);
        if ($user !== false) {
            if (is_null($password)) $password = $user['password'];
            if (is_null($nickname)) $nickname = $user['nickname'];
        }
        $this->_users[$username] = array('username' => $username,
                                        'password' => $this->_encrypted($password),
                                        'nickname' => $nickname,
                                        'modified' => time());
        $this->_save_users();
        return $this;
    }
    
    
    /**
     * Checks if a given user exists.
     *
     * If password is provided then this will be checked for also
     *
     * @return boolean  true on success
     */
    private function _get_user($username, $password = null) {
        if (isset($this->_users[$username])) {
            $user = $this->_users[$username];
            if (is_null($password) || $user['password'] == $this->_encrypted($password)) {
                return $user;
            }
        }
        return false;
    }
    
    
    private function _del_user($username) {
        unset($this->_users[$username]);
        $this->_save_users();
        return $this;
    }
    
    
    private function _encrypted($password) {
        if ($this->_encrypt) {
            $password = sha1($password);
        }
        return $password;
    }

    
    private function _valid_username($username) {
        return preg_match('/^[a-z\d_]{6,24}$i/', $username);
    }
    
    
    // TODO: this is a bit weak for now, to be improved one day
    private function valid_password($password) {
        return strlen($password) >= 4;
    }
}
?>