<?php

use Phalcon\Mvc\Router;

// Create the router
$router = new Router();
$router->setDefaults(
	[
		'controller' => "user",
		'action'     => "login",
	]
);
// Define a route
$router->add(
	':controller/:action/:params',
	[
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	]
);
