<?php
namespace MVC;
/**
 * Application
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * FreMVC Application class
 *
 * Manage the application construct
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class Application {
	private $db;
	private $config;
	private $router;
	private $cryptography;
	private $models = array();
	private $registry  = array();
	protected $app;

	/**
	 * Constructor
	 *
	 * Pass a configuration to load things like the database
	 *
	 * Supported configuration options:
	 * $config = array (
	 * 	'db' => array(
	 * 		'dsn'  => 'dsn',
	 * 		'user' => 'user',
	 * 		'pass' => 'pass',
	 * 	),
	 * 	'routes' => array(
	 * 		'/some/path',
	 * 		'/some/otherpath',
	 * 	),
	 * 	'defaultRoute' => 'some/path'
	 * )
	 *
	 * @param array $config optional configuration array
	 */
	function __construct(array $config = array()) {
		$this->config = $config;
		$this->app = $this;
		$this->router = new Router($this, $config);
	}

	/**
	 * Application Registry
	 *
	 * Provides read and write access to the application level registry,
	 * you can use this method to store and access things throughout the
	 * execution of your application (think globals)
	 *
	 * @param string $name name of the key to store
	 * @param mixed $value optional value to store
	 * @return mixed stored $value
	 */
	function registry($name, $value = null) {
		if (!is_null($value))
			$this->registry[$name] = $value;
		return $this->registry[$name];
	}

	/**
	 * Application Database
	 *
	 * Return a handle to the database used by this application (optionally
	 * connecting to the database as required)
	 *
	 * @return PDO database object
	 */
	function db() {
		if (!$this->db) {
			try {
				$this->db = new PDO($this->config['db']['dsn'], $this->config['db']['user'], $this->config['db']['pass']);
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			}
			catch (Exception $e) {
				header("HTTP/1.0 500 Internal Server Error");
				exit;
			}
		}
		return $this->db;
	}

	/**
	 * Application Cryptography
	 *
	 * Init (if required) and return the application level cryptographic
	 * module providing common cryptographic routines for all
	 *
	 * @return MVC\Cryptography
	 */
	function cryptography() {
		if (!$this->cryptography)
			$this->cryptography = new MVC\Cryptography;
		return $this->cryptography;
	}

	/**
	 * Application Models
	 *
	 * Include, init and return models used by the application
	 *
	 * @param string $name model name
	 * @return MVC\Model
	 */
	function model($name) {
		if (!isset($this->models[$name])) {
			$filename = Loader::appRoot() . 'model/' . strtolower($name) . '.php';
			$class = 'Model_' . ucwords($name);
			if (file_exists($filename)) {
				require($filename);
				$this->models[$name] = new $class($this);
			}
			else {
				throw new Exception ('Invalid module ' . $name . ' specified');
			}
		};
		return $this->models[$name];
	}

	/**
	 * Application router
	 *
	 * Return the application router
	 *
	 * @return MVC\Router
	 */
	public function router() {
		return $this->router;
	}

	/**
	 * Alias for router()->addRoute($path)
	 * @see MVC\Router::addRoute()
	 */
	public function addRoute($path) {
		$this->router->addRoute($path);
	}

	/**
	 * Alias for router()->setDefaultRoute($path)
	 * @see MVC\Router::setDefaultRoute()
	 */
	public function setDefaultRoute($path) {
		$this->router->setDefaultRoute($path);
	}

	/**
	 * Alias for router()->output()
	 * @see MVC\Router::output()
	 */
	public function output() {
		$this->router->output();
	}
}
