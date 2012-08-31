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

	/**
	 * restKit() is needed here because we defined RestException
	 *
	 * @param type RestKitException $error
	 * return void
	 */
	public function restKit(RestKitException $error) {

		$this->_setRichErrorInformation($error);
		$this->controller->response->statusCode($error->getCode());  // this will use our custom HTTP Status Code (if defined in config)
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
	private function _setRichErrorInformation(CakeException $error) {

		$url = $this->controller->request->here();
		$code = $error->getCode();

		$message = h($error->getMessage());
		if (Configure::read('debug') == 0) {
			$httpCode = $this->controller->response->httpCodes($code);
			if ($httpCode){
				$message = $httpCode[$code];
			}else{
				$message = "Unknown HTTP Status Code Detected";
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