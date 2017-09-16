<?php

require_once dirname(dirname(__FILE__)).'/src/SimplePDOWrapper.php';

$conf = array(
	'database' => 'your_db_name',
	'username' => 'root',
	'password' => 'toor',
	'host' => 'localhost'
);

$SimplePDOWrapper = new SimplePDOWrapper($conf);

$save = array(
	'id' => 10,
	'username' => 'jaime.ziga@gmail.com',
	'password' => 'Dude, it\'s private…',
	'name' => 'John Doe'
);
$user_saved = $SimplePDOWrapper->save('users', $save);

$update = array(
	'name' => 'Adrián Zúñiga'
);
$SimplePDOWrapper->update('users', $update, array(
	'conditions' => array(
		'id' => $user_saved['id']
	)
))

$options = array(
	'conditions' => array(),
	'limit' => 10,
	'fields' => array('id', 'username', 'password', 'name'),
	'order' => array('id DESC')
);

$user = $SimplePDOWrapper->findOne('users', $options);
$users = $SimplePDOWrapper->findAll('users', $options);
