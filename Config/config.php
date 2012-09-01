<?php

$config['RestKit'] = array(

    'Request' => array(
	'enabledExtensions' => array('xml', 'json'),
	'prefix' => 'v1',			// prefix string to enable, false to disable
	'forcePrefix' => false			// true will allow access through the prefix path only (disables default CakePHP routes)
	),

    'version' => '1.0.0',
    'enableOptionValidation' => true,

    // Override default CakePHP HTTP Status Codes or add custom ones
    'statusCodes' => array(
		429 => 'Too Many Requests',
		666 => 'Something Very Evil',
    )
);