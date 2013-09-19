<?php
namespace tpp\user;
use tpp\storage\Files;


class StatePersist {

    protected static $_active = '';
    protected static $_states = array();
    protected static $_files;


    /**
     * Persist tab state data back to the session. 
     * 
     * You can also update a specific state at the same time (used by the State class).
     *
     * @return chainable
     */
    function persist(State $state = null) {
        if ($state !== null) {
            self::$_states[$state->address->tab] = $state;
        }
        $_SESSION['active_state'] = self::$_active;
        $_SESSION['states']       = serialize(self::$_states);
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
        self::persist();
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
    function __construct($files) {
        self::$_files = $files;
        $this->_default_tab = $files->first()->name;
        $this->_load();
    }


    /**
     * Either return a specific Tab's state or the active one
     * 
     * Same as get(), used if State object is called directly.
     *
     * @param string $id A State id (Tab name)
     * @return State
     */
    function __invoke($id = null) {
        return $this->get($id);
    }


    /**
     * Get the state of a Tab: i.e. name, action, value, [draft]
     * 
     * Can be used in 3 ways:
     *  1. get()    gets the curent/active tab
     *              if then active tab does not exist, then get the first (user) tab
     *  2. get(id)  gets a specific id
     *  3. get(id?) if ID is unknown, then it 'creates' a new tab state, and gets that
     *
     * @param string $id   A state id (same as Tab name); leave blank to get active tab
     * @return State
     */
    function get($id = null) {
        if (is_null($id)) {
            $id = self::$_active;
        }
        if (array_key_exists($id, self::$_states)) {
            return self::$_states[$id];
        } else {
            return $this->create(new Address($id));
        }
    }


    /**
     * Sets the active tab. Similar to get() however resets the active tab to this address
     * 
     * @param Address | string $address   The full hash address or just the tab
     * @return State
     */
    function set($address) {
        if (is_string($address)) {
            $id    = $address;
            $state = $this->get($id);
        } elseif ($address instanceof Address) {
            $id             = $address->tab;
            $state          = $this->get($id);
            $state->address = $address;
        } else {
            return false;
        }
        self::$_active = $id;
        return $state;
    }
    
    
    /**
     * Set a new tab state ONLY if this state does not exist.
     * 
     * @return State|false  False if state already exists
     */
    function create(Address $address) {
        $id = $address->tab;
        if ( ! array_key_exists($id, self::$_states)) {
            $state = &self::$_states[$id];
            $state = new State($address);
            $state->save();
            return $state;
        }
        return false;            
    }

    /**
     * Clear all state data and reload defaults.
     *
     * @return \tpp\user\States
     */
    function clear() {
        $_SESSION['states']       = self::$_states = array();
        $_SESSION['active_state'] = self::$_active = '';
        $this->_load();
        return $this;
    }
    
    
    function save(State $state = null) {
        parent::persist($state);
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
            $states[$this->_default_tab] = new State(new Address($this->_default_tab));
            $_SESSION['states']          = serialize($states);
            $_SESSION['active_state']    = $this->_default_tab;
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

    public $draft = null;
    public $address;
    

    function __construct(Address $address) {
        $this->update($address);
    }
    
    
    function update($address) {
        $this->address = $address;
        return $this;
    }

    
    function save() {
        parent::persist($this);
        return $this;
    }

    
    function active($set_active = null) {
        if ($set_active) {
            self::$_active = $this->tab;
            return $this;
        } else {
            return (self::$_active == $this->tab);
        }
    }
    
    
    function reset() {
        $this->address->reset();
        $this->draft = '';
        $this->save();
        return $this;
    }
}


/**
 * A simple page address hash
 */
class Address {
    
    public $tab;
    public $action;
    public $value;
    
    
    function __construct($tab, $action = DEFAULT_ACTION, $value = DEFAULT_VALUE) {
        $this->tab    = $tab;
        $this->action = $action;
        $this->value  = $value;
    }
    
    
    function reset () {
        $this->action = DEFAULT_ACTION;
        $this->value  = DEFAULT_VALUE;
        return $this;
    }
    
    
    function __toString () {
        return "$this->tab/$this->action/$this->value";
    }
}