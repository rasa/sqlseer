/* Copyright (c) 2010-2015 Ross Smith II. MIT Licensed. */

CREATE DATABASE IF NOT EXISTS temp; /*autorun=true*/

DROP TABLE IF EXISTS temp.test_report_dates;

CREATE TABLE temp.test_report_dates
(
  dates DATE, --
  times TIME, --
  timestamps TIMESTAMP, --
  datetimes DATETIME, --
  years YEAR,
  i INT DEFAULT 1 --
);

-- SET GLOBAL  time_zone = '+00:00';
SET SESSION time_zone = @@global.time_zone;

SET @date = '0000-00-00'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '0000-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '0001-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '0069-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '0070-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '0099-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '0100-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1899-12-31'; SET @time = '23:59:59'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1900-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1901-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1969-12-31'; SET @time = '23:59:59'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1970-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1970-01-01'; SET @time = '00:00:01'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '1970-01-01'; SET @time = '08:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = CURDATE(); SET @time = CURTIME(); SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2038-01-19'; SET @time = '03:14:07'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2038-01-19'; SET @time = '03:14:08'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2038-01-19'; SET @time = '03:14:09'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2099-12-31'; SET @time = '23:59:59'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2100-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2155-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2156-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '2999-12-31'; SET @time = '23:59:59'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '3000-01-01'; SET @time = '00:00:00'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SET @date = '9999-12-31'; SET @time = '23:59:59'; SET @datetime = CONCAT(@date, ' ', @time);
INSERT INTO temp.test_report_dates SET dates = @date , times = @time, timestamps = @datetime, datetimes = @datetime, years = @date;

SELECT /*autorun=true*/
  DATE_FORMAT(t.datetimes, /*groupby=*/'%Y-%m-%d') AS groupby,
  t.dates,
  t.times,
  t.timestamps,
  t.datetimes,
  t.years,
  SUM(t.i) AS sum_of_i
FROM
  temp.test_report_dates t
GROUP BY 1
ORDER BY 1;
