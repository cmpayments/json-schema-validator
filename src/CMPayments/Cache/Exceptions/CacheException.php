<?php namespace CMPayments\Cache\Exceptions;

use CMPayments\Exceptions\BaseException;

/**
 * Class CacheException
 *
 * @package CMPayments\Cache\Exceptions
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class CacheException extends BaseException
{
    const ERROR_CACHE_FILENAME_NOT_SET       = 1;
    const ERROR_CACHE_DIRECTORY_NOT_WRITABLE = 2;

    protected $messages = [
        self::ERROR_CACHE_FILENAME_NOT_SET       => 'The cache filename option \'%s\' is not set',
        self::ERROR_CACHE_DIRECTORY_NOT_WRITABLE => 'The cache directory \'%s\' is not writable'
    ];

    /**
     * prepend class name to clarify error origin
     *
     * @param int    $code
     * @param null   $default
     * @param string $msgArray
     *
     * @return string
     */
    public function getItemFromVariableArray($code, $default = null, $msgArray = 'messages')
    {
        return (new \ReflectionClass($this))->getShortName() . ': ' . parent::getItemFromVariableArray($code, $default, $msgArray);
    }
}