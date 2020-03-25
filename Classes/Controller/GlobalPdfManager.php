<?php

namespace FluentFormPdf\Classes\Controller;


use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


class GlobalPdfManager
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;

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
                    'key' => 'orientation',
                    'label' => 'Orientation',
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
                        'view' => 'View',
                        'download' => 'download'
                    ]
               ],
                [
                    'key' => 'background',
                    'label' => 'Background',
                    'component' => 'switch'
                ],
                [
                    'key' => 'debug_mode',
                    'label' => 'Debug mode',
                    'component' => 'switch'
                ],
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


    /*
    * when download button will press
    * Pdf rendering will control from here
    */
    public function pdfDownload() 
    {
        if (!isset($_REQUEST['entry']) || !isset($_REQUEST['settings'])) {
            return ;
        }

        $settings = $_REQUEST['settings'];
        $userInputData = $_REQUEST['entry']["user_inputs"];
        $templateKey = $_REQUEST['settings']['value']['template'];

        $allTemplates =  $this->getAvailableTemplates();

        $template = new $allTemplates[$templateKey]["path"]($this->app);

        $inputHtml = apply_filters('fluentform_get_pdf_html_template_' . $templateKey, $userInputData);

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L', 'orientation' => 'P']);

        //
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output('yourFileName.pdf', 'I');
    }
}
