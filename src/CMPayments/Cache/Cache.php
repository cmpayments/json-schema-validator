<?php namespace CMPayments\Cache;

use CMPayments\Cache\Exceptions\CacheException;

/**
 * Class Cache
 *
 * @package CMPayments\Cache
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class Cache
{
    /**
     * @var array
     */
    private $passthru = [];

    /**
     * @var array
     */
    private $options = [
        'directory' => __DIR__ . '/../../../storage/cache/',
        'debug'     => false
    ];

    /**
     * Cache constructor.
     *
     * @param array $options
     * @param array $passthru
     */
    public function __construct(array $options = [], &$passthru = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->passthru =& $passthru;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the cache directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->options['directory'];
    }

    /**
     * Get the cache filename
     *
     * @return string|null
     */
    public function getFilename()
    {
        return (isset($this->options['filename'])) ? $this->options['filename'] : null;
    }

    /**
     * Set the cache filename
     *
     * @param $filename
     */
    public function setFilename($filename)
    {
        $this->options['filename'] = $filename;
    }

    /**
     * Get the debug option (boolean)
     *
     * @return boolean
     */
    public function getDebug()
    {
        return $this->options['debug'];
    }

    /**
     * Returns the full path of the filename
     *
     * @return string
     * @throws CacheException
     */
    public function getAbsoluteFilePath()
    {
        // check is the cache filename option is set
        if (!isset($this->options['filename'])) {

            throw new CacheException(CacheException::ERROR_CACHE_FILENAME_NOT_SET, '$options[\'filename\']');
        }

        return $this->options['directory'] . $this->options['filename'];
    }

    /**
     * Store Cache content
     *
     * @param             $data
     * @param null|string $filename
     *
     * @throws CacheException
     */
    public function putContent($data, $filename = null)
    {
        $options = $this->getOptions();

        // check if the directory is writable or not and throw exception when $this->config['debug'] is true
        if (!is_writable($options['directory'])) {

            $this->passthru['warnings'][] = convert_exception_to_array(new CacheException(CacheException::ERROR_CACHE_DIRECTORY_NOT_WRITABLE, $options['directory']));
        } else {

            file_put_contents(((is_null($filename)) ? $this->getAbsoluteFilePath() : $filename), $this->generateRunnableCache($data));
        }
    }

    /**
     * Get Cache content
     *
     * @param $location
     *
     * @return mixed|null
     */
    public function getContent($location)
    {
        if (file_exists($location)) {

            return require $location;
        }

        return null;
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
            $result = '(object) ' . $this->generateRunnableCache(get_object_vars($variable), true);
        } else {

            if (is_array($variable)) {
                $array = [];

                foreach ($variable as $key => $value) {

                    $array[] = var_export($key, true) . ' => ' . $this->generateRunnableCache($value, true);
                }
                $result = 'array (' . implode(', ', $array) . ')';
            } else {

                $result = var_export($variable, true);
            }
        }

        return $recursion ? $result : sprintf('<?php return %s;', $result);
    }
}
