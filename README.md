# CakePHP RestKit plugin

Need a full-featured REST API?

Load this plugin and you are ready to go!

## Version ##

This plugin is heavily under construction so do not use it unless you value your production environment ;)

Current limitations:

* automagic SimpleXML reformatting for CakePHP find() results limited to 'recursive' levels 0 and 1

## Features

* custom HTTP Status Codes
* custom XML/Json errors (with the RestKitExceptionHandler)
* vaidation rules for URI parameters
* version prefixing (e.g. /v1/)

## Requirements

The RestKit plugin requires CakePHP 2.2

## Installation

Clone the repository into /app/Plugin/RestKit:

     git submodule add git@github.com:bravo-kernel/cakephp-restkit.git Plugin/RestKit

Enable the RestKit plugin in /app/Config/bootstrap.php:

    CakePlugin::load(array(
        'RestKit' => array('bootstrap' => true, 'routes' => true),
    ));

Disable CakePHP default routing in /app/Config/routes.php:

    //require CAKE . 'Config' . DS . 'routes.php';


# Documentation

## Configuration

All options can be configurated by editing /app/Plugin/RestKit/Config/config.php.

* **enableOptionValidation**: set to 'true' to turn validation on
* HTTP Status Codes

## Usage

### Response rendering

Never worry about reformatting your arrays for SimpleXML again. RestKit will automagically
handle all array reformatting for you to produce correctly encapsulated XML responses.

**Example1:**

    function index() {
        $users = $this->User->find('all');
        $this->RestKit->render(array(
            $users));
    }

Will produce the following XML with an autogenerated &lt;users&gt; rootnode based on the User model found in the passed array:

    <response>
      <users>
        <user>
          <id>1</id>
          <username>BravoKernel</username>
        </user>
        <user>
          <id>2</id>
          <username>Ceeram</username>
        </user>
      </users>
    </response>

**Example2:**

    function index() {
        $entries = $this->Entry->find('all');
        $this->RestKit->render(array(
            'debug' => $entries));
    }

Will use the passed rootnode &lt;debug&gt; instead of the normally autogenerated &lt;entries&gt; to produce the following XML:

    <response>
      <debug>
        <entry>
          <id>1</id>
          <message>Just a log message</message>
        </entry>
        <entry>
          <id>2</id>
          <message>Another log message</message>
        </entry>
      </debug>
    </response>

**Example3:**

Or use any combination of autogenerated and specified rootnodes:

    function index() {
        $entries = $this->Entry->find('all');
        $this->RestKit->render(array(
            $users,
            'debug' => $entries,
            $tests));
    }

**Example4**

Find() results with associated Models will produce the following XML:

    <response>
      <users>
        <user>
          <id>1</id>
          <username>BravoKernel</username>
          <posts>
            <post>
              <id>1</id>
              <body>This is a post</body>
            </post>
          </posts>
        </user>
      </users>
    </response>

### Default Exceptions

RestKit uses the RestKitExceptionRenderer to respond with REST errors containing rich error information and
corresponding HTTP Status Codes.

Please note that behavior in non-debug-mode is different than that of Cake (returning only
the 404 and 500 errors). In non-debug-mode the error response will contain:
* the actual HTTP Status Code
* the actual HTTP Status Code description
* an error message reset to the actual description of the HTTP Status Code
to prevent any sensitive information from becoming public

**NotFoundException() example**

    <response>
        <status>404</status>
        <message>Not Found</message>
        <code>12001</code>
        <moreInfo>http:///www.bravo-kernel.com/docs/errors/12001</moreInfo>
    </response>

**ForbiddenException() example**

    <response>
        <status>403</status>
        <message>Forbidden</message>
        <code>12002</code>
        <moreInfo>http:///www.bravo-kernel.com/docs/errors/12002</moreInfo>
     </response>

**Programming error in debug mode**

    <response>
        <status>500</status>
        <message>
            Call to undefined method RestKitComponent::crashMe()
        </message>
        <code>500</code>
        <moreInfo>http:///www.bravo-kernel.com/docs/errors/12001</moreInfo>
    </response>

**Programming error in non-debug mode**

    <response>
        <status>500</status>
        <message>Internal Server Error</message>
        <code>12003</code>
        <moreInfo>http:///www.bravo-kernel.com/docs/errors/12003</moreInfo>
    </response>

### RestKitExceptions

RestKitExceptions error messages are usefull for providing informational error feedback
about your application's internal usage. The error messages will appear in both debug and
non-debug mode using and will respond with a custom HTTP Status Code (666) and description (RestKit).

Please note that custom HTTP Status Codes and messages must be defined in /RestKit/Config/config.php

* `throw new RestKitException();`

* `throw new RestKitException('Invalid phone number')`

* `throw new RestKitException('Invalid phone number', 666)`

* `throw new RestKitException(array('message' => 'Invalid phone number', 'errorCode' => 12345), 666);`


### Validating URI parameters

Validate URI options by simply defining your default options (all others passed
options will be ignored):

    function index() {
        $options = $this->RestKit->parseUriOptions(array(
            'sort' => 'asc',
            'limit' => 10));
        $users = $this->User->find('all');
        $this->RestKit->render(array('users'));
    }

RestKit supports out-of-the box validation for the following URI options

* **sort** either asc or desc

# TODO

* add security (deny all unless authorized)
* add OAuth token handling (will require a separate app with OAuth server and login functionality)
* add an extra 'exception' tag for returned error XML (requires overriding default XmlView somehow)
* make prefixed route exclusive when enabled (making the default (direct) Cake routes to the controllers no longer available)
* extend validations for known URI-options (or completely rethink this feature)
* enable/disable URI-options from the config
* possibly very silly idea: add errorcode system for easy usage in IDE