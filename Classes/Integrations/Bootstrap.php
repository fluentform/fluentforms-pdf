<?php

namespace FluentFormPdf\Classes\Integrations;

use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Acl\Acl;
use FluentFormPdf\Classes\Templates\Template2;

class Bootstrap
{


    protected $app = null;
    public function __construct( Application $app)
    {
        $this->app = $app;

        add_filter('fluentform_global_settings_components', array($this, 'globalSettings'));
        add_filter('fluentform_form_settings_menu', array($this, 'settingsMenu'));
       
        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', array($this, 'pdfDownload'));  
        add_action('init', array($this, 'setAjaxHandler'));
      
    }

    public function globalSettings($setting){
        $setting["pdf"]  = [
             "hash" => "pdf",
             "title" => "PDF"
        ];
       return $setting;

     }

     public function settingsMenu($settingsMenus) {
        $settingsMenus['pdf'] = array(
            'title' => __('PDF settings', 'fluentform'),
            'slug'  => 'pdf_settings',
            'hash'  => 'pdf',
            'route' => '/pdf-settings'
        );
        return $settingsMenus;
     }

     public function setAjaxHandler() {
        $this->app->addAdminAjaxAction(
            'fluentform_pdf_admin_ajax_actions',
            function () {
                Acl::verify('fluentform_forms_manager');
        
              
        
                wp_send_json_success([
                    'pages' => 'shamim'
                ], 200);
            }
        );
     }
     

     public function pdfDownload() {

        if(!isset($_REQUEST['entry'])) {
            return ;
        }
     
        // $userInputData = $_REQUEST['entry']["user_inputs"];

        // $inputHtml = '';
        // foreach($userInputData as $key => $value) {
        //     $inputHtml .=  '<p>'.$key . ': ' .$value. '</p>';
        // };


        $inputHtml = (new Template2())->getHtmlTemplate('shamim');
        
        // dd($tem);



        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output();
     }


}
