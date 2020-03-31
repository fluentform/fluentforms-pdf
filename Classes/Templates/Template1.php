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
            //     'tab'           => 'tab1',
            //     'tips'          => 'Allow Pdf conditions',
            //     'component'     => 'conditional_block'
            // ],
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

        $styles = 'table { border-collapse:separate; border-spacing: 0 15px; width: 100%;}
            tr{ border-radius:15px;}
            td{color:'.$color.'; border: 1px solid '.$accent.'; border-radius:15px; font-size:'.$fontSize.' px!important; text-align: left; padding:20px;}
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
            $inputHtml .= '<tr><td height="20px"><strong>'.$labels[$key] .':</strong>  ';
            if (strpos($key, 'image-upload')!== false) {
                $inputHtml .= '<img src="'.$value.'"/>';
            }else {
                $inputHtml .= $value;
            }
           
            $inputHtml .= '</td></tr>';
        };
        $inputHtml .= '</table></div></div>';

        return [
            'html' => wp_unslash($inputHtml),
            'styles' => $this->getStyles($settings, $default)
        ];
    }
}
