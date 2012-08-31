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
	 * Override of default Cake method (in subclasses) to send CUSTOM HTTP Status Codes
	 *
	 * @param Exception $exception The exception to get a controller for.
	 * @return Controller
	 */
	protected function _getController($exception) {
		$controller = parent::_getController($exception);
		$controller->response->httpCodes(Configure::read('RestKit.statusCodes'));
		return $controller;
	}

	protected function _getControllerDISABLED($exception) {
		if (!$request = Router::getRequest(false)) {
			$request = new CakeRequest();
		}
		$response = new CakeResponse(array('charset' => Configure::read('App.encoding')));
		try {
			$controller = new CakeErrorController($request, $response);
		} catch (Exception $e) {
			$controller = new Controller($request, $response);
			$controller->viewPath = 'Errors';
		}

		// make additional custom HTTP Status Codes available
		$controller->response->httpCodes(Configure::read('RestKit.httpCodes'));
		return $controller;
	}

	/**
	 * restKit() is needed here because we defined RestException
	 *
	 * Standard CakePHP errors require the following to properly render:
	 * - variable $name
	 * - $error Object
	 *
	 *
	 * @param type $error
	 * return void
	 */
	public function restKit(RestKitException $error) {

		// Define our custom error-information here
		$attributes = $error->getAttributes();
		$message = $attributes['message'];
		$errorCode = $attributes['errorCode'];

		// The following variables are required by Cake's default error views
		$url = $this->controller->request->here();
		$statusCode = $error->getCode();
		$this->controller->response->statusCode($statusCode);
		$this->controller->response->statusCode(666);

//		$moreInfo = "http://www.bravo-kernel.com/docs/errors/$errorCode";
		// We exclude 'url' from serialization because we do not want it to
		// show up in our JSON/XML responses. We do set them for the controller
		// because the Cake html error-views require it to be present.
//		if (!isset($attributes['message'])){
//			$attributes['message'] = "no message provided";
//		}


		$this->controller->set(array(
		    'status' => '123',
		    'name' => 'fuck you and your name',
		    'message' => 'message',
		    'url' => 'url',
		    'moreInfo' => 'moreInfo',
		    'error' => $error,
		    '_serialize' => array('status', 'name', 'message', 'code', 'moreInfo',) // don't forget to serialize custom info here as well
		));

		// this will make sure rest.ctp is used
		$this->_outputMessage($this->template);
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
	 * @todo differentiate between REST and HTML responses so we can fill the REST errors with meaningfull
	 * messages when in production. Simply put: the message 'not found' will no appear in the REST response
	 * even though Access Denied would be better (also keeping the moreInfo link in mind).
	 *
	 * @param CakeException $error
	 */
	private function _setRichErrorInformation(CakeException $error) {

		$url = $this->controller->request->here();
		$code = ($error->getCode() >= 400 && $error->getCode() < 506) ? $error->getCode() : 500;

		$message = h($error->getMessage());
		if ($debug == 0) {
			if ($code >= 400 && $code < 500) {
				$message = "The requested resource was not found";
			} else {
				$message = 'An internal error prevented processing your request';
			}
		}

		// the HTTP Response Header "Status Code" is set here
		$this->controller->response->statusCode($code);

		$this->controller->set(array(
		    'name' => $message,
		    'url' => $url,
		    'status' => $code,
		    'message' => $message,
		    'code' => $code,
		    'moreInfo' => 'http:///www.bravo-kernel.com/docs/errors/12001',
		    'error' => $error,
		    '_serialize' => array('status', 'message', 'code', 'moreInfo')
		));
	}

}