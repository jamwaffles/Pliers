<?php
namespace Pliers;

Class TemplateController extends \Pliers\Controller {
	protected $template = 'default';
	protected $templateData;
	protected $beforeRenderCalled = false;

	public function __construct() {
		parent::__construct();

		$this->templateData = new \StdClass;
	}

	public function render($name = null, $data = array(), $status = null) {
		$contentView = new \Slim\View;

		if(isset($this->environment['slim.flash'])) {
			$contentView->setData('flash', $this->environment['slim.flash']);
			$this->templateData->flash = $this->environment['slim.flash'];
		}

		$contentView->setTemplatesDirectory($this->appRoot() . '/views');
		$contentView->appendData((array)$this->templateData);
		$contentView->appendData($data);

		if($name !== null) {
			$this->templateData->content = $contentView->fetch(rtrim($name, '.php') . '.php');
		} else {
			$this->templateData->content = '';
		}

		// Call before render
		if(method_exists($this, 'beforeRender') && !$this->beforeRenderCalled) {
			$this->beforeRender();
		}

		parent::render('template-' . rtrim($this->template, '.php') . '.php', (array)$this->templateData, $status);
	}	
}
?>