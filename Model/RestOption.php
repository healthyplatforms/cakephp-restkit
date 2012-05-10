<?php

/**
 * Description of RestOption
 *
 * @author rob
 */
class RestOption extends RestKitAppModel{

	var $useTable = false;

/**
* Here we define the validations for all user-passable options.
* In the Controller:
* 1. all optional parameters have default values ($defaults)
* 2. $defaults are merged with passed $users)

* @var type
*/
    public $validate = array(
        'sort' => array(
            'rule'    => array('inList', array('asc', 'desc')),
            'allowEmpty' => false,
            'message' => 'Use either asc or desc'
        )
     );



}