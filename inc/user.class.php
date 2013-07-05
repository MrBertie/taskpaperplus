<?php
namespace tpp\user;

//For security reasons, don't display any errors or warnings.
//error_reporting(0);

class User {

    private $_userfile = '';     // storage of login data
    private $_users = array();   // array of all valid users
    private $_error = array();

    
    function __construct($userfile) {
        $this->_userfile = $userfile;
        $this->_load_users();

        log&&msg('Loaded user authenication');
    }
    
    /**
     * THe main login function.
     * 
     * Call this to either login or show the login form
     * 
     * @return
     */
    function do_login() {
        
        // basic login check, ignore rest if logged in
        if ($this->logged_in()) return true;

        // first check for a ajax post request
        // with the login data
        if (isset($_POST['login'])) {

            $login_msg = '';
            $login_err = '';
            $tab = 'login';

            $logged_in = false;

            $post = $_POST;

            if (isset($post['login-form'])) {

                $username = $post['username'];
                $password = $post['password'];
                if ($this->login($username, $password)) {
                    $logged_in = true;
                } else {
                    $login_msg = \tpp\lang('login_failed_msg');
                }

            } elseif (isset($post['register-form'])) {

                $username = $post['username'];
                $email = $post['email'];
                $password1 = $post['password1'];
                $password2 = $post['password2'];
                if ($this->register($username, $password1, $password2, $email)) {
                    $login_msg = \tpp\lang('registration_msg');
                } else {
                    $login_msg = \tpp\lang('registration_failed_msg');
                    $tab = 'register';
                }
            }

            if ( ! $logged_in) {

                foreach ($this->errors() as $err) {
                    $login_err .= '<li>' . $err . '</li>' . "\n";
                }
                $response = array(
                    'success' => false,
                    'tab' => $tab,
                    'login_msg' => $login_msg,
                    'login_err' => $login_err,
                );

            } else {

                // the index page is refreshed via js
                $response = array(
                    'success' => true
                );
            }
            
            // all login info set via ajax post request
            // no caching
            header('Content-type: application/json', true, 200);
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            echo json_encode($response);

        } else {

            // show the login and register forms
            header('Content-type: text/html; charset=utf-8');
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
            header("Cache-Control: no-store, no-cache, must-revalidate"); 
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            include('tpl/login.tpl.php');
        }
    }

    
    function login($username, $password) {
        if ($this->_get_user($username, $password) !== false) {
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
        $valid = isset($_SESSION['user']) && $this->_get_user($_SESSION['user']) !== false;
        if ( ! $valid) {
            $this->_error[] = \tpp\lang('no_such_user_err');
        }
        return $valid;
    }
    
    
    function logged_in_as() {
        if ($this->logged_in()) {
            return $_SESSION['user'];
        } else {
            return '';
        }
    }

    
    function register($username, $password1, $password2, $email) {
        $created = $this->_create_user($username, $password1, $password2, $email);
        return $created;
    }
    
    
    function errors() {
        return $this->_error;
    }

    
    function change_password($username, $old_password, $new_password) {
        if ($this->_valid_password($new_password) && $this->_user_exists($username, $old_password)) {
            $this->_set_user($username, $new_password);
            return true;
        } else {
            return false;
        }
    }

    
    function change_email($username, $email) {
        if ($this->_get_user($username) !== false) {
            $this->_edit_user($username, null, $email);
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
            $this->_users = unserialize(file_get_contents($this->_userfile));
        } else {
            $this->_users = array();
            $this->_error[] = \tpp\lang('userfile_missing_err');
        }
        return $this;
    }
    
    
    private function _save_users() {
        file_put_contents($this->_userfile, serialize($this->_users));
        return $this;
    }
    
    
    private function _create_user($username, $password1, $password2, $email) {
        // reset errors, to remove any previous login failure
        $this->_error = array();
        
        if ($this->_get_user($username) !== false) {
            $this->_error[] = \tpp\lang('user_exists_err');
        }
        if ( ! $this->_valid_username($username)) {
            $this->_error[] = \tpp\lang('invalid_username_err');
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->_error[] = \tpp\lang('invalid_email_err');
        }
        if ( ! $this->_valid_password($password1)) {
            $this->_error[] = \tpp\lang('invalid_password_err');
        }
        if ($password1 !== $password2) {
            $this->_error[] = \tpp\lang('nonmatch_passwords_err');
        }
        
        if (empty($this->_error)) {
            $this->_users[$username] = array(
                                            'username' => $username,
                                            'password' => $this->_encrypted($password1),
                                            'email' => $email,
                                            'modified' => time(),
                                            );
            $this->_save_users();
            return true;
        } else {
            return false;
        }
    }
    
    
    private function _edit_user($username, $password = null, $email = null) {
        $user = $this->_get_user($username);
        if ($user !== false) {
            if ( ! is_null($password)) {
                if ($this->_valid_password($password)) {
                    $this->_users[$username]['password'] = $this->_encrypted($password);
                } else {
                    $this->_error[] = \tpp\lang('invalid_password_err');
                }
            }
            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $this->_users[$username]['email'] = $email;
            } else {
                $this->_error[] = \tpp\lang('invalid_email_err');
            }
            
            if (empty($this->_error)) {
                $this->_save_users();
                return true;
            } else {
                return false;
            }
        }
    }
    
    
    /**
     * Checks if a given user exists.
     *
     * If password is provided then this will be checked for also
     *
     * @return boolean | array  false on failure | array of user on success
     */
    private function _get_user($username, $password = null) {
        if (isset($this->_users[$username])) {
            $user = $this->_users[$username];
            $encrypt_password = $this->_encrypted($password);
            if (is_null($password) || $user['password'] === $encrypt_password) {
                return $user;
            }
        }
        return false;
    }
    
    
    private function _delete_user($username) {
        unset($this->_users[$username]);
        $this->_save_users();
        return $this;
    }
    
    
    private function _encrypted($password) {
        $password = sha1($password);
        return $password;
    }

    
    private function _valid_username($username) {
        $succ = (preg_match('/' . \tpp\config('username_pattern') . '/i', $username) == 1);
        return $succ;
    }
    
    
    private function _valid_password($password) {
        $succ = (preg_match('/' . \tpp\config('password_pattern') . '/i', $password) == 1);
        return $succ;
    }
}
?>