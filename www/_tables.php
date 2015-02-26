<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

require_once dirname(__DIR__) . '/www/_bootstrap.php';

#require_once 'Rasa/Reporter.php';
$tables = Rasa_Reporter::getTables(isset($database) ? $database : '');

if (!isset($database)) {
  if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) {
    $pi = pathinfo($_SERVER['REDIRECT_URL']);
    if (isset($pi['dirname']) && strlen($pi['dirname']) > 1) {
      if (isset($pi['basename']) && $pi['basename'] && $pi['basename'] <> pathinfo(__FILE__, PATHINFO_FILENAME)) {
        $sql = sprintf('SELECT * FROM %s ORDER BY 1 DESC', $pi['basename']);
        require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';
        return;
      }
    }
  }

  if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']) {
    $pi = pathinfo($_SERVER['PATH_INFO']);
    if (isset($pi['dirname']) && strlen($pi['dirname']) > 1) {
      if (isset($pi['basename']) && $pi['basename']) {
        $sql = sprintf('SELECT * FROM %s ORDER BY 1 DESC', $pi['basename']);
        require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';
        return;
      }
    }
  }
}

$pageTitle = 'Reports';

$parts = parse_url($_SERVER['REQUEST_URI']);

if (isset($parts['query'])) {
  parse_str($parts['query'], $qs);
} else {
  $qs = array();
}

$rows = array();
foreach ($tables as $table) {
  $mtime = time();

  $name = preg_replace('/[\-_]+/', ' ', $table);

  $date = date('Y-m-d H:i:s', $mtime);

  if (isset($_REQUEST['sort']) && $_REQUEST['sort']) {
    $key = $$_REQUEST['sort'] . $table;
  } else {
    $key = $name;
  }

  #if (isset($database)) {
  #  $table = $database . '.' . $table;
  #}

  $url = $parts['path'] . '/' . $table;
  if ($qs) {
    $url .= '?' . http_build_query($qs);
  }

  $rows[$key] = array(
    'name'  => $name,
    'url' => $url,
    'date'  => $date,
  );
}

if (isset($_REQUEST['desc']) && $_REQUEST['desc']) {
  krsort($rows);
} else {
  ksort($rows);
}

$html = '';

$n = 0;
foreach ($rows as $row) {
  ++$n;
  $url = $row['url'];

  $html .= sprintf(
    "<tr><td class='right'>%s</td><td class='left'><a href=\"%s\">%s</a></td><td class='right'>%s</td></tr>\n",
    $n,
    $url,
    $row['name'],
    $row['date']
  );
}

if (!isset($_REQUEST['desc'])) {
  $_REQUEST['desc'] = 1;
}
$desc = $_REQUEST['desc'] ? 0 : 1;

$p = parse_url($_SERVER['REQUEST_URI']);
$path = $p['path'];

$links = array();
$titles = array();

while ($path > '/') {
  $name = rtrim($path, '/');
  $name = urldecode($name);
  $name = preg_replace('/[\-_]+/', ' ', basename($path, '.php'));
  $name = ucwords($name);
  $titles[] = $name;
  $links[] = sprintf('<a href="%s">%s</a>', $path, $name);

  $path = dirname($path);
}

$links[] = sprintf('<a href="/">%s</a>', $pageTitle);

$titles[] = $pageTitle;

$links = array_reverse($links);

$links = join(" &gt; ", $links);
$title = join(' - ', $titles);

?>

<!DOCTYPE html>
<html>
  <head>
  <title><?= htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="/default.css" type="text/css" />
  </head>

  <body>
  <div class="header">
    <?= $links ?>
    <img align='right' valign='bottom' src='/default.png' alt=''/>
  </div>
  <hr/>
  <div class="report">
    <table class='border'>
      <tr>
      <th class='r'>#</th>
      <th><a href="?sort=name&desc=<?= $desc ?>">Report Name</a></th>
      <th class='r'><a href="?sort=date&desc=<?= $desc ?>">Last Updated</a></th>
      </tr>
      <?php echo $html ?>
    </table>
  </div>
  </br>
  </body>
</html>
<?php

/*
echo "<pre>";
$a = $_SERVER;
ksort($a);
print_r($a);
*/

# EOF
