<?php namespace BapCat\Router;

use Exception;

class MissingInputException extends Exception {
  private $name;
  private $type;
  
  public function __construct($name, $type) {
    parent::__construct("The parameter [$name] of type [$type] was not provided a value");
    
    $this->name = $name;
    $this->type = $type;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getType() {
    return $this->Type;
  }
}
