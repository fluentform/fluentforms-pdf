<?php 
namespace FluentFormPdf\Classes\Templates;

abstract class TemplateManager {
    abstract public function getHtmlTemplate($title);
}