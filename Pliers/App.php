<?php
namespace Pliers;

use RedBean_Facade as R;

class App extends \Slim\Slim {
	protected $conf;
	protected $app;

	private static $initialRoutes;

	public function __construct($routes = null) {
		@session_start();

		parent::__construct(array(
			'templates.path' => realpath('../views')
		));

		if($routes !== null) {
			self::$initialRoutes = $routes;
		}

		$this->addRoutes();

		$this->setupConfig();

		// Set up RedBean
		R::setup($this->conf->db->dsn, $this->conf->db->username, $this->conf->db->password);
		R::freeze(true);		// Don't allow modifications to the DB schema

		$this->app = self::getInstance();
	}

	public function start() {
		$this->run();
	}

	protected function appRoot() {
		return realpath($this->root() . '../');
	}

	private function setupConfig() {
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

		$this->conf = $conf->$mode;
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
}
?>