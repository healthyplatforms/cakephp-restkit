<?php

// We need to load the config file here because initializing it from the App's bootstrap.php
// using CakePlugin::load(array('RestKit' => array('bootstrap' => true))
// will only do a require() and not a load() of the configfile making the settings
// unavailable for use inside the plugin.
Configure::load('RestKit.config');
