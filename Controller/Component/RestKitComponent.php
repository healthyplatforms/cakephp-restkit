<?php

App::uses('Component', 'Controller');
App::uses('RestOption', 'Model');

/**
 * Description of RestKitComponent
 *
 * @todo (might have to) build in a check in validateUriOptions for this->controller->$modelName->validates() because it will break if the model has $uses = false or array()
 *
 * @author bravo-kernel
 */
class RestKitComponent extends Component {

/**
* $errorBuffer will hold all error-messages to be included in the response
*/
	protected $_errors = array();

/**
* startup() is used to make the calling Controller available as $this->controller
* and to return 404 errors for all non JSON/XML requests (when enabled in config.php)
*
* NOTE: startup() is called before the controller's beforeFilter()
*
* @param Controller $controller
*/
	public function startup(Controller $controller) {
		$this->controller = $controller;
		$this->checkRequestMethod($controller);
	}

/**
* _parseUriOptions() will use passed array as default options and will validate passed URI options against the Model's validation rules
*
* @param type $default_options
* @return type array
*/
	public function parseUriOptions($default_options) {
		$options = $this->_validateUriOptions($default_options);
		return $options;
	}

/**
* setError() is used to buffer error-messages to be included in the response
*
* @param string $optionName is the exact name of the URI option (e.g. limit, sort, etc)
* @param string $type to specify the type of error (e.g. optionValidation)
* @param string $message with informative information about the error
*/
	public function setError($type, $optionName, $message) {
		array_push($this->_errors, array('Error' => array(
			'type' => $type,
			'option' => $optionName,
			'message' => $message,
			'moreInfo' => 'http://ecloud.alt3.virtual/errors/23532'
			)));
	}

/**
* _getErrors() ......
*
* @todo add documentation
*/
	public function getErrors() {
		return $this->_errors;
	}

/**
* render() is a convenient function used to set data in format as required for Viewless XML/Json rendering
*
* @param type $arrays in standard Cakephp find() result format
* TODO calling without arguments now generates an empty JSON array. Maybe throw a 500 here so we can detect ?
*/
	public function render($arrays = array()) {
		$this->_setViewData($arrays);
	}

/**
* _setViewData() handles setting data and _serialize logic needed to render viewless XML/JSON responses
*
* TODO include debug information only if enabled in app.config(?)
*
* The following will be generated when a single array is passed
* $this->set(array('users' => $users));
* $this->set(array('_serialize' => array('users')));
*
* The following will be generated when multiple arrays are passed
* $this->set(array('users' => $users, 'debug' => $debug));
* $this->set(array('_serialize' => array('users', 'debug')));
*
* NOTE arrays passed (resulting from 'find' queries MUST be re-formatted using the following:
* $users = array('user' => Set::extract('{n}.User', $users));
*
* @param type $arrays
*/
	private function _setViewData($arrays) {

		// add debug information to the JSON response
		$errors = $this->getErrors();
		if (!empty($errors)) {
			$arrays['errors'] = $errors;
		}

		$serializeKeynames = array();
		foreach ($arrays as $arrayKey => $arrayContent) {

			// reformat array to make it compatible with testRenderWithoutViewMulitple format
			$modelLower = Inflector::singularize($arrayKey);    // e.g. user
			$modelName = Inflector::camelize($modelLower);      // e.g. User
			//if (count($arrayContent[0]) == 0){                // extract single root-elements as produced by findById()
			if (!array_key_exists('0', $arrayContent)) {
				$extracted = array(
				    $modelLower => Set::extract($modelName, $arrayContent)
				);
			} else {	   // extract multiple root-elements as produced by find('all')
				$extracted = array(
				    $modelLower => Set::extract('{n}.' . $modelName, $arrayContent)
				);
			}

			// make data available for the view and remember the keyname for _serialize later
			$this->controller->set(array($arrayKey => $extracted));
			array_push($serializeKeynames, $arrayKey);
		}

		// _serialize all arrays at once
		$this->controller->set(array('_serialize' => $serializeKeynames));      // serialize all arrays at once
	}

/**
 * _checkExtension() is used to:
 * - prevent a 500-error for html calls to XML/JSON actions with no existing html views (and instead return a 404)
 * - allow only specific pages to be requested as HTML (e.g. for OAuth or logging in)
 *
 * @TODO decide whether to add support for specified exceptions in controller::action pairs
 *
 * @param void
 */
	public function checkRequestMethod(Controller $controller) {

		// skip if .xml or .json extension is used
		if (in_array($controller->params['ext'], array('xml', 'json'))) {
			return;
		}

		// skip if the accept-header is JSON or XML
		$acceptHeaders = $controller->request->parseAccept();
		if (in_array($acceptHeaders['1.0'][0], array('application/xml', 'application/json'))) {
			return;
		}

		// This request is neither JSON nor XML so return a 404 for all calls
		// that are not in the exceptions array (these will be accessible as html)
		// TODO: make these controller/action pairs
		//if (!in_array($controller->params['controller'], array('OAuth'))) {
		//	throw new NotFoundException();
		//}
	}

	/**
	 * validateUriOptions() merges passed URI options with default options, validates them against the model and resets unvalidated options to the default value.
	 */
	private function _validateUriOptions($default_options = array()) {

		// no URI parameters passed so return (and use) default values
		if (!$this->controller->request->query) {
			return $default_options;
		}

		// construct new arrays with keynames as used in the Model´s validation rules (e.g. option_index_limit)
		$modelDefaults = $default_options;
		$modelDirties = $this->controller->request->query;

		// Merge values (only for dirty keys existing in $default?options)
		$modelMerged = array_intersect_key($modelDirties + $modelDefaults, $modelDefaults);

		// Set data and return the merged array if validation is instantly successfull
		$this->Model = ClassRegistry::init('RestKit.RestOption');
		$this->Model->set($modelMerged);
		if ($this->Model->validates(array('fieldList' => array_keys($modelDefaults)))) {
			return $modelMerged;
		}

		// reset non-validating fields to default values + fill the debug array
		foreach ($this->Model->validationErrors as $key => $value) {
			$modelMerged[$key] = $modelDefaults[$key];       // reset invalidated key
			$key = preg_replace('/.+_/', '', $key);
			$this->setError('optionValidation', $key, $value[0]);
		}
		return $modelMerged;
	}


/**
 * routes() is used to provide the functionality normally used in routes.php like
 * setting the allowed extensions, prefixing, etc.
 */
    public static function routes() {
	   self::_mapResources();
	   self::_enableExtensions();

    }
/**
 * _mapResources() is used to enable REST for controllers + enable prefix routing (if enabled)
 */
	private static function _mapResources() {

		if (Configure::read('RestKit.Request.prefix') == true){
			Router::mapResources(
				array('Users','Exampreps'),
				array('prefix' => '/' . Configure::read('RestKit.Request.prefix') . '/')
			);

			// skip loading Cake's default routes when forcePrefix is disabled in config
			if (Configure::read('RestKit.Request.forcePrefix') == false){
				require CAKE . 'Config' . DS . 'routes.php';
			}
		}else{
			require CAKE . 'Config' . DS . 'routes.php';	// load CakePHP''s default routes
			Router::mapResources(
				array('Users','Exampreps')
			);
		}
	}

/**
 * _enableExtensions() is used to enable servicing only those extensions that are
 * specified in config.php
 *
 * @todo get extensions from config
 */
	private static function _enableExtensions(){
		Router::parseExtensions();
		Router::setExtensions(Configure::read('RestKit.Request.enabledExtensions'));
	}
}