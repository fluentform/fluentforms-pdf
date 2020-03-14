<?php 
namespace FluentFormPdf\Classes\Templates;

abstract class TemplateManager {
    protected $templateName = '';
    protected $templateKey = '';

    public function __construct ($templateName, $templateKey) {
        $this->templateName = $templateName;
        $this->templateKey = $templateKey;
    }

    abstract public function getHtmlTemplate($title);

   
}