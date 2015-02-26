<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter
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
        if (session_id() == '') {
            session_start();
        }

        if (is_callable(array($this, 'init'))) {
            $this->init($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        } elseif ($options !== null) {
            #require_once __DIR__ . '/Rasa/Exception.php';
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

        if (is_callable(array($this, '_setOption'))) {
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
     * options
     *
     * @var string
     */
    const OPTION_CSS = 'css';
    const OPTION_DB = 'db';
    const OPTION_DEBUG = 'debug';
    const OPTION_LOGGER = 'logger';
    const OPTION_LOGO = 'logo';
    const OPTION_LOGO_URL = 'logo_url';
    const OPTION_MAX_PLACES = 'max_places';
    const OPTION_MAX_ROWS = 'max_rows';
    const OPTION_PAGE_TITLE = 'page_title';
    const OPTION_ROW_COLUMN = 'row_column';
    const OPTION_SQL = 'sql';
    const OPTION_TITLE = 'title';
    const OPTION_URLS = 'urls';
    const OPTION_NOT_NUMS = 'not_nums';

    /**
     * params
     *
     * @var string
     */
    const OPTION_GROUPBY = 'groupby';
    const OPTION_PARAMS = 'params';
    const OPTION_TOTALS = 'totals';

    /**
     * @var array
     */
    protected $defaults = array(
        self::OPTION_CSS => 'default.css',
        self::OPTION_DEBUG => 0,
        self::OPTION_GROUPBY => 'day',
        #self::OPTION_LOGGER   => null,
        self::OPTION_LOGO => 'default.png',
        self::OPTION_LOGO_URL => '',
        self::OPTION_MAX_PLACES => 10,
        self::OPTION_MAX_ROWS => 30,
        self::OPTION_NOT_NUMS => '/(_no|_id|phone)[_\s]*$/i',
        self::OPTION_PAGE_TITLE => 'Reports',
        #self::OPTION_PARAMS   => array(),
        #was groupby=month, totals=1
        self::OPTION_ROW_COLUMN => true,
        self::OPTION_SQL => '',
        #self::OPTION_TITLE    => '',
        self::OPTION_TOTALS => true,
        self::OPTION_URLS => array()
    );

    /**
     * @var double
     */
    protected $elapsed0 = 0.0;

    /**
     * @var double
     */
    protected $elapsed1 = 0.0;

    /**
     * @var double
     */
    protected $elapsed2 = 0.0;

    /**
     * @var int
     */
    protected $foundRows = 0;

    /**
     * @var array
     */
    protected static $groupbys = array(
        '--Select--' => '',
        'Year' => '%Y',
        'Month' => '%Y-%m',
        'Week' => '%Y Week %U',
        'Day' => '%Y-%m-%d',
        'Hour' => '%Y-%m-%d %H',
        'Minute' => '%Y-%m-%d %H:%i',
        'Second' => '%Y-%m-%d %H:%i:%s'
    );

    /**
     * @var string
     */
    protected $lastError = '';

    /**
     * @var null|object
     */
    protected $metadata = null;

    /**
     * @var string
     */
    protected $modifiedSql = '';

    /**
     * @var string
     */
    # protected $pageTitle = 'Reports';

    /**
     * The original params
     *
     * @var array
     */
    protected $_params = array();

    /**
     * @var array
     */
    # protected $params = array();

    /**
     * @var array
     */
    static protected $sqlKeywords = array(
        'select' => 'SELECT ',
        'sql' => '',
        'where' => "\nWHERE ",
        'groupby' => "\nGROUP BY ",
        'having' => "\nHAVING ",
        'orderby' => "\nORDER BY ",
        'limit' => "\nLIMIT "
    );

    /**
     * @var array
     */
    protected $sqls = array();

    /**
     * @var string
     */
    # protected $title = '';

    /**
     * @var array
     */
    # protected $urls = array();

    /**
     * @var null|object
     */
    protected $stmt = null;

    /**
     * @param  array $options (Optional) Options to set
     * @return boolean
     */
    protected function _getOption($key, $default = null)
    {
        $key = (string) $key;

        if (array_key_exists($key, $this->_options)) {
            return $this->_options[$key];
        }

        switch ($key) {
            case self::OPTION_LOGGER:
                if (class_exists('Zend_Registry')
                    && Zend_Registry::isRegistered('logger')) {
                    $this->_options[self::OPTION_LOGGER] = Zend_Registry::get('logger');
                } else {
                    #require_once 'Rasa/Logger.php';
                    $this->_options[self::OPTION_LOGGER] = new Rasa_Logger();
                }

                return $this->_options[self::OPTION_LOGGER];

            case self::OPTION_PARAMS:
                $this->_options[self::OPTION_PARAMS] = $_REQUEST;
                return $this->_options[self::OPTION_PARAMS];

            case self::OPTION_TITLE:
                $name = 'Untitled';
                if (isset($_SERVER['SCRIPT_FILENAME'])) {
                    $name = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_BASENAME);
                }
                if (isset($_SERVER['REDIRECT_URL'])) {
                    $name = pathinfo($_SERVER['REDIRECT_URL'], PATHINFO_BASENAME);
                }
                $name                               = preg_replace('|[\-_]|', ' ', $name);
                $name                               = ucwords($name);
                $this->_options[self::OPTION_TITLE] = $name;
                return $this->_options[self::OPTION_TITLE];
        }

        return $default;
    }

    /**
     * Set the options from an array object
     *
     * @param  array $options (Optional) Options to set
     * @return boolean
     */
    protected function _setOption($key, $value)
    {
        $logger = $this->getOption(self::OPTION_LOGGER);

        if (!array_key_exists($key, $this->defaults)) {
            throw new Rasa_Exception(sprintf("Unexpected option key: '%s'", $key));
        }

        switch ($key) {
            case self::OPTION_PARAMS:
                $this->_params = $value;

                $params = $value;

                $params = array_merge($this->defaults['params'], $params);

                /*
                if (isset($params['params'])) {
                $p = unserialize($params['params']);
                unset($params['params']);
                foreach($p as $k => $v) {
                if (!isset($params[$k])) {
                $params[$k] = $v;
                }
                }

                #$params = array_merge($params, $p);
                }
                */
                if (isset($params['groupby'])) {
                    foreach (self::$groupbys as $k => $v) {
                        if (strcasecmp($params['groupby'], $k) == 0) {
                            $params['groupby'] = $v;
                        }
                    }
                }

                $this->_options[self::OPTION_PARAMS] = $params;
                break;

            case self::OPTION_SQL:
                $sql = $value;

                if (is_array($sql)) {
                    $this->sqls = $sql;
                } elseif (is_string($sql)) {
                    $this->sqls = array();

                    $sqls = preg_split('/;/', $sql);

                    foreach ($sqls as $s) {
                        $s = trim($s);
                        if (!$s) {
                            continue;
                        }
                        $this->sqls[] = $s;
                    }
                } else {
                    throw new Exception(
                        sprintf(
                            'setSql() accepts string or array, but found %s',
                            gettype($sql)
                        )
                    );
                }

                $params = $this->getOption(self::OPTION_PARAMS);
                if (!isset($params['groupby'])) {
                    $sqlss   = $this->getSql();
                    $groupby = $this->getGroupBy($sqlss);
                    if ($groupby) {
                        $this->_options['params']['groupby'] = $groupby;
                    }
                }
                break;
        }

        return $value;
    }

    /**
     * @return boolean
     */
    protected function init($options = array())
    {
        $this->_options = $this->defaults;
        return true;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param string $lastError
     * @return object $this
     */
    public function setLastError($lastError)
    {
        $this->lastError = $lastError;
        return $this;
    }

    /**
     * @return object
     */
    public function getLogger()
    {
        return $this->getOption(self::OPTION_LOGGER);
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->getOption(self::OPTION_PAGE_TITLE);
    }

    /**
     * @param string $pageTitle
     * @return object $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->setOption(self::OPTION_PAGE_TITLE, $pageTitle);
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->getOption(self::OPTION_PARAMS);
    }

    /**
     * @param array $params
     * @return object $this
     */
    /*
    public function setParams($params) {
    $this->_params = $params;

    $params = array_merge($this->defaults, $params);

    if (isset($params['params'])) {
    $p = unserialize($params['params']);
    unset($params['params']);
    foreach($p as $k => $v) {
    if (!isset($params[$k])) {
    $params[$k] = $v;
    }
    }

    #$params = array_merge($params, $p);
    }

    if (isset($params['groupby'])) {
    foreach(self::$groupbys as $k => $v) {
    if (strcasecmp($params['groupby'], $k) == 0) {
    $params['groupby'] = $v;
    }
    }
    }

    $this->params = $params;
    return $this;
    }
    */

    /**
     * @return string
     */
    public function getLastSql()
    {
        if (count($this->sqls) == 0) {
            return false;
        }
        return $this->sqls[count($this->sqls) - 1];
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return join(";\n", $this->sqls);
    }

    /**
     * @param string $sql
     * @return object $this
     */
    public function setSql($sql)
    {
        $this->setOption(self::OPTION_SQL, $sql);
        /*
        if (is_array($sql)) {
        $this->sqls = $sql;
        } elseif (is_string($sql)) {
        $this->sqls = array();

        $sqls = preg_split('/;/', $sql);

        foreach ($sqls as $s) {
        $s = trim($s);
        if (!$s) {
        continue;
        }
        $this->sqls[] = $s;
        }
        } else {
        throw new Exception(sprintf('setSql() accepts string or array, but found %s', gettype($sql)));
        }

        if (!isset($this->_params['groupby'])) {
        $sqlss = join(";\n", $this->sqls);
        $groupby = $this->getGroupBy($sqlss);
        if ($groupby) {
        $this->params['groupby'] = $groupby;
        }
        }

        return $this;
        */
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getOption(self::OPTION_TITLE);
    }

    /**
     * @param string $title
     * @return object $this
     */
    public function setTitle($title)
    {
        $this->setOption(self::OPTION_TITLE, $title);
        return $this;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->getOption(self::OPTION_URLS);
    }

    /**
     * @param array $urls
     * @return object $this
     */
    public function setUrls($urls)
    {
        $this->setOption(self::OPTION_URLS, $urls);
        return $this;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $title
     * @return void
     */
    /*
    public function __construct($options = array())
    {
    if (!isset($options['params'])) {
    $options['params'] = $_REQUEST;
    }

    if (!isset($options['title'])) {
    $name = 'Untitled';
    if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $name = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME);
    }
    if (isset($_SERVER['REDIRECT_URL'])) {
    $name = pathinfo($_SERVER['REDIRECT_URL'], PATHINFO_FILENAME);
    }
    $options['title'] = ucwords(preg_replace('|[\-_]|', ' ', $name));
    }

    $this->setParams($options['params']);
    $this->setTitle($options['title']);
    }
    */

    /**
     * @return object
     */
    protected function getConnection()
    {
        static $rv = null;

        if ($rv === null) {
            #require_once 'Rasa/Db/Connector.php';

            $rv = Rasa_Db_Connector::getDefaultConnection();

            #$connector->setDatabase('default');
            #$connector->setConfigFile(dirname(dirname(__DIR__)) . '/app/configs/application.ini');

            #$rv = $connector->getZendDbDefaultConnection();
        }

        return $rv;
    }

    /**
     * @return void
     */
    protected function getData($sql)
    {
        $conn = $this->getConnection();

        try {
            $stmt = $conn->query($sql);
        }
        catch (Exception $e) {
            $msg = sprintf('Error %s: %s', $e->getCode(), $e->getMessage());
            $logger->err($msg);
            $this->setLastError($msg);
            $stmt = null;
            return false;
        }

        if (!$stmt) {
            $msg = sprintf('Error %s: %s', $conn->errno, $conn->error);
            $logger->err($msg);
            $this->setLastError($msg);
            return false;
        }

        $rows = array();

        if (is_callable(array($stmt, 'fetch_all'))) {
            $rows = $stmt->fetch_all(MYSQLI_ASSOC);
        } else {
            for ($rows = array(); $tmp = $stmt->fetch_array(MYSQLI_ASSOC);) {
                $rows[] = $tmp;
            }
        }

        return $rows;
    }

    /**
     * @return void
     */
    protected function setMetadata()
    {
        static $numbers = array(
            'MYSQLI_TYPE_BIT',
            'MYSQLI_TYPE_CHAR', # actually TINYINT
            'MYSQLI_TYPE_DECIMAL',
            'MYSQLI_TYPE_DOUBLE',
            'MYSQLI_TYPE_FLOAT',
            'MYSQLI_TYPE_INT24',
            'MYSQLI_TYPE_LONG',
            'MYSQLI_TYPE_LONGLONG',
            'MYSQLI_TYPE_NEWDECIMAL',
            'MYSQLI_TYPE_SHORT',
            'MYSQLI_TYPE_TINY'
            #'MYSQLI_TYPE_YEAR',
        );

        $doubles = array(
            'MYSQLI_TYPE_DECIMAL',
            'MYSQLI_TYPE_DOUBLE',
            'MYSQLI_TYPE_FLOAT',
            'MYSQLI_TYPE_NEWDECIMAL'
        );

        static $alignRight = array(
            'MYSQLI_TYPE_BIT', # => 'right',
            'MYSQLI_TYPE_CHAR', # actually TINYINT
            'MYSQLI_TYPE_DATE', # => 'right',
            'MYSQLI_TYPE_DATETIME', # => 'right',
            'MYSQLI_TYPE_DECIMAL', # => 'right',
            'MYSQLI_TYPE_DOUBLE', # => 'right',
            'MYSQLI_TYPE_FLOAT', # => 'right',
            'MYSQLI_TYPE_INT24', # => 'right',
            'MYSQLI_TYPE_INTERVAL', # => 'right',
            'MYSQLI_TYPE_LONG', # => 'right',
            'MYSQLI_TYPE_LONGLONG', # => 'right',
            'MYSQLI_TYPE_NEWDATE', # => 'right',
            'MYSQLI_TYPE_NEWDECIMAL', # => 'right',
            'MYSQLI_TYPE_NULL', # => 'right',
            'MYSQLI_TYPE_SHORT', # => 'right',
            'MYSQLI_TYPE_TIME', # => 'right',
            'MYSQLI_TYPE_TIMESTAMP', # => 'right',
            'MYSQLI_TYPE_TINY', # => 'right',
            'MYSQLI_TYPE_YEAR' # => 'right',
            #'MYSQLI_TYPE_BLOB' => 'left',
            #'MYSQLI_TYPE_GEOMETRY' => 'left',
            #'MYSQLI_TYPE_LONG_BLOB' => 'left',
            #'MYSQLI_TYPE_MEDIUM_BLOB' => 'left',
            #'MYSQLI_TYPE_SET' => 'left',
            #'MYSQLI_TYPE_STRING' => 'left', # ACTUALLY CHAR
            #'MYSQLI_TYPE_TINY_BLOB' => 'left',
            #'MYSQLI_TYPE_VAR_STRING' => 'left',
        );

        if (!$this->stmt) {
            return false;
        }

        $a = get_defined_constants('mysqli');
        $a = $a['mysqli'];

        $types = array();
        $flags = array();

        foreach ($a as $k => $v) {
            if (preg_match('/_TYPE_/', $k)) {
                $types[$v] = $k;
            }
            if (preg_match('/_FLAG$/', $k)) {
                $flags[$v] = $k;
            }
        }

        ksort($types);
        ksort($flags);

        $fields = $this->stmt->fetch_fields();

        $max_places = $this->getOption(self::OPTION_MAX_PLACES);

        $not_nums = $this->getOption(self::OPTION_NOT_NUMS);

        $tables = array();

        for ($i = 0; $i < count($fields); ++$i) {

            if ($fields[$i]->orgtable) {
                if (isset($fields[$i]->db)) {
                    $tables[$fields[$i]->db . '.' . $fields[$i]->orgtable] =
                        sprintf("('%s','%s')", $fields[$i]->db, $fields[$i]->orgtable);
                } else {
                    $tables[$fields[$i]->orgtable] = sprintf("('','%s')", $fields[$i]->orgtable);
                }
            }

            $fields[$i]->field_type = $types[$fields[$i]->type];
            $fields[$i]->numeric    = in_array($fields[$i]->field_type, $numbers);
            $fields[$i]->double     = in_array($fields[$i]->field_type, $doubles);
            $fields[$i]->alignment  = in_array($fields[$i]->field_type, $alignRight)
                ? 'right'
                : 'left';

            $fields[$i]->format = false;

            $fields[$i]->places = 0;

            if ($fields[$i]->numeric) {
                $fields[$i]->format = true;
                if ($fields[$i]->decimals > 0) {
                    $fields[$i]->places = $fields[$i]->decimals > $fields[$i]->max_length
                        ? $fields[$i]->max_length
                        : $fields[$i]->decimals;
                    $fields[$i]->places = min($fields[$i]->places, $max_places);
                }
            }

            if (preg_match($not_nums, $fields[$i]->name)) {
                $fields[$i]->format = false;
            }

            $fields[$i]->flag = array();
            foreach ($flags as $k => $v) {
                $fields[$i]->flag[$v] = ($fields[$i]->flags & $k) == $k;
            }
        }

        if ($tables) {
            $tables_sql = join(",\n", array_values($tables));

            $sql  = <<<EOT
SELECT
table_schema,
table_name,
column_name,
index_type,
seq_in_index
FROM
information_schema.statistics
WHERE
(table_schema, table_name) IN (
$tables_sql
)
ORDER BY
table_schema,
table_name,
column_name,
index_type,
seq_in_index
EOT;
            $rows = $this->getData($sql);

            $indexes = array();
            foreach ($rows as $row) {
                if (!isset($indexes[$row['table_schema']])) {
                    $indexes[$row['table_schema']] = array();
                }
                if (!isset($indexes[$row['table_schema']][$row['table_name']])) {
                    $indexes[$row['table_schema']][$row['table_name']] = array();
                }

                $indexes[$row['table_schema']][$row['table_name']][$row['column_name']] = array(
                    'type' => $row['index_type'],
                    'seq' => $row['seq_in_index']
                );
            }

            for ($i = 0; $i < count($fields); ++$i) {
                $db                         = isset($fields[$i]->db)
                    ? $fields[$i]->db
                    : '';
                $fields[$i]->index_type     = isset($indexes[$db][$fields[$i]->orgtable][$fields[$i]->orgname])
                    ? $indexes[$db][$fields[$i]->orgtable][$fields[$i]->orgname]['type']
                    : false;
                $fields[$i]->index_sequence = isset($indexes[$db][$fields[$i]->orgtable][$fields[$i]->orgname])
                    ? $indexes[$db][$fields[$i]->orgtable][$fields[$i]->orgname]['seq']
                    : false;
            }
        }

        $this->metadata = $fields;

        return true;
    }

    /**
     * @param int $fieldNo
     * @return boolean
     */
    protected function isFieldNumeric($fieldNo)
    {
        if (!$this->metadata) {
            return false;
        }

        if (!isset($this->metadata[$fieldNo])) {
            return false;
        }

        return $this->metadata[$fieldNo]->numeric;
    }

    /**
     * @param int $fieldNo
     * @return boolean
     */
    protected function isAnyFieldNumeric()
    {
        if (!$this->metadata) {
            return false;
        }

        foreach ($this->metadata as $k => $field) {
            if ($field->numeric) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $fieldNo
     * @return boolean
     */
    protected function isFieldCalculated($fieldNo)
    {
        if (!$this->metadata) {
            return false;
        }

        if (!isset($this->metadata[$fieldNo])) {
            return false;
        }

        $field = $this->metadata[$fieldNo];

        return $field->orgname == '';
    }

    /**
     * @param int $fieldNo
     * @return string
     */
    protected function getFieldName($fieldNo)
    {
        if (!$this->metadata) {
            return false;
        }

        if (!isset($this->metadata[$fieldNo])) {
            return false;
        }

        $field = $this->metadata[$fieldNo];

        if ($field->table > '') {
            return sprintf('`%s`.', $field->table) . sprintf('`%s`', $field->orgname);
        }

        return $field->name;
    }

    /**
     * @param int $fieldNo
     * @return string
     */
    protected function getFieldId($fieldName)
    {
        if (!$this->metadata) {
            return false;
        }

        foreach ($this->metadata as $fieldNo => $field) {
            if (strcasecmp($fieldName, $field->name) == 0) {
                return $fieldNo;
            }
        }
        return -1;
    }

    /**
     * @param int $fieldNo
     * @return string
     */
    protected function getFieldCount()
    {
        return $this->metadata ? count($this->metadata) : 0;
    }

    /**
     * @param string $sql
     * @return array
     */
    public function parseSql($sql)
    {
        $rv = array();

        foreach (self::$sqlKeywords as $keyword => $v) {
            $rv[$keyword] = '';
        }

        $c = chr(138);

        $from = "\n";
        $to   = ' ' . $c . ' ';

        $sql = preg_replace('/\r/', '', $sql);
        $sql = preg_replace('/' . $from . '/', $to, $sql);

        # doesn't support offset
        if (preg_match('/(.*)(\bLIMIT\b)([\s\d,' . $c . ']+)$/i', $sql, $m)) {
            $rv['limit'] = $m[3];
            $sql         = $m[1];
        }


        if (preg_match('/(.*)(\bORDER\s+BY\b)(\s+.*)$/i', $sql, $m)) {
            $rv['orderby'] = $m[3];
            $sql           = $m[1];
        }


        if (preg_match('/(.*)(\bHAVING\b)(\s+.*)$/i', $sql, $m)) {
            $rv['having'] = $m[3];
            $sql          = $m[1];
        }


        if (preg_match('/(.*)(\bGROUP\s+BY\b)(\s+.*)$/i', $sql, $m)) {
            $rv['groupby'] = $m[3];
            $sql           = $m[1];
        }


        if (preg_match('/(.*)(\bWHERE\b)(\s+.*)$/i', $sql, $m)) {
            $rv['where'] = $m[3];
            $sql         = $m[1];
        }

        if (preg_match('/^\s*SELECT\b(.*)/i', $sql, $m)) {
            $rv['select'] = $m[1];
        } else {
            $rv['sql'] = $sql;
        }

        foreach ($rv as $k => $v) {
            $rv[$k] = preg_replace('|' . $to . '|', $from, $v);
        }

        return $rv;
    }

    /**
     * @param array $parts
     * @return string
     */
    public function buildSql($parts)
    {
        $rv = '';

        foreach (self::$sqlKeywords as $k => $v) {
            if (isset($parts[$k]) && trim($parts[$k]) > '') {
                $rv .= $v . $parts[$k];
            }
        }

        return $rv;
    }

    /**
     * @return boolean
     */
    protected function parseUrl($title = null, $pageTitle = null, $url = null)
    {
        if ($pageTitle === null) {
            $pageTitle = $this->getOption(self::OPTION_PAGE_TITLE);
        }

        if ($url === null) {
            $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }

        $p    = parse_url($url);
        $path = $p['path'];

        if (isset($p['query'])) {
            parse_str($p['query'], $qs);
        } else {
            $qs = array();
        }
        unset($qs['go']);
        $url = $qs ? ($path . '?' . http_build_query($qs)) : $path;

        $links  = array();
        $titles = array();

        $n = 0;
        while ($path > '/') {
            $name = rtrim($path, '/');
            $name = urldecode($name);
            if ($n == 0 && $title) {
                $name = $title;
            } else {
                $name = basename($path, '.php');
            }
            $name     = preg_replace('/[\-_]+/', ' ', $name);
            $name     = ucwords($name);
            $titles[] = $name;
            $_url     = $n == 0 ? $url : $path;

            $links[] = sprintf('<a href="%s">%s</a>', $_url, $name);

            $path = dirname($path);
            ++$n;
        }

        $links[] = sprintf('<a href="/">%s</a>', $pageTitle);

        $titles[] = $pageTitle;

        $links = array_reverse($links);

        $links = join(" &gt; ", $links);
        $title = join(' - ', $titles);

        return array(
            'links' => $links,
            'title' => $title
        );
    }

    /**
     * @return boolean
     */
    public function getPageLinks($title = null, $pageTitle = null, $url = null)
    {
        $a = $this->parseUrl($title, $pageTitle, $url);
        return $a['links'];
    }

    /**
     * @return boolean
     */
    public function getPageTitles($title = null, $pageTitle = null, $url = null)
    {
        $a = $this->parseUrl($title, $pageTitle, $url);
        return $a['title'];
    }

    /**
     * @param string $sql
     * @return boolean
     */
    protected function hasGroupBy($sql)
    {
        return preg_match("|/\*\s*groupby\s*=\s*\*/\s*'[^']+'|i", $sql)
            || preg_match('|/\*\s*groupby\s*=\s*\*/\s*"[^"]+"|i', $sql);
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function getGroupBy($sql)
    {
        if (preg_match("|/\*\s*groupby\s*=\s*\*/\s*'([^']+)'|i", $sql, $m)) {
            return $m[1];
        }
        if (preg_match('|/\*\s*groupby\s*=\s*\*/\s*"([^"]+)"|i', $sql)) {
            return $m[1];
        }

        return '';
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function substituteGroupBy($sql)
    {
        $params = $this->getOption(self::OPTION_PARAMS);

        if (isset($params['groupby']) && $params['groupby']) {

            foreach (self::$groupbys as $k => $v) {
                if (strcasecmp($params['groupby'], $k) == 0) {
                    $params['groupby'] = $v;
                }
            }

            $groupby = "'" . $params['groupby'] . "'";

            if (preg_match("|/\*\s*groupby\s*=\s*\*/\s*'[^']+'|i", $sql)) {
                $sql = preg_replace("|/\*\s*groupby\s*=\s*\*/'[^']+'|i", $groupby, $sql);
            }

            if (preg_match('|/\*\s*groupby\s*=\s*\*/\s*"[^"]+"|i', $sql)) {
                $sql = preg_replace('|/\*\s*groupby\s*=\s*\*/"[^"]+"|i', $groupby, $sql);
            }
        }

        return $sql;
    }

    /**
     * @return boolean
     */
    protected function runSqls()
    {
        $conn = $this->getConnection();

        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $sqls = $this->sqls;

        array_pop($sqls);

        $this->elapsed0 = 0.0;

        if (count($sqls) < 1) {
            return true;
        }

        foreach ($sqls as $sql) {
            $sql = $this->substituteGroupBy($sql);

            $startTime = microtime(true);

            try {
                $rv = $conn->query($sql);
            }
            catch (Exception $e) {
                $msg = sprintf('Error %s: %s', $e->getCode(), $e->getMessage());
                $logger->err($msg);
                $this->setLastError($msg);
                return false;
            }

            $this->elapsed0 += microtime(true) - $startTime;

            if (!$rv) {
                $conn = $this->getConnection();
                if ($conn->errno <> 0) {
                    $msg = sprintf('Error %s: %s', $conn->errno, $conn->error);
                    $logger->err($msg);
                    $this->setLastError($msg);
                }
                return false;
            }
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function calculateMetadata()
    {
        $logger = $this->getOption(self::OPTION_LOGGER);

        $sql = $this->getLastSql();

        $parts          = $this->parseSql($sql);
        $parts['limit'] = '0';
        $sql0           = $this->buildSql($parts);

        $conn = $this->getConnection();

        try {
            $stmt = $conn->query($sql0);
        }
        catch (Exception $e) {
            $msg = sprintf('Error %s: %s', $e->getCode(), $e->getMessage());
            $logger->err($msg);
            $this->setLastError($msg);
            $this->stmt = null;
            return false;
        }

        $this->stmt = $stmt;

        if (!$stmt) {
            $conn = $this->getConnection();
            if ($conn->errno <> 0) {
                $msg = sprintf('Error %s: %s', $conn->errno, $conn->error);
                $logger->err($msg);
                $this->setLastError($msg);
            }
            return false;
        }

        $this->setMetadata();

        return true;
    }

    /**
     * @param string $v
     * @param string $fieldName
     * @return boolean
     */
    protected function getSqlClauses($v, $k)
    {
        $conn   = $this->getConnection();
        $logger = $this->getOption(self::OPTION_LOGGER);

        $fieldName = $this->getFieldName($k);
        $metadata  = isset($this->metadata[$k]) ? $this->metadata[$k] : array();

        $fulltext = isset($metadata->index_type)
            && preg_match('/FULLTEXT/i', $metadata->index_type);

        $wheres = array();

        $v = trim($v);
        if (strlen($v)) {
            $choices = preg_match('|^[/<>=]{1,2}.*,|', $v)
                ? preg_split('/,/', $v)
                : array($v);
            foreach ($choices as $v2) {
                $v2 = trim($v2);

                while (true) {
                    if (preg_match('|^/|', $v2)) {
                        $fulltext = false;
                        $op       = 'REGEXP';
                    } elseif (preg_match('|[\*\?]|', $v2)) {
                        if (preg_match('|^[\*\?]|', $v2)) {
                            $fulltext = false;
                        }
                        $v2 = $fulltext
                            ? preg_replace(array('/\*/', '/\?/'), array('*', '*'), $v2)
                            : preg_replace(array('/\*/', '/\?/'), array('%', '_'), $v2);
                        $op = 'LIKE';
                    } elseif (preg_match('|^~(.*)|', $v2, $m)) {
                        $op = 'SOUNDS LIKE';
                        $v2 = $m[1];
                        if (preg_match('|^[\*\?]|', $v2)) {
                            $fulltext = false;
                        }
                        $v2 = $fulltext
                            ? preg_replace(array('/\*/', '/\?/'), array('*', '*'), $v2)
                            : $v2;
                    } else {
                        $op = '=';
                    }
                    break;
                }

                if (preg_match('|^/(.*)|', $v2, $m)) {
                    $v2 = $m[1];

                    $v2 = rtrim($v2, '/');

                    $q = $conn->real_escape_string($v2);

                    $n = 0;
                    static $wordBoundries = array('[[:<:]]', '[[:>:]]');
                    while (preg_match('|\\\\\\\\b|', $q)) {
                        $q = preg_replace('|\\\\\\\\b|', $wordBoundries[$n], $q, 1);
                        $n = ++$n % 2;
                    }

                    static $map = array(
                        '|\\\\\\\\d|' => '[0-9]',
                        '|\\\\\\\\D|' => '[^0-9]',
                        '|\\\\\\\\s|' => '[\t\n\r ]',
                        '|\\\\\\\\S|' => '[^\t\n\r ]',
                        '|\\\\\\\\w|' => '[a-zA-Z0-9]',
                        '|\\\\\\\\W|' => '[^a-zA-Z0-9]'
                    );

                    $q = preg_replace(array_keys($map), array_values($map), $q);

                } elseif (preg_match('/^([<>=]{1,2})(.*)/', $v2, $m)) {
                    $op = $m[1];
                    $v2 = $m[2];
                    $q  = $conn->real_escape_string($v2);
                } else {
                    $q = $conn->real_escape_string($v2);
                }

                $q = sprintf('"%s"', $q);

                $wheres[] = $fulltext
                    ? sprintf("MATCH(%s) AGAINST (%s IN BOOLEAN MODE)", $fieldName, $q)
                    : sprintf("%s %s %s ", $fieldName, $op, $q);
            }
        }

        return $wheres;
    }

    /**
     * @return int
     */
    protected function getRows()
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $rows = isset($params['rows'])
            ? intval($params['rows'])
            : $this->defaults[self::OPTION_MAX_ROWS];

        if ($rows < 0) {
            $rows = 0;
        }

        if ($rows > 10000) {
            $rows = 10000;
        }

        return $rows;
    }

    /**
     * @return int
     */
    protected function getRecords()
    {
        if ($this->foundRows) {
            return $this->foundRows;
        }

        if (!isset($_SESSION[__CLASS__])) {
            $_SESSION[__CLASS__] = array();
        }

        if (isset($_SESSION[__CLASS__]['records'])) {
            return $_SESSION[__CLASS__]['records'];
        }

        return 0;

        /*
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $rv = isset($params['records'])
        ? intval($params['records'])
        : 0;

        if ($rv < 0) {
        $rv = 0;
        }

        return $rv;
        */
    }

    /**
     * @return int
     */
    protected function getPages()
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $rows = $this->getRows();

        $pages = 0;

        $records = $this->getRecords();

        if ($rows > 0) {
            $pages = $records / $rows;
            if ($pages <> intval($pages)) {
                $pages = intval($pages) + 1;
            }
        }

        return $pages;
    }

    /**
     * @return int
     */
    protected function getPage()
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $page = isset($params['page']) ? intval($params['page']) : 1;

        if ($page < 1) {
            $page = 1;
        }

        $pages = $this->getPages();

        if (isset($params['go'])) {
            switch (strtolower($params['go'])) {
                case 'first':
                    $page = 1;
                    break;
                case 'prev10':
                    $page = max($page - 10, 1);
                    break;
                case 'prev':
                    $page = max($page - 1, 1);
                    break;
                case 'next':
                    $page = $page + 1;
                    break;
                case 'next10':
                    $page = $page + 10;
                    break;
                case 'last':
                    $page = $pages ? $pages : $page + 1;
                    break;
                default:
            }
        }

        if ($pages > 0) {
            $page = min($page, $pages);
        }

        return $page;
    }

    /**
     * @param string $s
     * @param array $values
     * @return boolean
     */
    static protected function _is($s, $values)
    {
        foreach ($values as $value) {
            if (strcasecmp($value, $s) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $s
     * @param boolean $default
     * @return boolean
     */
    static public function is($s, $default = false)
    {
        static $trues = array('y', 'yes', '1', 'on', 't', 'true');

        static $falses = array('n', 'no', '0', 'off', 'f', 'false');

        if (static::_is($s, $trues)) {
            return true;
        }

        if (static::_is($s, $falses)) {
            return false;
        }

        return $default;
    }

    /**
     * @param string sql
     * @return string
     */
    protected function addSearchClauses($sql)
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $parts = $this->parseSql($sql);
        if (isset($params['orderby']) && $params['orderby'] > '') {
            $order_by = sprintf('`%s`', $params['orderby']);
            if (isset($params['desc']) && $params['desc']) {
                $order_by .= ' DESC';
            }
            $parts['orderby'] = $parts['orderby']
                ? $order_by . ', ' . $parts['orderby']
                : $order_by;

            if (preg_match('|^(.*)WITH\s+ROLLUP|im', $parts['groupby'], $m)) {
                $parts['groupby'] = $m[1];
            }
        } else {
            if (isset($params['subtotals'])) {
                if (static::is($params['subtotals'])) {
                    $params['totals'] = '';
                    if ($parts['groupby'] && !preg_match('/\bWITH\s+ROLLUP\b\s*/i', $parts['groupby'])) {
                        $parts['groupby'] .= ' WITH ROLLUP';
                    }
                    $parts['orderby'] = '';
                } elseif (!static::is($params['subtotals'], true)) {
                    if (preg_match('|^(.*)WITH\s+ROLLUP|im', $parts['groupby'], $m)) {
                        $parts['groupby'] = $m[1];
                    }
                }
            }
        }

        #@todo fixme
        $rows  = $this->getRows();
        $page  = $this->getPage();
        $pages = $this->getPages();

        $offset = ($page - 1) * $rows;

        $parts['limit'] = $page > 1 ? $offset . ', ' . $rows : $rows;

        if ($parts['select']) {
            if (!preg_match('/SQL_CALC_FOUND_ROWS/i', $parts['select'])) {
                $parts['select'] = ' /*!SQL_CALC_FOUND_ROWS*/ ' . $parts['select'];
            }
        }

        if (isset($params['f']) && count($params['f']) > 0) {
            $wheres  = array();
            $havings = array();

            foreach ($params['f'] as $fieldName => $v) {
                $k = $this->getFieldId($fieldName);

                if ($this->isFieldCalculated($k)) {
                    $rv = $this->getSqlClauses($v, $k);
                    if ($rv) {
                        $havings = array_merge($havings, $rv);
                    }
                } else {
                    $rv = $this->getSqlClauses($v, $k);
                    if ($rv) {
                        $wheres = array_merge($wheres, $rv);
                    }
                }
            }

            if ($wheres) {
                if ($parts['where']) {
                    $parts['where'] = rtrim($parts['where']) . " AND\n\t";
                }
                $parts['where'] .= join(" AND\n\t", $wheres) . "\n";
            }

            if ($havings) {
                if (!$parts['groupby']) {
                    $fieldCount = $this->getFieldCount();
                    if ($fieldCount) {
                        $a = array();
                        for ($i = 1; $i <= $fieldCount; ++$i) {
                            $a[] = $i;
                        }
                        $parts['groupby'] = join(', ', $a);
                    }
                }
                if ($parts['having']) {
                    $parts['having'] = rtrim($parts['having']) . " AND\n\t";
                }
                $parts['having'] .= join(" AND\n\t", $havings) . "\n";
            }
        }

        $sql = $this->buildSql($parts);

        return $sql;
    }

    /**
     * @param int $rows
     * @return array
     */
    public function addTotalRow($rows)
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        if (isset($params['subtotals']) && static::is($params['subtotals'])) {
            $this->setOption(self::OPTION_TOTALS, false);
            return $rows;
        }
        #   if (!$rows) {
        #     return $rows;
        #   }

        #   $midnight = array();
        $numeric = array();
        $totals  = array();
        $row     = array();

        foreach ($this->metadata as $i => $field) {
            $name           = $field->name;
            #     $midnight[$name] = 0;
            $totals[$name]  = 0;
            $row[$name]     = '';
            $numeric[$name] = preg_match('/(_no|_id|phone)[\s_]*$/i', $name)
                ? false
                : $this->isFieldNumeric($i);
            if (preg_match('/^(#|row)$/', $name)) {
                $numeric[$name] = false;
            }
        }

        $rowCount = count($rows);

        $max_places = $this->getOption(self::OPTION_MAX_PLACES);

        $diff = 1;
        for ($i = 1; $i < $max_places; ++$i) {
            $diff /= 10;
        }
        $diff = round($diff, $max_places);

        for ($i = 0; $i < $rowCount; ++$i) {
            foreach ($rows[$i] as $k => $v) {
                if ($numeric[$k] && is_numeric($v)) {
                    if (abs($v - round($v, $max_places)) >= $diff) {
                        $rows[$i][$k] = round($v, $max_places);
                    }
                    $totals[$k] += $v;
                }

                #       if (preg_match('| 00:00:00$|', $v)) {
                #         ++$midnight[$k];
                #       }
            }
        }

        /*
        foreach ($midnight as $k => $v) {
        if ($v == $rowCount) {
        for ($i = 0; $i < $rowCount; ++$i) {
        $rows[$i][$k] = substr($rows[$i][$k], 0, -9);
        }
        }
        }
        */

        $_totals = isset($params['totals'])
            ? $params['totals']
            : $this->getOption(self::OPTION_TOTALS);

        if ($_totals) {
            $n = 0;
            foreach ($numeric as $k => $v) {
                if (is_numeric($totals[$k])) {
                    if (abs($totals[$k] - round($totals[$k], $max_places)) >= $diff) {
                        $totals[$k] = round($totals[$k], $max_places);
                    }
                }
                $row[$k] = $v ? $totals[$k] : '';

                #if ($n == 0) {
                # $row[$k] = 'Totals: ' . $row[$k];
                #}
                ++$n;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param int $rows
     * @return string
     */
    public function getHtmlTable($rows)
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        $_rows = $this->getRows();
        $page  = $this->getPage();

        $offset = ($page - 1) * $_rows;

        $x = new Rasa_Reporter_Html();
        $x->setOptions($this->getOptions());
        $x->setLinks(true);
        $x->setMetadata($this->metadata);
        $x->setOffset($offset);
        $x->setParams($params);
        $x->setRows($rows);
        #$x->setTotals(isset($params['totals']) && static::is($params['totals']));
        $x->setTotals($this->getOption(self::OPTION_TOTALS));
        $x->setUrls($this->getOption(self::OPTION_URLS));
        $report = $x->getTable();

        return $report;
    }

    /**
     * @param int $rows
     * @return string
     */
    public function getFoundRows()
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        static $rv = null;

        while ($rv === null) {
            $sql = $this->modifiedSql;

            if (!preg_match('/SQL_CALC_FOUND_ROWS/i', $sql)) {
                $rv = 0;
                break;
            }

            $conn = $this->getConnection();
            try {
                $stmt = $conn->query('SELECT FOUND_ROWS()');
            }
            catch (Exception $e) {
                $msg = sprintf('Error %s: %s', $e->getCode(), $e->getMessage());
                $logger->err($msg);
                $this->setLastError($msg);
                return 0;
            }

            if (!$stmt) {
                if ($conn->errno <> 0) {
                    $msg = sprintf('Error %s: %s', $conn->errno, $conn->error);
                    $logger->err($msg);
                    $this->setLastError($msg);
                }

                return 0;
            }

            $a  = $stmt->fetch_array(MYSQLI_NUM);
            $rv = isset($a[0]) ? $a[0] : 0;
            break;
        }

        return $rv;
    }

    /**
     * @param array $data
     * @return string
     */
    public function outputHtml($data, $go = true)
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        static $yesno = array('Yes' => '1', 'No' => '0');

        static $noyes = array('No' => '0', 'Yes' => '1');

        $rows  = $this->getRows();
        $page  = $this->getPage();
        $pages = $this->getPages();

        $ofpages = $pages > 0 ? sprintf('of %d', $pages) : 'of ?';
        if ($ofpages) {
            $records = $this->getRecords();
            if ($records) {
                $offset      = ($rows * ($page - 1) + 1);
                $last_record = $offset + $rows - 1;
                $approx      = 'approximate ';
                if ($last_record >= $records) {
                    $records = $last_record;
                    $approx  = '';
                }

                $ofpages_title = sprintf('%s %stotal records', $records, $approx);
                $ofpages       = sprintf("<span title='%s'>%s</span>", $ofpages_title, $ofpages);
            }
        }

        $sql = $this->getLastSql();

        $sqlss = $this->getSql();

        static $disabled = ' disabled="disabled" class="disabled"';

        $groupby_disabled = $this->hasGroupby($sqlss) ? '' : $disabled;

        # @todo move to function
        $options = array();
        foreach (self::$groupbys as $k => $v) {
            $selected = isset($params['groupby'])
                    && (
                        (strcasecmp($k, $params['groupby']) == 0)
                        || (strcasecmp($v, $params['groupby']) == 0)
                    )
                ? ' selected="selected"'
                : '';
            if ($v) {
                $v = strtolower($k);
            }
            $options[] = sprintf("<option value='%s'%s>%s</option>", $v, $selected, $k);
        }
        $groupby = join("\n\t", $options);

        /*
        $totals_disabled = $this->isAnyFieldNumeric()
        ? ''
        : $disabled;
        */

        $totals_disabled = '';

        $options = array();
        foreach ($yesno as $k => $v) {
            $selected  =
                #(!isset($params['totals']) && !$v) ||
                (isset($params['totals']) && strcasecmp($params['totals'], $v) == 0)
                ? ' selected="selected"'
                : '';
            $options[] = sprintf("<option value='%s'%s>%s</option>", $v, $selected, $k);
        }
        $totals = join("\n\t", $options);


        $parts = $this->parseSql($sql);

        $subtotals_disabled = $parts['groupby'] > '' ? '' : $disabled;

        $subtotals = '';

        if (isset($params['subtotals'])) {
            $subtotals = $params['subtotals'] ? $noyes['Yes'] : $noyes['No'];
        }

        if (!isset($params['subtotals'])) {
            $parts = $this->parseSql($sqlss);
            if (preg_match('/WITH\s+ROLLUP/i', $parts['groupby'])) {
                $subtotals = $noyes['Yes'];
            }
        }

        $options = array(
            #sprintf("<option value='%s'%s>%s</option>", '', '', '--Select--')
                );
        foreach ($noyes as $k => $v) {
            $selected  =
            #(!isset($params['subtotals']) && !$v) ||
                (strcasecmp($subtotals, $v) == 0) ? ' selected="selected"' : '';
            $options[] = sprintf("<option value='%s'%s>%s</option>", $v, $selected, $k);
        }
        $subtotals = join("\n\t", $options);


        $format  = isset($params['format']) ? $params['format'] : '';
        $save_as = Rasa_Reporter_Select::getSelectHtml('format', $format);

        $title = $this->getOption(self::OPTION_TITLE);

        $titles = $this->getPageTitles($title);
        $links  = $this->getPageLinks($title);

        #   if (!$error && count($data) > 0) {
        $links .= ' <a href="#eop" title="Jump to the end of the page">&#x2935;</a>';
        #   }

        #   $paramValue = htmlspecialchars(serialize($params));

        $d = isset($params['d']) && $params['d'];

        $d_checked = $d ? " checked='checked'" : '';

        $first_disabled  = $page <= 1 ? $disabled : '';
        $prev10_disabled = $page <= 1 ? $disabled : '';
        $prev_disabled   = $page <= 1 ? $disabled : '';
        $next_disabled   = $page == $pages ? $disabled : '';
        $next10_disabled = $page == $pages ? $disabled : '';
        $last_disabled   = ($page == $pages) || ($pages == 0) ? $disabled : '';

        static $form_fields = array('orderby' => '', 'desc' => '');

        $hiddens = '';

        if (isset($params['orderby']) && $params['orderby']) {
            $hiddens .= sprintf("<input type='hidden' name='orderby' value='%s'/>\n", htmlspecialchars($params['orderby']));
        }

        if (isset($params['desc']) && $params['desc']) {
            $hiddens .= sprintf("\t\t<input type='hidden' name='desc' value='%s'/>\n", htmlspecialchars($params['desc']));
        }
        /*
        $p = parse_url($_SERVER['REQUEST_URI']);

        $qs = array_intersect_key($params, $form_fields);

        $url = $p['path'];

        if ($qs) {
        $url .= '?' . http_build_query($qs);
        }
        */

        $no_rows = !$data;

        $error = $this->getLastError() > '';

        $defaultMsg = !$go
            ? 'This report will take a long time to generate. Please click [Go] to generate the report.'
            : '';

        $footer = $error ? $this->getLastError() : $defaultMsg;

        $table = $this->getHtmlTable($data);

        $top_style = $error || $no_rows ? ' class="hidden"' : '';

        $records = $this->foundRows;

        $defaultRows = $this->defaults[self::OPTION_MAX_ROWS];

        if ($go) {
            $c = date('M. jS, Y \a\t g:i:sa T');
            $footer .= sprintf("<i style='font-size:smaller'>Generated at %s.</i> ", $c);

            $offset      = ($rows * ($page - 1) + 1);
            $last_record = $offset + $rows - 1;
            $approx      = 'approximate ';
            if ($last_record >= $records) {
                $records = $last_record;
                $approx  = '';
            }

            $total_records = $rows
                ? sprintf(
                    'Records %s of %s of %s %stotal records.',
                    $offset,
                    $offset + $rows - 1,
                    number_format($records, 0),
                    $approx
                    )
                : sprintf('%s %stotal records.', number_format($records, 0), $approx);

            $total_records = sprintf("<i style='font-size:smaller'>%s</i> ", $total_records);

            $footer .= $total_records;

            $elapsed = $this->elapsed0 + $this->elapsed1 + $this->elapsed2;

            $t = $elapsed
                ? sprintf(
                    "step 0: %.4f (%.1f%%)\nstep 1: %.4f (%.1f%%)\nstep 2: %.4f (%.1f%%)",
                    $this->elapsed0,
                    100 * $this->elapsed0 / $elapsed,
                    $this->elapsed1,
                    100 * $this->elapsed1 / $elapsed,
                    $this->elapsed2,
                    100 * $this->elapsed2 / $elapsed
                    )
                : '';

            $footer .= !$error && $elapsed
                ? sprintf(
                    ' <i style="font-size:smaller" title="%s">%.4f seconds.</i> ',
                    $t,
                    $elapsed
                    )
                : '';
        }

        $sqls = $this->sqls;

        array_pop($sqls);

        $sqls[] = $this->modifiedSql;

        $sqlx = join(";\n \n", $sqls);
        $sqlx = preg_replace("/\r+/", "\n", $sqlx);
        $sqlx = preg_replace("/\n+/", "\n", $sqlx);

        $_rows = substr_count($sqlx, "\n") + 2;
        $_cols = 80;
        $lines = preg_split("/\n/", $sqlx);
        foreach ($lines as $line) {
            $_cols = max($_cols, strlen($line) + 1);
        }

        $d = isset($params['d']) && $params['d'];

        #   if (!$error && $this->foundRows > 0) {
        #     $footer .= sprintf(' <i style="font-size:smaller">%s total rows.</i> ', number_format($this->foundRows, 0));
        #   }

        $footer .= $d
            ? sprintf(
                "<br/>\n<hr/>\n<pre><textarea rows='%s' cols='%s' readonly='readonly'>%s</textarea></pre>\n",
                $_rows,
                $_cols,
                htmlspecialchars($sqlx)
                )
            : '';

        $footer .= $d
            ? sprintf(
                "<br/>\n<hr/>\n<pre>params=%s</pre>\n",
                htmlspecialchars(print_r($params,
                true))
                )
            : '';

        $server = $_SERVER;
        ksort($server);
        $footer .= $d
            ? sprintf(
                "<br/>\n<hr/>\n<pre>_SERVER=%s</pre>\n",
                htmlspecialchars(print_r($server,
                true))
                )
            : '';

        $footer .= $d
            ? sprintf(
                "<br/>\n<hr/>\n<pre>metadata=%s</pre>\n",
                htmlspecialchars(print_r($this->metadata,
                true))
                )
            : '';

        $footer .= $d
            ? sprintf(
                "<br/>\n<hr/>\n<pre>data=%s</pre>\n",
                htmlspecialchars(print_r($data,
                true))
            )
            : '';

        static $fields = array(
            'd_checked',
            'defaultRows',
            'first_disabled',
            'footer',
            'groupby',
            'groupby_disabled',
            'hiddens',
            'last_disabled',
            'links',
            'next10_disabled',
            'next_disabled',
            'ofpages',
            'page',
            'prev10_disabled',
            'prev_disabled',
            'rows',
            'save_as',
            'subtotals',
            'subtotals_disabled',
            'table',
            'title',
            'titles',
            'top_style',
            'totals',
            'totals_disabled'
        );

        $view = new Rasa_Reporter_View();
        $view->setView(dirname(__FILE__) . '/Reporter/views/default.phtml');

        foreach ($fields as $field) {
            if (isset($$field)) {
                $view->$field = $$field;
            }
        }

        $rv = $view->render();

        #$rv = include(dirname(__FILE__) . '/Reporter/views/default.phtml');
        return $rv;
    }

    /**
     * @return void
     */
    public function flush()
    {
        echo str_repeat(" ", 1024);

        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            @ob_end_flush();
        }
        @ob_implicit_flush(1);
        @flush();
    }

    /**
     * @param int $rows
     * @return void
     */
    public function export($rows)
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        # @todo fixme
        $basename = 'Untitled';

        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $basename = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME);
        }
        if (isset($_SERVER['REDIRECT_URL'])) {
            $basename = pathinfo($_SERVER['REDIRECT_URL'], PATHINFO_FILENAME);
        }

        $params['format'] = strtolower($params['format']);

        $filename = $basename . '.' . $params['format'];

        $x = new Rasa_Reporter_Select();
        $x->setFilename($filename);
        $x->setFormat($params['format']);
        $x->setMetadata($this->metadata);
        $x->setParams($params);
        $x->setRows($rows);
        $x->setSql($this->getLastSql());
        $x->setTitle($this->getOption(self::OPTION_TITLE));
        $x->setUrls($this->getOption(self::OPTION_URLS));
        $x->export();
        exit(0);
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $logger = $this->getOption(self::OPTION_LOGGER);
        $params = $this->getOption(self::OPTION_PARAMS);

        /*
        if (!isset($params['format']) || !$params['format']) {
        $html = $this->getHtmlHead();
        echo $html;
        $this->flush();
        }
        */

        $sqls = $this->sqls;

        $sql = $this->getLastSql();

        if (isset($params['groupby']) && $params['groupby']) {
            $sql = $this->substituteGroupby($sql);
        }

        while (true) {
            if (count($sqls) == 0) {
                $go = false;
                break;
            }

            if (isset($params['go']) && !$params['go']) {
                $go = false;
                break;
            }

            #     if (isset($params['format']) && $params['format'])) {
            #       $go = false;
            #       break;
            #     }

            if (!isset($params['go'])) {
                if (preg_match('~/\*\s*autorun\s*=\s*(on|1|true|yes)\s*\*/~i', $sql)) {
                    $go = true;
                    break;
                }

                if (count($sqls) > 1) {
                    $go = false;
                    break;
                }

                if (preg_match('~/\*\s*autorun\s*=\s*(off|0|false|no)\s*\*/~i', $sql)) {
                    $go = false;
                    break;
                }
            }

            $rv = $this->runSqls();
            if (!$rv) {
                $go = false;
                break;
            }

            if (!$sql) {
                $go = false;
                break;
            }

            $go = true;

            break;
        }

        $rows = array();

        while ($go) {
            $rv = $this->calculateMetadata();

            if (!$rv) {
                break;
            }

            $sql = $this->addSearchClauses($sql);

            $parts = $this->parseSql($sql);
            if (!isset($params['rows']) || strlen($params['rows']) == 0) {
                if ($parts['limit'] > 0) {
                    $params['rows'] = $parts['limit'];
                } else {
                    $params['rows'] = $this->getOption(self::OPTION_MAX_ROWS);
                    $parts['limit'] = $params['rows'];
                    $sql            = $this->buildSql($parts);
                }
            }

            if (isset($params['format']) && $params['format']) {
                $parts          = $this->parseSql($sql);
                $parts['limit'] = '';
                $sql            = $this->buildSql($parts);
            }

            $this->modifiedSql = $sql;

            $conn = $this->getConnection();

            $startTime = microtime(true);

            try {
                $stmt = $conn->query($sql);
            }
            catch (Exception $e) {
                $msg = sprintf('Error %s: %s', $e->getCode(), $e->getMessage());
                $logger->err($msg);
                $this->setLastError($msg);
                $stmt = null;
                break;
            }

            if (!$stmt) {
                break;
            }

            $this->elapsed1 = microtime(true) - $startTime;

            $startTime = microtime(true);

            if (is_callable(array($stmt, 'fetch_all'))) {
                $rows = $stmt->fetch_all(MYSQLI_ASSOC);
            } else {
                for ($rows = array(); $tmp = $stmt->fetch_array(MYSQLI_ASSOC);) {
                    $rows[] = $tmp;
                }
            }
            $this->elapsed2 = microtime(true) - $startTime;

            $this->stmt = $stmt;

            $this->foundRows = $this->getFoundRows();

            if (!isset($_SESSION[__CLASS__])) {
                $_SESSION[__CLASS__] = array();
            }

            $_SESSION[__CLASS__]['records'] = $this->foundRows;

            # required to determine the places for doubles
            $this->setMetadata();

            $rows       = $this->addTotalRow($rows);
            $this->rows = $rows;
            break;
        }

        if (isset($params['format']) && $params['format']) {
            return $this->export($rows);
        }

        $this->outputHtml($rows, $go);

        return true;
    }

    /**
     * @return array
     */
    protected static function getColumn($sql)
    {
        $conn = Rasa_Reporter::getConnection();

        try {
            $stmt = $conn->query($sql);
        }
        catch (Exception $e) {
            $msg = sprintf('Error %s: %s', $e->getCode(), $e->getMessage());
            $logger->err($msg);
            $this->setLastError($msg);
            $stmt = null;
            return false;
        }

        if (!$stmt) {
            $msg = sprintf('Error %s: %s', $conn->errno, $conn->error);
            $logger->err($msg);
            $this->setLastError($msg);
            return false;
        }

        $rows = array();

        if (is_callable(array($stmt, 'fetch_all'))) {
            $rows = $stmt->fetch_all(MYSQLI_ASSOC);
        } else {
            for ($rows = array(); $tmp = $stmt->fetch_array(MYSQLI_ASSOC);) {
                $rows[] = $tmp;
            }
        }

        $rv = array();

        foreach($rows as $row) {
          $rv[] = $row['name'];
        }

        return $rv;
    }

    /**
     * @return array
     */
    public static function getDatabases()
    {
        $sql = <<<EOT
SELECT
  SCHEMA_NAME AS name
FROM
  information_schema.SCHEMATA
WHERE
  SCHEMA_NAME NOT IN (
    'information_schema',
    'mysql',
    'performance_schema'
  )
ORDER BY
  name
EOT;
        return Rasa_Reporter::getColumn($sql);
    }

    /**
     * @return array
     */
    public static function getTables($database = '')
    {
      if ($database) {
        $sql = <<<EOT
SELECT
  TABLE_NAME AS name
FROM
  information_schema.TABLES
WHERE
  TABLE_SCHEMA = '$database'
ORDER BY
  name
EOT;
      } else {
        $sql = <<<EOT
SELECT
  CONCAT(TABLE_SCHEMA, '.', TABLE_NAME) AS name
FROM
  information_schema.TABLES
WHERE
  TABLE_SCHEMA NOT IN (
    'information_schema',
    'mysql',
    'performance_schema'
  )
ORDER BY
  name
EOT;
      }
      return Rasa_Reporter::getColumn($sql);
    }
}

# EOF
