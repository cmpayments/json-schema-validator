<?php namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\Exceptions\ValidateException;

/**
 * Class ArrayTrait
 *
 * @package CMPayments\SchemaValidator\Validators
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
trait ArrayTrait
{
    /**
     * Validate an array
     *
     * @param $data
     * @param $schema
     * @param $path
     */
    public function validateArray($data, $schema, $path)
    {
        // check for minItems property
        if (isset($schema->minItems) && (count($data) < $schema->minItems)) {

            $count = count($data);

            $this->addError(
                ValidateException::ERROR_USER_ARRAY_MINIMUM_CHECK,
                [$path, $schema->minItems, $this->conjugationObject($schema->minItems), $this->conjugationToBe($count), $count, $this->conjugationObject($count)]
            );
        }

        // check for maxItems property
        if (isset($schema->maxItems) && (count($data) > $schema->maxItems)) {

            $count = count($data);

            $this->addError(
                ValidateException::ERROR_USER_ARRAY_MAXIMUM_CHECK,
                [$path, $schema->maxItems, $this->conjugationToBe($count), $count, $this->conjugationObject($count)]
            );
        }

        // check for uniqueItems property
        if (isset($schema->uniqueItems) && $schema->uniqueItems && ($count = count($data))) {

            if (count(array_unique($data, SORT_REGULAR)) !== $count) {

                $this->addError(ValidateException::ERROR_USER_ARRAY_NO_DUPLICATES_ALLOWED, [$path]);
            }
        }

        // to prevent that this action is done for every item in $data we'll do it now instead of in validateEnum()
        // in order to prevent this action being redone in validateEnum, we unset the $schema->items->caseSensitive parameter
        if ((isset($schema->items->caseSensitive) && !$schema->items->caseSensitive) && (isset($schema->items->enum))) {

            $data                = array_map('strtolower', $data);
            $schema->items->enum = array_map('strtolower', $schema->items->enum);

            unset($schema->items->caseSensitive);
        }

        // Continue checking if every $row in $data matches $schema->items
        foreach ($data as $property => $row) {

            $this->validate($schema->items, $this->numberToOrdinal($property + 1) . ' child', $row, $path);
        }
    }

    /**
     * @param int   $code
     * @param array $args
     *
     * @return mixed
     */
    abstract public function addError($code, array $args = []);

    /**
     * @param int $number
     *
     * @return mixed
     */
    abstract public function numberToOrdinal($number);

    /**
     * Returns a valid representation of 'items' (or other value)
     *
     * @param int    $count
     * @param string $single
     * @param string $plural
     *
     * @return string
     */
    abstract public function conjugationObject($count, $single = 'item', $plural = 'items');

    /**
     * Returns a valid conjugation of the verb 'to be'
     *
     * @param int $count
     *
     * @return string
     */
    abstract public function conjugationToBe($count);
}
