<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces([
	'Phalcon' => $config->application->libraryDir.'/Phalcon/',
	'UserAuth' => $config->application->libraryDir.'/UserAuth/'
]);

$loader->registerDirs(
	[
		$config->application->controllersDir,
		$config->application->modelsDir
	]
	)->register();

