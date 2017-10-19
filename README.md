# Introduction

SimplePDOWrapper's purpose is to help you handle simple database actions with a reduced
amount of code as well as writting less-messy code following a simple structure.

You can:
 * Save
 * Update
 * Delete (with conditions)
 * Delete all (without conditions)
 * Find one
 * Find all
 * Set database (switch on the fly)
 * Error handling through public var @errors

# Dependencies

 * [PHP PDO](http://php.net/manual/en/book.pdo.php)

# PHP Version

This class is compatible with **PHP 5.0 and above** due to the **PHP PDO** dependency.

# Installing
Add this library to your [Composer](https://packagist.org/packages/adrian0350/simple-pdo-wrapper) configuration. In
composer.json:
```json
  "require": {
    "adrian0350/simple-pdo-wrapper": "1.*"
  }
```

### Or

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
## Options param
Options are available with
* conditions
  * ```>```
  * ```<```
  * ```>=```
  * ```<=```
  * ```!=```
  * ```LIKE```
  * And more complex clauses: ```(UNIX_TIMESTAMP(calldate) + callduration) >=```
* limit
* fields
* order

```
// For now conditions only has basic clause.
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

## findOne & findAll
This methods receive 3 params in total, entity (table), options (see above) and
assoc (boolean) to get an associative array or STDClass object.
```
// This findOne will return the one entity array or empty array.
$user = $SimplePDOWrapper->findOne('users', $options, $assoc = true);

// And findAll will return empty array or an array of STDClass objects.
$users = $SimplePDOWrapper->findAll('users', $options, $assoc = false);
```

## delete & deleteAll
The only difference between delete and deleteAll is that
'delete' receives the options argument with 'conditions'.
```
// Options just needs compliant conditions.
$options = array(
    'conditions' => array(
        'id' => 666
    )
);

// Boolean
$deleted = $this->SimplePDOWrapper->delete('users', $options);

// Boolean
$deleted = $this->SimplePDOWrapper->deleteAll('users');
```

## Switch database
Just like instantiating the class.
```
// Switch database with setDatabase() method
// and pass credentials in $conf array.
$conf = array(
	'database' => 'another_db',
	'username' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

// Will return boolean.
$SimplePDOWrapper->setDatabase($conf);
``````

## Handling errors
Since it's internally set to handle errors you can handle them like this.
```
// As I mentioned before update method will return a boolean value.
$updated = $SimplePDOWrapper->update('users', $update, array(
	'conditions' => array(
		'id' => $user_saved['id']
	)
))

// Watching errors
if (!$updated || $SimplePDOWrapper->errors)
{
	var_dump($SimplePDOWrapper->errors['code']);
	var_dump($SimplePDOWrapper->errors['message']);
}
```
