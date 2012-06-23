<?php

/**
 * Set up REST configuration as described in http://book.cakephp.org/2.0/en/development/rest.html
 *
 * @todo move logic into component (using Use::) so we can expand logic
 * (e.g. stripping leading plugin paths, etc)
 */

App::uses('RestKitComponent', 'RestKit.Controller/Component');

	// pass routing over to functions in our Component so we can easily
	// add more complex functional shizzle like optional prefixing etc.
	RestKitComponent::routes();
