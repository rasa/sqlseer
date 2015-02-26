<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

$insert_rand = <<<EOT

INSERT INTO
  temp.test_report_fulltext
SET
  tinytexts   = MAKE_SET(RAND()*128+1,"search","terms","randomly","selected","for","your","searching","amusement"),
  mediumtexts = MAKE_SET(RAND()*128+1,"search","terms","randomly","selected","for","your","searching","amusement"),
  longtexts   = MAKE_SET(RAND()*128+1,"search","terms","randomly","selected","for","your","searching","amusement"),
  comment = "\"for\" and \"your\" are unsearchable \"stop words\""
EOT;

$sql = <<<EOT

CREATE DATABASE IF NOT EXISTS temp;

DROP TABLE IF EXISTS temp.test_report_fulltext;

CREATE TABLE IF NOT EXISTS temp.test_report_fulltext
(
  tinytexts TINYTEXT , -- [BINARY]
  mediumtexts MEDIUMTEXT , -- [BINARY]
  longtexts LONGTEXT , -- [BINARY]
  comment VARCHAR(255),
  FULLTEXT (tinytexts),
  FULLTEXT (mediumtexts),
  FULLTEXT (longtexts)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;

SELECT /*autorun=true*/
  t.*
FROM
  temp.test_report_fulltext t;
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';

# EOF