<?php

namespace FluentFormPdf\Classes\Controller;


use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Acl\Acl;
use FluentFormPdf\Classes\Templates\TemplateManager;


class GlobalPdfManager
{
    protected $app = null;

    public function __construct($app)
    {
        $this->app = $app;

        // Global settings register
        add_filter('fluentform_global_settings_components', array($this, 'globalSettings'));
        add_filter('fluentform_form_settings_menu', array($this, 'settingsMenu'));
        add_action('wp_ajax_fluentform_get_pdf_global_setting_options', array($this, 'getGlobalSettings'));

        // single form pdf settings fields ajax
        add_action('wp_ajax_fluentform_get_form_pdf_template_settings', array($this, 'getFormTemplateSettings'));
        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions',array($this, 'getAllTemplates'));

        // Frontend render When download button clicked
        add_action('wp_ajax_fluentform_pdf_download_ajax', array($this, 'pdfDownload'));
        
    }

   
    public function globalSettings($setting)
    {
        $setting["pdf"]  = [
             "hash" => "pdf",
             "title" => "PDF"
        ];
       return $setting;
    }

    
    public function settingsMenu($settingsMenus) 
    {
        $settingsMenus['pdf'] = array(
            'title' => __('PDF settings', 'fluentform'),
            'slug'  => 'pdf_settings',
            'hash'  => 'pdf',
            'route' => '/pdf-settings'
        );
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
                "template1" =>[
                    'path' => "\FluentFormPdf\Classes\Templates\Template1",
                    'name'  => 'Blank Green'
                ],
                "template2" => [
                    'path' => "\FluentFormPdf\Classes\Templates\Template2",
                    'name'  => 'Color Red' 
                ]
            ]
        );
        return $classes;
    }

    /*
    * @return [ key => value]
    * processed for dropdown fields
    */ 
    public function formattedTemplates() {
        $classes = $this->getAvailableTemplates();
        $allTemplates = [];
        forEach($classes as $key => $value){    
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
           'fields' =>[
               [
                    'key'       => 'paper_size',
                    'label'     => 'Paper size',
                    'component' => 'dropdown',
                    'tips'      => 'All available templates are shown here, select a default template',
                    'options'   => [
                        'a_four'    => 'A4 (210 x 297mm)',
                        'letter'    =>'Letter (8.5 x 11in)',
                        'legal'     =>'Legal (8.5 x 14in)',
                        'ledger'    =>'Ledger / Tabloid (11 x 17in)',
                        'executive' =>'Executive (7 x 10in)',
                        'a_zero'    => 'A0 (841 x 1189mm)',
                        'a_one'     => 'A1 (594 x 841mm)'

                    ]
               ],
               [
                    'key' => 'template',
                    'label' => 'Template',
                    'component' => 'dropdown',
                    'options'   => $this->formattedTemplates()
               ],
               [
                    'key' => 'font',
                    'label' => 'Font family',
                    'placeholder' => 'Your Feed Name',
                    'component' => 'dropdown',
                    'options'   => [
                        'serif' => "Serif",
                        'mono'  => 'mono' 
                    ]
               ],
               [
                    'key' => 'font_size',
                    'label' => 'Font size',
                    'placeholder' => 'Your Feed Name',
                    'component' => 'number'
               ],
               [
                    'key' => 'font_color',
                    'label' => 'Feed Name',
                    'placeholder' => 'Your Feed Name',
                    'component' => 'color_picker'
               ],
               [
                    'key' => 'entry_view',
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
        $allTemplates = $this->formattedTemplates();
        wp_send_json_success( $allTemplates ,200);
    }


    /*
    * Global settings options will get from this 
    * method @getPdfSettingOptions
    */
    public function getGlobalSettings() 
    {
        $paperDetails = $this->getGlobalFields();
        wp_send_json_success(
           $paperDetails, 200);

    }


    /*
    * single form setting Fields 
    * according to specific template
    */
    public function getFormTemplateSettings()
    {
        $templateKey = $_REQUEST['templateKey'];
        $allTemplates =  $this->getAvailableTemplates();
       
        foreach( $allTemplates as $key => $value ){
            new $value['path']($this->app);
        };

        $settingsFields = apply_filters('fluentform_get_pdf_settings_fields_' . $templateKey, [], $templateKey);
        
        wp_send_json_success( $settingsFields, 200);
    }


    /*
    * when download button will press
    * Pdf rendering process will control from here
    */
    public function pdfDownload() 
    {
        if(!isset($_REQUEST['entry']) || !isset($_REQUEST['settings'])) {
            return ;
        }   
        $settings = $_REQUEST['settings'];
        $templateKey = $_REQUEST['settings']['value']['template'];
        $userInputData = $_REQUEST['entry']["user_inputs"];

        $allTemplates =  $this->getAvailableTemplates();

        $template = new $allTemplates[$templateKey]["path"]($this->app); 
        $inputHtml = apply_filters('fluentform_get_pdf_html_template_' . $templateKey, $userInputData);

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output();
    }


}
