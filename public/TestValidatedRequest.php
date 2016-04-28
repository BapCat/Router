<?php

require __DIR__ . '/MacAddress.php';

use BapCat\Router\ValidatedRequest;
use BapCat\Router\Validator;
use BapCat\Values\Url;

class TestValidatedRequest extends ValidatedRequest {
  protected function validateInput(Validator $validator) {
    
  }
  
  protected function validateQuery(Validator $validator) {
    $validator->required('id',  MacAddress::class, 'Invalid device MAC');
    $validator->required('ap',  MacAddress::class, 'Invalid AP MAC');
    $validator->required('url', Url::class,        'Invalid URL');
  }
}
