<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Foundation\Application;

class Template1 extends TemplateManager {

    public function __construct(Application $app)
    {
        parent::__construct(
            $app,
            'template1'
        );

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
                    'category'      => 'general',
                    'placeholder'   => 'Your File Name',
                    'component'     => 'text'
                ],
                [
                    'key'           => 'pdfheader',
                    'label'         => 'PDF Header',
                    'required'      => true,
                    'require_list'  => false,
                    'category'      => 'appearance',
                    'placeholder'   => 'Your Pdf Header',
                    'component'     => 'text'
                ],
                [
                    'key'           => 'conditionals',
                    'label'         => 'Conditional Logics',
                    'tips'          => 'Allow Pdf conditions',
                    'component'     => 'conditional_block'
                ],
            ]
        ];
    }

    public function getHtmlTemplate ($userInputData) {
        
        $inputHtml = '';
     
        foreach($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:green;">'.$key . ':</p><p>' .$value. '</p>';
        };
        return $inputHtml;
    }
}