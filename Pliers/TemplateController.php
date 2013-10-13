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
		
		$contentView->setTemplatesDirectory($this->config('templates.path'));
		$contentView->appendData($data);

		if($name !== null) {
			$this->templateData->content = $contentView->fetch($name);
		} else {
			$this->templateData->content = '';
		}

		parent::render('template_' . $this->template, (array)$this->templateData, $status);
	}	
}
?>