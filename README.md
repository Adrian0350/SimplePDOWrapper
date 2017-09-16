# Introduction

SimplePDOWrapper's purpose is to help you handle simple database actions with a reduced
amount of code as well as writting less-messy code following a simple structure.

# Dependencies

 * [PHP PDO](http://php.net/manual/en/book.pdo.php)

# PHP Version

This class is compatible with *PHP 5.0 and above* due to the *PHP PDO* class dependency.

# Installing
Add this library to your [Composer](https://packagist.org/packages/adrian0350/simple-pdo-wrapper) configuration. In
composer.json:
```json
  "require": {
    "adrian0350/simple-pdo-wrapper": "1.*"
  }
```

## OR

If you're using bash.
```
$ composer require adrian0350/simple-pdo-wrapper
```

# Usage

For usage just call the methods from your SimplePDOWrapper instance object.
```
<?php

require_once dirname(dirname(__FILE__)).'/src/SimplePDOWrapper.php';

$conf = array(
	'database' => 'your_db_name',
	'username' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

$SimplePDOWrapper = new SimplePDOWrapper($conf);
```
## Save New Entry

```
// After saving you will receive the last saved entity.
$save = array(
	'id' => 10,
	'username' => 'jaime.ziga@gmail.com',
	'password' => 'Dude, it\'s private…',
	'name' => 'John Doe'
);
$user_saved = $SimplePDOWrapper->save('users', $save);
```

## Update New Entry

```
// When updating it will only return true or false.
$update = array(
	'name' => 'Adrián Zúñiga'
);
$SimplePDOWrapper->update('users', $update, array(
	'conditions' => array(
		'id' => $user_saved['id']
	)
));
```

## Find Single Entry

Options are available with
* conditions
* limit
* fields
* order

```
// For now conditions are very simple:
$options = array(
	'conditions' => array(
			'username' => 'john.doe@email.com'
		),
	'limit' => 10,
	'fields' => array('id', 'username', 'password', 'name'),
	'order' => array('id DESC')
);
```

## Save New Entry
This methods receive 3 params in total, entity (table), options (see above) and
assoc (boolean) to get an associative array or STDClass object.
```
// This findOne will return the one entity array or null.
$user = $SimplePDOWrapper->findOne('users', $options, $assoc = true);

// And findAll will return null or an array of STDClass objects.
$users = $SimplePDOWrapper->findAll('users', $options, $assoc = false);
```
