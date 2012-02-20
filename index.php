<?php
include('system/include.php');

$app = new MVC\Application(array(
	'db'           => array(
		'dsn'      => 'a_pdo_dsn',
		'user'     => 'username',
		'pass'     => 'password',
	),
	'routes'       => array(
		'index/index'
	),
	'defaultRoute' => 'index/index'
);

$app->output();