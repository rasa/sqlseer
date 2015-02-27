# SqlSeer [![Flattr this][flatter_png]][flatter]

Simple, yet powerful, MySQL report generator. Reports can be saved to .csv, .html, .txt, .xls, and .xml formats.

## Quick Start

First, install any [dependencies](#dependencies) that are needed. Then, install sqlseer:

````bash
git clone https://github.com/rasa/sqlseer
cd sqlseer
cp app/configs/dbconfig.ini.example app/configs/dbconfig.ini
chmod 600 app/configs/dbconfig.ini
vi app/configs/dbconfig.ini # add your database credentials
composer install
````
For the SQL scripts in [www/test-reports](www/test-reports) to work, the MySQL user will need to have
CREATE/INSERT/DROP rights on the `test` database. The database does not need to be created beforehand.

Lastly, set up your web server to point to `sqlseer/www`. For example, with Apache 2.3 or earlier, use:

````
<VirtualHost *:80>
   ServerName sqlseer
   DocumentRoot /path/to/sqlseer/www

   <Directory /path/to/sqlseer/www>
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>
</VirtualHost>
````

With Apache 2.4, use:

````
<VirtualHost *:80>
   ServerName sqlseer
   DocumentRoot /path/to/sqlseer/www

   <Directory /path/to/sqlseer/www>
       AllowOverride All
       Require all granted
   </Directory>
</VirtualHost>
````

### Sample Data

To load the MySQL [employee][], [sakila][] and [world][] sample databases, install `make`, and then type:

````bash
cd sample_data
make MYSQL_OPTS="-u user -ppassword"
make distclean
````

where `user` is the MySQL username, and `password` is that user's password.

To remove the employee, sakila, and world databases, type:

````bash
make drop MYSQL_OPTS="-u user -ppassword"
````

### Export Formats

SqlSeer can export in the following formats:

* Comma separated values (.csv)
* HTML (.html)
* Plain Text (tab separated values) (.txt)
* Microsoft Excel (.xls)
* XML (.xml) (Same as [`mysql --xml`][option_mysql_xml] output)
* Zip (.zip)

## Dependencies

SqlSeer depends on the following:

* A web server, such as [Apache]
* [PHP][]
* [Composer][]

### Install Apache

To install Apache in Debian and Debian derivatives, type:

````bash
sudo apt-get install apache2
````

### Install PHP

To install PHP in Debian and Debian derivatives, type:

````bash
sudo apt-get install php5
````

### Install Composer

To install Composer in `~/bin` type:

````bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=~/bin --filename=composer
````

To install Composer in `/usr/local/bin` type:

````bash
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
````

## Contributing

To contribute to this project, please see [CONTRIBUTING.md](CONTRIBUTING.md).

## Bugs

To view existing bugs, or report a new bug, please see [issues](../../issues).

## Changelog

To view the version history for this project, please see [CHANGELOG.md](CHANGELOG.md).

## License

This project is [MIT licensed](LICENSE).

## Contact

This project was created and is maintained by [Ross Smith II][] [![endorse][endorse_png]][endorse]

Feedback, suggestions, and enhancements are welcome.

[Ross Smith II]: mailto:ross@smithii.com "ross@smithii.com"
[flatter]: https://flattr.com/submit/auto?user_id=rasa&url=https%3A%2F%2Fgithub.com%2Frasa%2Fsqlseer
[flatter_png]: http://button.flattr.com/flattr-badge-large.png "Flattr this"
[endorse]: https://coderwall.com/rasa
[endorse_png]: https://api.coderwall.com/rasa/endorsecount.png "endorse"

[option_mysql_xml]: http://dev.mysql.com/doc/refman/5.6/en/mysql-command-options.html#option_mysql_xml
[Apache]: http://httpd.apache.org/
[PHP]: http://php.net/
[Composer]: https://getcomposer.org/
[employee]: https://dev.mysql.com/doc/employee/en/
[world]: https://dev.mysql.com/doc/world-setup/en/
[sakila]: http://dev.mysql.com/doc/sakila/en/