<?php

namespace FluentFormPdf\Classes\Templates;

use FluentForm\App\Services\Emogrifier\Emogrifier;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

abstract class TemplateManager
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract public function getSettingsFields();

    abstract public function generatePdf($submissionId, $settings);

    public function viewPDF($submissionId, $settings)
    {
        $this->generatePdf($submissionId, $settings);
    }

    public function getGenerator($mpdfConfig)
    {
        return new \Mpdf\Mpdf($mpdfConfig);
    }


    public function pdfBuilder($fileName, $feed, $body = '', $footer = '', $outPut = 'I')
    {
        $appearance = Arr::get($feed, 'appearance');
        $mpdfConfig = array(
            'mode' => 'utf-8',
            'format' => Arr::get($appearance, 'paper_size'),
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => Arr::get($appearance, 'orientation'),
        );

        if(!defined('FLUENTFORMPRO')) {
            $footer .= '<p style="text-align: center;">Powered By <a target="_blank" href="https://wpmanageninja.com/downloads/fluentform-pro-add-on/">Fluent Forms</a></p>';
        }

        $pdfGenerator = $this->getGenerator($mpdfConfig);
        $fileName = $feed['name'];
        $fileName = sanitize_title($fileName, 'pdf-file', 'display');

        try {
            // apply CSS styles inline for picky email clients
            $emogrifier = new Emogrifier($body, $this->getPdfCss($appearance));
            $body = $emogrifier->emogrify();
        } catch (\Exception $e) {

        }

        $pdfGenerator->SetWatermarkText('PAID', 0.04);
        $pdfGenerator->showWatermarkText = true;

        $pdfGenerator->SetHTMLFooter($footer);
        $pdfGenerator->WriteHTML('<div class="ff_pdf_wrapper">'.$body.'</div>', \Mpdf\HTMLParserMode::HTML_BODY);
        $pdfGenerator->Output($fileName.'.pdf', $outPut);
    }

    public function getPdfCss($appearance)
    {
        $mainColor = Arr::get($appearance, 'font_color');
        if(!$mainColor) {
            $mainColor = '#4F4F4F';
        }
        $secondaryColor = Arr::get($appearance, 'accent_color');
        if(!$secondaryColor) {
            $secondaryColor = '#EAEAEA';
        }
        $headingColor = Arr::get($appearance, 'heading_color');

        $fontSize = Arr::get($appearance, 'font_size', 14);

        ob_start();
        ?>
        .ff_pdf_wrapper, p, li, td, th {
            color: <?php echo $mainColor;  ?>;
            font-size: <?php echo $fontSize; ?>px;
        }

        .ff_all_data {
            empty-cells: show;
            border-collapse: collapse;
            border: 1px solid <?php echo $secondaryColor; ?>;
            width: 100%;
            color: <?php echo $mainColor;  ?>;
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

table, .ff_all_data { width: 100%; } img.alignright { float: right; margin: 0 0 1em 1em; }
img.alignleft { float: left; margin: 0 1em 1em 0; }
img.aligncenter { display: block; margin-left: auto; margin-right: auto; text-align: center; }
.alignright { float: right; }
.alignleft { float: left; }
.aligncenter { display: block; margin-left: auto; margin-right: auto; text-align: center; }
<?php
        return ob_get_clean();
    }

}
