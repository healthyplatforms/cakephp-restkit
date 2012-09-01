<?php

$config['RestKit'] = array(

    'version' => '1.0.0',

    'Request' => array(
	'enabledExtensions' => array('xml', 'json'),
	'prefix' => 'v1', // prefix string to enable, false to disable
	'forcePrefix' => false   // true will allow access through the prefix path only (disables default CakePHP routes)
    ),

    'enableOptionValidation' => true,

    // Override or expand default CakePHP HTTP Status Codes
    'statusCodes' => array(
	428 => 'Precondition Required',			// proposed draft
	429 => 'Too Many Requests',			// proposed draft
	431 => 'Request Header Fields Too Large',	// proposed draft
	511 => 'Network Authentication Required',	// proposed draft
	666 => 'Something Very Evil',			// a completely custom status code
    )
);