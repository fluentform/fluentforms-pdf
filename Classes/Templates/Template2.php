<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;

class Template2 extends TemplateManager
{
    protected $templateKey = 'template2';

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->registerAdminHooks();
    }

    public function getSettingsFields()
    {
        return [
            'fields' => [
                [
                    'key'           => 'filename',
                    'label'         => 'File Name',
                    'required'      => true,
                    'require_list'  => false,
                    'placeholder'   => 'Your File Name',
                    'component'     => 'text'
                ],
                [
                    'key'           => 'conditionals',
                    'label'         => 'Conditional Logics',
                    'tips'          => 'Allow Pdf conditions',
                    'component'     => 'conditional_block'
                ]
            ]
        ];
    }

    public function getHtmlTemplate ($userInputData) {

        $inputHtml = '';
        
        foreach ($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:red;">'.$key . ': ' .$value. '</p>';
        };

        return $inputHtml;
    }
}
