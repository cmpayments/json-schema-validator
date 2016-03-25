<?php

if (!function_exists('convert_exception_to_array')) {

    /**
     * Converts an \Exception into an array
     *
     * @param $e
     *
     * @return array
     * @throws Exception
     */
    function convert_exception_to_array($e)
    {
        if (PHP_VERSION_ID < 70000) {

            if (!($e instanceof \Exception)) {

                throw new \Exception('input must be instance of Exception');
            }
        } else {

            if (!($e instanceof \Throwable)) {

                throw new \Exception('input must be instance of Throwable');
            }
        }

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
