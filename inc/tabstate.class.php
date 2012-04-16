<?php
/**
 * User session data: current tab and previous tab states
 *
 * @author syanna
 */
class TabState {
    private $_state;
    private $_default_tab;
    private $_default_event;

    function __construct($default_tab, $default_event) {
        $this->_default_tab = $default_tab;
        $this->_default_event = $default_event;
        $this->_state = $this->fetch_state();
    }
    function clear() {
        $_SESSION = array();
        log&&msg(__METHOD__, 'User: cleared session data; session:', $_SESSION);
        $this->_state = $this->fetch_state();
        return $this;
    }
    function state(State $state = null) {
        // get
        if ($state === null) {
            return $this->_state;
        // set
        } else {
            $this->_state = $state;
            $this->save();
            return $this;
        }
    }
    function save() {
        $tab = $this->_state->tab;
        $_SESSION['active_tab'] = $tab;
        $tab_name = $this->id($tab);
        $_SESSION[$tab_name]['event'] = $this->_state->event;
        $_SESSION[$tab_name]['value'] = $this->_state->value;
        return $this;
    }
    /**
     * Tries to get the current state for a tab; reverts to a default if missing
     * @param string $tab   specific tab name
     * @return State | false
     */
    function fetch_state($tab = null) {
        if ($tab === null) {
            // if no tab provided then try to state the last active tab
            if (isset($_SESSION['active_tab']) && ! empty($_SESSION['active_tab'])) {
                $tab = $_SESSION['active_tab'];
            } else {
                $tab = $this->_default_tab;
            }
        }
        $tab_name = $this->id($tab);
        $event = isset_or($_SESSION[$tab_name]['event'], $this->_default_event);
        $value = isset_or($_SESSION[$tab_name]['value'], '');
        return new State($tab, $event, $value);
    }
    // used by class to create session id's
    function id($tab) {
        return '__' . $tab;
    }
}
?>
