<?php
namespace Imgurian;

Class TemplateController extends \Imgurian\Controller {
	protected $template = 'default';

	protected $templateData;

	public function __construct() {
		parent::__construct();

		$this->templateData = new \StdClass;
	}

	public function render($name, $data = array(), $status = null) {
		$contentView = new \Slim\View;
		
		$contentView->setTemplatesDirectory($this->config('templates.path'));
		$contentView->appendData($data);

		$this->templateData->content = $contentView->fetch($name);

		parent::render('template_' . $this->template, (array)$this->templateData, $status);
	}	
}
?>