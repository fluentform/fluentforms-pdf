<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;


class Template2 extends TemplateManager{
    public function getHtmlTemplate ($userInputData) {

        $inputHtml = '';
        
        foreach($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:red;">'.$key . ': ' .$value. '</p>';
        };
        return $inputHtml;
    }
}