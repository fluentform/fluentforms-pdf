<?php 
namespace FluentFormPdf\Classes\Templates;
use FluentForm\Framework\Foundation\Application;

abstract class TemplateManager {
    private $app;
    protected $templateKey = '';

    public function __construct ($app, $templateKey) {
        $this->app = $app;
        $this->templateKey = $templateKey;
    }

    public function registerAdminHooks() {

        add_filter('fluentform_get_pdf_settings_fields_' . $this->templateKey, array($this, 'getSettingsFields'), 10, 2);
        add_filter('fluentform_get_pdf_html_template_' . $this->templateKey, array($this, 'getHtmlTemplate'), 10, 2);
    
    }

    abstract public function getSettingsFields();

    abstract public function getHtmlTemplate($userInputData);
   
   
}