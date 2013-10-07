<?php
namespace Pliers;

class App extends \Slim\Slim {
	public function __construct() {
		parent::__construct(array(
			'templates.path' => realpath('../views')
		));

		$this->router = new \Pliers\Router;

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

	public function start() {
		$this->router->start();
	}

	public function mode() {
		return $this->mode;
	}
}
?>