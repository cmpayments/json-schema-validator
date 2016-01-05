<?php namespace CMPayments\Json\Exceptions;

use CMPayments\SchemaValidator\Exceptions\BaseException;

class JsonException extends BaseException
{
    const ERROR_INPUT_IS_NOT_OF_TYPE_STRING       = 1;
    const ERROR_INPUT_IS_NOT_VALID_JSON           = 2;
    const ERROR_INPUT_IS_OF_TYPE_STRING_BUT_EMPTY = 3;

    protected $messages = [
        self::ERROR_INPUT_IS_NOT_OF_TYPE_STRING => '\'%s\' is not of type \'String\' but of type \'%s\'',
        self::ERROR_INPUT_IS_NOT_VALID_JSON     => '\'%s\' is not valid JSON%s',
        self::ERROR_INPUT_IS_OF_TYPE_STRING_BUT_EMPTY => '\'%s\' cannot be empty'

    ];
}