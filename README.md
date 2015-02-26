# SqlSeer [![Flattr this][flatter_png]][flatter]

Easily view, sort, filter, and download SQL data, using GUI, or custom written SQL or PHP.

## Quick Start

To install sqlseer, run:
````bash
git clone https://github.com/rasa/sqlseer
cd sqlseer
composer install
vi app/configs/dbconfig.ini
````
Then, set up your web server to point to `sqlseer/www`. For example, with Apache 2.3 or earlier, use:

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

### Export Formats

SqlSeer can export in the following formats:

* Comma separated values (.csv)
* HTML (.html)
* Plain Text (tab separated values) (.txt)
* Microsoft Excel (.xls)
* XML (.xml) (Same as [`mysql --xml`][option_mysql_xml] output)
* Zip (.zip)

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
