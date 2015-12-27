<?php namespace BapCat\Router;

use BapCat\Propifier\PropifierTrait;

class State {
  use PropifierTrait;
  
  private $segments = [];
  private $action;
  
  protected function getSegments() {
    return $this->segments;
  }
  
  protected function getAction() {
    return $this->action;
  }
  
  protected function setAction($action) {
    $this->action = $action;
  }
}
