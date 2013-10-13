<?php
namespace Pliers;

use RedBean_Facade as R;

class App extends \Slim\Slim {
	protected $conf;

	private static $initialRoutes;

	public function __construct($routes = null) {
		parent::__construct(array(
			'templates.path' => realpath('../views')
		));

		$this->setupConfig();

		if($routes !== null) {
			self::$initialRoutes = $routes;
		}

		$this->addRoutes();

		// Set up RedBean
		R::setup($this->conf->db->dsn, $this->conf->db->username, $this->conf->db->password);
		R::freeze(true);		// Don't allow modifications to the DB schema

		$this->add(new \Slim\Middleware\SessionCookie(array(
			'expires' => '2 weeks',
			'path' => '/',
			'domain' => null,
			'secure' => true,
			'httponly' => false,
			'name' => 'Pliers_session',
			'secret' => 'magic_unicorns',
			'cipher' => MCRYPT_RIJNDAEL_256,
			'cipher_mode' => MCRYPT_MODE_CBC
		)));
	}

	protected function appRoot() {
		return realpath($this->root() . '../');
	}

	public function mode() {
		return $this->mode;
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
					// foreach($path as $method => $action) {
					// 	$this->addRoute($route, $action . "@" . $method);
					// }
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

		$r = new \Slim\Route($route, $func);
		$r->setHttpMethods(strtoupper($method));

		if($name !== null) {
			$r->setName($name);

			$this->router->addNamedRoute($name, $r);
		} else {
			$this->$method($route, $func);
		}
	}

	protected function processCallback($path) {
		$class = "Main";

		if(strpos($path, ":") !== false) {
			list($class, $path) = explode(":", $path);
		}

		$class = ucfirst($class);

		$function = ($path != "") ? $path : "index";

		$func = function () use ($class, $function) {
			$classPath = '\Controller\\' . $class;
			$instance = new $classPath();

			return call_user_func_array(array($instance, $function), array($class, $function));
		};

		return $func;
	}
}
?>