<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\App\Services\Emogrifier\Emogrifier;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


class GeneralTemplate extends TemplateManager
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    public function getDefaultSettings($form)
    {
        return [
            'header' => '<h2>PDF Title</h2>',
            'footer' => '<table width="100%"><tr><td width="50%">{DATE j-m-Y}</td><td width="50%"  style="text-align: right;" align="right">{PAGENO}/{nbpg}</td></tr></table>',
            'body' => '{all_data}'
        ];
    }

    public function getSettingsFields()
    {
        return array(
            [
                'key' => 'header',
                'label' => 'Header Content',
                'tips' => 'Write your header content which will be shown every page of the PDF',
                'component' => 'wp-editor'
            ],
            [
                'key' => 'body',
                'label' => 'PDF Body Content',
                'tips' => 'Write your Body content for actual PDF body',
                'component' => 'wp-editor'
            ],
            [
                'key' => 'footer',
                'label' => 'Footer Content',
                'tips' => 'Write your Footer content which will be shown every page of the PDF',
                'component' => 'wp-editor'
            ]
        );
    }

    public function generatePdf($submissionId, $feed, $outPut = 'I', $fileName = '')
    {
        $settings = $feed['settings'];
        $submission = wpFluent()->table('fluentform_submissions')
                        ->where('id', $submissionId)
                        ->first();
        $formData = json_decode($submission->response, true);

        $settings = ShortCodeParser::parse($settings, $submissionId, $formData);

        $htmlBody = $settings['header'];
        $htmlBody .= $settings['body'];

        $footer = $settings['footer'];

        if(!$fileName) {
            $fileName = ShortCodeParser::parse( $feed['name'], $submissionId, $formData);
            $fileName = sanitize_title($fileName, 'pdf-file', 'display');
        }

        return $this->pdfBuilder($fileName, $feed, $htmlBody, $footer, $outPut);
    }
}
