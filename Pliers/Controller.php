<?php
namespace Pliers;

class Controller extends \Pliers\App {
	protected $templateData;

	public function render($name, $data = array(), $status = null) {
		if (strpos($name, ".php") === false) {
			$name = $name . ".php";
		}

		$this->app->render($name, $data, $status);
	}	
}
?>