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
        // Add any custom settings here, but the tab must be declare
        $customSettings = [
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
                'component'     => 'editor'
            ],
            [
                'key'           => 'conditionals',
                'label'         => 'Conditional Logics',
                'tab'           => 'tab1',
                'tips'          => 'Allow Pdf conditions',
                'component'     => 'conditional_block'
            ],
        ];


        return [ 'fields' => array_merge( 
            $customSettings,
            PdfOptions::commonSettings()
       )];
    }

    public function getPreferences($settings, $default) {
       return [ 
        'color' => Arr::get($settings, 'font_color', Arr::get($default, 'font_color')),
        'accent' => Arr::get($settings, 'accent_color', Arr::get($default, 'accent_color')),
        'font' => Arr::get($settings, 'font', Arr::get($default, 'font')),
        'fontSize' => Arr::get($settings, 'font_size', Arr::get($default, 'font_size'))
       ];

    }

    public function getStyles ($settings, $default) 
    {
        extract($this->getPreferences($settings, $default));

        $styles = 'table {width: 100%; border-radius:10px; border:1px solid '.$accent.'}
            tr:nth-child(even){background-color: #dddddd} tr:nth-child(odd){background-color: #F8F8F8}
            td{color:'.$color.'; font-size:'.$fontSize.' px!important; text-align: left; padding:10px;}
            .ff-pdf-header {text-align:center;}';

        if ( $font && !($font=='default')) {
            $styles .= '.ff-pdf-table tr td, .ff-pdf-header {font-family:'.$font.'}';
        }
        return $styles;
            
    }

    public function getHtmlTemplate ($data, $settings, $default) 
    {   
        $inputs = Arr::get($data, 'user_inputs');
        $labels = Arr::get($data, 'labels');
        if ( Arr::get($settings, 'empty_fields') == 'no') {
            $inputs = array_filter($inputs);
        };

        $inputHtml = '<div class="ff-pdf-wrapper">';

        $inputHtml .= '<div class="ff-pdf-table"><table>';
        foreach ($inputs as $key => $value) {
            $inputHtml .= '<tr>';
            $inputHtml .= '<td width="20%">'.$labels[$key] .'</td>';
            if (strpos($key, 'image-upload')!== false) {
                $inputHtml .= '<td width="20%"><img src="'.$value.'"/></td>';
            }else {
                $inputHtml .= '<td width="20%">'.$value.'</td>';
            }
           
            $inputHtml .= '</tr>';
        };
        $inputHtml .= '</table></div></div>';

        return [
            'html' => wp_unslash($inputHtml),
            'styles' => $this->getStyles($settings, $default)
        ];
    }
}
