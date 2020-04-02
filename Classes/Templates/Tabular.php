<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;

class Tabular extends TemplateManager
{
    protected $templateKey = 'tabular';

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->registerAdminHooks();
    }

    public function getSettingsFields($settings)
    {
        $customSettings =  [
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
                'placeholder'   => 'Your PDF Header',
                'component'     => 'editor'
            ],
            [
                'key'           => 'footer',
                'label'         => 'Pdf Footer',
                'tab'           => 'tab1',
                'tips'          => 'This text will be added to the footer section of pdf',
                'placeholder'   => 'Your PDF Footer',
                'component'     => 'editor'
            ],
            // [
            //     'key'           => 'conditionals',
            //     'label'         => 'Conditional Logics',
            //     'tips'          => 'Allow Pdf conditions',
            //     'tab'           => 'tab1',
            //     'component'     => 'conditional_block'
            // ]

        ];

       return [
            'fields' => array_merge($customSettings, $settings)
        ];
    }


    public function getStyles($preferences, $settings, $default) 
    {
        $color = Arr::get($preferences,'color');
        $accent = Arr::get($preferences,'accent');
        $font = Arr::get($preferences,'font');
        $fontSize = Arr::get($preferences,'fontSize');

        $styles = 'table {width: 100%; border-radius:10px; border:1px solid '.$accent.'}
            tr:nth-child(even){background-color: #dddddd} tr:nth-child(odd){background-color: #F8F8F8}
            td{color:'.$color.'; font-size:'.$fontSize.'px!important; text-align: left; padding:10px;}
            .ff-pdf-header {text-align:center;}';

        if ($font && !($font == 'default')) {
            $styles .= '.ff-pdf-table tr td, .ff-pdf-header {font-family:'.$font.'}';
        }

        return $styles;
    }

    public function getHtmlTemplate($templateData, $inputs, $settings, $default) 
    {
        $inputHtml = '<div class="ff-pdf-wrapper">';

        $inputHtml .= '<div class="ff-pdf-table"><table>';

        foreach ($templateData['data'] as $key => $value) {
            $inputHtml .= '<tr>';
            $inputHtml .= '<td width="20%">'.$templateData['labels'][$key] .'</td>';
            $inputHtml .= '<td width="20%">'.$value.'</td>';
            $inputHtml .= '</tr>';
        };
        
        $inputHtml .= '</table></div></div>';

        return wp_unslash($inputHtml);
    }
}
