<?php namespace CM\JsonSchemaValidator\Exceptions;

class BaseException extends \ErrorException
{
    private $args = [];

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     */
    public function setArgs($args)
    {
        // check is $args is an array, if not, cast it into an array
        if (!is_array($args)) {

            $args = [$args];
        }

        $this->args = $args;
    }

    /**
     * Constructor
     *
     * @param string $code
     * @param array  $args
     * @param null   $message
     */
    public function __construct($code, $args = [], $message = null)
    {
        $this->setArgs($args);

        parent::__construct($this->getCustomMessage($code, $message), $code);
    }


    /**
     * Return the message for an $errorCode
     *
     * @param      $code
     * @param null $message
     *
     * @return string
     */
    public function getCustomMessage($code, $message = null)
    {
        // if message is empty, check for a message in the exception class itself
        // since this is an exception we cannot afford any new exception so we are extra careful
        if (empty($message) && defined('static::MESSAGES')) {

            // PHP 5.6 it is (for now) unable to check if array keys exist when this array is actually a (class) constant
            try {
                $message = static::MESSAGES[$code];
                $message = vsprintf($message, $this->getArgs());

            } catch (\Exception $e) {

                $message = 'This service is temporarily unavailable';
            }
        }

        return $message;
    }
}