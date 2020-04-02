<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


class Blank extends TemplateManager
{
    protected $templateKey = 'blank';

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->registerAdminHooks();
    }
    
    public function getSettingsFields($settings)
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
                'tips'          => 'This text will be added to the header section of every single page',
                'placeholder'   => 'Your PDF Header',
                'component'     => 'editor'
            ],
            [
                'key'           => 'footer',
                'label'         => 'Pdf Footer',
                'tab'           => 'tab1',
                'tips'          => 'This text will be added to the footer section of every single page',
                'placeholder'   => 'Your PDF Footer',
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

        $styles = 'table { border-collapse:separate; border-spacing: 0 15px; width: 100%;}
            tr{ border-radius:15px;}
            td{color:'.$color.'; border: 1px solid '.$accent.'; border-radius:15px; font-size:'.$fontSize.' px!important; text-align: left; padding:20px;}
            .ff-pdf-header {text-align:center;}';

        if ( $font && !($font=='default')) {
            $styles .= '.ff-pdf-table tr td, .ff-pdf-header {font-family:'.$font.'}';
        }
        return $styles;
            
    }

    public function getHtmlTemplate($templateData, $inputs, $settings, $default) 
    {
        $inputHtml = '<div class="ff-pdf-wrapper">';

        $inputHtml .= '<div class="ff-pdf-table"><table>';

        foreach ($templateData['data'] as $key => $value) {
            $inputHtml .= '<tr><td height="20px"><strong>'.$templateData['labels'][$key] .':</strong>  ';
            
            $inputHtml .= $value;
           
            $inputHtml .= '</td></tr>';
        };

        $inputHtml .= '</table></div></div>';

        return wp_unslash($inputHtml);
    }
}
