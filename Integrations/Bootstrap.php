<?php

namespace FluentFormPdf\Integrations;

use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;


class Bootstrap
{
    public function __construct(Application $app)
    {
        add_filter('fluentform_global_settings_components', array($this, 'globalSettings'));
        add_filter('fluentform_form_settings_menu', array($this, 'settingsMenu'));

        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', array($this, 'pdfDownload'));  
      
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

     public function pdfDownload() {

        if(!isset($_REQUEST['entry'])) {
            return ;
        }
     
        $userInputData = $_REQUEST['entry']["user_inputs"];

        // dd($userInputData);
        $inputHtml = '';
        foreach($userInputData as $key => $value) {
            $inputHtml .=  '<p>'.$key . ': ' .$value. '</p>';
        };

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output();
     }


}
