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
        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', array($this, 'pdfDownload'));
       
        
      
    }

    public function globalSettings($setting){
        $setting["pdf"]  = [
             "hash" => "pdf",
             "title" => "PDF"
        ];
       return $setting;

     }

     public function pdfDownload() {
        extract($_REQUEST);
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML('<h1>'.$entry_id.'</h1><p style="color:red;">this is test para by hasanuzzaman</p>');
        $mpdf->Output();
     }


}
