<?php

/**
 * Models a reproducible page state (i.e. that could be requested via URL)
 */
class State {
    public $tab;
    public $event;
    public $value;

    function __construct($tab, $event = '', $value = '') {
        $this->tab = $tab;
        $this->event = $event;
        $this->value = $value;
    }
    function address() {
        $tab = $this->tab;
        $event = (!empty($tab)) ? "/" . $this->event : '';
        $event .= (!empty($tab)) ? "/" . $this->value : '';
        return $tab . $event;
    }
}

/**
 * This models a request (could be index, ajax, url or invalid)
 */
class Request extends State {
    public $type = REQ_INDEX;
    public $draft;
    
    function __construct($type = REQ_INDEX, $tab = '', $event = '', $value = '', $draft = '') {
        parent::__construct($tab, $event, $value);
        $this->type = $type;
        $this->draft = $draft;
    }
    function to_state() {
        return new State($this->tab, $this->event, $this->value);
    }
}
?>
