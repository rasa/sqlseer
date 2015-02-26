/* Copyright (c) 2010-2015 Ross Smith II. MIT Licensed. */

CREATE DATABASE IF NOT EXISTS temp; /*autorun=true*/

DROP TABLE IF EXISTS temp.test_report_dates;

CREATE TABLE temp.test_report_dates
(
  dt DATETIME,
  dummy INT UNSIGNED
);

INSERT INTO temp.test_report_dates SET dt = NOW() - INTERVAL RAND() * 86400 * 365 SECOND, dummy = RAND() * 65536;

INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 2
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 4
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 8
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 16
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 32
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 64
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 128
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 256
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 512
INSERT INTO temp.test_report_dates SELECT NOW() - INTERVAL RAND() * 86400 * 365 SECOND AS dt, RAND() * 65536 AS dummy FROM temp.test_report_dates; -- 1024

SELECT /*autorun=true*/
  DATE_FORMAT(dt, /*groupby=*/'%Y-%m-%d') AS dt,
  AVG(dummy) AS 'avg', -- Return the average value of the argument
  BIT_AND(dummy) AS 'bit_and', -- Return bitwise and
  BIT_OR(dummy) AS 'bit_or', -- Return bitwise or
  BIT_XOR(dummy) AS 'bit_xor', -- Return bitwise xor
  COUNT(DISTINCT dummy) AS 'count_distinct', -- Return the count of a number of different values
  COUNT(dummy) AS 'count', -- Return a count of the number of rows returned
  -- GROUP_CONCAT(dummy) AS 'group_concat', -- Return a concatenated string
  MAX(dummy) AS 'max', -- Return the maximum value
  MIN(dummy) AS 'min', -- Return the minimum value
  -- STD(dummy) AS '-- ', -- Return the population standard deviation
  STDDEV_POP(dummy) AS 'stddev_pop', -- Return the population standard deviation
  STDDEV_SAMP(dummy) AS 'stddev_samp', -- Return the sample standard deviation
  -- STDDEV(dummy) AS '-- ', -- Return the population standard deviation
  SUM(dummy) AS 'sum', -- Return the sum
  VAR_POP(dummy) AS 'var_pop', -- Return the population standard variance
  VAR_SAMP(dummy) AS 'var_samp', -- Return the sample variance
  VARIANCE(dummy) AS 'variance' -- Return the population standard variance
FROM
  temp.test_report_dates t
GROUP BY 1
ORDER BY 1;
