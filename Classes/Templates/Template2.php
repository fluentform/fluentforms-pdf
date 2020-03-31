<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
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
                'placeholder'   => 'Your File Name',
                'component'     => 'editor'
            ],
            [
                'key'           => 'footer',
                'label'         => 'Pdf Footer',
                'tab'           => 'tab1',
                'tips'          => 'This text will be added to the footer section of pdf',
                'placeholder'   => 'Your Pdf Footer',
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


       return [ 'fields' => array_merge( 
            $customSettings,
            PdfOptions::commonSettings()
       )];
    }


    public function getStyles ($settings, $default) 
    {
        // will @return $color, $accent, $font, $fontSize
        extract(PdfOptions::getPreferences($settings, $default)); 

        $styles = 'table {width: 100%; border-radius:10px; border:1px solid '.$accent.'}
            tr:nth-child(even){background-color: #dddddd} tr:nth-child(odd){background-color: #F8F8F8}
            td{color:'.$color.'; font-size:'.$fontSize.'px!important; text-align: left; padding:10px;}
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
                $inputHtml .= '<td width="20%"><img width="100" src="'.urlencode($value).'"/></td>';
            }else {
                $inputHtml .= '<td width="20%">'.$value.'</td>';
            }
           
            $inputHtml .= '</tr>';
        };
        $inputHtml .= '</table></div></div>';
;
        return [
            'html'  => wp_unslash($inputHtml),
            'styles'=> $this->getStyles($settings, $default)
        ];
    }
}
