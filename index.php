<?php

require_once dirname(dirname(__FILE__)).'/src/SimplePDOWrapper.php';

// Declare database configuration.
$conf = array(
	'database' => 'your_db_name',
	'username' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

$SimplePDOWrapper = new SimplePDOWrapper($conf);


// Switch database with setDatabase() method and pass credentials in $conf array.
$conf = array(
	'database' => 'another_db',
	'username' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

// Will return boolean.
$SimplePDOWrapper->setDatabase($conf);

// Watch for errors through @var $errors.
// Errors is an array with code and message.
$SimplePDOWrapper->errors;

// After saving you will receive the last saved entity.
$save = array(
	'id' => 10,
	'username' => 'jaime.ziga@gmail.com',
	'password' => 'Dude, it\'s private…',
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
))

// Watching errors
if (!$updated || $SimplePDOWrapper->errors)
{
	var_dump($SimplePDOWrapper->errors['code']);
	var_dump($SimplePDOWrapper->errors['message']);
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
