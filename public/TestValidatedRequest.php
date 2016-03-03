<?php

use BapCat\Router\ValidatedRequest;
use BapCat\Values\Text;

class TestValidatedRequest extends ValidatedRequest {
  protected function validate() {
    $this->optional('name', Text::class);
  }
}
