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
            1
        );

        add_filter(
            'fluentform_get_pdf_html_' . $this->templateKey,
            [$this, 'getHtmlTemplate'],
            10,
            4
        );

        add_filter(
            'fluentform_get_pdf_style_' . $this->templateKey,
            [$this, 'getStyles'],
            10,
            3
        );
    }
    
    abstract public function getSettingsFields($settings);

    abstract public function getStyles($preferences, $settings, $default);

    abstract public function getHtmlTemplate($templateData, $inputs, $settings, $default);
}
