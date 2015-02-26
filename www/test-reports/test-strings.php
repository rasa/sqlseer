<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

$insert_rand = <<<EOT

INSERT INTO
  temp.test_report_strings
SELECT
  LEFT(MD5(CONCAT(chars, RAND())), 10),
  LEFT(MD5(CONCAT(varchars, RAND())), 10),
  binarys,
  varbinarys,
  UNHEX(LEFT(MD5(CONCAT(tinyblobs, RAND())), 20)),
  UNHEX(LEFT(MD5(CONCAT(mediumblobs, RAND())), 20)),
  UNHEX(LEFT(MD5(CONCAT(longblobs, RAND())), 20)),
  LEFT(MD5(CONCAT(tinytexts, RAND())), 10),
  LEFT(MD5(CONCAT(mediumtexts, RAND())), 10),
  LEFT(MD5(CONCAT(longtexts, RAND())), 10),
  CAST(ROUND(RAND() * 123456789012345, 0) AS CHAR(20)),
  CAST(-128 + RAND() * 256 AS SIGNED) AS i,
  '100 main st, la, ca' AS address,
  CONCAT(LEFT(MD5(RAND()), 5), '@example.com') AS emails,
  CONCAT('http://www.example.com/', LEFT(MD5(RAND()), 10)) AS urls
FROM
  temp.test_report_strings;
EOT;

$sql = <<<EOT

CREATE DATABASE IF NOT EXISTS temp;

DROP TABLE IF EXISTS temp.test_report_strings;

CREATE TABLE temp.test_report_strings
(
  chars CHAR(10), -- [(length)]
  varchars VARCHAR(10), -- (length)
  binarys BINARY(60), -- [(length)]
  varbinarys VARBINARY(60), -- (length)
  tinyblobs TINYBLOB, --
  mediumblobs MEDIUMBLOB, --
  longblobs LONGBLOB, --
  tinytexts TINYTEXT , -- [BINARY]
  mediumtexts MEDIUMTEXT , -- [BINARY]
  longtexts LONGTEXT , -- [BINARY]
  number_in_string VARCHAR(20),
  i INT DEFAULT 1, --
  address VARCHAR(255),
  emails VARCHAR(255),
  urls VARCHAR(255)

) DEFAULT CHARSET=utf8;

INSERT INTO
  temp.test_report_strings
SET
  chars = LEFT(MD5(RAND()), 10),
  varchars = LEFT(MD5(RAND()), 10),
  binarys = CHAR(
    0xC2,0xA5,
    0xC2,0xA3,
    0xE2,0x82,0xAC,
    0xC2,0xA2,
    0xE2,0x82,0xA1,
    0xE2,0x82,0xA2,
    0xE2,0x82,0xA3,
    0xE2,0x82,0xA4,
    0xE2,0x82,0xA5,
    0xE2,0x82,0xA6,
    0xE2,0x82,0xA7,
    0xE2,0x82,0xA8,
    0xE2,0x82,0xA9,
    0xE2,0x82,0xAA,
    0xE2,0x82,0xAB,
    0xE2,0x82,0xAD,
    0xE2,0x82,0xAE,
    0xE2,0x82,0xAF
  ),
  varbinarys = CHAR(
    0xC2,0xA5,
    0xC2,0xA3,
    0xE2,0x82,0xAC,
    0xC2,0xA2,
    0xE2,0x82,0xA1,
    0xE2,0x82,0xA2,
    0xE2,0x82,0xA3,
    0xE2,0x82,0xA4,
    0xE2,0x82,0xA5,
    0xE2,0x82,0xA6,
    0xE2,0x82,0xA7,
    0xE2,0x82,0xA8,
    0xE2,0x82,0xA9,
    0xE2,0x82,0xAA,
    0xE2,0x82,0xAB,
    0xE2,0x82,0xAD,
    0xE2,0x82,0xAE,
    0xE2,0x82,0xAF
  ),
  tinyblobs = UNHEX(LEFT(MD5(RAND()), 20)),
  mediumblobs = UNHEX(LEFT(MD5(RAND()), 20)),
  longblobs = UNHEX(LEFT(MD5(RAND()), 20)),
  tinytexts = LEFT(MD5(RAND()), 10),
  mediumtexts = LEFT(MD5(RAND()), 10),
  longtexts = LEFT(MD5(RAND()), 10),
  number_in_string = CAST(ROUND(RAND() * 123456789012345, 0) AS CHAR(20)),
  address = '100 main st, la, ca',
  emails = CONCAT(LEFT(MD5(RAND()), 5), '@example.com'),
  urls = CONCAT('http://www.example.com/', LEFT(MD5(RAND()), 10));

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
  temp.test_report_strings t;
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/_runsql.php';

# EOF