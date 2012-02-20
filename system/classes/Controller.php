<?php
namespace MVC;
/**
 * Controllers
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * FreMVC Controller class
 *
 * Base class for all controllers
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class Controller {
	protected $app;
	protected $render_called;

	/**
	 * Constructor
	 *
	 * Should never be called by human hands
	 *
	 * @param Application $app The application
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}

	/**
	 * Render
	 *
	 * Does exactly nothing in the grand scheme of things
	 * @todo do it
	 *
	 * @param array $config
	 */
	public function render(array $config) {
		// Prevent double rendering!
		if ($this->render_called) return;
		// Todo
		$this->render_called = true;
	}

}

/**
 * FreMVC JSON Controller class
 *
 * Much like the above but does all the jsony wrappy stuff for you
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class JsonController extends Controller {

	/**
	 * Render
	 *
	 * Overloaded from the Controller class
	 * Wrapps the output in json
	 *
	 * @param array $config
	 */
	public function render(array $config) {
		// Prevent double rendering!
		if ($this->render_called) return;

		$output = array();

		if (isset($config['result'])) {
			// Render a yummy result
			$output = array(
				'status' => 'ok',
				'result' => $config['result']
			);
		}
		elseif (isset($config['exception'])) {
			// Render a nasty ole error
			$output = array(
				'status'  => 'error',
				'code'    => $config['exception']->getCode(),
				'message' => $config['exception']->getMessage()
			);
		}

		echo json_encode($output);

		$this->render_called = true;
	}
}