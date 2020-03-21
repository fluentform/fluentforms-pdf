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

        add_filter('fluentform_global_settings_components', array($this, 'globalSettings'));

        // Global settings menu declaration
        add_filter('fluentform_form_settings_menu', array($this, 'settingsMenu'));

        // Frontend render When download button clicked
        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', array($this, 'pdfDownload'));

        // single form pdf settings fields ajax
        add_action('wp_ajax_fluentform_get_form_pdf_template_settings', array($this, 'getFormTemplateSettings'));
        
        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions',array($this, 'getAllTemplates'));
        
        add_action('wp_ajax_fluentform_get_pdf_global_setting_options', array($this, 'getGlobalSettingOptions'));
        
        
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
    * @required for new template 
    * return key => [ path, name]
    * To register a new template this filter must use for path mapping
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
    * return [ key name]
    * global pdf paper fields options
    */ 
    public function getGlobalOptions() 
    {
        return [
            "paper_size" =>[
                'a_four'    => 'A4 (210 x 297mm)',
                'letter'    =>'Letter (8.5 x 11in)',
                'legal'     =>'Legal (8.5 x 14in)',
                'ledger'    =>'Ledger / Tabloid (11 x 17in)',
                'executive' =>'Executive (7 x 10in)',
                'a_zero'    => 'A0 (841 x 1189mm)',
                'a_one'     => 'A1 (594 x 841mm)'
            ],
            "font_family" => [
                'serif' => "Serif",
                'mono'  => 'mono' 
            ]
        ];
    }


    /*
    * All the registered template will return
    * by this @getAllTemplates 
    * data structure: key => [ path, name]
    */ 
    public function getAllTemplates()
    {
        $classes = $this->getAvailableTemplates();
        $allTemplates = [];
        forEach($classes as $key => $value){    
            $allTemplates[$key] = $value['name'];
        };
        wp_send_json_success( $allTemplates ,200);
    }


    /*
    * Global settings options will get from this 
    * method @getPdfSettingOptions
    */
    public function getGlobalSettingOptions() {
        $classes = $this->getAvailableTemplates();
        $allTemplates = [];
        forEach($classes as $key => $value){    
            $allTemplates[$key] = $value['name'];      
        };

        $paperDetails = $this->getGlobalOptions();

        wp_send_json_success(array(
            'templates'   => $allTemplates,
            'paper_size'  => $paperDetails['paper_size'],
            'fonts' => $paperDetails['font_family']
        ), 200);

    }


    /*
    * return Fields which are available on specific template
    * in single form settings from method @getFormTemplateSettings
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
    * render the main pdf in frontend
    * when download button will press
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
