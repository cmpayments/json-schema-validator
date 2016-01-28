<?php

namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\BaseValidator;
use CMPayments\SchemaValidator\Exceptions\ValidateException;

trait RegexTrait {

  /**
   * Validates $data against a specific $schema->regex
   *
   * @param $data
   * @param $schema
   * @param $path
   */
  public function validateRegex($data, $schema, $path) {
	if (!isset($schema->pattern)) {
	  return;
	}
	
	$pattern = '/'.trim($schema->pattern, '/').'/';

	if ( ($schema->type != 'number' || $schema->type != 'string') && !is_scalar($data)) {
	  $this->addError(ValidateException::ERROR_USER_REGEX_DATA_NOT_SCALAR, [$data]);
	}

	/**
	 * Use try catch to be able to handle malformed regex
	 */
	try {
	  
	  if (!preg_match($pattern, $data)) {
		$this->addError(ValidateException::ERROR_USER_REGEX_NOMATCH, [$data, $schema->pattern]);
	  }
	  
	} catch (Exception $ex) {
		$this->addError(ValidateException::ERROR_USER_REGEX_PATTERN_NOT_VALID, [$schema->pattern, $data]);
	  
	}
  }



}
