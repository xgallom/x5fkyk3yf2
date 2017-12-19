<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;
$configurator->setDebugMode(false);

$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Bratislava');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$config_local = __DIR__ . '/config/config.local.neon';
if (file_exists($config_local)) {
	$configurator->addConfig($config_local);
}

$container = $configurator->createContainer();

return $container;
