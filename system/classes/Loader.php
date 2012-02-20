<?php
namespace MVC;
/**
 * Loader
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * FreMVC Loader class
 *
 * Autoloader and hack to get app root
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class Loader {
	static private $root = '';

	/**
	 * Initialize the loader
	 *
	 * Called by system/include.php
	 * Sets up the $root variable and registers the autloader
	 */
	static public function init() {
		self::$root = realpath('../..');
		self::registerAutoloader();
	}

	/**
	 * Register Autoloader
	 *
	 * Does pretty much as is written on the box
	 */
	static public function registerAutoloader() {
		spl_autoload_register(__CLASS__ . '::autoload');
	}

	/**
	 * Unregister Autoloader
	 *
	 * Should do what you think it does
	 */
	static public function unregisterAutoloader() {
		spl_autoload_unregister(__CLASS__ . '::autoload');
	}

	/**
	 * Autoload
	 *
	 * Will autoload MVC namespaced classes from system/classes
	 * otherwise will attempt to load th classes from include/classes
	 *
	 * @param string $class
	 */
	static public function autoload($class) {
		$path = 'include';
		if (strpos($class, 'MVC\\') === 0) {
			$path = 'system';
			$class = substr($class, 4);
		}

		require(self::$root . '/' . $path . '/classes/' . strtr($class, '_\\', '//') . '.php');
	}

	/**
	 * App Root
	 *
	 * Return the $root class variable
	 *
	 * @return string
	 */
	static public function appRoot() {
		return self::$root;
	}
}