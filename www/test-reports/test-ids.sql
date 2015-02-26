/* Copyright (c) 2010-2015 Ross Smith II. MIT Licensed. */

CREATE DATABASE IF NOT EXISTS temp; /*autorun=true*/

DROP TABLE IF EXISTS temp.ids;

CREATE TABLE IF NOT EXISTS temp.ids (
    id TINYINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY
) ENGINE=InnoDB;

SET sql_mode='NO_AUTO_VALUE_ON_ZERO';

INSERT INTO
  temp.ids
SELECT 0 UNION
SELECT NULL; -- 2

INSERT INTO
  temp.ids
SELECT
  NULL
FROM
   temp.ids a -- 2 + 2
  ,temp.ids b -- 2 + 4
  ,temp.ids c -- 2 + 8
  ,temp.ids d -- 2 + 16
  ,temp.ids e -- 2 + 32
  ,temp.ids f -- 2 + 64
  ,temp.ids g -- 2 + 128
  ,temp.ids h -- 2 + 256
LIMIT 254;

SELECT /*autorun=true*/
    *
FROM
    temp.ids;
