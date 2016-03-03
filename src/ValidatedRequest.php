<?php namespace BapCat\Router;

use BapCat\Collection\Exceptions\NoSuchKeyException;
use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Propifier\PropifierTrait;
use BapCat\Request\Request;

use Exception;

abstract class ValidatedRequest {
  use PropifierTrait {
    PropifierTrait::__get as ___get;
  }
  
  private $ioc;
  private $request;
  
  private $vars = [];
  private $validated = true;
  private $errors = [];
  
  public function __construct(Ioc $ioc, Request $request) {
    $this->ioc = $ioc;
    $this->request = $request;
    
    $this->validate();
  }
  
  protected function required($name, $type) {
    try {
      $this->set($name, $this->make($name, $type));
    } catch(Exception $ex) {
      $this->errors[$name] = $ex;
      $this->validated = false;
    }
  }
  
  protected function optional($name, $type) {
    try {
      $this->set($name, $this->make($name, $type));
    } catch(NoSuchKeyException $ex) {
      $this->vars[$name] = null;
    } catch(Exception $ex) {
      $this->errors[$name] = $ex;
      $this->validated = false;
    }
  }
  
  protected function set($name, $value) {
    $this->vars[$name] = $value;
  }
  
  private function make($name, $type) {
    return $this->ioc->make($type, [$this->request->input->get($name)]);
  }
  
  public function __get($name) {
    if(isset($this->vars[$name])) {
      return $this->vars[$name];
    }
    
    return $this->___get($name);
  }
  
  protected abstract function validate();
  
  protected function getValidated() {
    return $this->validated;
  }
  
  protected function getValidationErrors() {
    return $this->validation_errors;
  }
}
