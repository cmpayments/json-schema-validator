<?php namespace CM\JsonSchemaValidator\Validators;

use CM\JsonSchemaValidator\Exceptions\ValidateException;

trait EnumTrait
{
    /**
     * Validates $data against a specific $schema->enum
     *
     * @param $data
     * @param $schema
     * @param $path
     */
    public function validateEnum($data, $schema, $path)
    {
        if (!isset($schema->enum)) {
            return;
        }

        // if $data comes from an array than the action below has already been done in validateArray()
        $needle   = (isset($schema->caseSensitive) && !$schema->caseSensitive) ? strtolower($data) : $data;
        $haystack = (isset($schema->caseSensitive) && !$schema->caseSensitive) ? array_map('strtolower', $schema->enum) : $schema->enum;

        if (!in_array($needle, $haystack)) {

            $this->addError(
                ValidateException::USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK,
                [$path, $data, $this->conjugationObject(count($schema->enum), 'this specific value', 'one of these values'), implode('\', \'', $schema->enum)]
            );
        }
    }
}