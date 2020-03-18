<?php

namespace FluentFormPdf\Classes\Controller;


use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Acl\Acl;
use FluentFormPdf\Classes\Templates\Template1;
use FluentFormPdf\Classes\Templates\Template2;

class GlobalPdfManager
{
    protected $app = null;
    public function __construct( Application $app)
    {
        $this->app = $app;

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
        if(!isset($_REQUEST['entry']) || !isset($_REQUEST['settings'])) {
            return ;
        }
        $settings = $_REQUEST['settings'];
        $userInputData = $_REQUEST['entry']["user_inputs"];

        

        // $inputHtml = (new Template1())->getHtmlTemplate($userInputData);
        $inputHtml = '';
        foreach($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:red;">'.$key . ': ' .$value. '</p>';
        };

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($inputHtml);
        $mpdf->Output();
     }


}
