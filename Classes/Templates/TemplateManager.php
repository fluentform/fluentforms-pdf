<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\App\Services\Emogrifier\Emogrifier;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions;

abstract class TemplateManager
{
    protected $app;

    public $workingDir = '';
    public $tempDir = '';
    public $pdfCacheDir = '';

    public $fontDir = '';

    public function __construct(Application $app)
    {
        $this->app = $app;

        $dirStructure = AvailableOptions::getDirStructure();

        $this->workingDir = $dirStructure['workingDir'];
        $this->tempDir = $dirStructure['tempDir'];
        $this->pdfCacheDir = $dirStructure['pdfCacheDir'];
        $this->fontDir = $dirStructure['fontDir'];
    }

    abstract public function getSettingsFields();

    abstract public function generatePdf($submissionId, $settings, $outPut, $fileName = '');

    public function viewPDF($submissionId, $settings)
    {
        $this->generatePdf($submissionId, $settings, 'I');
    }

    /*
     * This name should be unique otherwise, it may just return from another file cache
     */
    public function outputPDF($submissionId, $settings, $fileName = '', $forceClear = false)
    {
        $fileName = $this->pdfCacheDir . '/' . $fileName;
        if (!$forceClear && file_exists($fileName . '.pdf')) {
            return $fileName . '.pdf';
        }

        $this->generatePdf($submissionId, $settings, 'F', $fileName);

        if (file_exists($fileName . '.pdf')) {
            return $fileName . '.pdf';
        }

        return false;
    }

    public function downloadPDF($submissionId, $settings)
    {
        return $this->generatePdf($submissionId, $settings, 'D');
    }

    public function getGenerator($mpdfConfig)
    {
        $defaults = [
            'fontDir' => [
                $this->fontDir
            ],
            'tempDir' => $this->tempDir,
            'curlCaCertificate' => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
            'curlFollowLocation' => true,
            'allow_output_buffering' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
            'ignore_invalid_utf8' => true,
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'enableImports' => true,
            'use_kwt' => true,
            'keepColumns' => true,
            'biDirectional' => true,
            'showWatermarkText' => true,
            'showWatermarkImage' => true,
        ];

        $mpdfConfig = wp_parse_args($mpdfConfig, $defaults);

        $mpdfConfig = apply_filters('fluentform_mpdf_config', $mpdfConfig);

        if (!class_exists('\Mpdf\Mpdf')) {
            require_once FLUENTFORM_PDF_PATH . 'vendor/autoload.php';
        }

        return new \Mpdf\Mpdf($mpdfConfig);
    }

    public function pdfBuilder($fileName, $feed, $body = '', $footer = '', $outPut = 'I')
    {
        $body = str_replace('{page_break}', '<page_break />', $body);

        $appearance = Arr::get($feed, 'appearance');
        $mpdfConfig = array(
            'mode' => 'utf-8',
            'format' => Arr::get($appearance, 'paper_size'),
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => Arr::get($appearance, 'orientation'),
        );

        if ($fontFamily = ArrayHelper::get($appearance, 'font_family')) {
            $mpdfConfig['default_font'] = $fontFamily;
        }

        if (!defined('FLUENTFORMPRO')) {
            $footer .= '<p style="text-align: center;">Powered By <a target="_blank" href="https://wpmanageninja.com/downloads/fluentform-pro-add-on/">Fluent Forms</a></p>';
        }

        $pdfGenerator = $this->getGenerator($mpdfConfig);

        if (ArrayHelper::get($appearance, 'security_pass')) {
            $password = ArrayHelper::get($appearance, 'security_pass');
            $pdfGenerator->SetProtection(array(), $password, $password);
        }

        if (ArrayHelper::get($appearance, 'language_direction') == 'rtl') {
            $pdfGenerator->SetDirectionality('rtl');
            $body = '<div class="ff_rtl">' . $body . '</div>';
        }


        try {
            // apply CSS styles inline for picky email clients
            $emogrifier = new Emogrifier($body, $this->getPdfCss($appearance));
            $body = $emogrifier->emogrify();
        } catch (\Exception $e) {
        }

        if (!empty($appearance['watermark_text']) || !empty($appearance['watermark_image'])) {
            $alpha = Arr::get($appearance, 'watermark_opacity');
            if (!$alpha || $alpha > 100) {
                $alpha = 5;
            }
            $alpha = $alpha / 100;

            if (!empty($appearance['watermark_image'])) {
                $pdfGenerator->SetWatermarkImage($appearance['watermark_image'], $alpha);
                $pdfGenerator->showWatermarkImage = true;
            } else {
                $pdfGenerator->SetWatermarkText($appearance['watermark_text'], $alpha);
                $pdfGenerator->showWatermarkText = true;
            }
        }

        $pdfGenerator->SetHTMLFooter($footer);
        $pdfGenerator->WriteHTML('<div class="ff_pdf_wrapper">' . $body . '</div>', \Mpdf\HTMLParserMode::HTML_BODY);

        if ($outPut == 'S') {
            return $pdfGenerator->Output($fileName . '.pdf', $outPut);;
        }

        $pdfGenerator->Output($fileName . '.pdf', $outPut);

    }

    public function getPdfCss($appearance)
    {
        $mainColor = Arr::get($appearance, 'font_color');
        if (!$mainColor) {
            $mainColor = '#4F4F4F';
        }
        $secondaryColor = Arr::get($appearance, 'accent_color');
        if (!$secondaryColor) {
            $secondaryColor = '#EAEAEA';
        }
        $headingColor = Arr::get($appearance, 'heading_color');

        $fontSize = Arr::get($appearance, 'font_size', 14);

        ob_start();
        ?>
        .ff_pdf_wrapper, p, li, td, th {
        color: <?php echo $mainColor; ?>;
        font-size: <?php echo $fontSize; ?>px;
        }

        .ff_all_data, table {
        empty-cells: show;
        border-collapse: collapse;
        border: 1px solid <?php echo $secondaryColor; ?>;
        width: 100%;
        color: <?php echo $mainColor; ?>;
        }
        hr {
        color: <?php echo $secondaryColor; ?>;
        background-color: <?php echo $secondaryColor; ?>;
        }
        h1, h2, h3, h4, h5, h6 {
        color: <?php echo $headingColor; ?>;
        }
        .ff_all_data th {
        border-bottom: 1px solid <?php echo $secondaryColor; ?>;
        border-top: 1px solid <?php echo $secondaryColor; ?>;
        padding-bottom: 10px !important;
        }
        .ff_all_data tr td {
        padding-left: 30px !important;
        padding-top: 15px !important;
        padding-bottom: 15px !important;
        }

        .ff_all_data tr td, .ff_all_data tr th {
        border: 1px solid <?php echo $secondaryColor; ?>;
        text-align: left;
        }

        table, .ff_all_data { width: 100%; } img.alignright { float: right; margin: 0 0 1em 1em; }
        img.alignleft { float: left; margin: 0 10px 10px 0; }
        img.aligncenter { display: block; margin-left: auto; margin-right: auto; text-align: center; }
        .alignright { float: right; }
        .alignleft { float: left; }
        .aligncenter { display: block; margin-left: auto; margin-right: auto; text-align: center; }

        .invoice_title {
        padding-bottom: 10px;
        display: block;
        }
        .ffp_table thead th {
        background-color: #e3e8ee;
        color: #000;
        text-align: left;
        vertical-align: bottom;
        }
        table th {
        padding: 5px 10px;
        }
        .ff_rtl table th, .ff_rtl table td {
            text-align: right !important;
        }
        <?php
        $css = ob_get_clean();
        return apply_filters('fluentform_pdf_generator_css', $css, $appearance);
    }
}
