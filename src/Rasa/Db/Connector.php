<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
@todo http://dev.mysql.com/doc/refman/5.6/en/option-files.html
*/

/**
 * Loads the database credentials in the following order:
 *
 * 1. Default settings:
 *     database: (none)
 *     host:     127.0.0.1
 *     password: (none)
 *     port:     3306
 *     socket:   (none)
 *     user:     root
 *
 * 2. php.ini settings:
 *
 *    host:     mysql.default_host
 *    password: mysql.default_password
 *    port:     mysql.default_port
 *    socket:   mysql.default_socket
 *    user:     mysql.default_user
 *
 * 3. $HOME/.my.cnf
 *
 * 4. application.ini (Zend Framework)
 *
 * 5. Environment variables:
 *    host:     MYSQL_HOST
 *    password: MYSQL_PWD
 *    port:     MYSQL_TCP_PORT
 *    socket:   MYSQL_UNIX_PORT
 *    user:     USER
 *    user:     MYSQL_USER
 *
 * 6. Command line parameters:
 *    -D | --database
 *    -h | --host
 *    -p | --password
 *    -P | --port
 *    -S | --socket
 *    -u | --user

/**
 */
class Rasa_Db_Connector
{
  /**
   *
   */
  const DEFAULT_DATABASE = '';

  /**
   */
  const DEFAULT_HOST = '127.0.0.1';

  /**
   */
  const DEFAULT_PASSWORD = '';

  /**
   */
  const DEFAULT_PORT = '3306';

  /**
   */
  const DEFAULT_SOCKET = '';

  /**
   */
  const DEFAULT_USER = 'root';

  static protected $_application_ini_map = array(
    'host' => 'host',
    'port' => 'port',
    'user' => 'username',
    'password' => 'password',
    'socket' => 'socket',
    'database' => 'dbname'
  );

  /**
   */
  protected $_config_file = null;

  /**
   */
  protected $_database = null;

  /**
   */
  protected $_env = null;

  /**
   */
  public function getConfigFile()
  {
    if ($this->_config_file === null) {
      $this->_config_file = dirname(__FILE__) . '/dbconfig.ini';
    }

    return $this->_config_file;
  }

  /**
   */
  public function setConfigFile($config_file)
  {
      $rv                 = $this->_config_file;
      $this->_config_file = $config_file;
      return $rv;
  }

  /**
   */
  public function getDatabase()
  {
      return $this->_database;
  }

  /**
   */
  public function setDatabase($database)
  {
      $rv              = $this->_database;
      $this->_database = strtolower($database);
      return $rv;
  }

  /**
   */
  public function getEnvironment()
  {
      if ($this->_env === null) {
          $this->_env = getenv('APPLICATION_ENV');
          if (empty($this->_env)) {
              $this->_env = 'production';
          }
      }

      return $this->_env;
  }

  /**
   */
  public function setEnvironment($env)
  {
      $rv         = $this->_env;
      $this->_env = $env;
      return $rv;
  }

  /**
   */
  public function getApplicationIniParams()
  {
      $rv = array();

      $file = $this->getConfigFile();

      if (!is_file($file)) {
          return $rv;
      }

      $ini = parse_ini_file($file, true);

      $envs = array();

      foreach ($ini as $env => $values) {
          if (preg_match('/(\w+)\s*:\s*(\w+)/', $env, $m)) {
              $env        = $m[1];
              $parent     = $m[2];
              $envs[$env] = $values + $envs[$parent];
          } else {
              $envs[$env] = $values;
          }
      }

      $application_env = $this->getEnvironment();

      if (!isset($envs[$application_env])) {
          $msg = sprintf("Environment '%s' not found in '%s'", $application_env, $file);
          throw new Exception($msg);
      }

      $params = $envs[$application_env];

      $params = array_change_key_case($params);

      $map = array_flip(self::$_application_ini_map);

      foreach ($params as $k => $v) {
          if (preg_match('/resources\.db\.params\.(.*)$/i', $k, $m)) {
              $key = $m[1];
              if (isset($map[$key])) {
                  $rv[$map[$key]] = $v;
              }
          }
      }

      return $rv;
  }

  /**
   */
  public function getCliParams()
  {
      static $opts = array(
          'D' => 'database',
          'h' => 'host',
          'p' => 'password',
          'P' => 'port',
          'S' => 'socket',
          'u' => 'user'
      );

      static $rv = null;

      if ($rv == null) {
          $shortopts = '';

          $longopts = array();

          foreach ($opts as $key => $value) {
              $shortopts .= $key . ':';
              $longopts[] = $value . ':';
          }

          $options = getopt($shortopts, $longopts);

          foreach ($opts as $key => $value) {
              if (isset($options[$key])) {
                  $rv[$value] = $options[$key];
              }
              if (isset($options[$value])) {
                  $rv[$value] = $options[$key];
              }
          }
      }

      return $rv;
  }

  /**
   */
  public function getDefaultParams()
  {
      static $rv = array(
          'host' => self::DEFAULT_HOST,
          'port' => self::DEFAULT_PORT,
          'user' => self::DEFAULT_USER,
          'password' => self::DEFAULT_PASSWORD,
          'socket' => self::DEFAULT_SOCKET,
          'database' => self::DEFAULT_DATABASE
      );

      return $rv;
  }

  /**
   */
  public function getEnvironmentParams()
  {
      static $rv = null;

      if ($rv === null) {
          static $map = array(
              'MYSQL_DATABASE' => 'host',
              'MYSQL_HOST' => 'host',
              'MYSQL_TCP_PORT' => 'port',
              'MYSQL_UNIX_PORT' => 'socket',
              'USER' => 'user',
              'MYSQL_USER' => 'user',
              'MYSQL_PWD' => 'password'
          );

          foreach ($map as $key => $value) {
              if (getenv($key)) {
                  $rv[$value] = getenv($key);
              }
          }
      }

      return $rv;
  }

  /**
   */
  protected function _getMysqlParams($file)
  {
      static $map = array(
          'database' => 'database',
          'host' => 'host',
          'password' => 'password',
          'port' => 'port',
          'socket' => 'socket',
          'user' => 'user'
      );

      static $sections = array('mysqladmin', 'mysqldump', 'client');

      static $rv = null;

      if ($rv === null) {
          $rv = array();

          if (!is_file($file)) {
              return $rv;
          }

          $i = parse_ini_file($file, true);

          foreach ($map as $from => $to) {
              if (isset($i[$from])) {
                  $rv[$to] = $i[$from];
              }
          }

          foreach ($sections as $section) {
              if (isset($i[$section])) {
                  foreach ($map as $from => $to) {
                      if (isset($i[$from])) {
                          $rv[$to] = $i[$from];
                      }
                  }
              }
          }
      }

      return $rv;
  }

  /**
   */
  public function getMyCnfParams()
  {
      static $rv = null;

      if ($rv === null) {
          $rv = array();

          if (!isset($_SERVER['HOME'])) {
              return $rv;
          }

          $file = $_SERVER['HOME'] . '/.my.cnf';

          $rv = $this->_getMysqlParams($file);
      }

      return $rv;
  }

  /**
   */
  public function getPhpIniParams()
  {
      static $map = array(
          'mysql.default_host' => 'host',
          'mysql.default_password' => 'password',
          'mysql.default_port' => 'port',
          'mysql.default_socket' => 'socket',
          'mysql.default_user' => 'user'
      );

      static $rv = null;

      if ($rv === null) {
          $rv = array();

          foreach ($map as $key => $value) {
              if (ini_get($key)) {
                  $rv[$value] = ini_get($key);
              }
          }
      }

      return $rv;
  }

  /**
   */
  public function getDbParams()
  {
      $params = array();

      $params[] = $this->getDefaultParams();
      $params[] = $this->getPhpIniParams();
      $params[] = $this->getMyCnfParams();
      $params[] = $this->getApplicationIniParams();
      $params[] = $this->getEnvironmentParams();
      $params[] = $this->getCliParams();

      $rv = array();

      foreach ($params as $a) {
          if (is_array($a)) {
              $rv = array_merge($rv, $a);
          }
      }

      if ($this->_database) {
          $rv['database'] = $this->_database;
      }

      return $rv;
  }

  /**
   */
  public function getPdo()
  {
      $params = $this->getDbParams();

      static $map = array(
          'host' => 'host',
          'port' => 'port',
          'database' => 'dbname',
          'socket' => 'unix_socket'
      );

      $dsn = '';

      foreach ($map as $k => $v) {
          if ($params[$k]) {
              if ($dsn) {
                  $dsn .= ';';
              }
              $dsn .= sprintf('%s=%s', $v, $params[$k]);
          }
      }

      $dsn = 'mysql:' . $dsn;

      return new PDO($dsn, $params['user'], $params['password']);
  }

  /**
   */
  public function getMysqliConnection()
  {
      $params = $this->getDbParams();

      $connection = mysqli_init();

      $bool = mysqli_real_connect(
          $connection,
          $params['host'],
          $params['user'],
          $params['password'],
          $params['database'],
          $params['port'],
          $params['socket']
      );

      mysqli_set_charset($connection, 'utf8');

      return $bool ? $connection : null;
  }

  /**
   */
  public function getZendDbDefaultConnection()
  {
      #require_once 'Zend/Db/Table/Abstract.php';

      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      return $db->getConnection();
  }

  /**
   */
  public function getZendDbMysqlConnection()
  {
      $db_params = $this->getDbParams();

      $params = array();

      foreach (self::$_application_ini_map as $k => $v) {
          if (isset($db_params[$k])) {
              $params[$v] = $db_params[$k];
          }
      }

      #require_once 'Zend/Db/Adapter/Mysqli.php';

      $db = new Zend_Db_Adapter_Mysqli($params);
      return $db->getConnection();
  }

  /**
   */
  public function getZendDbPdoMysqlConnection()
  {
      $db_params = $this->getDbParams();

      $params = array();

      foreach (self::$_application_ini_map as $k => $v) {
          if (isset($db_params[$k])) {
              $params[$v] = $db_params[$k];
          }
      }

      #require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

      $db = new Zend_Db_Adapter_Pdo_Mysql($params);
      return $db->getConnection();
  }


  /**
   * Singleton instance
   *
   * @var __CLASS__
   */
  protected static $_instance = null;

  /**
   * Returns an instance of __CLASS__
   *
   * Singleton pattern implementation
   *
   * @param  array|Zend_Config|null $options (Optional) Options to set
   * @return __CLASS__ Provides a fluent interface
   */
  public static function getInstance($options = null)
  {
      if (self::$_instance === null) {
          self::$_instance = new self($options);
      }

      return self::$_instance;
  }

  public static function getDefaultConnection($options = null)
  {
    static $conn = null;
    static $instance;

    while ($conn === null) {
      $instance = Rasa_Db_Connector::getInstance($options);

      $application_ini = dirname(dirname(dirname(dirname(__FILE__)))) . '/app/configs/application.ini';
      if (file_exists($application_ini)) {
        $conn = $instance->getZendDbDefaultConnection();
        break;
      }

      $dbconfig_ini = dirname(dirname(dirname(dirname(__FILE__)))) . '/app/configs/dbconfig.ini';
      if (file_exists($dbconfig_ini)) {
        $instance->setConfigFile($dbconfig_ini);
      }

      $conn = $instance->getMysqliConnection();
      break;
    }

    return $conn;
  }
}

# EOF
