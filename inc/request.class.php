<?php
namespace tpp\control;
use tpp\user\State as State;


/**
 * This models a request from the browser.
 *
 * Obtains basic HTTP request and fills other useful parameters
 * E.g. source = REQ_AJAX or REQ_INDEX
 *      verb = HTTP verb used (GET or POST currently)
 */
class Request {

    public $source = '';
    public $verb = '';
    private $_vars = array();

    /**
     * @param array $defaults   Any default request parameters that must be present
     */
    function __construct(Array $defaults = array()) {

        $verb = strtoupper($_SERVER['REQUEST_METHOD']);

        $ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $req = array();
        if ($verb == 'POST' && ! empty($_POST)) {
            $req = $_POST;
        } elseif ($verb == 'GET' && ! empty($_GET)) {
            $req = $_GET;
        }

        $refresh = (empty($req));

        if ($refresh) {
            $source = REQ_INDEX;
        } elseif ($ajax) {
            $source = REQ_AJAX;
        } else {
            $source = REQ_INDEX;
        }

        // fill in any missing parameters/keys
        $req = array_merge($defaults, $req);

        $this->source = $source;
        $this->verb = $verb;
        $this->_vars = $req;
    }

    function __get($name) {
        if( isset($this->_vars[$name])) {
            return $this->_vars[$name];
        }
        return null;
    }

    function __set($name, $value) {
        $this->_vars[$name] = $value;
    }

    function __isset($name) {
        return ! empty($this->_vars[$name]);
    }

    function to_state($set_default = false) {
        if ($set_default) {
            return new State($this->tab);
        } else {
            return new State($this->tab, $this->event, $this->value);
        }
        return false;
    }
}
?>
