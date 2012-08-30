<?php

App::uses('Component', 'Controller');
App::uses('RestOption', 'Model');

/**
 * Description of RestKitComponent
 *
 * @todo fix Accept Headers not handled in isJson() and isXMl()
 *
 * @todo (might have to) build in a check in validateUriOptions for this->controller->$modelName->validates() because it will break if the model has $uses = false or array()
 *
 * @author bravo-kernel
 */
class RestKitComponent extends Component {

	/**
	 * $controller holds a reference to the current controller
	 *
	 * @var Controller
	 */
	protected $controller;

	/**
	 * $request holds a reference to the current request
	 *
	 * @var CakeRequest
	 */
	protected $request;

	/**
	 * $response holds a reference to the current response
	 *
	 * @var CakeResponse
	 */
	protected $response;

	/**
	 * $publicActions holds a list of actions (for the calling controller)
	 * that can be accessed without authentication
	 *
	 * @var array
	 */
	protected $publicActions = array();

	/**
	 * $_errors holds all error-messages to be included in the response
	 */
	protected $_errors = array();

	/**
	 * initialize() is used to create references to the the calling Controller,
	 * initialize callback and set up the Component.
	 *
	 * @todo check if this is correct ==> initialize() is called before the calling Controller's beforeFilter()
	 *
	 * @param Controller $controller
	 * @return void
	 */
	public function initialize(Controller $controller) {
		$this->setup($controller);
	}

	/**
	 * setup() is used to configure the RestKit component
	 *
	 * @param Controller $controller
	 * @return void
	 */
	protected function setup(Controller $controller) {
		// Cache local properties from the controller
		$this->controller = $controller;
		$this->request = $controller->request;
		$this->response = $controller->response;

		// Configure detectors
		$this->addDetectors();
		//pr($this->request);
		// return a 404 error in production mode for all non-enabled extensions
		if (!$this->request->is('api')) {
			return;
		}

		// 1. check allowPublic()
		// 2. fetch Authentication Method from the request
		//list($authMethod) = explode(' ', $authorizationHeader);
		//pr("authMethod = $authMethod");
		//pr ("BASIC zit in : " . env('PHP_AUTH_USER'));
		//pr ("DIGEST zit in : " . env('PHP_AUTH_DIGEST'));

		//$http_request_headers = self::getHttpRequestHeaders();
		//pr($http_request_headers);
		$authorizationRequestHeader = self::getHttpRequestHeader('authorization');
		pr("Authorization header = $authorizationRequestHeader");

		//$auth_method = $request_headers['Authorization'];
		//pr("Requested AUTH method = $auth_method");
	}

	/**
	 * getHttpRequestHeader() is a helper function used to retrieve the value
	 * of a single given HTTP request header.
	 *
	 * @param string $headerKey
	 * @return string|boolean
	 */
	public function getHttpRequestHeader ($headerKey){
		$http_request_headers = self::getHttpRequestHeaders();
		foreach( $http_request_headers as $key => $value ) {
			if( strtolower($key) == strtolower($headerKey)) {
				return $http_request_headers[$key];
			}
		}
		return false;
	}

	/**
	 * getHttpHeaders() is a clone of the get_headers() helper function found in oauth-php
	 * (http://code.google.com/p/oauth-php/) and is required to retrieve the "Authorization"
	 * header on webservers not running Apache.
	 *
	 * Note: Apache will not show "Authorization" as one of the $_SERVER variables
	 * so we MUST use apache_get_headers() to get it. Because other webservers will not
	 * have that function we just hope the Authorization header will be present
	 * in $_SERVER on those machine. Please let me know if you can confirm that this
	 * will actually work on a non-Apache webserver.
	 *
	 * @return array in format identical to PHP's apache_request_headers()
	 */
	public function getHttpRequestHeaders() {
		if (function_exists('apache_request_headers')) {	// Apache webserver
			return apache_request_headers();
		}
		return self::getServerHttpRequestHeaders();		// non-Apache webserver so parse $_SERVER
	}

	/**
	 * getServerHttpHeaders() is used to parse $_SERVER and return only the
	 * HTTP headers found there.
	 *
	 * @return array in format identical to PHP's apache_request_headers()
	 */
	private static function getServerHttpRequestHeaders() {
		$result = array();
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) == "HTTP_") {
				// this is chaos, basically it is just there to capitalize the first letter of every
				// word that is not an initial HTTP and strip HTTP (code from przemek)
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * startup() is used to enforce API access/authentication
	 *
	 * @todo check if this is correct ==> startup() is called after the calling Controller's beforeFilter()
	 *
	 * @param Controller $controller
	 * @return void
	 */
	public function startup(Controller $controller) {
		self::configureApiAccess();  // Enforce API authentication
	}

	/**
	 * hasError() checks if the current controller is an Error controller
	 *
	 * @return boolean
	 */
	public function hasError() {
		return get_class($this->controller) == 'CakeErrorController';
	}

	/**
	 * configureApiAccess() is used to.....
	 *
	 * @todo implement authentication mechanism using external mechanism (e.g. OAuth)
	 */
	protected function configureApiAccess() {

		// always return a 404 if the call is not an enabled method
		if (!$this->request->isApi()) {
			throw new NotFoundException('Unsupported API request format'); // TODO: check if this works
		}

		// check for errors first (to render API error response) ?!?
		if (self::hasError()) {
			return;
		}

		// Execute security checks if the action is not public
		if (!in_array($this->controller->action, $this->publicActions)) {
			pr("Action defined as PRIVATE, execute security checks:");

			// check token (or multiple methods)
			// deny if not found
		}
	}

	/**
	 * allowPublic() is used to allow public access to an API action
	 *
	 * You can use allowPublic() just like AuthComponent allow() so with either an array, or var args.
	 *
	 * `$this->RestKit->allowPublic(array('edit', 'add'));` or
	 * `$this->RestKit->allowPublic('edit', 'add');` or
	 * `$this->RestKit->allowPublic();` to allow all actions
	 *
	 * @param string|array $action,... Controller action name or array of actions
	 * @return void
	 * @link http://book.cakephp.org/2.0/en/core-libraries/components/authentication.html#making-actions-public
	 */
	public function allowPublic($action = null) {
		$args = func_get_args();
		if (empty($args) || $action === null) {
			$this->publicActions = $this->controller->methods;
		} else {
			if (isset($args[0]) && is_array($args[0])) {
				$args = $args[0];
			}
			$this->publicActions = array_merge($this->publicActions, $args);
		}
	}

	/**
	 * denyPublic() is used to deny public access to an action (requires authentication)
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function denyPublic($action) {
		$pos = array_search($action, $this->publicActions);
		if (false === $pos) {
			return false;
		}
		unset($this->publicActions[$pos]);
		return true;
	}

	/**
	 * _parseUriOptions() will use passed array as default options and will validate passed URI options against the Model's validation rules
	 *
	 * @param type $default_options
	 * @return type array
	 */
	public function parseUriOptions($default_options) {
		$options = self::_validateUriOptions($default_options);
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
		self::_setViewData($arrays);
	}

	/**
	 * _setViewData() handles setting data and _serialize logic needed to render viewless XML/JSON responses
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
	 * @todo include debug information only if enabled in configuration file
	 *
	 * @param mixed optional string used as the SimpleXml rootnode (e.g. users for <users>)
	 * @param mixed array with default CakePHP find() result
	 */
	protected function _setViewData($arrays) {

		// add debug information to the JSON response
		$errors = self::getErrors();
		if (!empty($errors)) {
			$arrays['errors'] = $errors;
		}

		$serializeKeynames = array();
		foreach ($arrays as $key => $array) {

			$simpleXml = self::formatCakeFindResultForSimpleXML($array);
			$defaultRootNode = key($simpleXml);

			// Manipulate $simpleXml array if a rootnode-string is passed
			if (is_string($key)) {
				$simpleXml[$key] = $simpleXml[$defaultRootNode];
				unset($simpleXml[$defaultRootNode]);
			} else {
				$key = $defaultRootNode;
			}

			// make data available and remember the key for mass _serialize()
			$this->controller->set($simpleXml);
			$key = key($simpleXml);
			array_push($serializeKeynames, $key);
		}
		// we MUST _serialize all arrays at once
		$this->controller->set(array('_serialize' => $serializeKeynames));
	}

	/**
	 * formatCakeFindResultForSimpleXML() reformats default CakePHP arrays produced
	 * by find() queries into a format that is suitable for passing to SimpleXML
	 *
	 * @param mixed CakePHP find() result
	 * @return mixed array ready for passing to SimpleXml
	 */
	public function formatCakeFindResultForSimpleXML($cakeFindResult) {

		// make findById() and find('first') format identical to find() format
		if (Hash::check($cakeFindResult, '{s}')) {
			$temp = $cakeFindResult;
			unset($cakeFindResult);
			$cakeFindResult[] = $temp;
		}

		$recordIndex = 0;
		$simpleXmlArray = array();
		foreach ($cakeFindResult as $foundRecord) {

			$modelIndex = 0;
			foreach (array_keys($cakeFindResult[0]) as $modelKey) {  // multiple root Models mean associations present (recursive > 1)
				$modelUnderscored = Inflector::underscore($modelKey); //e.g. ExamprepCustom to examprep_custom
				$extracted = array($modelUnderscored => Hash::extract($foundRecord, "{$modelKey}"));

				// first Model needs to be processed differently
				if ($modelIndex == 0) {
					$rootKey = $modelUnderscored;
					$rootKeyPluralized = Inflector::pluralize($rootKey); // store to return as the root-node later (e.g. users)
					$simpleXmlArray[$rootKeyPluralized][$rootKey][$recordIndex] = Hash::extract($extracted, "{$modelUnderscored}"); // extract only array-keys to prevent double <tags>
				} else {
					$pluralized = Inflector::pluralize($modelUnderscored);
					$simpleXmlArray[$rootKeyPluralized][$rootKey][$recordIndex][$pluralized] = $extracted;
				}
				$modelIndex++;
			}
			$recordIndex++;
		}
		return $simpleXmlArray;
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
			self::setError('optionValidation', $key, $value[0]);
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

		if (Configure::read('RestKit.Request.prefix') == true) {
			Router::mapResources(
				array('Users', 'Exampreps'), array('prefix' => '/' . Configure::read('RestKit.Request.prefix') . '/')
			);

			// skip loading Cake's default routes when forcePrefix is disabled in config
			if (Configure::read('RestKit.Request.forcePrefix') == false) {
				require CAKE . 'Config' . DS . 'routes.php';
			}
		} else {
			require CAKE . 'Config' . DS . 'routes.php'; // load CakePHP''s default routes
			Router::mapResources(
				array('Users', 'Exampreps')
			);
		}
	}

	/**
	 * _enableExtensions() is used to enable servicing only those extensions that are
	 * specified in config.php
	 *
	 * @todo get extensions from config
	 */
	private static function _enableExtensions() {
		Router::parseExtensions();
		Router::setExtensions(Configure::read('RestKit.Request.enabledExtensions'));
	}

	/**
	 * addDetectors() is used to configure extra detectors for API requests
	 *
	 * Adds the following detectors for CakeRequest:
	 * ->is('api')
	 * ->is('json')
	 * ->is('xml')
	 *
	 * @return void
	 */
	public function addDetectors() {
		$this->request->addDetector('api', array('callback' => 'RestKitComponent::isApi'));
		$this->request->addDetector('json', array('callback' => 'RestKitComponent::isJson'));
		$this->request->addDetector('xml', array('callback' => 'RestKitComponent::isXml'));
	}

	/**
	 * isApi() determines if the request is an API request.
	 *
	 * - currently only support for XML and JSON is implemented
	 * - only extenions present in the the configuration array 'enabledExtensions'
	 * will lead to isApi() returning TRUE. In other words, if 'json' is not defined
	 * in the configuration file even a valid JSON call will lead to isApi() returning FALSE.
	 *
	 * @param CakeRequest $request
	 * @return boolean
	 */
	public static function isApi(CakeRequest $request) {
		if (in_array('json', Configure::read('RestKit.Request.enabledExtensions'))) {
			if ($request->is('json')) {
				return true;
			}
		}

		if (in_array('xml', Configure::read('RestKit.Request.enabledExtensions'))) {
			if ($request->is('xml')) {
				return true;
			}
		}
		// neither XML or JSON
		return false;
	}

	/**
	 * isJson() determines if a Json request was made
	 *
	 * @todo passed accept-headers are not picked up at all by Cake atm
	 *
	 * @param CakeRequest $request
	 * @return boolean
	 */
	public static function isJson(CakeRequest $request) {
		// first check the extension used
		if (isset($request->params['ext']) && $request->params['ext'] === 'json') {
			return true;
		}
		// then sniff the accept-header (will return false if not present)
		//$acceptHeaders = $controller->request->parseAccept();
		//if (in_array($acceptHeaders['1.0'][0], array('application/xml', 'application/json'))) {
		//	return;
		//}
		//
		//OR TRY THIS
		//return ($request->accepts('application/json'));
		//}
	}

	/**
	 * isXml() determines if an XML request was made
	 *
	 * @todo passed accept-headers are not picked up at all by Cake atm
	 *
	 * @param CakeRequest $request
	 * @return boolean
	 */
	public static function isXml(CakeRequest $request) {
		// first check the extension used
		if (isset($request->params['ext']) && $request->params['ext'] === 'xml') {
			return true;
		}
		// then sniff the accept-header (will return false if not present)
		//return $request->accepts('application/xml');
	}

}