<?php
/**
 * Rasa Framework
 *
 * @copyright  Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', dirname(__DIR__) . '/app');

if (file_exists(APPLICATION_PATH . '/configs/application.ini')) {
  // Define application environment
  defined('APPLICATION_ENV')
      || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

  Zend_Loader_Autoloader::getInstance();

  $application = new Zend_Application(
      APPLICATION_ENV,
      APPLICATION_PATH . '/configs/application.ini'
  );

  Zend_Registry::set('application', $application);

  $application->bootstrap();

  unset($application);
}

# EOF
