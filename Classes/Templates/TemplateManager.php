<?php 
namespace FluentFormPdf\Classes\Templates;
use FluentForm\Framework\Foundation\Application;

abstract class TemplateManager {
    protected $templateName = '';
    protected $templateKey = '';
    private $app;


    public function __construct (Application $app) {
        $this->app = $app;
        $this->templateName = $templateName;
        $this->templateKey = $templateKey;
    }

    abstract public function getHtmlTemplate($title);
    abstract public function getTemplateSettings($title);
   
}