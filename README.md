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
mysql -u root -p api < php-cli-app-phalcon/mysql.data.sql
```

Command Line Examples
----------------------

General Syntax for running a task/job (Note: only Task is required)

```bash
cd php-cli-app-phalcon/private 
php cli.php [Task] [Action] [Param1] [Param2] [...]
```

Tasks are stored in `php-cli-app-phalcon/app/tasks` directory. The following example task is named `ExampleTask.php`.
Basic example of how to kick off a cli job/task.

```bash
cd php-cli-app-phalcon/private
php cli.php Example test1 
```

Passing parameters to your application

```bash
php cli.php Example test2 bob sanders 
```

Special Flags
---------------------

Enable debug mode to see a more detailed overview of what is going on `--debug`

```bash
php cli.php Example cmd --debug
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

Go to `php-cli-app-phalcon/app/tasks` directory. This is where all the tasks are stored.
Just go ahead and create a new file here (eg. NewTask.php)

```php
<?php

namespace Tasks;

use \Cli\Output as Output;

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
$autoload = [
        'Utilities\Debug' => $dir . '/library/utilities/debug/',
	'Trend' => $dir . '/library/trend/'
];

return $autoload;
```
