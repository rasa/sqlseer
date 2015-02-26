<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
abstract class Rasa_Reporter_AbstractReporter
{
    #use Rasa_Trait_Options
    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     * @throws Rasa_Exception
     */

    public function __construct($options = null)
    {
        if (is_callable(array($this, 'init'))) {
            $this->init($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        } elseif ($options !== null) {
            #require_once 'Rasa/Exception.php';
            throw new Rasa_Exception('Invalid option provided to constructor');
        }
    }

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * Set the options from an array object
     *
     * @param  array $options (Optional) Options to set
     * @return __CLASS__ Provides a fluent interface
     * @throws Rasa_Exception
     */
    public function setOptions(array $options = array())
    {
        assert('is_array($options)');
        if (!is_array($options)) {
            #require_once 'Rasa/Exception.php';
            throw new Rasa_Exception('Parameter is not an array');
        }
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * Set the options from a Zend_Config object
     *
     * @param  Zend_Config $config
     * @return __CLASS__ Provides a fluent interface
     */
    public function setConfig(Zend_Config $config)
    {
        assert('$config instanceof Zend_Config');
        $this->setOptions($config->toArray());
        return $this;
    }

    /**
     * Set a single option
     *
     * @param  string $key
     * @param  mixed $value
     * @return __CLASS__ Provides a fluent interface
     */
    public function setOption($key, $value)
    {
        $key = (string) $key;
        #assert('array_key_exists($key, $this->_options)');

        if (is_callable(array($this, '_setOption'))) {
            $value = $this->_setOption($key, $value);
        }

        $this->_options[$key] = $value;

        return $this;
    }

    /**
     * Get a single option, if the option is not defined, return null
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        $key = (string) $key;
        if (array_key_exists($key, $this->_options)) {
            return $this->_options[$key];
        }

        if (is_callable(array(
            $this,
            '_getOption'
        ))) {
            return $this->_getOption($key, $default);
        }

        return $default;
    }

    /**
     * Retrieve all the options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * If the option is defined, return true, otherwise false
     *
     * @param  string $key
     * @return boolean is option defined
     */
    public function hasOption($key)
    {
        $key = (string) $key;
        return array_key_exists($key, $this->_options);
    }
    #}use Rasa_Trait_Options

    /**
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * @var string
     */
    protected $extension = '';

    /**
     * @var string
     */
    protected $filename = 'untitled';

    /**
     * @var array
     */
    protected $metadata = array();

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var array
     */
    protected $rows = array();

    /**
     * @var string
     */
    protected $title = 'untitled';

    /**
     * @var boolean
     */
    protected $titles = true;

    /**
     * @var array
     */
    protected $urls = array();

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setExtension($extension)
    {
        $this->extension = extension;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        if (!$this->title) {
            $p           = pathinfo($title);
            $title       = basename(@$p['basename'], '.' . @$p['extension']);
            $this->title = ucwords(str_replace('_', ' ', $title));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return object $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return array
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setTitles($titles)
    {
        $this->titles = $titles;
        return $this;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @param array $urls
     * @return object $this
     */
    public function setUrls($urls)
    {
        $this->urls = $urls;
        return $this;
    }

    /**
     * @return int
     */
    public function getFieldCount()
    {
        if (!$this->metadata) {
            return 0;
        }

        return count($this->metadata);
    }

    /**
     * @return array
     */
    public function getFieldNames()
    {
        $rv = array();

        if (!$this->metadata) {
            return $rv;
        }

        foreach ($this->metadata as $k => $field) {
            $rv[] = $field->name;
        }

        return $rv;
    }

    /**
     * @return int
     */
    public function getRowCount()
    {
        $rows = $this->getRows();

        return is_array($rows) ? count($rows) : 0;
    }

    /**
     * @param int $rowNum
     * @return array
     */
    public function getRow($rowNum)
    {
        $rows = $this->getRows();

        return isset($rows[$rowNum]) ? array_values($rows[$rowNum]) : array();
    }

    /**
     * @param boolean $echo
     * @return boolean
     */
    public function sendHeaders()
    {
        header(sprintf('Content-type: %s', $this->contentType));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $this->filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0,pre-check=0');
        header('Pragma: public');
        return true;
    }

    /**
     * @return string
     */
    abstract public function export($echo = true);
}

# EOF
