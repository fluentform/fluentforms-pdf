<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;


class Template2 extends TemplateManager{
    public function getHtmlTemplate ($title) {
        $html  = '<h3 style="color:red;">';
        $html .= $title;
        $html .= '</h3>';
        return $html;
    }
}