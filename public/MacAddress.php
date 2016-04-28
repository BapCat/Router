<?php

use BapCat\Interfaces\Values\Value;
use BapCat\Values\Regex;
use BapCat\Values\Text;

class MacAddress extends Value {
  private $raw;
  
  public function __construct($raw) {
    $regex = new Regex('/^(?:[[:xdigit:]]{2}([-:]))(?:[[:xdigit:]]{2}\1){4}[[:xdigit:]]{2}$/');
    
    if(!$regex->check(new Text($raw))) {
      throw new InvalidArgumentException('Expected MAC address, but got [' . var_export($raw, true) . '] instead');
    }
    
    $this->raw = $raw;
  }
  
  public function __toString() {
    return $this->raw;
  }
  
  public function jsonSerialize() {
    return $this->raw;
  }
  
  protected function getRaw() {
    return $this->raw;
  }
}
