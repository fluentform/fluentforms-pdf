<?php
namespace FluentFormPdf\Classes\Templates;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Foundation\Application;

class Template1 extends TemplateManager {

    public function __construct(Application $app)
    {
        parent::__construct(
            $app,
            'Template1',
            'fluentform_pdf_template1'
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
                $inputHtml .=  '<p style="color:green;">'.$key . ':</p><p>' .$value. '</p>';
        };
        return $inputHtml;
    }
}