<?php

// We need to load our config file here because initializing it from the App's bootstrap.php
// using CakePlugin::load(array('RestKit' => array('bootstrap' => true))
// would only do a require() and not a load() making the settings unavailable
// for use inside the plugin.
Configure::load('RestKit.config');

// We override the default ExceptionHandler with our own RestKitExceptionHandler here
Configure::write('Exception', array(
	'handler' => 'ErrorHandler::handleException',
	'renderer' => 'RestKit.RestKitExceptionRenderer',
	'log' => true
));
