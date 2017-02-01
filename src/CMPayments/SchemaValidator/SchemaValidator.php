<?php namespace CMPayments\SchemaValidator;

use CMPayments\Cache\Cache;
use CMPayments\Json\Json;
use CMPayments\SchemaValidator\Exceptions\ValidateException;
use CMPayments\SchemaValidator\Exceptions\ValidateSchemaException;

/**
 * Class SchemaValidator
 *
 * @package CMPayments\Validator
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 * @Author  Rob Theeuwes <Rob.Theeuwes@cm.nl>
 */
class SchemaValidator extends BaseValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    private $cacheReferencedSchemas = [];

    /**
     * @var null|object
     */
    private $rootSchema = null;

    /**
     * @var Cache|null
     */
    protected $cache;

    /**
     * @var array
     */
    private $minMaxProperties = [
        'minimum'   => ['minimum', 'maximum'],
        'minItems'  => ['minItems', 'maxItems'],
        'minLength' => ['minLength', 'maxLength']
    ];

    /**
     * SchemaValidator constructor.
     *
     * @param            $data
     * @param            $schema
     * @param Cache|null $cache
     *
     * @throws ValidateSchemaException
     */
    public function __construct($data, $schema, Cache $cache = null)
    {
        // if $cache is empty, create a new instance of Cache
        if (is_null($cache)) {

            $cache = new Cache();
        }

        $this->cache = $cache;

        // check if $schema is an object to begin with
        if (!is_object($schema) || (is_callable($schema) && ($schema instanceof \Closure))) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_INPUT_IS_NOT_A_OBJECT, ['Schema', $this->getPreposition(gettype($schema)), gettype($schema), '']);
        }

        // PHP 5.4
        $filename = $cache->getFilename();
        if (empty($filename)) {

            $cache->setFilename(md5(json_encode($schema)) . '.php');
        }

        // if cache file exists, require it, if not, validate the schema
        if (file_exists(($filename = $cache->getAbsoluteFilePath()))) {

            $this->rootSchema = require $filename;
        } else {

            // decode the valid JSON
            $this->rootSchema = $schema;

            // validate Schema
            $this->rootSchema = $this->validateSchema($this->rootSchema);

            $cache->putContent($this->rootSchema, $filename);
        }

        // decode the valid JSON
        $this->validateData($this->rootSchema, $data);
    }

    /**
     * Validate the Data
     *
     * @param \stdClass $schema
     * @param             $data
     * @param null|string $path
     *
     * @return boolean|null
     */
    public function validateData($schema, $data, $path = null)
    {
        // check if the required property is set
        if (isset($schema->required)) {

            if (count($missing = array_diff_key(array_flip($schema->required), (array)$data)) > 0) {

                $count = count($missing);

                // add error
                $this->addError(
                    ValidateException::ERROR_USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET,
                    [$this->conjugationObject($count, 'property', 'properties'), implode('\', \'', array_flip($missing)), $this->conjugationToBe($count), (($path) ?: '/')]
                );
            }
        }

        // BaseValidator::_ARRAY
        if (is_array($data) && ($schema->type === BaseValidator::_ARRAY)) {

            // check if the expected $schema->type matches gettype($data)
            $this->validateType($schema, $data, $path);

            // when variable path is null it means that the base element is an array validate it anyway (even when there are no items present in the array)
            if (is_null($path)) {

                $this->validate($schema, null, $data, null);
            } else {

                foreach ($data as $property => $value) {

                    $this->validate($schema->items, $property, $value, $path);
                }
            }
            // BaseValidator::OBJECT
        } elseif (is_object($data)) {

            // check if the expected $schema->type matches gettype($data)
            $this->validateType($schema, $data, (($path) ?: '/'));

            foreach ($data as $property => $value) {

                if (isset($schema->properties->$property)) {

                    $this->validate($schema->properties->$property, $property, $value, $path);
                    // $schema->properties->$property is not set but check if it allowed based on $schema->additionalProperties
                } elseif (
                    (isset($schema->additionalProperties) && !$schema->additionalProperties)
                    || (!isset($schema->additionalProperties) && (isset($this->rootSchema->additionalProperties) && !$this->rootSchema->additionalProperties))
                ) {
                    // $schema->additionalProperties says NO, log that a fields is missing
                    $this->addError(ValidateException::ERROR_USER_DATA_PROPERTY_IS_NOT_AN_ALLOWED_PROPERTY, [$path . '/' . $property]);
                }
            }
            // Everything else
        } else {

            $this->validate($schema, null, $data, $path);
        }
    }

    /**
     * Validate a single Data value
     *
     * @param $schema
     * @param $data
     * @param $property
     * @param null|string $path
     *
     * @return bool
     */
    public function validate($schema, $property, $data, $path)
    {
        // check if the expected $schema->type matches gettype($data)
        $type = $this->validateType($schema, $data, ((substr($path, -1) !== '/') ? $path . '/' . $property : $path . $property));

        // append /$property to $path
        $path .= (substr($path, 0, 1) !== '/') ? '/' . $property : $property;

        // if $type is an object
        if ($type === BaseValidator::OBJECT) {

            $this->validateData($schema, $data, $path);
        } elseif (
            ($type !== BaseValidator::BOOLEAN)
            && ($schema->type === $type)
        ) { // everything else except boolean

            $method = 'validate' . ucfirst($type);
            $this->$method($data, $schema, $path);

            // check for format property on schema
            $this->validateFormat($data, $schema, $path);

            // check for enum property on schema
            $this->validateEnum($data, $schema, $path);

            // check for pattern (regex) property on schema
            $this->validateRegex($data, $schema, $path);

            // @TODO; check for $schema->oneOf { format: "" }, { format: "" }
            //$this->validateOneOf($data, $schema, $path);
        }

        return false;
    }

    /**
     * Validates a schema
     *
     * @param      $schema
     * @param null $path
     *
     * @return mixed
     * @throws ValidateSchemaException
     */
    public function validateSchema($schema, $path = null)
    {
        $path = ($path) ?: 'root';

        // check if there is a reference to another schema, update the current $parameters either with a online reference or a local reference
        if (isset($schema->{'$ref'}) && !empty($schema->{'$ref'})) {

            $schema = $this->getReference($schema);
        }

        // PHP 5.4
        $schemaInArray = (array)$schema;

        // check if the given schema is not empty
        if (empty($schemaInArray)) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH, [$path]);
        }

        // validate mandatory $schema properties
        $this->validateSchemaMandatoryProperties($schema, $path);

        // validate optional $schema properties
        $this->validateSchemaOptionalProperties($schema, $path);

        // validate $schema->properties
        if (isset($schema->properties)) {

            // PHP 5.4
            $schemaPropertiesInArray = (array)$schema->properties;

            // check if the given schema is not empty
            if (empty($schemaPropertiesInArray)) {

                throw new ValidateSchemaException(ValidateSchemaException::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH, [$path . '/properties']);
            }

            foreach ($schema->properties as $property => $childSchema) {

                $subPath = $path . '.properties';

                // when an object key is empty it becomes '_empty_' by json_decode(), catch it since this is not valid
                if ($property === '_empty_') {

                    throw new ValidateSchemaException(ValidateSchemaException::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT, [$subPath]);
                }

                // check if $childSchema is an object to begin with
                if (!is_object($childSchema)) {

                    throw new ValidateSchemaException(
                        ValidateSchemaException::ERROR_INPUT_IS_NOT_A_OBJECT,
                        [
                            'Schema',
                            $this->getPreposition(gettype($childSchema)),
                            gettype($childSchema),
                            (' in ' . $subPath)
                        ]);
                }

                // do recursion
                $schema->properties->$property = $this->validateSchema($childSchema, ($subPath . '.' . $property));
            }

            // check if the optional property 'required' is set on $schema
            if (isset($schema->required)) {

                $this->validateSchemaRequiredAgainstProperties($schema, $path);
            }
            // when dealing with an array we want to do recursion
        } elseif ($schema->type === BaseValidator::_ARRAY) {

            // do recursion
            $schema->items = $this->validateSchema($schema->items, ($path . '.items'));
        }

        return $schema;
    }

    /**
     * Validate mandatory $schema->$property properties
     *
     * @param $schema
     * @param string $path
     *
     * @return mixed
     * @throws ValidateException
     */
    private function validateSchemaMandatoryProperties($schema, $path)
    {
        $input = [
            sprintf('type|is_string:is_array|%s', BaseValidator::STRING),
        ];

        if (isset($schema->type) && $schema->type === BaseValidator::_ARRAY) {

            // field|must_be|type_in_error_msg
            $input = array_merge($input, [
                sprintf('items|is_object|%s', BaseValidator::OBJECT)
            ]);
        }

        return $this->validateSchemaProperties($input, $schema, $path, true);
    }

    /**
     * Validate optional $schema->$property properties
     *
     * @param $schema
     * @param string $path
     *
     * @return mixed
     * @throws ValidateException
     */
    private function validateSchemaOptionalProperties($schema, $path)
    {
        $input = [
            sprintf('format|is_string|%s', BaseValidator::STRING),
            sprintf('enum|is_array|%s', BaseValidator::_ARRAY),
            sprintf('caseSensitive|is_bool|%s', BaseValidator::BOOLEAN)
        ];

        if (isset($schema->type) && ($schema->type === BaseValidator::_ARRAY)) {

            // field|must_be|type_in_error_msg
            $input = array_merge($input, [
                sprintf('minItems|is_int|%s', BaseValidator::INTEGER),
                sprintf('maxItems|is_int|%s', BaseValidator::INTEGER),
                sprintf('uniqueItems|is_bool|%s', BaseValidator::BOOLEAN)
            ]);
        }

        if (isset($schema->type) && ($schema->type === BaseValidator::STRING)) {

            $input = array_merge($input, [
                sprintf('minLength|is_int|%s', BaseValidator::INTEGER),
                sprintf('maxLength|is_int|%s', BaseValidator::INTEGER),
                sprintf('format|is_string|%s', BaseValidator::STRING)
            ]);
        }

        if (isset($schema->type) && ($schema->type === BaseValidator::INTEGER)) {

            $input = array_merge($input, [
                sprintf('minimum|is_int|%s', BaseValidator::INTEGER),
                sprintf('maximum|is_int|%s', BaseValidator::INTEGER)
            ]);
        }

        if (isset($schema->type) && ($schema->type === BaseValidator::NUMBER)) {

            $input = array_merge($input, [
                sprintf('minimum|is_numeric|%s', BaseValidator::NUMBER),
                sprintf('maximum|is_numeric|%s', BaseValidator::NUMBER)
            ]);
        }

        return $this->validateSchemaProperties($input, $schema, $path);
    }

    /**
     * Validate $schema->$property
     *
     * @param            string[] $input
     * @param            $schema
     * @param            $path
     * @param bool|false $mandatory
     *
     * @return mixed
     * @throws ValidateSchemaException
     */
    private function validateSchemaProperties($input, $schema, $path, $mandatory = false)
    {
        foreach ($input as $properties) {

            list($property, $method, $expectedType) = explode('|', $properties);

            // when $mandatory is true, check if a certain $property isset
            if ($mandatory && !isset($schema->$property)) {

                throw new ValidateSchemaException(ValidateSchemaException::ERROR_SCHEMA_PROPERTY_NOT_DEFINED, [$property, $path]);
            }

            // check if $property is of a certain type
            if (isset($schema->$property)) {

                $methods = explode(':', $method);
                $isValid = false;

                foreach ($methods as $checkMethod) {
                    if ($checkMethod($schema->$property)) {
                        $isValid = true;
                        break;
                    }
                }
                if (!$isValid) {

                    $actualType = gettype($schema->$property);

                    throw new ValidateSchemaException(
                        ValidateSchemaException::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID,
                        [$path, $property, $this->getPreposition($expectedType), $expectedType, $this->getPreposition($actualType), $actualType]
                    );
                }

                if (is_array($schema->$property) && count($schema->$property) != count(array_unique($schema->$property))) {

                    throw new ValidateSchemaException(
                        ValidateSchemaException::ERROR_SCHEMA_PROPERTY_TYPES_NOT_UNIQUE,
                        [$path, str_replace('"', "'", json_encode($schema->$property)), str_replace('"', "'", json_encode(array_unique($schema->$property)))]
                    );

                }

                // check if a $property' value must match a list of predefined values
                $this->validateSchemaPropertyValue($schema, $property, $path);
            }

            if (in_array($property, array_keys($this->minMaxProperties))) {

                $this->validateSchemaMinMaxProperties($schema, $this->minMaxProperties[$property][0], $this->minMaxProperties[$property][1], $path);
            }
        }

        return $schema;
    }

    /**
     * Validate Schema property against a predefined list of values
     *
     * @param $schema
     * @param $property
     * @param $path
     *
     * @throws ValidateSchemaException
     */
    private function validateSchemaPropertyValue($schema, $property, $path)
    {
        // return if not applicable
        if (!in_array($property, [BaseValidator::TYPE, BaseValidator::FORMAT])) {

            return;
        }

        // set the correct $expected
        switch (1) {
            case ($property === BaseValidator::TYPE):
                $expected = $this->getValidTypes();
                break;
            case ($property === BaseValidator::FORMAT):
                $expected = $this->getValidFormats();
                break;
            default:
                $expected = [];
                break;
        }

        // check if $expected contains the $property
        $values = (array) $schema->$property;
        foreach ($values as $value) {
            if (!in_array($value, $expected)) {

                $count = count($expected);

                throw new ValidateSchemaException(
                    ValidateSchemaException::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID,
                    [$value, $path, $property, $this->conjugationObject($count, '', 'any of '), $this->conjugationObject($count), implode('\', \'', $expected)]
                );
            }
        }
    }

    /**
     * Validate Schema a min and max properties (if set)
     *
     * @param $schema
     * @param $minProperty
     * @param $maxProperty
     * @param $path
     *
     * @throws ValidateSchemaException
     */
    private function validateSchemaMinMaxProperties($schema, $minProperty, $maxProperty, $path)
    {
        // both $schema->$maxProperty cannot be zero
        if (isset($schema->$maxProperty) && ($schema->$maxProperty === 0)) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO, [$path, $maxProperty]);
        }

        if (isset($schema->$minProperty) && isset($schema->$maxProperty) && ($schema->$minProperty > $schema->$maxProperty)) {

            throw new ValidateSchemaException(
                ValidateSchemaException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX,
                [$path, $minProperty, $schema->$minProperty, $path, $maxProperty, $schema->$maxProperty]
            );
        }
    }

    /**
     * Walk through all $schema->required items and check if there is a $schema->properties item defined for it
     *
     * @param $schema
     * @param string $path
     *
     * @throws ValidateSchemaException
     */
    private function validateSchemaRequiredAgainstProperties($schema, $path)
    {
        $requiredPath = $path . '.required';

        // $schema->required must be an array
        if (!is_array($schema->required)) {

            $type        = gettype($schema->required);
            $preposition = $this->getPreposition($type);

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY, [$requiredPath, $preposition, $type]);
        }

        // check if the $schema->required property contains fields that have not been defined in $schema->properties
        if (!empty($schema->required) && count($missing = array_diff_key(array_flip($schema->required), (array)$schema->properties)) > 0) {

            $count = count($missing);
            $verb  = $this->conjugationToBe($count);

            throw new ValidateSchemaException(
                ValidateSchemaException::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH,
                [$this->conjugationObject($count), implode('\', \'', array_flip($missing)), $verb, $requiredPath, $verb, $path]
            );
        }
    }

    /**
     * Retrieve and validate a reference
     *
     * @param  $schema
     *
     * @return mixed
     * @throws ValidateSchemaException
     */
    private function getReference($schema)
    {
        // return any previously requested definitions
        if (isset($this->cacheReferencedSchemas[$schema->{'$ref'}])) {

            return $this->cacheReferencedSchemas[$schema->{'$ref'}];
        }

        $referencedSchema = null;

        // fetch local reference
        if (strpos($schema->{'$ref'}, '#/definitions/') !== false) {

            $referencedSchema = $this->getLocalReference($schema);
            // fetch remote reference
        } elseif (strpos($schema->{'$ref'}, 'http') !== false) {

            $referencedSchema = $this->getRemoteReference($schema);
        }

        // not a local reference nor a remote reference
        if (is_null($referencedSchema)) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_INVALID_REFERENCE, $schema->{'$ref'});
        }

        // cache the result
        $this->cacheReferencedSchemas[$schema->{'$ref'}] = $referencedSchema;

        // unset the reference for the $schema for cleanup purposes
        unset($schema->{'$ref'});

        // augment the current $schema with the fetched referenced schema properties (and override if necessary)
        foreach (get_object_vars($referencedSchema) as $property => $value) {

            $schema->$property = $value;
        }

        return $schema;
    }

    /**
     * Matches and returns a local reference
     *
     * @param $schema
     *
     * @return mixed
     * @throws ValidateSchemaException
     */
    private function getLocalReference($schema)
    {
        // check if there is at least one local reference defined to match it to
        if (!isset($this->rootSchema->definitions) || (count($definitions = get_object_vars($this->rootSchema->definitions)) === 0)) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED);
        }

        // check if the referenced schema is locally defined
        $definitionKeys = array_keys($definitions);
        $reference      = substr($schema->{'$ref'}, strlen('#/definitions/'));

        if (!in_array($reference, $definitionKeys)) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS, [$schema->{'$ref'}, implode('\', ', $definitionKeys)]);
        }

        return $this->rootSchema->definitions->$reference;
    }

    /**
     * Matches, validates and returns a remote reference
     *
     * @param $schema
     *
     * @return mixed
     * @throws ValidateSchemaException
     */
    private function getRemoteReference($schema)
    {
        // check if the curl_init exists
        if (!function_exists('curl_init')) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_CURL_NOT_INSTALLED);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $schema->{'$ref'},
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/schema+json'],
            CURLOPT_CONNECTTIMEOUT => 10, //timeout in seconds
            CURLOPT_TIMEOUT        => 10  //timeout in seconds
        ]);

        $response = curl_exec($ch);
        $info     = curl_getinfo($ch);

        curl_close($ch);

        if (isset($info['http_code']) && (($info['http_code'] < 200) || ($info['http_code'] > 207))) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST, $schema->{'$ref'});
        }

        // if the response is empty no valid schema (or actually no data at all) was found
        if (empty($response)) {

            throw new ValidateSchemaException(ValidateSchemaException::ERROR_NO_DATA_WAS_FOUND_IN_REMOTE_SCHEMA, $schema->{'$ref'});
        }

        $json = new Json($response);

        if ($json->validate()) {

            // if the validate method returned true it means valid JSON was found, return the decoded JSON schema
            return $json->getDecodedJSON();
        } else {

            // if the validate method returned false it means the JSON Linter can not make chocolate from $response
            throw new ValidateSchemaException(ValidateSchemaException::ERROR_NO_VALID_JSON_WAS_FOUND_IN_REMOTE_SCHEMA, $schema->{'$ref'});
        }
    }

    /**
     * Validates the JSON SCHEMA data type against $data
     *
     * @param $schema
     * @param $data
     * @param null|string $path
     *
     * @return string
     * @throws ValidateException
     */
    private function validateType($schema, $data, $path)
    {
        // gettype() on a closure returns 'object' which is not what we want
        if (is_callable($data) && ($data instanceof \Closure)) {

            $type = BaseValidator::CLOSURE;
        } else {

            // override because 'double' (float), 'integer' are covered by 'number' according to http://json-schema.org/latest/json-schema-validation.html#anchor79
            if (in_array(($type = gettype($data)), [BaseValidator::DOUBLE, BaseValidator::INTEGER])) {

                $type = BaseValidator::NUMBER;
            }
        }

        // check if given type matches the expected type, if not add verbose error
        $type = strtolower($type);
        $types = (array) $schema->type;
        if (!in_array($type, $types)) {

            $msg    = ValidateException::ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1;
            $params = [$path, $this->getPreposition($schema->type), implode(' or ', $types), $this->getPreposition($type), $type];

            if (!in_array($type, [BaseValidator::OBJECT, BaseValidator::CLOSURE, BaseValidator::_ARRAY, BaseValidator::BOOLEAN])) {

                $msg = ValidateException::ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2;

                if (in_array($type, [BaseValidator::STRING])) {

                    $data = str_replace("\n", '', $data);
                    $data = preg_replace("/\r|\n/", '', $data);
                    $data = (strlen($data) < 25) ? $data : substr($data, 0, 25) . ' [...]';
                }

                $params[] = $data;
            }

            // add error
            $this->addError($msg, $params);
        }

        return $type;
    }
}
