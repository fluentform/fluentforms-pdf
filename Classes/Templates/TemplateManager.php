<?php 

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;

abstract class TemplateManager
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function registerAdminHooks()
    {

        add_filter(
            'fluentform_get_pdf_settings_fields_' . $this->templateKey,
            [$this, 'getSettingsFields'],
            10,
            2
        );

        add_filter(
            'fluentform_get_pdf_html_template_' . $this->templateKey,
            [$this, 'getHtmlTemplate'],
            10,
            2
        );
    }

    abstract public function getSettingsFields();

    abstract public function getHtmlTemplate($userInputData);
}
