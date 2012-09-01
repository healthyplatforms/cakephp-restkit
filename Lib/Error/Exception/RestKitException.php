<?php

/**
 * Description of RestException
 *
 * @author rob
 */
class RestKitException extends CakeException {

	/**
	 * The first element in the arguments array should be a single element while all
	 * other data needs to be passed as key/value pairs.
	 *
	 * The HTTP Status Code can be used as long as it exists in availble list of
	 * CakePHP Status Codes. Otherwise it will fall back to using 500.
	 *
	 * The example below generates the string 'It seems that a RestError has occured' and
	 * automatically makes it available as $name in the view whilst also setting variables
	 * $message and $moreInfo for the view.
	 *
	 * throw new RestException(array('RestError', 'message' => 'Just testing', 'moreInfo' => 'http://www.bravo-kernel.com/errors/123'), 501);
	 *
	 * Please note that we could also pass in an array as the second element in the argument array.
	 * That way we can make the array available in the view instead of several variables.
	 * In the example below we would have the array $myDetails available in the view.
	 *
	 * throw new RestException(array('RestError', 'myDetails' => array('message' => 'Just testing', 'moreInfo' => 'http://www.bravo-kernel.com/errors/123')), 501);
	 *
	 * And finally any combination of arrays and variables is also possible if you feel like it:
	 *
	 * throw new RestException(array('RestError', 'myDetails' => array('message' => 'Just testing', 'moreInfo' => 'http://www.bravo-kernel.com/errors/123'), 'foo' => 'bar'), 501);
	 */


	/**
	 * _construct() is used to prevent internal errors wheb throwing a RestKitException without parameters
	 *
	 * @param string $message
	 * @param type $code
	 */
	public function __construct($message = null, $code = 666) {
		if (empty($message)) {
			$message = 'RestKit Error';
		}
		parent::__construct($message, $code);
	}
}