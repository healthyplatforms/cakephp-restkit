<?php

/**
 * Description of AppExceptionRenderer
 *
 * @author bravo-kernel
 *
 * TODO make the moreInfo URL configurable
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
* rest() is needed here because we defined RestException
* @param type $error
*
* TODO replace static errorCode
*/
	public function restKit(RestKitException $error) {

		// Define our custom error-information here
		$attributes = $error->getAttributes();
		$message = $attributes['message'];
		$errorCode = $attributes['errorCode'];

		// Cake's default error variables
		$url = $this->controller->request->here();
		$statusCode = $error->getCode();
		$this->controller->response->statusCode($statusCode);

		$moreInfo = "http://www.bravo-kernel.com/docs/errors/$errorCode";

		$this->controller->set(array(
		    'status' => $statusCode,
		    'message' => $attributes['message'],
		    'code' => $errorCode,
		    'moreInfo' => $moreInfo,
		    '_serialize' => array('status', 'message', 'code', 'moreInfo') // don't forget to serialize custom info here as well
		));

		// this will make sure rest.ctp is used
		// TODO: enable for automatically rendered JSON/XML as well
		$this->_outputMessage($this->template);
	}


/**
 * Override to return 500 xml/json errors in standardized format
 *
 * @param Exception $error
 * @return void
 */
	public function error500($error) {
		$message = $error->getMessage();
		if (Configure::read('debug') == 0) {
			$message = __d('cake', 'An Internal Error Has Occurred.');
		}
		$url = $this->controller->request->here();
		$code = ($error->getCode() > 500 && $error->getCode() < 506) ? $error->getCode() : 500;
		$this->controller->response->statusCode($code);

		$this->controller->set(array(
			'status' => 500,
			'message' => 'An internal error has occurred that prevented A.P.E. from processing your response.',
			'code' => 12001,
			'moreInfo' => 'http:///www.bravo-kernel.com/docs/errors/12001',
			//'name' => $message,
			//'message' => h($url),
			'error' => $error,
			'_serialize' => array('status', 'message', 'code', 'moreInfo')
		));
		$this->_outputMessage('error500');
	}

}