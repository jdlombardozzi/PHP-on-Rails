<?php
// This library is a mimic of the session library use via ruby Rack apps.
// rack/lib/rack/session/abstract/id.rb

class Session {
}

# SessionHash is responsible to lazily load the session from store.
class SessionHash extends Session {
  public $id;

  function __construct($store, $env) {
    $this->store = $store;
    $this->env = $env;
    $this->loaded = false;
  }

  function id() {
    if($this->loaded || $this->id) return $this->id;
    return $this->id = call_user_func(array($this->store, 'extract_session_id'), $this->env);
  }
}

class ID extends Session {
  private static $DEFAULT_OPTIONS = array('key'           => 'rack.session',
                                          'path'          => '/',
                                          'domain'        => nil,
                                          'expire_after'  => nil,
                                          'secure'        => false,
                                          'httponly'      => true,
                                          'defer'         => false,
                                          'renew'         => false,
                                          'sidbits'       => 128,
                                          'cookie_only'   => true,
                                          'secure_random' => 'try{ SecureRandom } catch(Exception $e) {return false;}');

  private $attr_reader = array('key', 'default_options');

  function __get($name) {
    if(array_key_exists($name, $this->attr_reader)) {
      return $this->$name;
    }
  }

  function __construct($app, $options = array()) {
    $this->app = $app;
    $this->default_options = array_merge(self::$DEFAULT_OPTIONS, $options);

    $this->key = $this->default_options['key'];
    unset($this->default_options['key']);

    $this->cookie_only = $this->default_options['cookie_only'];
    unset($this->default_options['cookie_only']);

    $this->initialize_sid();
  }

  function initialize_sid() {
    $this->sidbits = $this->default_options['sidbits'];
    $this->sid_secure = $this->default_options['secure_random'];
    $this->sid_length = $this->sidbits / 4;
  }
}