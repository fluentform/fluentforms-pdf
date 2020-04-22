<?php 

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;

abstract class TemplateManager
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract public function getSettingsFields();

}
