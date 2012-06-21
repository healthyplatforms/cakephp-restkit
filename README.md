# CakePHP RestKit plugin

This plugin will instantly add some spice to the default Cake REST functionality:

* return custom HTTP Status Codes
* return custom XML/Json errors using a custom ExceptionHandler
* use vaidation rules to handle URI options

## Requirements

The RestKit plugin requires CakePHP 2.2

## Setup

Clone the repository into /app/Plugin/RestKit:

     git submodule add git@github.com:bravo-kernel/cakephp-restkit.git Plugin/RestKit


Enable the RestKit plugin in /app/Config/bootstrap.php:

    CakePlugin::load(array(
        'RestKit' => array('bootstrap' => true),
    ));

Change the default ExceptionHandler in /app/Config/core.php:

    Configure::write('Exception', array(
        'handler' => 'ErrorHandler::handleException',
        'renderer' => 'RestKit.RestKitExceptionRenderer',
        'log' => true
    ));

Optionally add the following check to AppController´s beforeFilter() to return
404 errors for non xml/json requests (instead of the default 500 behavior):

    public function beforeFilter() {
        $this->RestKit->checkRequestMethod($this);
    }

## Options

Options can be configurated by editing /app/Plugin/RestKit/Config/bootstrap.php.

* **enableOptionValidation**: set to 'true' to turn validation on
* HTTP Status Codes

## Usage

Never worry about reformatting arrays for _serialize(). RestKit will automagically
handle all array reformatting for you.

    function index() {
        $users = $this->User->find('all');
        $this->RestKit->render(array('users' => $users));
    }

Validate URI options by simply defining your default options (all others passed
options will be ignored):

    function index() {
        $options = $this->RestKit->parseUriOptions(array(
            'sort' => 'asc',
            'limit' => 10));
        $users = $this->User->find('all');
        $this->RestKit->render(array('users' => $users));
    }

Use the RestKitException to return errors with custom HTTP Status Codes and rich
error information:

    throw new RestKitException(array('message' => 'You are overloading my API', 'errorCode' => 12345), 666);

Will produce the following XML

    <response>
      <status>666</status>
      <message>You are overloading my API</message>
      <code>12345</code>
      <moreInfo>http://www.bravo-kernel.com/docs/errors/12345</moreInfo>
    </response>

## URI options ##

RestKit supports validation for the following URI options out-of-the-box.

* **sort** either asc or desc

## TODO ##

* add errorcode system to make them selectable in your IDE and to prevent having to pass them as a parameter
* add an extra 'exception' tag for returned error XML (requires overriding default XmlView somehow)
* extend validations for known URI-options
* enable/disable URI-options from the bootstrap
* add tests