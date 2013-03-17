<?php
namespace tpp\user;
use tpp\storage\Files;


class StatePersist {

    protected static $_active = '';
    protected static $_states = array();
    protected static $_files;


    /**
     * Transfer all tab state data back to the Session. You can also update a specific state at the same time.
     *
     * @return chainable
     */
    function save(State $state = null) {
        if ($state !== null) {
            self::$_active = $state->tab;
            self::$_states[$state->tab] = $state;
        }
        $_SESSION['active_state'] = self::$_active;
        $_SESSION['states'] = serialize(self::$_states);
        return $this;
    }

    /**
     * Make sure that all tab references are still valid, a tab file could have been deleted.
     *
     * @return chainable
     */
    function refresh() {
        $names = array_flip(self::$_files);
        self::$_states = array_intersect_key(self::$_states, $names);
        self::save();
        return $this;
    }
}



class States extends StatePersist implements \IteratorAggregate {

    protected $_default_tab;


    /**
     * Set up and load all available Tab States.
     *
     * @param Files $files
     */
    function __construct(Files $files) {
        self::$_files = $files;
        $this->_default_tab = $files->first()->name;
        $this->_load();
    }


    /**
     * Either return a specific Tab's state or the active one; used if State object is called directly.
     *
     * @param string $id A State id (Tab name)
     * @return State
     */
    function __invoke($id = null) {
        if (is_null($id)) {
            return $this->active();
        } else {
            return $this->item($id);
        }
    }


    /**
     * Get the active tab state.
     *
     * @return State
     */
    function active() {
        return $this->item(self::$_active);
    }


    /**
     * Get a specific or new Tab State.
     *
     * If id does not exist then create it.
     *
     * @param string $id   A state id (same as Tab name)
     * @return State
     */
    function item($id) {
        if ( ! array_key_exists($id, self::$_states)) {
            self::$_states[$id] = new State($id);
        }
        return self::$_states[$id];
    }


    /**
     * Clear all state data and reload defaults.
     *
     * @return \tpp\user\States
     */
    function clear() {
        $_SESSION['states'] = self::$_states = array();
        $_SESSION['active_state'] = self::$_active = '';
        $this->_load();
        return $this;
    }


    function getIterator() {
        return new \ArrayIterator(self::$_states);
    }



    /**
     * Tries to get the state of current active tab; reverts to default settings if missing.
     *
     * @return object   State
     */
    private function _load() {

        // set some defaults if session data does not exist yet...
        if (empty($_SESSION['states']) || ! isset($_SESSION['states'])) {
            $states[$this->_default_tab] = new State($this->_default_tab);
            $_SESSION['states'] = serialize($states);
            $_SESSION['active_state'] = $this->_default_tab;
        }

        self::$_states = unserialize($_SESSION['states']);
        self::$_active = $_SESSION['active_state'];
    }
}



/**
 * Models a reproducible page state (i.e. that could be requested via URL).
 * Call save() to persist current values back to session.
 */
class State extends StatePersist {

    public $tab = '';
    public $event = '';
    public $value = '';
    public $draft = null;

    function __construct($tab, $event = DEFAULT_EVENT, $value = DEFAULT_VALUE) {
        $this->tab = $tab;
        $this->event = $event;
        $this->value = $value;
    }

    function activate() {
        return parent::save($this);
    }

    function is_active() {
        return (self::$_active == $this->tab);
    }

    function to_address() {
        $tab = $this->tab;
        $event = ( ! empty($tab)) ? "/" . $this->event : '';
        $value = ( ! empty($tab)) ? "/" . $this->value : '';
        return $tab . $event . $value;
    }
}