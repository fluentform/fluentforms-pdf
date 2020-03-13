<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;

class Template1 extends TemplateManager {
    public function getHtmlTemplate ($title) {
        $html  = '<p>';
        $html .= $title;
        $html .= '</p>';
        return $html;
    }
}