<?php
namespace Pliers;

use RedBean_Facade as R;

class App extends \Slim\Slim {
	private static $staticConf = null;
	protected $conf;
	protected $app;
	protected $utils;

	private static $initialRoutes;

	public function __construct($routes = null) {
		$appConfig = array(
			'templates.path' => realpath('../views'),
			'mode' => isset($_SERVER['PLIERS_ENV']) ? $_SERVER['PLIERS_ENV'] : 'development'
		);

		if($appConfig['mode'] === 'development') {
			$appConfig['debug'] = true;
		} else {
			$appConfig['debug'] = false;
		}

		parent::__construct($appConfig);

		if($routes !== null) {
			self::$initialRoutes = $routes;
		}

		$this->addRoutes();

		$this->setupConfig();

		// Set up RedBean
		R::setup($this->conf->db->dsn, $this->conf->db->username, $this->conf->db->password);

		$this->app = self::getInstance();

		$this->utils = new Utils;
	}

	protected function log($message, $type = 'notice', $exception = null) {
		$typeMap = array('error' => E_USER_ERROR, 'warning' => E_USER_WARNING, 'notice' => E_USER_NOTICE);

		file_put_contents(dirname(__FILE__) . '/../../../../error.log', $message . $exception, FILE_APPEND);

		error_log($message, $typeMap[$type]);

		if(extension_loaded('newrelic')) {
			newrelic_notice_error($message, $exception);
		}

		return $this;
	}

	public function start() {
		$this->run();
	}

	public function appRoot() {
		return realpath(__DIR__ . '/../../../../');
	}

	private function setupConfig() {
		if(self::$staticConf !== null) {
			$this->conf = self::$staticConf;

			return $this;
		}

		$confFile = $this->appRoot() . '/config.json';

		$file = file_get_contents($confFile);

		if(!$file) {
			throw new \Slim\Exception\Stop('No configuration file found. Add config.json to your app root.');

			$app->halt(500);
		}

		$mode = $this->getMode();
		$conf = json_decode($file);

		if(!$conf->$mode) {
			throw new \Slim\Exception\Stop('No configuration found for mode ' . $mode . '. Check your configuration.');

			$app->halt(500);
		}

		self::$staticConf = $conf->$mode;

		$this->conf = self::$staticConf;

		return $this;
	}

	private function addRoutes() {
		foreach(self::$initialRoutes as $route => $path) {
			if(is_string($route)) {
				if(is_array($path)) {
					$this->addRoute($route, $path[1], $path[0]);
				} else {
					$this->addRoute($route, $path);
				}
			}
		}
	}

	protected function addRoute($route, $pathStr, $name = null) {
		$method = "any";

		if(strpos($pathStr, "@") !== false) {
			list($pathStr, $method) = explode("@", $pathStr);
		}

		$func = $this->processCallback($pathStr);

		if($name !== null) {
			$this->$method($route, $func)->name($name);
		} else {
			$this->$method($route, $func);
		}
	}

	protected function processCallback($path) {
		$class = "Main";

		$params = explode(":", $path);

		if(count($params) > 1) {
			list($class, $path) = $params;
		} else {
			$class = $params[0];
			$path = 'index';
		}

		$class = ucfirst($class);
		$function = ($path != "") ? $path : "index";

		$func = function() use($class, $function) {
			$classPath = '\Controller\\' . $class;
			$instance = new $classPath();

			return call_user_func_array(array($instance, $function), func_get_args());
		};

		return $func;
	}

	// Redirect back to previous URL
	protected function redirectBack() {
		$this->app->redirect($this->app->request->getReferrer());
	}

	public function addMiddleware($mid) {
		$this->app->add($mid);
	}

	public function environ() {
		return $this->getMode();
	}
}
?>