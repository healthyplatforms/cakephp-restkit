<?php

/**
 * Set up required REST configuration using mapResources() and enableExtensions()
 * as described in http://book.cakephp.org/2.0/en/development/rest.html
 */

App::uses('RestKitComponent', 'RestKit.Controller/Component');

	// pass routing over to functions in our Component so we can easily
	// add more complex functional shizzle like optional prefixing etc.
	RestKitComponent::routes();
