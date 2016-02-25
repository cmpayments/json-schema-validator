<?php

if (!function_exists('convert_exception_to_array')) {
    /**
     * Converts an \Exception into an array
     *
     * @param Exception $e
     *
     * @return string
     */
    function convert_exception_to_array(\Exception $e)
    {
        $destination        = new \stdClass();
        $destination->class = get_class($e);
        foreach ((new \ReflectionObject($e))->getProperties() as $sourceProperty) {

            if (!in_array($sourceProperty->name, ['messages', 'severity', 'xdebug_message'])) {

                $sourceProperty->setAccessible(true);
                $destination->{$sourceProperty->getName()} = $sourceProperty->getValue($e);
            }
        }

        // return exception
        return (array)$destination;
    }
}
