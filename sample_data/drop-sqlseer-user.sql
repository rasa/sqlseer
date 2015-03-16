-- Copyright (c) 2010-2015 Ross Smith II. MIT Licensed.

REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'sqlseer'@'%';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'sqlseer'@'localhost';

DROP USER 'sqlseer'@'%';
DROP USER 'sqlseer'@'localhost';

FLUSH PRIVILEGES;

-- EOF
