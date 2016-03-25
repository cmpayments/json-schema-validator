<?php namespace CMPayments\Json;

use CMPayments\Cache\Cache;
use CMPayments\JsonLint\Exceptions\JsonLintException;
use CMPayments\JsonLint\Exceptions\ParseException;
use CMPayments\JsonLint\JsonLinter;
use CMPayments\SchemaValidator\BaseValidator;
use CMPayments\Json\Exceptions\JsonException;
use CMPayments\SchemaValidator\Exceptions\ValidateSchemaException;
use CMPayments\SchemaValidator\SchemaValidator;

/**
 * Class Json
 *
 * @package CMPayments\SchemaValidator
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class Json
{
    const SCHEMA   = 'Schema';
    const INPUT    = 'Input';
    const ERRORS   = 'errors';
    const WARNINGS = 'warnings';

    /**
     * @var null|object|string
     */
    private $input = null;

    /**
     * @var null|object|string
     */
    private $schema = null;

    /**
     * @var bool
     */
    private $isValid = false;

    /**
     * @var null|string
     */
    private $encodedJSON;

    /**
     * @var bool
     */
    private $validatedInput = null;

    /**
     * Json constructor.
     *
     * @param string $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Validates $this->input and optionally against $this->schema as well
     *
     * @param null|string $schema
     * @param array       $passthru Stores the error(s) that might occur during validation
     * @param array       $options
     *
     * @return bool
     * @throws JsonException|ParseException|JsonLintException|ValidateSchemaException|bool
     */
    public function validate($schema = null, &$passthru = [], $options = [])
    {
        $passthru = [
            self::ERRORS   => [],
            self::WARNINGS => []
        ];

        try {
            $cache = new Cache($options, $passthru);

            if (!is_null($schema)) {

                // validate $schema
                $this->schema = $this->validateAndConvertData($schema, self::SCHEMA, $cache);
            }

            // validate $this->input
            if (empty($this->validatedInput)) {

                $this->validatedInput = $this->validateAndConvertData($this->input, self::INPUT, $cache);
            }

            if (!is_null($this->schema)) {

                // validate $input against $this->schema
                $validator = new SchemaValidator($this->validatedInput, $this->schema, $cache);

                // check if there are errors, if so store them
                if (!$validator->isValid()) {

                    foreach ($validator->getErrors() as $error) {

                        $passthru[self::ERRORS][] = $error;
                    }

                    return $this->isValid = false;
                }

                // if $this->schema->type is other than object, decode it again but this time with $assoc = true
                $this->input = ($this->schema->type === BaseValidator::OBJECT) ? $this->validatedInput : $this->decodeJSON($this->input, self::INPUT, true);
            }
        } catch (ValidateSchemaException $e) {

            // re throw this one
            throw $e;
        } catch (\Exception $e) {

            // convert Exception to array
            $passthru[self::ERRORS][] = convert_exception_to_array($e);

            return $this->isValid = false;
        }

        return $this->isValid = true;
    }

    /**
     *  Returns the encoded JSON
     *
     * @return string
     */
    public function getEncodedJSON()
    {
        if (empty($this->encodedJSON)) {

            $this->encodedJSON = json_encode($this->input);
        }

        return $this->encodedJSON;
    }

    /**
     *  Returns the decoded JSON
     *
     * @return mixed|null|string
     * @throws JsonException
     */
    public function getDecodedJSON()
    {
        // cannot decode was not found valid by $this->validate() (or when $this->validate was never called)
        if (!$this->isValid) {

            return null;
        }

        return (is_string($this->input)) ? $this->validatedInput : $this->input;
    }

    /**
     * Validates if a string is valid JSON and converts it back to an object
     *
     * @param string|null|object $data
     * @param string             $type
     * @param Cache              $cache
     *
     * @return mixed
     * @throws JsonException
     * @throws ParseException|null
     * @throws ValidateSchemaException
     */
    private function validateAndConvertData($data, $type, Cache $cache)
    {
        // check type $data
        if (!is_string($data)) {

            throw new JsonException(JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING, [$type, gettype($data)]);
        } elseif (empty($data)) {

            throw new JsonException(JsonException::ERROR_INPUT_IS_OF_TYPE_STRING_BUT_EMPTY, [$type]);
        }

        if ($type === self::SCHEMA) {

            // calculate and set filename
            $cache->setFilename(md5($data) . '.php');

            // if cache file exits it means that this Schema has been correctly validated before
            if (file_exists($cache->getAbsoluteFilePath())) {

                return $this->decodeJSON($data, $type);
            }
        }

        // check if $data variable is valid JSON
        if (($result = (new JsonLinter())->lint($data)) instanceof JsonLintException) {

            // if the current $data
            if ($type === self::SCHEMA) {

                $result = new ValidateSchemaException(
                    ValidateSchemaException::ERROR_SCHEMA_IS_NOT_VALID_JSON,
                    [$data],
                    $result->getMessage()
                );
            }

            throw $result;
        }

        return $this->decodeJSON($data, $type);
    }

    /**
     * Does the actual decoding of a JSON string
     *
     * @param      $data
     * @param      $type
     * @param bool $assoc
     *
     * @return mixed
     * @throws JsonException
     */
    private function decodeJSON($data, $type, $assoc = false)
    {
        $result = json_decode($data, $assoc);

        if (empty($result) && (json_last_error() !== JSON_ERROR_NONE)) {

            throw new JsonException(JsonException::ERROR_INPUT_IS_NOT_VALID_JSON, [$type, $data]);
        }

        return $result;
    }
}