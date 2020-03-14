<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;


class Template1 extends TemplateManager {

    public function __construct()
    {
          parent::__construct(
                'Template1',
                'fluentform_pdf_template1'
         );
    }
    

    public function getHtmlTemplate ($userInputData) {
        
        $inputHtml = '';
     
        foreach($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:green;">'.$key . ':</p><p>' .$value. '</p>';
        };
        return $inputHtml;
    }
}