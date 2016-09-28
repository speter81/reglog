<?php

use Phalcon\Mvc\Router;

$router = new Router();
$router->setDefaults(
	[
		'controller' => "user",
		'action'     => "login",
	]
);

$router->add(
	':controller/:action/:params',
	[
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	]
);
