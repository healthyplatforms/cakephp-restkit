# CakePHP RestKit plugin

Need a full-featured REST API? Just load this plugin and start delivering XML/Json instantly ;)

## Version ##

This plugin is heavily under construction so do not use it unless you know what you are doing.

Current limitations:

* XML transformations supported for CakePHP find() results with recursive => 0 only.


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


### Custom Exceptions
Use the RestKitException to return errors with custom HTTP Status Codes and rich
error information:

    throw new RestKitException(array('message' => 'You are overloading my API', 'errorCode' => 12345), 666);

To return the following XML along with a Response Header using Status Code 666 and message 'Something very evil':

    <response>
      <status>666</status>
      <message>You are overloading my API</message>
      <code>12345</code>
      <moreInfo>http://www.bravo-kernel.com/docs/errors/12345</moreInfo>
    </response>

**Note:** custom Status Codes and messages must be defined in /RestKit/Config/config.php

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

* add errorcode system to make them selectable in your IDE and to prevent having to pass them as a parameter
* add an extra 'exception' tag for returned error XML (requires overriding default XmlView somehow)
* add security (deny all unless authorized)
* add OAuth token handling (will require a separate app with OAuth server and login functionality)
* extend validations for known URI-options
* enable/disable URI-options from the config
* update/extend unit tests
* make prefixed route exclusive when enabled (making the default (direct) Cake routes to the controllers no longer available)
