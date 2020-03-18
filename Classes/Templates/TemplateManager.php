<?php 
namespace FluentFormPdf\Classes\Templates;
use FluentForm\Framework\Foundation\Application;

abstract class TemplateManager {
    protected $templateName = '';
    protected $templateKey = '';
    private $app;


    public function __construct ($app, $templateName, $templateKey) {
        $this->app = $app;
        $this->templateName = $templateName;
        $this->templateKey = $templateKey;
    }

    public function registerAdminHooks() {
        add_action('wp_ajax_fluentform_get_form_pdf_template_settings', array($this, 'getTemplateSettings')); 
    }
    // abstract public function getHtmlTemplate($title);
    abstract public function getSettingsFields($settings, $formId);
   
}