<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;

class Template1 extends TemplateManager
{
    protected $templateKey = 'template1';

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
                    'tab'           => 'tab1',
                    'placeholder'   => 'Your File Name',
                    'component'     => 'value_text'
                ],
                [
                    'key'           => 'pdfheader',
                    'label'         => 'PDF Header',
                    'tab'           => 'tab2',
                    'placeholder'   => 'Your Pdf Header',
                    'component'     => 'value_text'
                ],
                [
                    'key'           => 'conditionals',
                    'label'         => 'Conditional Logics',
                    'tab'           => 'tab1',
                    'tips'          => 'Allow Pdf conditions',
                    'component'     => 'conditional_block'
                ],
                [
                    'key'       => 'paper_size',
                    'label'     => 'Paper size',
                    'component' => 'dropdown',
                    'tab'       => 'tab2',
                    'tips'      => 'All available templates are shown here, select a default template',
                    'options'   => [
                        'a_four'    => 'A4 (210 x 297mm)',
                        'letter'    =>'Letter (8.5 x 11in)',
                        'legal'     =>'Legal (8.5 x 14in)',
                        'ledger'    =>'Ledger / Tabloid (11 x 17in)',
                        'executive' =>'Executive (7 x 10in)',
                        'a_zero'    => 'A0 (841 x 1189mm)',
                        'a_one'     => 'A1 (594 x 841mm)'

                    ]
                ],
                [
                    'key' => 'font',
                    'label' => 'Font family',
                    'component' => 'dropdown',
                    'tab'       => 'tab2',
                    'options'   => [
                        'serif' => "Serif",
                        'mono'  => 'mono' 
                    ]
               ],
               [
                    'key' => 'form_title',
                    'label' => 'Entry view',
                    'tab'   =>'tab2',
                    'component' => 'radio_choice',
                    'options'   => [
                        'yes' => 'Yes',
                        'no' => 'No'
                    ]
               ],
               [
                    'key' => 'empty_fields',
                    'label' => 'Show empty fields',
                    'tab'   =>'tab2',
                    'component' => 'radio_choice',
                    'options'   => [
                        'yes' => 'Yes',
                        'no' => 'No'
                    ]
                ],
                [
                    'key' => 'reverse_text',
                    'label' => 'Reverse text',
                    'tab'   =>'tab2',
                    'tips'   =>'Script like Arabic and Hebrew are written right to left.',
                    'component' => 'radio_choice',
                    'options'   => [
                        'yes' => 'Yes',
                        'no' => 'No'
                    ]
               ]

            ]
        ];
    }

    public function getHtmlTemplate ($userInputData) {
        
        $inputHtml = '';
     
        foreach ($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:green;">'.$key . ':</p><p>' .$value. '</p>';
        };

        return $inputHtml;
    }
}
