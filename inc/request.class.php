<?php
namespace tpp\control;


/**
 * This models a request from the browser.
 *
 * Obtains basic HTTP request and fills other useful parameters
 *      source = REQ_AJAX or REQ_INDEX
 *      verb   = HTTP verb used (GET or POST only)
 */
class Request {

    public $source = '';
    public $verb = '';
    
    private $_vars = array();

    
    function __construct() {

        // http verb used for this request
        $verb = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->verb = $verb;

        // request parameters, accessible via the __set/__get methods below
        $request = array();
        if ($verb == 'POST' && ! empty($_POST)) {
            $request = $_POST;
        } elseif ($verb == 'GET' && ! empty($_GET)) {
            $request = $_GET;
        }
        $this->_vars = $request;
        
        // source: page load/refresh or ajax
        $is_index = (empty($request));
        $is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if ($is_index) {
            $source = REQ_INDEX;
        } elseif ($is_ajax) {
            $source = REQ_AJAX;
        } else {
            $source = REQ_INDEX;
        }
        $this->source = $source;
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
        $is_set = isset($this->_vars[$name]);
        return $is_set;
    }
    
    
    function __unset($name) {
        if (isset($this->_vars[$name])) {
            unset($this->_vars[$name]);
        }
    }
}