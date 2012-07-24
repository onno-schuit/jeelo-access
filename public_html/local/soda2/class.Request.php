<?php

class Soda2_Request {
  public function __construct() {
    $this->raw_get = $_GET;
    $this->raw_post = $_POST;
    $this->raw_files = $_FILES;
    $this->raw_cookies = $_COOKIE;
    $this->raw_session = $_SESSION;

    $this->method = $_SERVER['REQUEST_METHOD'];
  }

  public function __call($method, $args) {
    $_default = false;

    if (!count($args)) {
      return false;
    }

    if (count($args) > 1) {
      $_default = $args[1];
    }

    $_ = sprintf('raw_%s', $method);
    $array = $this->$_;

    if (array_key_exists($args[0], $array)) {
      return $array[$args[0]];
    }
    return $_default;

  }
}