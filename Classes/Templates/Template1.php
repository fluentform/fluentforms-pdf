<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;

class Template1 extends TemplateManager {
    public function getHtmlTemplate ($userInputData) {

        $inputHtml = '';
        
        foreach($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:green;">'.$key . '</p>:<p>' .$value. '</p>';
        };
        return $inputHtml;
    }
}