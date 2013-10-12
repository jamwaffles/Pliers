<?php
namespace Pliers;

use RedBean_Facade as R;

class App extends \Slim\Slim {
	protected $conf;

	public function __construct() {
		parent::__construct(array(
			'templates.path' => realpath('../views')
		));

		$this->setupConfig();

		// Set up RedBean
		R::setup($this->conf->db->dsn, $this->conf->db->username, $this->conf->db->password);
		R::freeze(true);		// Don't allow modifications to the DB schema

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

	protected function appRoot() {
		return realpath($this->root() . '../');
	}

	public function start() {
		$this->router->start();
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
}
?>