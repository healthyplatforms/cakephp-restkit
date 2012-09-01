<?php

$config['RestKit'] = array(
    'version' => '1.0.0',

    'Request' => array(
	'enabledExtensions' => array('xml', 'json'),
	'prefix' => 'v1',						// prefix string to enable, false to disable
	'forcePrefix' => false						// true will disable the default CakePHP routes (allowing only prefixed access)
    ),

    'Response' => array(
	'moreInfo' => 'http://www.bravo-kernel.com/docs/errors',	// base URL pointing to your API error documentation
	'statusCodes' => array(						// override or append the default CakePHP HTTP Status Codes
	    428 => 'Precondition Required',				// proposed draft
	    429 => 'Too Many Requests',					// proposed draft
	    431 => 'Request Header Fields Too Large',			// proposed draft
	    511 => 'Network Authentication Required',			// proposed draft
	    666 => 'Something Very Evil',				// custom (non-standard REST!)
	)
    ),

    'enableOptionValidation' => true

);