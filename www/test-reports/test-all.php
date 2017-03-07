<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2017 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

$insert_rand = <<<EOT
INSERT INTO
  temp.test_report_all
SELECT
  IF(bits+0b1>0b1,0b0,0b1),
  CAST(tinyints + RAND() AS DECIMAL(10,4)),
  CAST(smallints + RAND() AS DECIMAL(10,4)),
  CAST(mediumints + RAND() AS DECIMAL(10,4)),
  CAST(bigints + RAND() AS DECIMAL(10,4)),
  doubles + RAND(),
  CAST(decimals + RAND() AS DECIMAL(10,4)),
  CURDATE() - INTERVAL RAND() * 12345 DAY,
  TIME(NOW() - INTERVAL RAND() * 86400 SECOND),
  NOW() - INTERVAL RAND() * 12345 DAY - INTERVAL RAND() * 86400 SECOND,
  NOW() - INTERVAL RAND() * 12345 DAY - INTERVAL RAND() * 86400 SECOND,
  YEAR(NOW() + INTERVAL 50 YEAR - INTERVAL RAND() * 99 YEAR),
  CAST(ROUND(chars + RAND(), 0) MOD 256 AS CHAR(1)),
  CAST(ROUND(varchars + RAND(), 0) MOD 256 AS CHAR(1)),
  CAST(ROUND(binarys + RAND(), 0) MOD 256 AS BINARY(1)),
  CAST(ROUND(varbinarys + RAND(), 0) MOD 256 AS BINARY(1)),
  CAST(tinyblobs + RAND() AS DECIMAL(10,4)),
  CAST(mediumblobs + RAND() AS DECIMAL(10,4)),
  CAST(longblobs + RAND() AS DECIMAL(10,4)),
  CAST(tinytexts + RAND() AS DECIMAL(10,4)),
  CAST(mediumtexts + RAND() AS DECIMAL(10,4)),
  CAST(longtexts + RAND() AS DECIMAL(10,4)),
  ELT(FLOOR(1+RAND()*3), 'a', 'b', 'c'),
  ELT(FLOOR(1+RAND()*7), 'a', 'b', 'c','a,b','a,c','b,c','a,b,c'),
  CAST(ROUND(RAND() * 123456789012345, 0) AS CHAR(20))
FROM
  temp.test_report_all;
EOT;

$sql = <<<EOT

SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS temp;

DROP TABLE IF EXISTS temp.test_report_all;

CREATE TABLE temp.test_report_all
(
  bits BIT, -- [(length)]
  tinyints TINYINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  smallints SMALLINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  mediumints MEDIUMINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  bigints BIGINT, -- [(length)] [UNSIGNED] [ZEROFILL]
  doubles DOUBLE, -- [(length,decimals)] [UNSIGNED] [ZEROFILL]
  decimals DECIMAL(10,2), -- [(length[,decimals])] [UNSIGNED] [ZEROFILL]
  dates DATE, --
  times TIME, --
  timestamp_ TIMESTAMP, --
  datetimes DATETIME, --
  years YEAR, --
  chars CHAR(1), -- [(length)]
  varchars VARCHAR(1), -- (length)
  binarys BINARY(1), -- [(length)]
  varbinarys VARBINARY(1), -- (length)
  tinyblobs TINYBLOB, --
  mediumblobs MEDIUMBLOB, --
  longblobs LONGBLOB, --
  tinytexts TINYTEXT , -- [BINARY]
  mediumtexts MEDIUMTEXT , -- [BINARY]
  longtexts LONGTEXT , -- [BINARY]
  enums ENUM('a', 'b', 'c'),
  sets SET('a', 'b', 'c'),
  number_in_string VARCHAR(20)
);

INSERT INTO
  temp.test_report_all
SET
  bits = 0b0,
  tinyints = 0,
  smallints = 0,
  mediumints = 0,
  bigints = 0,
  doubles = 0,
  decimals = 0,
  dates = CURDATE(),
  times = CURTIME(),
  timestamp_ = NOW(),
  datetimes = NOW(),
  years = CURDATE(),
  chars = 0,
  varchars = 0,
  binarys = 0,
  varbinarys = 0,
  tinyblobs = 0,
  mediumblobs = 0,
  longblobs = 0,
  tinytexts = 0,
  mediumtexts = 0,
  longtexts = 0,
  enums = ELT(FLOOR(1+RAND()*3), 'a', 'b', 'c'),
  sets = ELT(FLOOR(1+RAND()*7), 'a', 'b', 'c','a,b','a,c','b,c','a,b,c'),
  number_in_string = CAST(ROUND(RAND() * 123456789012345, 0) AS CHAR(20));

$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;
$insert_rand;

SET @row := 0;

SELECT /*autorun=true*/ /*row_column=0*/
  (@row := @row + 1) AS 'no',
  t.*
FROM
  temp.test_report_all t;
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';

# EOF

