<?php
namespace Pliers;

class Router extends \Slim\Router {
	public function __construct() {
		parent::__construct();

		$app = \Slim\Slim::getInstance();

		$app->notFound(function() use($app) {
			$app->render('404.php');
		});	
	}	

	public function addRoutes($routes) {
		foreach($routes as $route => $path) {
			if(is_string($route)) {
				if(is_array($path)) {
					$this->addRoute($route, $path[1], $path[0]);
					// foreach($path as $method => $action) {
					// 	$this->addRoute($route, $action . "@" . $method);
					// }
				} else {
					$this->addRoute($route, $path);
				}
			} else {
				$this->addRoute($path[0], $path[1]);
			}

		}
	}

	protected function addRoute($route, $pathStr, $name = null) {
		$method = "any";

		if(strpos($pathStr, "@") !== false) {
			list($pathStr, $method) = explode("@", $pathStr);
		}

		$func = $this->processCallback($pathStr);

		var_dump($pathStr);

		if(is_array($route)) {
			$name = $route[0];
			$route = $route[1];
		}

		$r = new \Slim\Route($route, $func);
		$r->setHttpMethods(strtoupper($method));

		if($name !== null) {
			$r->setName($name);
		}

		$this->map($r);
	}

	protected function processCallback($path) {
		$class = "Main";

		if(strpos($path, ":") !== false) {
			list($class, $path) = explode(":", $path);
		}

		$function = ($path != "") ? $path : "index";

		$func = function () use ($class, $function) {
			$class = '\Controller\\' . $class;
			$class = new $class();

			$args = func_get_args();

			return call_user_func_array(array($class, $function), $args);
		};

		return $func;
	}
}
?>