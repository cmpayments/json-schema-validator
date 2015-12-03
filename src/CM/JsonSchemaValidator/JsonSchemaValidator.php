<?php namespace CM\JsonSchemaValidator;

use CM\JsonSchemaValidator\Exceptions\ValidateException;

/**
 * Class JsonSchemaValidator
 *
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 * @package CM\Validator
 */
class JsonSchemaValidator extends BaseValidator implements ValidatorInterface
{
    private $cacheReferencedSchemas = [];

    private $rootSchema = null;

    private $minMaxProperties = [
        'minimum'   => ['minimum', 'maximum'],
        'minItems'  => ['minItems', 'maxItems'],
        'minLength' => ['minLength', 'maxLength']
    ];

    /**
     * JsonSchemaValidator constructor.
     *
     * @param null|string $data
     * @param null|string $schema
     * @param array       $config
     *
     * @throws ValidateException
     */
    public function __construct($data = '', $schema = '', $config = [])
    {
        if (!empty($schema) && !empty($schema)) {

            $this->check($data, $schema, $config);
        }
    }

    /**
     * Start checking the $data against a $schema
     *
     * @param       $data
     * @param       $schema
     * @param array $config
     *
     * @throws ValidateException
     */
    public function check($data, $schema, $config = [])
    {
        // merge $config with default config (if $config is not empty)
        if (!empty($config)) {

            $this->config = array_merge($this->config, $config);
        }

        // check if $schema and $data variable are both a string to begin with
        foreach (['Data' => $data, 'Schema' => $schema] as $type => $property) {

            if (!is_string($property)) {
                throw new ValidateException(ValidateException::ERROR_INPUT_IS_NOT_A_STRING, [$type, $this->getPreposition(gettype($property)), gettype($property)]);
            }
        }

        // Validate if all types in $this->validTypes have a callable validation method
        $this->validateValidTypes();

        // calculate filename
        $filename = $this->config['cache.directory'] . md5($schema) . '.php';

        if (file_exists($filename)) {

            $this->rootSchema = require $filename;
        } else {

            // validate and decode JSON, all in one method because json_decode used in < PHP7 is not that optimized
            // so reduce the number of json_decode() calls
            $this->rootSchema = $this->validateAndConvertJSON($schema, 'Schema');

            // validate Schema
            $this->rootSchema = $this->validateSchema($this->rootSchema);

            // write to cache file (if directory is writable)
            if (is_writable($this->config['cache.directory'])) {

                file_put_contents($filename, $this->generateRunnableCache($this->rootSchema));
            } elseif ($this->config['debug']) {

                // output exception when $this->config['debug'] === true
                throw new ValidateException(ValidateException::ERROR_CACHE_DIRECTORY_NOT_WRITABLE, $this->config['cache.directory']);
            }
        }

        // validate $data
        $this->validateData($this->validateAndConvertJSON($data, 'Data'), $this->rootSchema);
    }

    /**
     * Validate the Data
     *
     * @param             $data
     * @param             $schema
     * @param null|string $path
     */
    public function validateData($data, $schema, $path = null)
    {
        // check if the required property is set
        if (isset($schema->required)) {

            if (count($missing = array_diff_key(array_flip($schema->required), (array)$data)) > 0) {

                $count = count($missing);

                $this->addError(
                    ValidateException::USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET,
                    [$this->conjugationObject($count, 'property', 'properties'), implode('\', \'', array_flip($missing)), $this->conjugationToBe($count), (($path) ?: '/')]
                );
            }
        }

        // loop through all the data
        foreach ($data as $property => $value) {

            // check if $property in $data actually exist in $schema, if so, validate
            if (isset($schema->properties->$property)) {

                $this->validate($schema->properties->$property, $value, $property, $path);
            } elseif (
                (isset($schema->additionalProperties) && !$schema->additionalProperties)
                || (!isset($schema->additionalProperties) && (isset($this->rootSchema->additionalProperties) && !$this->rootSchema->additionalProperties))
            ) {
                // log that a fields is missing
                $this->addError(ValidateException::USER_DATA_PROPERTY_IS_NOT_A_VALID_PROPERTY, ($path . '/' . $property));
            }
        }
    }

    /**
     * Validate a single Data value
     *
     * @param $schema
     * @param $data
     * @param $property
     * @param $path
     *
     * @return bool
     */
    public function validate($schema, $data, $property, $path)
    {
        // override because 'double' (float), 'integer' are covered by 'number' according to http://json-schema.org/latest/json-schema-validation.html#anchor79
        if (in_array(($type = gettype($data)), [BaseValidator::DOUBLE, BaseValidator::INTEGER])) {

            $type = BaseValidator::NUMBER;
        }

        // check if given type matches the expected type, if not add verbose error
        if ($type !== $schema->type) {

            $msg    = ValidateException::USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1;
            $params = [($path . '/' . $property), $this->getPreposition($schema->type), $schema->type, $this->getPreposition($type), $type];

            if (!in_array($type, [BaseValidator::OBJECT, BaseValidator::_ARRAY])) {

                $msg      = ValidateException::USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2;
                $params[] = (string)$data;
            }

            // add error
            $this->addError($msg, $params);

            return false;
        }

        // append /$property to $path
        $path .= '/' . $property;

        // do recursion
        if (is_object($data)) {

            $this->validateData($data, $schema, $path);
            // everything else except boolean
        } elseif ($type !== BaseValidator::BOOLEAN) {

            $method = 'validate' . ucfirst($type);
            $this->$method($data, $schema, $path);

            // check for $schema->format
            $this->validateFormat($data, $schema, $path);

            // check for $schema->enum
            $this->validateEnum($data, $schema, $path);

            // @TODO; check for $schema->pattern (regex)
            //$this->validatePattern($data, $schema, $path);

            // @TODO; check for $schema->oneOf { format: "" }, { format: "" }
            //$this->validateOneOf($data, $schema, $path);
        }

        return true;
    }

    /**
     * Validates a schema
     *
     * @param      $schema
     * @param null $path
     *
     * @return mixed
     * @throws ValidateException
     */
    public function validateSchema($schema, $path = null)
    {
        $path = ($path) ?: 'root';

        // check if there is a reference to another schema, update the current $parameters either with a online reference or a local reference
        if (isset($schema->{'$ref'}) && !empty($schema->{'$ref'})) {

            $schema = $this->getReference($schema);
        }

        // check if the given schema is an object
        if (!is_object($schema)) {

            throw new ValidateException(ValidateException::ERROR_SCHEMA_NO_OBJECT, $path);
        }

        // check if the given schema is not empty
        if (empty((array)$schema)) {

            throw new ValidateException(ValidateException::ERROR_SCHEMA_CANNOT_BE_EMPTY, $path);
        }

        // validate mandatory $schema properties
        $this->validateSchemaMandatoryProperties($schema, $path);

        // validate optional $schema properties
        $this->validateSchemaOptionalProperties($schema, $path);

        // validate $schema->properties
        if (isset($schema->properties)) {

            foreach ($schema->properties as $property => $childSchema) {

                // when an object key is empty it becomes '_empty_' by json_decode(), catch it since this is not valid
                if ($property === '_empty_') {

                    throw new ValidateException(ValidateException::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT, $path);
                }

                // do recursion
                $schema->properties->$property = $this->validateSchema($childSchema, ($path . '.properties.' . $property));
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
     * @param $path
     *
     * @return mixed
     * @throws ValidateException
     */
    private function validateSchemaMandatoryProperties($schema, $path)
    {
        $input = [
            sprintf('type|is_string|%s', BaseValidator::STRING),
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
     * @param $path
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

        if (isset($schema->type) && ($schema->type === BaseValidator::NUMBER)) {

            $input = array_merge($input, [
                sprintf('minimum|is_int|%s', BaseValidator::INTEGER),
                sprintf('maximum|is_int|%s', BaseValidator::INTEGER)
            ]);
        }

        return $this->validateSchemaProperties($input, $schema, $path);
    }

    /**
     * Validate $schema->$property
     *
     * @param            $input
     * @param            $schema
     * @param            $path
     * @param bool|false $mandatory
     *
     * @return mixed
     * @throws ValidateException
     */
    private function validateSchemaProperties($input, $schema, $path, $mandatory = false)
    {
        foreach ($input as $properties) {

            list($property, $method, $expectedType) = explode('|', $properties);

            // when $mandatory is true, check if a certain $property isset
            if ($mandatory && !isset($schema->$property)) {

                throw new ValidateException(ValidateException::ERROR_SCHEMA_PROPERTY_NOT_DEFINED, [$property, $path]);
            }

            // check if $property is of a certain type
            if (isset($schema->$property)) {

                if (!$method($schema->$property)) {

                    $actualType = gettype($schema->$property);

                    throw new ValidateException(
                        ValidateException::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID,
                        [$path, $property, $this->getPreposition($expectedType), $expectedType, $this->getPreposition($actualType), $actualType]
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
     * @throws ValidateException
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
        if (!in_array($schema->$property, $expected)) {

            $count = count($expected);

            throw new ValidateException(
                ValidateException::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID,
                [$schema->$property, $path, $property, $this->conjugationObject($count, '', 'any of '), $this->conjugationObject($count), implode('\', \'', $expected)]
            );
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
     * @throws ValidateException
     */
    private function validateSchemaMinMaxProperties($schema, $minProperty, $maxProperty, $path)
    {
        // both $schema->$maxProperty cannot be zero
        if (isset($schema->$maxProperty) && ($schema->$maxProperty === 0)) {

            throw new ValidateException(ValidateException::ERROR_SCHEMA_PROPERTY_CANNOT_NOT_BE_ZERO, [$path, $maxProperty]);
        }

        if (isset($schema->$minProperty) && isset($schema->$maxProperty) && ($schema->$minProperty > $schema->$maxProperty)) {

            throw new ValidateException(
                ValidateException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX,
                [$path, $minProperty, $schema->$minProperty, $path, $maxProperty, $schema->$maxProperty]
            );
        }
    }


    /**
     * Walk through all $schema->required items and check if there is a $schema->properties item defined for it
     *
     * @param $schema
     * @param $path
     *
     * @throws ValidateException
     */
    private function validateSchemaRequiredAgainstProperties($schema, $path)
    {
        $requiredPath = $path . '.required';

        // $schema->required must be an array
        if (!is_array($schema->required)) {

            $type        = gettype($schema->required);
            $preposition = $this->getPreposition($type);

            throw new ValidateException(ValidateException::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY, [$requiredPath, $preposition, $type]);
        }

        // check if the $schema->required property contains fields that have not been defined in $schema->properties
        if (count($missing = array_diff_key(array_flip($schema->required), (array)$schema->properties)) > 0) {

            $count = count($missing);
            $verb  = $this->conjugationToBe($count);

            throw new ValidateException(
                ValidateException::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH,
                [$this->conjugationObject($count), implode('\', \'', array_flip($missing)), $verb, $requiredPath, $verb, $path]
            );
        }
    }

    /**
     * Validate if all types in $this->getValidTypes() have a callable validation method
     */
    private function validateValidTypes()
    {
        foreach ($this->getValidTypes() as $validType) {

            if ((!in_array($validType, [BaseValidator::OBJECT, BaseValidator::BOOLEAN]))
                && !method_exists($this, ($method = 'validate' . ucfirst($validType)))
            ) {
                throw new ValidateException(ValidateException::ERROR_VALIDATION_METHOD_DOES_NOT_EXIST, [$method, $validType]);
            }
        }
    }

    /**
     * Retrieve and validate a reference
     *
     * @param  $schema
     *
     * @return mixed
     * @throws ValidateException
     */
    private function getReference($schema)
    {
        // return any previously requested definitions
        if (isset($this->cacheReferencedSchemas[$schema->{'$ref'}])) {

            return $this->cacheReferencedSchemas[$schema->{'$ref'}];
        }

        $referencedSchema = $reference = null;

        // fetch local reference
        if (strpos($schema->{'$ref'}, '#/definitions/') !== false) {

            $referencedSchema = $this->getLocalReference($schema);
            // fetch remote reference
        } elseif (strpos($schema->{'$ref'}, 'http') !== false) {

            $referencedSchema = $this->getRemoteReference($schema);
        }

        // not a local reference nor a remote reference
        if (is_null($referencedSchema)) {

            throw new ValidateException(ValidateException::ERROR_INVALID_REFERENCE, $schema->{'$ref'});
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
     * @throws ValidateException
     */
    private function getLocalReference($schema)
    {
        // check if there is at least one local reference defined to match it to
        if (!isset($this->rootSchema->definitions) || (count($definitions = get_object_vars($this->rootSchema->definitions)) === 0)) {

            throw new ValidateException(ValidateException::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED);
        }

        // check if the referenced schema is locally defined
        $definitionKeys = array_keys($definitions);
        $reference      = substr($schema->{'$ref'}, strlen('#/definitions/'));

        if (!in_array($reference, $definitionKeys)) {

            throw new ValidateException(ValidateException::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS, [$schema->{'$ref'}, implode('\', ', $definitionKeys)]);
        }

        return $this->rootSchema->definitions->$reference;
    }

    /**
     * Matches, validates and returns a remote reference
     *
     * @param $schema
     *
     * @return mixed
     * @throws ValidateException
     */
    private function getRemoteReference($schema)
    {
        // check if the curl_init exists
        if (!function_exists('curl_init')) {

            throw new ValidateException(ValidateException::ERROR_CURL_NOT_INSTALLED);
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

            throw new ValidateException(ValidateException::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST, $schema->{'$ref'});
        }

        if (empty($response)) {

            throw new ValidateException(ValidateException::ERROR_NO_JSON_SCHEMA_WAS_FOUND, $schema->{'$ref'});
        }

        // check if $response is valid JSON and return
        return $this->validateAndConvertJSON($response, $schema->{'$ref'});
    }

    /**
     * Check if $string is valid JSON and return the decoded value
     *
     * @param $input
     * @param $type
     *
     * @return mixed
     * @throws ValidateException
     */
    private function validateAndConvertJSON($input, $type)
    {
        // check if $data variable is valid JSON
        $result = json_decode($input);
        if (empty($result) && (json_last_error() !== JSON_ERROR_NONE)) {

            throw new ValidateException(ValidateException::ERROR_INPUT_IS_NOT_VALID_JSON, $type);
        }

        return $result;
    }

    /**
     * Create runnable cache for $variable
     *
     * @param            $variable
     * @param bool|false $recursion
     *
     * @return mixed|string
     *
     * @author Bas Peters <bp@cm.nl>
     */
    private function generateRunnableCache($variable, $recursion = false)
    {
        if ($variable instanceof \stdClass) {

            // workaround for a PHP bug where var_export cannot deal with stdClass
            $result = '(object) ' . self::generateRunnableCache(get_object_vars($variable), true);
        } else {

            if (is_array($variable)) {
                $array = [];

                foreach ($variable as $key => $value) {

                    $array[] = var_export($key, true) . ' => ' . self::generateRunnableCache($value, true);
                }
                $result = 'array (' . implode(', ', $array) . ')';
            } else {

                $result = var_export($variable, true);
            }
        }

        return $recursion ? $result : sprintf('<?php return %s;', $result);
    }
}