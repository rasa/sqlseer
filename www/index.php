<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) {
  $pi = pathinfo($_SERVER['REDIRECT_URL']);

  if (isset($pi['dirname'])) {
    $pi2 = pathinfo($pi['dirname']);
    if (isset($pi2['dirname']) && $pi2['dirname'] <> '/') {
      $pi = $pi2;
    }
  }

  $file = $_SERVER['DOCUMENT_ROOT'];
  if (isset($pi['dirname'])) {
    $file .= $pi['dirname'];
  }
  $php_file = $file . '.php';
  if (is_file($php_file)) {
    require_once $php_file;
    return;
  }

  if (substr($file, -1) <> '/') {
    $file .= '/';
  }
  if (isset($pi['filename'])) {
    $file .= $pi['filename'];
  }
  $php_file = $file . '.php';
  if (is_file($php_file)) {
    require_once $php_file;
    return;
  }

  $sql_file = $file . '.sql';

  if (is_file($sql_file)) {
    $sql = @file_get_contents($sql_file);
    require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';
    return;
  }
}

$pageTitle = 'Reports';

$dir = dirname($_SERVER['SCRIPT_FILENAME']);
$files = scandir($dir);

$rows = array();
foreach ($files as $file) {
  if ($file[0] == '.') {
    continue;
  }
  if (is_file($file)) {
    if (!preg_match('/\.(php|sql)$/i', $file)) {
      continue;
    }
    if (preg_match('/index\.php$/i', $file)) {
      continue;
    }
  }
  $p = pathinfo($file);

  if ($p['filename'][0] == '.') {
    continue;
  }
  if ($p['filename'][0] == '_') {
    continue;
  }
  $mtime = filemtime($file);
  $name = $p['filename'];
  $name = preg_replace('/[\-_]+/', ' ', $name);
  $name = ucwords($name);

  $date = date('Y-m-d H:i:s', $mtime);

  if (isset($_REQUEST['sort']) && $_REQUEST['sort']) {
    $key = $$_REQUEST['sort'];
    if ($_REQUEST['sort'] <> 'name') {
      $key .= $name;
    }
  } else {
    $key = $name;
  }

  $rows[$key] = array(
    'name'  => $name,
    'file'  => $file,
    'date'  => $date,
  );
}

ksort($rows);

if (isset($_REQUEST['desc']) && $_REQUEST['desc']) {
  krsort($rows);
}

$html = '';

$n = 0;
foreach ($rows as $row) {
  ++$n;
  $file = $row['file'];
  if (preg_match('/(.*)\.(php|sql)$/i', $file, $m)) {
    $file = $m[1];
  }
  $url = urlencode($file);

  $html .= sprintf(
    "<tr><td class='r'>%s</td><td><a href='%s'>%s</a></td><td class='r'>%s</td></tr>\n",
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
  <link rel="icon" type="image/png" href="/favicon.png" />
  <link rel="stylesheet" href="/default.css" type="text/css" />
  </head>

  <body>
  <div class="header">
    <?= $links ?>
    <img align='right' valign='bottom' src='/default.png'  alt=''/>
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
