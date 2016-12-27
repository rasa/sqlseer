<?php

/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2016 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

$insert_rand = <<<EOT
INSERT INTO
  temp.test_report_numbers
SELECT
  CAST(bits + 10 + RAND() AS DECIMAL(10,4)),
  CAST(tinyints + 10 + RAND() AS DECIMAL(10,4)),
  CAST(smallints + 10 + RAND() AS DECIMAL(10,4)),
  CAST(mediumints + 10 + RAND() AS DECIMAL(10,4)),
  CAST(bigints + 10 + RAND() AS DECIMAL(10,4)),
  floats + 10 + RAND(),
  floats4 + 10 + RAND(),
  doubles + 10 + RAND(),
  doubles7 + 10 + RAND(),
  CAST(decimals + 10 + RAND() AS DECIMAL(20,10)),
  CAST(decimals2 + 10 + RAND() AS DECIMAL(10,2))
FROM
  temp.test_report_numbers;
EOT;

$sql = <<<EOT

SET @@sql_mode=REPLACE(@@sql_mode, 'STRICT_TRANS_TABLES', '');

CREATE DATABASE IF NOT EXISTS temp;

DROP TABLE IF EXISTS temp.test_report_numbers;

CREATE TABLE temp.test_report_numbers
(
  bits BIT, -- [(length)]
  tinyints TINYINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  smallints SMALLINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  mediumints MEDIUMINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  bigints BIGINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  floats FLOAT, -- [(length,decimals)] [UNSIGNED] [ZEROFILL]
  floats4 FLOAT(10,4), -- [(length,decimals)] [UNSIGNED] [ZEROFILL]
  doubles DOUBLE, -- [(length,decimals)] [UNSIGNED] [ZEROFILL]
  doubles7 DOUBLE(10,7), -- [(length,decimals)] [UNSIGNED] [ZEROFILL]
  decimals DECIMAL, -- [(length[,decimals])] [UNSIGNED] [ZEROFILL]
  decimals2 DECIMAL(10,2) -- [(length[,decimals])] [UNSIGNED] [ZEROFILL]
);

INSERT INTO
  temp.test_report_numbers
SET
  bits = 0,
  tinyints = 0,
  smallints = 0,
  mediumints = 0,
  bigints = 0,
  floats = 0,
  floats4 = 0,
  doubles = 0,
  doubles7 = 0,
  decimals = 0,
  decimals2 = 0;

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
  temp.test_report_numbers t;
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';

# EOF
