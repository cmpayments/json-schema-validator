<?php namespace CM\JsonSchemaValidator;

interface ValidatorInterface
{
    public function __construct($data, $schema, $config = []);
    public function validateData($data, $schema, $path = null);
    public function validate($schema, $data, $property, $path);
    public function validateSchema($schema, $path = null);
}