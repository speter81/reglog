<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Security;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
	return include APP_PATH . "/config/config.php";
});

$di->setShared('url', function () use ($di) {
	$config = $di->getConfig();
	$url = new UrlResolver();
	$url->setBaseUri($config->application->baseUri);
	return $url;
});

$di->setShared('view', function () use ($di) {
	$config = $di->getConfig();

	$view = new View();
	$view->setDI($di);
	$view->setViewsDir($config->application->viewsDir);

	$view->registerEngines([
		'.phtml' => PhpEngine::class
	]);

	return $view;
});

$di->setShared('db', function () use ($di) {
	$config = $di->getConfig();

	$class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
	$connection = new $class([
		'host'     => $config->database->host,
		'username' => $config->database->username,
		'password' => $config->database->password,
		'dbname'   => $config->database->dbname,
		'charset'  => $config->database->charset
	]);

	return $connection;
});


$di->setShared('modelsMetadata', function () {
	return new MetaDataAdapter();
});

$di->set('flash', function () {
	return new Flash([
		'error'   => 'alert alert-danger',
		'success' => 'alert alert-success',
		'notice'  => 'alert alert-info',
		'warning' => 'alert alert-warning'
	]);
});

$di->setShared('session', function () {
	$session = new SessionAdapter();
	$session->start();
	return $session;
});

$di->set('router', function() {
	require __DIR__ . '/router.php';
	return $router;
	}
);

$di->set('security', function () {
		$security = new Security();
		$security->setWorkFactor(12);
		return $security;
	},
	true
);

$di->set('userAuth', function () {
	$userAuth = new UserAuth\Auth;
	return $userAuth;
	}
);

$di->setShared('mailer', function() {
	include APP_PATH.'/library/SwiftMailer/lib/swift_required.php';
	$transport = Swift_MailTransport ::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);
	return $mailer;
});
