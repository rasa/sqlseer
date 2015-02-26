<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

$insert_rand = <<<EOT

INSERT INTO
  temp.test_report_other
SELECT
  ELT(FLOOR(1+RAND()*3), 'a', 'b', 'c'),
  ELT(FLOOR(1+RAND()*7), 'a', 'b', 'c','a,b','a,c','b,c','a,b,c'),
  CAST(-128 + RAND() * 256 AS SIGNED) AS i
FROM
  temp.test_report_other;
EOT;

$sql = <<<EOT

CREATE DATABASE IF NOT EXISTS temp;

DROP TABLE IF EXISTS temp.test_report_other;

CREATE TABLE temp.test_report_other
(
  enums ENUM('a', 'b', 'c'),
  sets SET('a', 'b', 'c'),
  i INT DEFAULT 1
);

INSERT INTO
  temp.test_report_other
SET
  enums = ELT(FLOOR(1+RAND()*3), 'a', 'b', 'c'),
  sets = ELT(FLOOR(1+RAND()*7), 'a', 'b', 'c','a,b','a,c','b,c','a,b,c');

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
  temp.test_report_other t;
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';

# EOF