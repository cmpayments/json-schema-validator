<?php namespace CMPayments\SchemaValidator;

use CMPayments\Cache\Cache;

interface ValidatorInterface
{
    public function __construct($data, $schema, Cache $cache);
    public function validateData($data, $schema, $path = null);
    public function validate($schema, $property, $data, $path);
    public function validateSchema($schema, $path = null);
}