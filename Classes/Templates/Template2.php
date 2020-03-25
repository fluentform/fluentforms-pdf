<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;

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
                    'tab'           => 'tab1',
                    'placeholder'   => 'Your File Name',
                    'component'     => 'value_text'
                ],
               
                [
                    'key'           => 'header',
                    'label'         => 'Pdf header',
                    'tab'           => 'tab1',
                    'tips'          => 'This text will be added to the header section of pdf',
                    'placeholder'   => 'Your File Name',
                    'component'     => 'value_text'
                ],
                 [
                    'key'           => 'conditionals',
                    'label'         => 'Conditional Logics',
                    'tips'          => 'Allow Pdf conditions',
                    'tab'           => 'tab1',
                    'component'     => 'conditional_block'
                ],
                [
                    'key'       => 'paper_size',
                    'label'     => 'Paper size',
                    'component' => 'dropdown',
                    'tab'       => 'tab2',
                    'tips'      => 'select a pdf paper size',
                    'options'   => PdfOptions::getPaperSizes()
                ],
                [
                    'key'       => 'orientation',
                    'label'     => 'Orientation',
                    'tab'       => 'tab2',
                    'component' => 'dropdown',
                    'options'   => PdfOptions::getOrientations()
                ],
                [
                    'key' => 'font',
                    'label' => 'Font family',
                    'component' => 'dropdown',
                    'tab'       => 'tab2',
                    'options'   => PdfOptions::getFonts()
               ],
                [
                    'key' => 'font_color',
                    'label' => 'Font color',
                    'tab'   => 'tab2',
                    'tips'  => 'The font color will use in the PDF.',
                    'placeholder' => 'Your Feed Name',
                    'component' => 'color_picker'
               ],
                [
                    'key' => 'accent_color',
                    'label' => 'Accent color',
                    'tab'   => 'tab2',
                    'tips'  => 'The accent color is used for the page, section titles and the border.',
                    'placeholder' => 'Your Feed Name',
                    'component' => 'color_picker'
               ],
               [
                    'key' => 'entry_view',
                    'label' => 'Entry view',
                    'tab'   =>'tab2',
                    'component' => 'radio_choice',
                    'options'   => [
                        'I' => 'View',
                        'D' => 'Download'
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

    public function getHtmlTemplate ($userInputData, $settings, $default) 
    {

        $inputHtml = '';
        
        foreach ($userInputData as $key => $value) {
                $inputHtml .=  '<p style="color:red;">'.$key . ': ' .$value. '</p>';
        };

        return $inputHtml;
    }
}
