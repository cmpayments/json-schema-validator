<?php namespace CM\JsonSchemaValidator;

use CM\JsonSchemaValidator\Exceptions\ValidateException;
use CM\JsonSchemaValidator\Validators\ArrayTrait;
use CM\JsonSchemaValidator\Validators\EnumTrait;
use CM\JsonSchemaValidator\Validators\ErrorTrait;
use CM\JsonSchemaValidator\Validators\FormatTrait;
use CM\JsonSchemaValidator\Validators\NumberTrait;
use CM\JsonSchemaValidator\Validators\StringTrait;

class BaseValidator
{
    use ErrorTrait;
    use ArrayTrait;
    use EnumTrait;
    use FormatTrait;
    use NumberTrait;
    use StringTrait;

    const TYPE   = 'type';
    const FORMAT = 'format';

    // Types
    const _ARRAY  = 'array';
    const BOOLEAN = 'boolean';
    const DOUBLE  = 'double';
    const INTEGER = 'integer';
    const NUMBER  = 'number';
    const OBJECT  = 'object';
    const STRING  = 'string';

    // String formats
    const DATE        = 'date';
    const DATETIME    = 'datetime';
    const EMAIL       = 'email';
    const TIME        = 'time';
    const UTC_SECONDS = 'utc-seconds';

    protected $config = [
        'cache.directory' => __DIR__ . '/cache/',
        'debug'           => false
    ];

    // Valid types for the items in $schema->properties
    private $validTypes = [
        self::_ARRAY,
        self::BOOLEAN,
        self::NUMBER,
        self::OBJECT,
        self::STRING
    ];

    // Valid formats for string typed items
    private $validFormats = [
        self::DATE,
        self::DATETIME,
        self::EMAIL,
        self::TIME,
        self::UTC_SECONDS
    ];

    /**
     * @param $validType
     */
    public function addValidType($validType)
    {
        $this->validTypes[] = $validType;
    }

    /**
     * @param array $validTypes
     */
    public function setValidTypes(array $validTypes)
    {
        $this->validTypes = $validTypes;
    }

    /**
     * @return array
     * @throws ValidateException
     */
    public function getValidTypes()
    {
        return $this->validTypes;
    }

    /**
     * @return array
     */
    public function getValidFormats()
    {
        return $this->validFormats;
    }


}