<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Foundation\Application;


class Template2 extends TemplateManager{


    public function __construct(Application $app)
    {
          parent::__construct(
                $app,
                'Template2',
                'fluentform_pdf_template2',
         );
         
         $this->registerAdminHooks();
    }

    public function getSettingsFields($settings, $formId) {
        return [
            'fields' => [
                [
                    'key' => 'filename',
                    'label' => 'Filename Name',
                    'required' => true,
                    'placeholder' => 'Your file Name',
                    'component' => 'text'
                ]
            ]
        ];
    }

    public function getHtmlTemplate ($userInputData) {

        $inputHtml = '';
        
        foreach($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:red;">'.$key . ': ' .$value. '</p>';
        };
        return $inputHtml;
    }
}