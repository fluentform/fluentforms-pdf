<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


class BasicTemplate extends TemplateManager
{
    protected $templateKey = 'blank';

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function getDefaultSettings($form)
    {
        return [
            'header' => '',
            'footer' => '',
            'body' => '{all_data}'
        ];
    }

    public function getSettingsFields()
    {
        return array(
            [
                'key' => 'header',
                'label' => 'Header Content',
                'tips' => 'Write your header content which will be shown every page of the PDF',
                'component' => 'wp-editor'
            ],
            [
                'key' => 'body',
                'label' => 'PDF Body Content',
                'tips' => 'Write your Body content for actual PDF body',
                'component' => 'wp-editor'
            ],
            [
                'key' => 'footer',
                'label' => 'Footer Content',
                'tips' => 'Write your Footer content which will be shown every page of the PDF',
                'component' => 'wp-editor'
            ]
        );
    }
}
