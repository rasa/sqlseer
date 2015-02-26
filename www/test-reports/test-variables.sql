/* Copyright (c) 2010-2015 Ross Smith II. MIT Licensed. */

SET @rand := RAND();

SELECT /*autorun=true*/
  @rand AS 'var',
  RAND() AS 'rand'
FROM
dual;
