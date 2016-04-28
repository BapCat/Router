<?php namespace BapCat\Router;

use BapCat\Collection\Exceptions\NoSuchKeyException;
use BapCat\Collection\Interfaces\Collection;
use BapCat\Interfaces\Ioc\Ioc;

use InvalidArgumentException;

class Validator {
  private $ioc;
  private $col;
  private $set;
  private $err;
  
  public function __construct(Ioc $ioc, Collection $col, callable $set, callable $err) {
    $this->ioc = $ioc;
    $this->col = $col;
    $this->set = $set;
    $this->err = $err;
  }
  
  public function required($name, $type, $msg) {
    try {
      // Stupid PHP5...
      $set = $this->set; $set($name, $this->make($name, $type));
    } catch(NoSuchKeyException $e) {
      $err = $this->err; $err($name, $msg);
    } catch(InvalidArgumentException $e) {
      $err = $this->err; $err($name, $msg);
    }
  }
  
  public function optional($name, $type, $msg) {
    try {
      $set = $this->set; $set($name, $this->make($name, $type));
    } catch(NoSuchKeyException $e) {
      $set = $this->set; $set($name, null);
    } catch(InvalidArgumentException $e) {
      $err = $this->err; $err($name, $msg);
    }
  }
  
  private function make($name, $type) {
    return $this->ioc->make($type, [$this->col->get($name)]);
  }
}
