<?php namespace BapCat\Router;

use BapCat\Collection\Exceptions\NoSuchKeyException;
use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Interfaces\Values\Value;
use BapCat\Propifier\PropifierTrait;
use BapCat\Request\Request;

use InvalidArgumentException;

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
    
    $set = function($name, Value $value) {
      $this->set($name, $value);
    };
    
    $err = function($name, $msg) {
      $this->errors[$name] = $msg;
      $this->validated = false;
    };
    
    $this->validateInput(new Validator($ioc, $request->input, $set, $err));
    $this->validateQuery(new Validator($ioc, $request->query, $set, $err));
  }
  
  protected function set($name, Value $value = null) {
    $this->vars[$name] = $value;
  }
  
  public function __get($name) {
    if(isset($this->vars[$name])) {
      return $this->vars[$name];
    }
    
    return $this->___get($name);
  }
  
  protected abstract function validateInput(Validator $validator);
  protected abstract function validateQuery(Validator $validator);
  
  protected function getValidated() {
    return $this->validated;
  }
  
  protected function getValidationErrors() {
    return $this->errors;
  }
}
