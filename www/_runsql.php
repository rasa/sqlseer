<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

require_once __DIR__ . '/_bootstrap.php';

set_time_limit(0);
# this works, but we don't really need it:
$urls = array();
$urls['/address$/'] = 'https://maps.google.com/maps?q=%s';

if (isset($_REQUEST['restart']) && $_REQUEST['restart']) {
    $url = isset($_SERVER['SCRIPT_URI'])
        ? $_SERVER['SCRIPT_URI']
        : sprintf('http%s://%s%s', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''), $_SERVER['SERVER_NAME'], $_SERVER['PHP_SELF']);
    if (isset($_REQUEST['records'])) {
        $url .= '?records=' . intval($_REQUEST['records']);
    }
    header(sprintf('Location: %s', $url));
    exit;
}

if (!$sql && isset($_REQUEST['sql'])) {
    $file = $_SERVER['DOCUMENT_ROOT'] . '/' . $_REQUEST['sql'];
    if (is_file($file)) {
        $sql = file_get_contents($file);
    } else {
        $sql = '';
    }
}

ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

#require_once 'Rasa/Reporter.php';

$reporter = new Rasa_Reporter();
$reporter->setSql($sql);
$reporter->setUrls($urls);
$reporter->run();

if (0 && isset($_REQUEST['d']) && $_REQUEST['d']) {
    $a = $_REQUEST;
    ksort($a);
    echo "<pre>_REQUEST=", print_r($a, true);
}

# EOF
