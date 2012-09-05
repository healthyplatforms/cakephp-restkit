<?php

/**
 * Description of AppExceptionRenderer
 *
 * @author bravo-kernel
 *
 * @todo make the moreInfo URL configurable
 */
App::uses('ExceptionRenderer', 'Error');
App::uses('CakeErrorController', 'Controller');

class RestKitExceptionRenderer extends ExceptionRenderer {

	public $controller = null;
	public $template = '';
	public $error = null;
	public $method = '';

	/**
	 * _getController() is an override of the default Cake method (in subclasses) and is used
	 * to send CUSTOM HTTP Status Codes
	 *
	 * @param Exception $exception The exception to get a controller for.
	 * @return Controller
	 */
	protected function _getController($exception) {
		$controller = parent::_getController($exception);
		$controller->response->httpCodes(Configure::read('RestKit.Response.statusCodes'));
		return $controller;
	}

	/**
	 * restKit() is used when throwing a RestKitException
	 *
	 * Calling it with
	 *
	 * @todo fix crash when throwing RestKitException() without message-string
	 *
	 * @param type RestKitException $error
	 * return void
	 */
	public function restKit(RestKitException $error) {

		$this->_setRichErrorInformation($error);
		$this->_outputMessage('restkit');  // this will make sure restkit.ctp is used
	}

	/**
	 * _cakeError() overrides the default Cake function so we can respond with rich XML/JSON errormessages
	 *
	 * @param CakeException $error
	 * @return void
	 */
	protected function _cakeError(CakeException $error) {
		$this->_setRichErrorInformation($error);
		$this->controller->set($error->getAttributes());
		$this->_outputMessage($this->template);
	}

	/**
	 * error400() overrides the default Cake function so we can respond with rich XML/JSON errormessages
	 *
	 * @param CakeException $error
	 * @return void
	 */
	public function error400($error) {
		$this->_setRichErrorInformation($error);
		$this->_outputMessage('error400');
	}

	/**
	 * error500() overrides the default Cake function so we can respond with rich XML/JSON errormessages
	 *
	 * @param CakeException $error
	 * @return void
	 */
	public function error500($error) {
		$this->_setRichErrorInformation($error);
		$this->_outputMessage('error500');
	}

	/**
	 * _setRichErrorInformation() is used to set up extra variables required for producing
	 * rich REST error-information
	 *
	 * Please note that only the serialized variables will appear in the JSON/XML output and
	 * will appear in the same order as they are serialized here.
	 *
	 * Also note that we set $name and $url here as well because they are required by the default HTML error-views.
	 *
	 * @todo add a check to detect REST or HTML so we can fill the REST errors with more meaningfull
	 * messages in production environments. Simply put: 'not found' will now appear in the REST response
	 * even though Access Denied would be better (also keeping the moreInfo link in mind).
	 *
	 * @param CakeException $error
	 */
	private function _setRichErrorInformation($error) {

		// set up variables
		$url = $this->controller->request->here();
		$code = $error->getCode();
		$message = $this->_getRichErrorMessage($error);

		// set the correct response header
		$this->_setHttpResponseHeader($code);

		// set variables for both view- and viewless JSON/XML
		$this->controller->set(array(
		    'name' => $message,
		    'url' => $url,
		    'status' => $code,
		    'message' => $message,
		    'code' => $error->getCode(),
		    'moreInfo' => $this->_getMoreInfo($error->getCode()),
		    'error' => $error
		));

		if (Configure::read('debug') == 0) {
			$this->controller->set(array('_serialize' => array(
				'status',
				'message',
				'code',
				'moreInfo'
				)));
		} else {
			$this->controller->set(array('_serialize' => array(
			'status',
			'message',
			'code',
			'moreInfo',
			'trace'
			)));
		}
	}

	/**
	 * _getRichErrorMessage() is used to return the appropriate error-message.
	 *
	 * When debug=0 all error messages (except those of type RestKitException)
	 * will be reset to the corresponding HTTP Status Code as found in
	 * CakeResponse::httpCodes() to prevent sensitive information appearing
	 * to the public.
	 *
	 * For CakeExceptions we retrieve the message using $error->getMessage().
	 * For RestKitExceptions we retrieve the message using:
	 * - either $error->getMessage() when the exception was declared using the shortcut form
	 * - or by parsing the $error->getAttributes() array if the exception was declared using the options array
	 *
	 * @param $error
	 * @return string
	 */
	private function _getRichErrorMessage($error) {

		// non-debug mode so set the CakeException message to the HTTP Status Code description
		if (Configure::read('debug') == 0 && (!$error instanceof RestKitException)) {
			$message = $this->controller->response->httpCodes($error->getCode());
			$message = $message[$error->getCode()];
			return $message;
		}

		// debug mode so show the full message
		$message = h($error->getMessage());
		if ($error instanceof RestKitException && (!$message)) { // option-array passed
			$attributes = $error->getAttributes();
			$message = $attributes['message'];
		}
		return $message;
	}


	/**
	 * _setHttpResponseHeader() is used to set the HTTP Response Header.
	 *
	 * Will reset $code to 500 if the passed code is not present in the
	 * RequestResponse::httpCodes() array to prevent an internal error.
	 *
	 * @param int $code
	 * @return void
	 */
	private function _setHttpResponseHeader($code = null){
		$httpCode = $this->controller->response->httpCodes($code);
		if ($httpCode[$code]) {
			$this->controller->response->statusCode($code);
		} else {
			$this->controller->response->statusCode(500);
			$code = 500;
		}
	}

	/**
	 * _getMoreInfo() auto-generates a moreInfo link by using the moreInfo base URL
	 * specified in the RestKit configfile) and then appending the HTTP Status Code.
	 *
	 * CakeExceptions will lead to links using the "valid" HTTP Status Code, e.g.:
	 * - http://www.yourapidocs.com/403
	 * - http://www.yourapidocs.com/500
	 *
	 * RestKitExceptions will lead to links using the default 500 or the $code parameter passed, e.g.:
	 * - http://www.apidocs.com/500
	 * - http://www.apidocs.com/12004
	 *
	 * @todo add support for link extension?
	 *
	 * @param type $error
	 * @return string
	 */
	private function _getMoreInfo($code) {
		$moreInfo = Configure::read('RestKit.Response.moreInfo') . '/' . $code;
		return $moreInfo;
	}

}