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
        add_filter('fluentform_form_settings_menu', array($this, 'settingsMenu'));
        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', array($this, 'pdfDownload'));   
        add_action('wp_ajax_fluentform_get_form_pdf_template_settings', array($this, 'getFormTemplateSettings')); 
        
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

    public function getAvailableTemplates() 
    {
        $classes = apply_filters(  
            'fluentform_pdf_template_map',
            [
                "template1" => "\FluentFormPdf\Classes\Templates\Template1",
                "template2" => "\FluentFormPdf\Classes\Templates\Template2"
            ]
        );
        return $classes;
    }

    public function getFormTemplateSettings() 
    {
        $templateKey = $_REQUEST['templateKey'];
        $allTemplates =  $this->getAvailableTemplates();
       
        foreach( $allTemplates as $key => $path ){
            new $path($this->app);
        };

        $settingsFields = apply_filters('fluentform_get_pdf_settings_fields_' . $templateKey, [], $templateKey);
        
        wp_send_json_success( $settingsFields, 200);
    }

    public function pdfDownload() 
    {
        if(!isset($_REQUEST['entry']) || !isset($_REQUEST['settings'])) {
            return ;
        }   
        $settings = $_REQUEST['settings'];
        $templateKey = $_REQUEST['settings']['value']['template'];
        $userInputData = $_REQUEST['entry']["user_inputs"];

        $allTemplates =  $this->getAvailableTemplates();
        $template = new $allTemplates[$templateKey]($this->app); 
        $inputHtml = apply_filters('fluentform_get_pdf_html_template_' . $templateKey, $userInputData);


        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output();
    }


}
