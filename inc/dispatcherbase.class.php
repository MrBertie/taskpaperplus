<?php

namespace tpp\control;


/**
 * Enum: Types of request response
 */
class eRespType {
    const INDEX   = 'index';
    const CONTENT = 'content';
    const EDIT    = 'edit';
    const ADDRESS = 'address';
    const DONE    = 'done';
    const ERROR   = 'error';
    const MISSING = 'missing';
}


abstract class DispatcherBase {
    
    
    /**
     * Dispatch to the correct function.
     * 
     * The first item in the array is always the function name to call, the rest the arguments.
     * This function is deliberately simple: it is assumed that most of the processing will
     * be done in an overridden 'route' function in the the dispatcher.
     */
    protected function route($args) {
        $existed = false;
        $func = array_shift($args);
            
        if (method_exists($this, $func)) {
            $this->before_route($func, $args);
            call_user_func_array(array($this, $func), $args);
            $existed = true;
        }
        return $existed;
    }
    
    
    // the initial page load function: must be implemented
    abstract function index();
    
    // HOOK: before anything happens; good for login check for example
    abstract protected function before();

    // HOOK: before routing a successful function match
    abstract protected function before_route(&$func, &$args);

    // HOOK: before sending the response to the browser
    abstract protected function before_html_response(Array &$response);

    // HOOK: before sending the response to the browser
    abstract protected function before_json_response(Array &$response);

    // HOOK: after sending the response to the browser
    abstract protected function after_response(Array &$response);
    
    
    /**
     * How to respond to a request.  Generally one of 3 ways: a full reload (index page), json data for an ajax request, or a successful action with no data response.
     *
     * @param string $type          Type of response to return (@see class eRespType); use the eRespType constants
     * @param mixed     $data       Content to be sent to browser, either json or straight html
     * @param string    $address    New URL for the page (::address type)
     * @param array     $errors     List of errors while preparing page/content; sent to browser as part of json_decode; if it failed 
     */
    function respond($type,
                     $data    = null, 
                     $address = null, 
                     $errors  = array()) {

        $response = array();
        // allow the response type to be overridden via reroute function
        $response['type']    = $type;
        $response['address'] = strval($address);
        $response['errors']  = $errors;
        if (is_array($data)) {
            $response = array_merge($response, $data);
        }
        if ($type == eRespType::INDEX) {
            $this->before_html_response($response);
            header('Content-type: text/html; charset=utf-8');
            echo $data;

        } else {
            // send json without caching
            $this->before_json_response($response);
            header('Content-type: application/json', true, 200);
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            echo json_encode($response);
        }

        $this->after_response($response, $address);
        // return true on success
        return ! ($type == eRespType::ERROR || $type == eRespType::MISSING);
    }
}