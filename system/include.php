<?php
/**
 * System include file
 *
 * Include this file in your application to load up the basic requirements
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

// Load the commonly required classes
require('classes/Router.php');
require('classes/Application.php');
require('classes/Controller.php');
require('classes/Model.php');
require('classes/Loader.php');

// Init the loader so it knows what the docroot is.
MVC\Loader::init();