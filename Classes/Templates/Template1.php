<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


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
                    'key'           => 'header',
                    'label'         => 'Pdf header',
                    'tab'           => 'tab1',
                    'tips'          => 'This text will be added to the header section of pdf',
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
                    'key' => 'form_title',
                    'label' => 'Show form title',
                    'tab'   =>'tab2',
                    'component' => 'radio_choice',
                    'options'   => [
                        'yes' => 'Yes',
                        'no' => 'No'
                    ]
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


    public function getStyles ($settings, $default) 
    {
        $color = Arr::get($default, 'font_color', '#000000');
        $accent = Arr::get($default, 'accent_color', '#000000');
        return 'table {width: 100%;}
            tr:nth-child(even){background-color: #dddddd};
            td { color:'.$color.';border: 1px solid '.$accent.'; 
            min-width: 200px; text-align: left; padding: 8px;}'; 
    }

    public function getHtmlTemplate ($data, $settings, $default) 
    {
        $inputHtml = '<div><table>';
        foreach (Arr::get($data, 'inputs') as $value => $key) {
            $inputHtml .= '<tr>';
            $inputHtml .= '<td>'.$key .'</td>';
            $inputHtml .= '<td>'.$value .'</td>';
            $inputHtml .= '</tr>';
        };
        $inputHtml .= '</table></div>';

        return [
            'html' => wp_unslash($inputHtml),
            'styles' => $this->getStyles($settings, $default)
        ];
    }
}
