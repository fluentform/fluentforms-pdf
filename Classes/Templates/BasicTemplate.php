<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\App\Services\Emogrifier\Emogrifier;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\Application;
use FluentFormPdf\Classes\Templates\TemplateManager;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;


class BasicTemplate extends TemplateManager
{
    protected $templateKey = 'blank';

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function getDefaultSettings($form)
    {
        return [
            'header' => '',
            'footer' => '',
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

    public function generatePdf($submissionId, $feed)
    {
        $settings = $feed['settings'];
        $submission = wpFluent()->table('fluentform_submissions')
                        ->where('id', $submissionId)
                        ->first();
        $formData = json_decode($submission->response, true);

        $settings = ShortCodeParser::parse($settings, $submissionId, $formData);

        $htmlBody = $settings['header'];
        $htmlBody .= $settings['body'];

        $htmlBody = str_replace('{page_break}', '<page_break />', $htmlBody);

        $footer = $settings['footer'];

        $fileName = $feed['name'];
        $fileName = sanitize_title($fileName, 'pdf-file', 'display');
        $this->pdfBuilder($fileName, $feed, $htmlBody, $footer);
    }
}
