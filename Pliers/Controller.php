<?php
namespace Pliers;

class Controller extends \Pliers\App {
	protected $templateData;

	public function render($name, $data = array(), $status = null) {
		if (strpos($name, ".php") === false) {
			$name = $name . ".php";
		}

		$data['app'] = $this;

		$app = \Slim\Slim::getInstance();

		$app->render($name, $data, $status);
	}	
}
?>