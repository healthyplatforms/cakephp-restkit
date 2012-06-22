<?php

/**
 * Set up REST configuration as described in http://book.cakephp.org/2.0/en/development/rest.html
 *
 * @todo move logic into component (using Use::) so we can expand logic
 * (e.g. stripping leading plugin paths, etc)
 */
	Router::mapResources(
		array('Users','Exampreps'),
		array('prefix' => '/v1/')
	);

	// enable extensions
	Router::parseExtensions();
	Router::setExtensions('xml', 'json');
