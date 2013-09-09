php-cli-app-phalcon
===================

Command Line Application built using phalcon framework

Requirements
---------
PHP 5.4 or greater


Required PHP Modules
- Phalcon

To check for those modules
```bash
php -m | grep -i "phalcon"
```

Database Configuration
--------------
Open  `php-cli-app-phalcon/app/config.php` and edit your database connection credentials

```php
$settings = array(
        'database' => array(
                'adapter' => 'Mysql',
                'host' => 'localhost',
                'username' => 'test',
                'password' => 'test',
                'name' => 'cli',
                'port' => 3306
        ),
);
```

Import the tables into your mysql database
```bash
mysql -u root -p api < php-cli-app-phalcon/data.sql
```

Command Line Examples
----------------------

Basic example of how to kick off the cli framework

```bash
cd php-cli-app-phalcon/private
php cli.php Example test1 
php cli.php Example main
```
