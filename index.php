<?php

require_once dirname(__FILE__).'/src/SimplePDOWrapper.php';

// Declare database configuration.
$conf = array(
	'database' => 'your_db_name',
	'user' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

$SimplePDOWrapper = new SimplePDOWrapper($conf);


// Switch database with setDatabase() method and pass credentials in $conf array.
$conf = array(
	'database' => 'another_db',
	'user' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

// Will return boolean.
if (!$SimplePDOWrapper->setDatabase($conf))
{
	// Watch for errors through @var $errors.
	// Errors is an array with code and message.
	throw new Exception($SimplePDOWrapper->errors['message'], $SimplePDOWrapper->errors['code']);
}

// After saving you will receive the last saved entity.
$save = array(
	'id' => 10,
	'username' => 'jaime.ziga@gmail.com',
	'password' => 'thypassword',
	'name' => 'John Doe'
);
$user_saved = $SimplePDOWrapper->save('users', $save);

// When updating it will only return true or false.
$update = array(
	'name' => 'Adrián Zúñiga'
);

$updated = $SimplePDOWrapper->update('users', $update, array(
	'conditions' => array(
		'id' => $user_saved['id']
	)
));

// Watching errors
if (!$updated)
{
	throw new Exception($SimplePDOWrapper->errors['message'], $SimplePDOWrapper->errors['code']);
}

// For now conditions only has basic clause.
$options = array(
	'conditions' => array(),
	'limit' => 10,
	'fields' => array('id', 'username', 'password', 'name'),
	'order' => array('id DESC')
);

// This findOne will return the one entity array or null.
$user = $SimplePDOWrapper->findOne('users', $options);

// And findAll will return null or an array of STDClass objects.
$users = $SimplePDOWrapper->findAll('users', $options);

var_dump($user);
var_dump($users);
var_dump($updated);
