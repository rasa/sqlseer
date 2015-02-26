/* Copyright (c) 2010-2015 Ross Smith II. MIT Licensed. */

SET @row := 0;

SELECT /*autorun=true*/ /*row_column=0*/
  (@row := @row + 1) AS 'row',
  c.*
FROM
  (
    SELECT DISTINCT a.i * 256 + b.i AS i FROM
      (SELECT a.i * 16 + b.i AS i FROM
          (SELECT a.i * 4 + b.i AS i FROM
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) a,
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) b
          ) a,
          (SELECT a.i * 4 + b.i AS i FROM
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) a,
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) b
          ) b
      ) a,
      (SELECT a.i * 16 + b.i AS i FROM
          (SELECT a.i * 4 + b.i AS i FROM
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) a,
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) b
          ) a,
          (SELECT a.i * 4 + b.i AS i FROM
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) a,
              (SELECT a.i * 2 + b.i AS i FROM (SELECT 0 AS i UNION SELECT 1) a, (SELECT 0 AS i UNION SELECT 1) b) b
          ) b
      ) b
    ORDER BY 1
  ) c
ORDER BY 1;
