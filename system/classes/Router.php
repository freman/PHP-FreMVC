<?php
namespace MVC;
/**
 * Router
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * FreMVC Router class
 *
 * Manage the application routes
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class Router {
	protected $routes = array();
	protected $defaultRoute;
	protected $app;

	/**
	 * Constructor
	 *
	 * Build a new router, passing the optional config to the bootstrap method
	 *
	 * @see bootstrap()
	 * @param array $config configuration
	 */
	public function __construct(Application $app, array $config = array()) {
		$this->app = $app;
		$this->bootstrap($config);
	}

	/**
	 * Bootstrap
	 *
	 * Rapidly configure the router routes
	 *
	 * $config = array(
	 * 	routes => array(
	 * 		'/some/path',
	 * 		'/some/other/path'
	 * 	),
	 * 	defaultRoute => '/some/path'
	 * )
	 *
	 * @param array $config configuration
	 */
	public function bootstrap(array $config = array()) {
		if (!empty($this->routes))
			throw new Exception('Router bootstrapping too late');

		if (isset($config['routes']) && is_array($config['routes']))
			$this->routes = array_map(function($arg) {return '/' . trim($arg, '/');}, $config['routes']);

		$this->defaultRoute = isset($config['defaultRoute']) ? '/' . trim($config['defaultRoute'], '/') : '/index/index';
	}

	/**
	 * Add route
	 *
	 * Add a new route to the router
	 *
	 * @param string $path
	 */
	public function addRoute($path) {
		array_push($this->routes, '/' . trim($path, '/'));
	}

	/**
	 * Set default route
	 *
	 * Set the route to follow when no route is provided
	 *
	 * @param string $path
	 */
	public function setDefaultRoute($path) {
		$this->defaultRoute = $path;
	}

	/**
	 * Output
	 *
	 * Do all the magic regarding resolving a route and populating the arguments
	 * including loading the actual class file
	 */
	public function output() {
		// Decide what path was called
		$request_path = '/' . trim(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $this->defaultRoute, '/');

		// See if the route has actually been defined
		$found_route = in_array($request_path, $this->routes) ? $request_path : (in_array("$request_path/index", $this->routes) ? "$request_path/index" : false);

		// Expand the route to a filename
		$include_file = Loader::appRoot() . '/controllers/' . $found_route . '.php';

		// Only continue if route exists and so does the filename
		if ($found_route && file_exists($include_file)) {
			// Expand the route to a class name
			$class_name = 'Controllers' . str_replace(' ', '_', ucwords(str_replace('/', ' ', $found_route)));

			// Load the class if required
			if (!class_exists($class_name, false))
				require($include_file);

			// Create an instance of the controller passing the application through
			$controller = new $class_name($this->app);

			// Check to see if the requested method exists, load a reflection of it
			$method = strtolower($_SERVER['REQUEST_METHOD']);
			if (method_exists($controller, $method)) {
				$reflection = new \ReflectionMethod($controller, $method);
				$arguments = array();

				// Build a list of arguments in call order
				foreach ($reflection->getParameters() as $param) {
					if (isset($_REQUEST[$param->name])) {
						$arguments[] = $_REQUEST[$param->name];
					}
					elseif ($param->isDefaultValueAvailable()) {
						$arguments[] = $param->getDefaultValue();
					}
					else {
						header("HTTP/1.0 400 Bad Request");
						exit;
					}
				}
				try {
					// Call it and render!
					$result = $reflection->invokeArgs($controller, $arguments);
					$controller->render(array('result' => $result));
				}
				catch (Exception $e) {
					// Render any exceptions
					$controller->render(array('exception' => $e));
				}
			}
			else {
				header("HTTP/1.0 501 Not Implemented");
				exit;
			}
		}
		else {
			header("HTTP/1.0 404 Not Found");
			exit;
		}
	}
}