<?php

/**
 * Description of RestException
 *
 * @author bravo-kernel
 */
class RestKitException extends CakeException {

	/**
	 * _construct() is used so we can throw RestKitException without any parameters
	 *
	 * HTTP Status Code 500 is used by default but you can also use your own custom
	 * status codes (just define them in the statuscodes array in the config file).
	 *
	 * @param string $message
	 * @param type $code
	 */
	public function __construct($message = null, $code = 500) {
		if (empty($message)) {
			$message = 'RestKit Error';
		}
		parent::__construct($message, $code);
	}

}