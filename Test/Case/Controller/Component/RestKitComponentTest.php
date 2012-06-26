<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('RestKitComponent', 'RestKit.Controller/Component');

// A fake controller to test against
class TestRestKitController extends Controller {
	public $paginate = null;
}

class RestKitComponentTest extends CakeTestCase {
	public $RestKitComponent = null;
	public $Controller = null;

	public function setUp() {
		parent::setUp();
		// Setup our component and fake test controller
		$Collection = new ComponentCollection();
		$this->RestKitComponent = new ExtendedRestKitComponent($Collection);
		$CakeRequest = new CakeRequest();
		$CakeResponse = new CakeResponse();
		$this->Controller = new TestRestKitController($CakeRequest, $CakeResponse);
		$this->RestKitComponent->startup($this->Controller);
	}

	public function testReformatArrays() {

		// Test reformatting of single dimension find('all') result
		$findAllResult  = array(
			array('User' => array('id' => 1, 'username' => 'bravo_kernel')),
			array('User' => array('id' => 2, 'username' => 'ceeram'))
		);
		$expected = array(
			'user' => array(
				array( 'id' => 1, 'username' => 'bravo_kernel'),
				array( 'id' => 2, 'username' => 'ceeram')));

		$output = $this->RestKitComponent->formatFindResultForSimpleXML($findAllResult);
		$this->assertSame($expected, $output);


		// Test reformatting of single dimension findById() result
		$findByIdResult = array(
			'User' => array(
				'id' => 1,
				'username' => 'bravo_kernel'));

		$expected = array(
			'user' => array(
				array( 'id' => 1, 'username' => 'bravo_kernel')));

		$output = $this->RestKitComponent->formatFindResultForSimpleXML($findByIdResult);
		$this->assertSame($expected, $output);
		}

	public function tearDown() {
		parent::tearDown();
		// Clean up after we're done
		unset($this->RestKitComponent);
		unset($this->Controller);
	}
}
	
class ExtendedRestKitComponent extends RestKitComponent {

	// placeholder example for testing protected component functions
	//public function publicSetViewData($arrays) {
	//	return $this->_setViewData($arrays);
	//}
}
