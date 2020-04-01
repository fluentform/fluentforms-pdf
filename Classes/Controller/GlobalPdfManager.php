<?php

namespace FluentFormPdf\Classes\Controller;

use Mpdf\Mpdf as Pdf;
use FluentForm\App\Modules\Entries\Entries;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;
use FluentForm\App\Services\FormBuilder\ShortCodeParser as ShortCodeParser;

class GlobalPdfManager
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Global settings register
        add_filter('fluentform_global_settings_components', [$this, 'globalSettings']);
        add_filter('fluentform_form_settings_menu', [$this, 'settingsMenu']);
        add_action('wp_ajax_fluentform_get_pdf_global_setting_options', [$this, 'getGlobalSettings']);

        // single form pdf settings fields ajax
        add_action(
            'wp_ajax_fluentform_get_form_pdf_template_settings',
            [$this, 'getFormTemplateSettings']
        );

        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', [$this, 'getAllTemplates']);

        // Frontend render When download button clicked
        add_action('wp_ajax_fluentform_pdf_download_ajax', [$this, 'pdfDownload']);
        add_action('wp_ajax_nopriv_fluentform_pdf_download_ajax', [$this, 'pdfDownload']);
    }

   
    public function globalSettings($setting)
    {
        $setting["pdf"] = [
             "hash" => "pdf",
             "title" => "PDF"
        ];
        
        return $setting;
    }

    
    public function settingsMenu($settingsMenus) 
    {
        $settingsMenus['pdf'] = [
            'title' => __('PDF settings', 'fluentform'),
            'slug'  => 'pdf_settings',
            'hash'  => 'pdf',
            'route' => '/pdf-settings'
        ];

        return $settingsMenus;
    }


    /*
    * @return key => [ path, name]
    * To register a new template this filter must hook for path mapping
    * filter: fluentform_pdf_template_map
    */ 
    public function getAvailableTemplates() 
    {
        $classes = apply_filters(
            'fluentform_pdf_template_map',
            [
                "blank" => [
                    'path'  => "\FluentFormPdf\Classes\Templates\Blank",
                    'name'  => 'Blank',
                    'key'   => 'blank',
                    'image' => plugin_dir_url( __FILE__ ) .('../../Images/Blank.png')
                    
                ],
                "tabular" => [
                    'path' => "\FluentFormPdf\Classes\Templates\Tabular",
                    'name'  => 'Tabular',
                    'key'   => 'tabular',
                    'image' => plugin_dir_url( __FILE__ ) .('../../Images/Tabular.png')
                ]
            ]
        );

        return $classes;
    }



    /*
    * @return [key => value]
    * processed for dropdown fields
    */ 
    public function formattedTemplates()
    {
        $allTemplates = [];

        foreach ($this->getAvailableTemplates() as $key => $value) {    
            $allTemplates[$key] = $value['name'];      
        };

        return [
            'formatted' => $allTemplates,
            'all'       => $this->getAvailableTemplates()
        ];
    }



    /*
    * @return [ key name]
    * global pdf setting fields
    */ 
    public function getGlobalFields() 
    {   
       return [ 
           'fields' => [
               [
                    'key'       => 'paper_size',
                    'label'     => 'Paper size',
                    'component' => 'dropdown',
                    'tips'      => 'All available templates are shown here, select a default template',
                    'options'   => PdfOptions::getPaperSizes()
               ],
               [
                    'key'       => 'template',
                    'label'     => 'Template',
                    'component' => 'dropdown',
                    'options'   => (Arr::get($this->formattedTemplates(), 'formatted'))
               ],
                [
                    'key'       => 'orientation',
                    'label'     => 'Orientation',
                    'component' => 'dropdown',
                    'options'   => PdfOptions::getOrientations()
               ],
               [
                    'key'       => 'font',
                    'label'     => 'Font family',
                    'component' => 'dropdown',
                    'options'   => PdfOptions::getFonts()
               ],
               [
                    'key'       => 'font_size',
                    'label'     => 'Font size',
                    'component' => 'number'
               ],
               [
                    'key'       => 'font_color',
                    'label'     => 'Font color',
                    'component' => 'color_picker'
               ],
                [
                    'key' => 'accent_color',
                    'label' => 'Accent color',
                    'tips'  => 'The accent color is used for the page, section titles and the border.',
                    'component' => 'color_picker'
               ],
               [
                    'key'   => 'entry_view',
                    'label' => 'Entry view',
                    'component' => 'radio_choice',
                    'options'   => [
                        'I' => 'View',
                        'D' => 'Download'
                    ]
                ],
                [
                    'key' => 'reverse_text',
                    'label' => 'Reverse text',
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


    /*
    * All the registered template will return
    * @return key => [ path, name]
    */ 
    public function getAllTemplates()
    {
        wp_send_json_success(
            $this->formattedTemplates()
        );
    }


    /*
    * Global settings options will get from this 
    * method @getPdfSettingOptions
    */
    public function getGlobalSettings() 
    {
        wp_send_json_success(
           $this->getGlobalFields()
        );
    }


    /*
    * single form setting Fields 
    * according to specific template
    */
    public function getFormTemplateSettings()
    {
        $commonSettings = PdfOptions::commonSettings();

        $templateKey = $_REQUEST['templateKey'];

        $allTemplates =  $this->getAvailableTemplates();
       
        foreach ($allTemplates as $key => $value) {
            new $value['path']($this->app);
        };

        $settingsFields = apply_filters(
            'fluentform_get_pdf_settings_fields_' . $templateKey, $commonSettings
        );
        
        wp_send_json_success($settingsFields);
    }

    
    public function getPdfConfig($settings, $default)
    {  
        return [
            'mode' => 'utf-8', 
            'format' => Arr::get($settings, 'paper_size', Arr::get($default, 'paper_size')),
            'orientation' => Arr::get($settings, 'orientation', Arr::get($default, 'orientation')),
            // 'debug' => true //uncomment this debug on development
        ];
    }

    /*
    * when download button will press
    * Pdf rendering will control from here
    */
    public function pdfDownload() 
    {

        $data['entry_id'] = Arr::get($_REQUEST, 'entry_id');
        $data['form_id'] = Arr::get($_REQUEST, 'form_id');
        $data['labels'] = Arr::get($_REQUEST, 'labels');

        $settings = Arr::get($_REQUEST, 'settings');

        if (!isset($data, $settings)) {
            return;
        }

        $default = get_option('_fluentform_global_pdf_settings');

        $defaultPdfOptions = PdfOptions::getDefaultSettings();

        if (!$default) {
            $default = $defaultPdfOptions;
        } else {
            foreach ($default as $key => $value) {
                $default[$key] = $value ?: $defaultPdfOptions[$key];
            }
        }

        $this->renderPdf(
            $settings['value'],
            $data,
            $default
        );
    }


    protected function renderPdf($settings, $data, $default)
    {
        $template = $this->initAndGetTemplateName($settings, $default);

        $templateData = $this->getTemplateData($data, $settings, $default);

        $settings = ShortCodeParser::parse(
            $settings, 
            Arr::get($data, 'entry_id'), 
            Arr::get($templateData, 'data'), 
        );

        $html = apply_filters(
            "fluentform_get_pdf_html_{$template}", $templateData, $data, $settings, $default
        );

        $preferences = PdfOptions::getPreferences($settings, $default);

        $style = apply_filters(
            "fluentform_get_pdf_style_{$template}", $preferences, $settings, $default
        );

        $entryView = Arr::get($settings, 'entry_view', Arr::get($default, 'entry_view'));

        $filename = Arr::get($settings, 'filename');

        $filename = $filename ?: Arr::get($default, 'filename');

        $mpdf = new Pdf($this->getPdfConfig($settings, $default));

        $mpdf->setAutoBottomMargin = $mpdf->setAutoTopMargin = 'stretch';

        $mpdf->SetHTMLHeader(
            wp_unslash((Arr::get($settings, 'header')))
        );
        
        $mpdf->SetHTMLFooter(
            wp_unslash((Arr::get($settings, 'footer')))
        );

        // For the right to left text like arabic or hebrew
        if ((Arr::get($settings, 'reverse_text', Arr::get($default, 'reverse_text'))) == 'yes') {
            $mpdf->SetDirectionality('rtl');
        }
     
        $mpdf->WriteHTML($style, 1);
        $mpdf->WriteHTML($html);
        $mpdf->Output(PdfOptions::slugify($filename), $entryView);
    }

    protected function initAndGetTemplateName($settings, $default)
    {
        $template = Arr::get($settings, 'template', $default['template']);

        $templateClass = $this->getAvailableTemplates()[$template]['path'];
        
        new $templateClass($this->app);

        return $template;
    }

    protected function getTemplateData($data, $settings, $default)
    {
        $entry = (new Entries)->_getEntry();

        $entry = reset($entry);

        $entry = $entry->user_inputs;

        $labels = Arr::get($data, 'labels');

        if (Arr::get($settings, 'empty_fields') == 'no') {
            $entry = array_filter($entry);
        };

        return [
            'data' => $entry,
            'labels' => $labels
        ];
    }
}
