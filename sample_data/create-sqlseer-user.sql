-- Copyright (c) 2010-2015 Ross Smith II. MIT Licensed.

/* per http://dev.mysql.com/doc/refman/5.1/en/adding-users.html :

It is necessary to have both accounts for monty to be able to connect from anywhere as monty.
Without the localhost account, the anonymous-user account for localhost that is created by mysql_install_db
would take precedence when monty connects from the local host.

As a result, monty would be treated as an anonymous user.
The reason for this is that the anonymous-user account has a more specific Host column value
than the 'monty'@'%' account and thus comes earlier in the user table sort order.

(user table sorting is discussed in Section 5.4.4, “Access Control, Stage 1: Connection Verification”.)

*/

CREATE USER 'sqlseer'@'%';
CREATE USER 'sqlseer'@'localhost';

REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'sqlseer'@'%';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'sqlseer'@'localhost';

GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON employees.* TO 'sqlseer'@'%';
GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON sakila.* TO 'sqlseer'@'%';
GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON sakila_spatial.* TO 'sqlseer'@'%';
GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON world.* TO 'sqlseer'@'%';

GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON employees.* TO 'sqlseer'@'localhost';
GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON sakila.* TO 'sqlseer'@'localhost';
GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON sakila_spatial.* TO 'sqlseer'@'localhost';
GRANT CREATE TEMPORARY TABLES, EXECUTE, SELECT, SHOW VIEW ON world.* TO 'sqlseer'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER
ON temp.* TO 'sqlseer'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER
ON temp.* TO 'sqlseer'@'localhost';

SET @password = CONCAT(
CONV(LEFT(SHA1(UUID()),8),16,36),
CONV(LEFT(SHA1(UUID()),8),16,36),
CONV(LEFT(SHA1(UUID()),8),16,36),
CONV(LEFT(SHA1(UUID()),8),16,36));

UPDATE mysql.user SET password = PASSWORD(@password) WHERE user = 'sqlseer';

FLUSH PRIVILEGES;

SELECT CONCAT('The sqlseer user has been created with the password ', @password) AS note;

SET @password='';

-- EOF
