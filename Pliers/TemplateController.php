<?php
namespace Pliers;

Class TemplateController extends \Pliers\Controller {
	protected $template = 'default';

	protected $templateData;

	public function __construct() {
		parent::__construct();

		$this->templateData = new \StdClass;
	}

	public function render($name = null, $data = array(), $status = null) {
		$contentView = new \Slim\View;

		if(isset($this->app->environment['slim.flash'])) {
			$contentView->setData('flash', $this->app->environment['slim.flash']);
			$this->templateData->flash = $this->app->environment['slim.flash'];
		}

		$contentView->setTemplatesDirectory($this->appRoot() . '/views');
		$contentView->appendData($data);

		if($name !== null) {
			$this->templateData->content = $contentView->fetch(rtrim($name, '.php') . '.php');
		} else {
			$this->templateData->content = '';
		}

		$this->app->render('template-' . rtrim($this->template, '.php') . '.php', (array)$this->templateData, $status);
	}	
}
?>