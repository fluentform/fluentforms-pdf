<?php

namespace FluentFormPdf\Classes\Controller;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


class GlobalPdfManager
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Global settings register
        add_filter('fluentform_global_settings_components', [$this, 'globalSettings']);
        add_filter('fluentform_form_settings_menu', [$this, 'settingsMenu']);
        add_action('wp_ajax_fluentform_get_pdf_global_setting_options', [$this, 'getGlobalSettings']);

        // single form pdf settings fields ajax
        add_action(
            'wp_ajax_fluentform_get_form_pdf_template_settings',
            [$this, 'getFormTemplateSettings']
        );

        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', [$this, 'getAllTemplates']);

        // Frontend render When download button clicked
        add_action('wp_ajax_fluentform_pdf_download_ajax', [$this, 'pdfDownload']);
        add_action('wp_ajax_nopriv_fluentform_pdf_download_ajax', [$this, 'pdfDownload']);
    }

   
    public function globalSettings($setting)
    {
        $setting["pdf"] = [
             "hash" => "pdf",
             "title" => "PDF"
        ];
        
        return $setting;
    }

    
    public function settingsMenu($settingsMenus) 
    {
        $settingsMenus['pdf'] = [
            'title' => __('PDF settings', 'fluentform'),
            'slug'  => 'pdf_settings',
            'hash'  => 'pdf',
            'route' => '/pdf-settings'
        ];

        return $settingsMenus;
    }


    /*
    * @return key => [ path, name]
    * To register a new template this filter must hook for path mapping
    * filter: fluentform_pdf_template_map
    */ 
    public function getAvailableTemplates() 
    {
        $classes = apply_filters(
            'fluentform_pdf_template_map',
            [
                "template1" => [
                    'path' => "\FluentFormPdf\Classes\Templates\Template1",
                    'name'  => 'Blank'
                ],
                "template2" => [
                    'path' => "\FluentFormPdf\Classes\Templates\Template2",
                    'name'  => 'Rubix' 
                ]
            ]
        );

        return $classes;
    }



    /*
    * @return [key => value]
    * processed for dropdown fields
    */ 
    public function formattedTemplates()
    {
        $allTemplates = [];

        foreach ($this->getAvailableTemplates() as $key => $value) {    
            $allTemplates[$key] = $value['name'];      
        };

        return $allTemplates;
    }


    /*
    * @return [ key name]
    * global pdf setting fields
    */ 
    public function getGlobalFields() 
    {   
       return [ 
           'fields' => [
               [
                    'key'       => 'paper_size',
                    'label'     => 'Paper size',
                    'component' => 'dropdown',
                    'tips'      => 'All available templates are shown here, select a default template',
                    'options'   => PdfOptions::getPaperSizes()
               ],
               [
                    'key'       => 'template',
                    'label'     => 'Template',
                    'component' => 'dropdown',
                    'options'   => $this->formattedTemplates()
               ],
                [
                    'key'       => 'orientation',
                    'label'     => 'Orientation',
                    'component' => 'dropdown',
                    'options'   => PdfOptions::getOrientations()
               ],
               [
                    'key'       => 'font',
                    'label'     => 'Font family',
                    'component' => 'dropdown',
                    'options'   => PdfOptions::getFonts()
               ],
               [
                    'key'       => 'font_size',
                    'label'     => 'Font size',
                    'component' => 'number'
               ],
               [
                    'key'       => 'font_color',
                    'label'     => 'Font color',
                    'component' => 'color_picker'
               ],
               [
                    'key'   => 'entry_view',
                    'label' => 'Entry view',
                    'component' => 'radio_choice',
                    'options'   => [
                        'I' => 'View',
                        'D' => 'Download'
                    ]
               ]
           ]
        ];
    }


    /*
    * All the registered template will return
    * @return key => [ path, name]
    */ 
    public function getAllTemplates()
    {
        wp_send_json_success(
            $this->formattedTemplates()
        );
    }


    /*
    * Global settings options will get from this 
    * method @getPdfSettingOptions
    */
    public function getGlobalSettings() 
    {
        wp_send_json_success(
           $this->getGlobalFields()
        );
    }


    /*
    * single form setting Fields 
    * according to specific template
    */
    public function getFormTemplateSettings()
    {
        $templateKey = $_REQUEST['templateKey'];

        $allTemplates =  $this->getAvailableTemplates();
       
        foreach ($allTemplates as $key => $value) {
            new $value['path']($this->app);
        };

        $settingsFields = apply_filters(
            'fluentform_get_pdf_settings_fields_' . $templateKey, [], $templateKey
        );
        
        wp_send_json_success($settingsFields);
    }

    
    public function getPdfConfig($settings, $default)
    {   
        return [
            'mode' => 'utf-8', 
            'format' => Arr::get($settings, 'paper_size', Arr::get($default, 'paper_size', 'A4')),
            'orientation' => Arr::get($settings, 'orientation', Arr::get($default, 'orientation', 'p'))
        ];
    }

    /*
    * when download button will press
    * Pdf rendering will control from here
    */
    public function pdfDownload() 
    {
        $entry = Arr::get($_REQUEST, 'entry');

        $settings = Arr::get($_REQUEST, 'settings');

        if (!$entry || !$settings) {
            return;
        }
        
        $this->renderPdf(
            $settings['value'],
            $entry["user_inputs"],
            get_option('_fluentform_global_pdf_settings')
        );
    }

    protected function renderPdf($settings, $userInputData, $default)
    {
        $template = $this->initAndGetTemplateName($settings, $default);

        $inputHtml = apply_filters(
            'fluentform_get_pdf_html_template_' . $template, $userInputData, $settings, $default
        );

        $filename = Arr::get($settings, 'filename', 'fluentformspdf');
        
        $entryView = Arr::get($settings, 'entry_view', Arr::get($default, 'entry_view', 'I'));
    
        $mpdf = new \Mpdf\Mpdf($this->getPdfConfig($settings, $default));
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output($filename, $entryView);
    }

    protected function initAndGetTemplateName($settings, $default)
    {
        $template = Arr::get($settings, 'template', $default['template']);

        $templateClass = $this->getAvailableTemplates()[$template]['path'];
        
        new $templateClass($this->app);

        return $template;
    }
}
