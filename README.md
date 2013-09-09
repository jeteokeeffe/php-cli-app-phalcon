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
```

Special Flags
---------------------

Enable debug mode to see a more detailed overview of what is going on `--debug`

```bash
php cli.php Example test1 --debug
```

Record all output to the database (in the task table) `--record`

```bash
php cli.php Example test1 --record
```

Only allow 1 instance to run at a time `--single`
```bash
php cli.php Example test1 --single
```

Enable all flags
```bash
php cli.php Example test1 --debug --record --single
```

Adding New Tasks
--------------------


```php
<?php

namespace Tasks;

class NewTask extends \Phalcon\Cli\Task {

    public function workAction() {
	Output::stdout("hi");
    }
}
?>
```


Autoloading new Classes
--------------------

Open `php-cli-app-phalcon/app/config/autoload.php` and an element to the existing array.
So, you have to use namespacing to load new classes.

```php

```
