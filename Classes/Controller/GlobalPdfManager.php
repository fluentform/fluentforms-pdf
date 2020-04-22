<?php

namespace FluentFormPdf\Classes\Controller;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Emogrifier\Emogrifier;
use FluentForm\Framework\Helpers\ArrayHelper;
use Mpdf\Mpdf as Pdf;
use FluentForm\App\Modules\Entries\Entries;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentFormPdf\Classes\Controller\AvailableOptions as PdfOptions;
use FluentForm\App\Services\FormBuilder\ShortCodeParser as ShortCodeParser;

class GlobalPdfManager
{
    protected $app = null;

    protected $optionKey = '_fluentform_pdf_settings';

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Global settings register
        add_filter('fluentform_global_settings_components', [$this, 'globalSettingMenu']);
        add_filter('fluentform_form_settings_menu', [$this, 'formSettingsMenu']);
        add_action('wp_ajax_fluentform_get_pdf_global_setting_options', [$this, 'getGlobalSettings']);

        // single form pdf settings fields ajax
        add_action(
            'wp_ajax_fluentform_get_form_pdf_template_settings',
            [$this, 'getFormTemplateSettings']
        );

        add_action('wp_ajax_fluentform_pdf_admin_ajax_actions', [$this, 'ajaxRoutes']);

        // Frontend render When download button clicked
        add_action('wp_ajax_fluentform_pdf_download_ajax', [$this, 'pdfDownload']);
        add_action('wp_ajax_nopriv_fluentform_pdf_download_ajax', [$this, 'pdfDownload']);

        add_filter('fluentform_single_entry_widgets', array($this, 'pushPdfButtons'), 10, 2);

    }

    public function globalSettingMenu($setting)
    {
        $setting["pdf_settings"] = [
            "hash" => "pdf_settings",
            "title" => __("PDF Settings", 'fluentform-pdf')
        ];

        return $setting;
    }

    public function formSettingsMenu($settingsMenus)
    {
        $settingsMenus['pdf'] = [
            'title' => __('PDF Feeds', 'fluentform-pdf'),
            'slug' => 'pdf-feeds',
            'hash' => 'pdf',
            'route' => '/pdf-feeds'
        ];

        return $settingsMenus;
    }

    public function ajaxRoutes()
    {
        $maps = [
            'get_global_settings' => 'getGlobalSettingsAjax',
            'save_global_settings' => 'saveGlobalSettings',
            'get_feeds' => 'getFeedsAjax',
            'create_feed' => 'createFeedAjax',
            'get_feed' => 'getFeedAjax',
            'save_feed' => 'saveFeedAjax',
            'delete_feed' => 'deleteFeedAjax',
            'download_pdf' => 'downloadPdf'
        ];

        $route = sanitize_text_field($_REQUEST['route']);

        Acl::verify('fluentform_forms_manager');

        if (isset($maps[$route])) {
            $this->{$maps[$route]}();
        }
    }

    public function getGlobalSettingsAjax()
    {
        wp_send_json_success([
            'settings' => $this->globalSettings(),
            'fields' => $this->getGlobalFields()
        ]);
    }

    private function globalSettings()
    {
        $defaults = [
            'paper_size' => 'A4',
            'orientation' => 'P',
            'font' => 'default',
            'font_size' => '14',
            'font_color' => '#323232',
            'accent_color' => '#989797',
            'entry_view' => 'download',
            'reverse_text' => 'no'
        ];

        $option = get_option($this->optionKey);
        if (!$option || !is_array($option)) {
            return $defaults;
        }

        return wp_parse_args($option, $defaults);

    }

    public function saveGlobalSettings()
    {
        $settings = wp_unslash($_REQUEST['settings']);
        update_option($this->optionKey, $settings);
        wp_send_json_success([
            'message' => __('Settings successfully updated', 'fluentform-pdf')
        ], 200);
    }

    public function getFeedsAjax()
    {
        $formId = intval($_REQUEST['form_id']);

        $form = wpFluent()->table('fluentform_forms')
            ->where('id', $formId)
            ->first();

        $feeds = $this->getFeeds($form->id);

        wp_send_json_success([
            'pdf_feeds' => $feeds,
            'templates' => $this->getAvailableTemplates($form)
        ], 200);

    }

    public function createFeedAjax()
    {
        $templateName = sanitize_text_field($_REQUEST['template']);
        $formId = intval($_REQUEST['form_id']);

        $form = wpFluent()->table('fluentform_forms')
            ->where('id', $formId)
            ->first();

        $templates = $this->getAvailableTemplates($form);

        if(!isset($templates[$templateName]) || !$formId) {
            wp_send_json_error([
                'message' => __('Sorry! No template found!', 'fluentform-pdf')
            ], 423);
        }

        $template = $templates[$templateName];

        $class = $template['class'];
        if(!class_exists($class)) {
            wp_send_json_error([
                'message' => __('Sorry! No template Class found!', 'fluentform-pdf')
            ], 423);
        }
        $instance = new $class($this->app);

        $defaultSettings = $instance->getDefaultSettings($form);

        $data = [
            'name' => $template['name'],
            'template_key' => $templateName,
            'settings' => $defaultSettings,
            'appearance' => $this->globalSettings()
        ];

        $insertId = wpFluent()->table('fluentform_form_meta')
            ->insert([
                'meta_key' => '_pdf_feeds',
                'form_id' => $formId,
                'value' => wp_json_encode($data)
            ]);

        wp_send_json_success([
            'feed_id' => $insertId,
            'message' => __('Feed has been created, edit the feed now')
        ], 200);
    }

    private function getFeeds($formId)
    {
        $feeds = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', '_pdf_feeds')
            ->get();
        $formattedFeeds = [];
        foreach ($feeds as $feed) {
            $settings = json_decode($feed->value, true);
            $settings['id'] = $feed->id;
            $formattedFeeds[] = $settings;
        }

        return $formattedFeeds;
    }

    public function getFeedAjax()
    {
        $formId = intval($_REQUEST['form_id']);

        $form = wpFluent()->table('fluentform_forms')
            ->where('id', $formId)
            ->first();

        $feedId = intval($_REQUEST['feed_id']);

        $feed = wpFluent()->table('fluentform_form_meta')
            ->where('id', $feedId)
            ->where('meta_key', '_pdf_feeds')
            ->first();

        $settings = json_decode($feed->value, true);
        $templateName = ArrayHelper::get($settings, 'template_key');

        $templates = $this->getAvailableTemplates($form);

        if(!isset($templates[$templateName]) || !$formId) {
            wp_send_json_error([
                'message' => __('Sorry! No template found!', 'fluentform-pdf')
            ], 423);
        }

        $template = $templates[$templateName];

        $class = $template['class'];
        if(!class_exists($class)) {
            wp_send_json_error([
                'message' => __('Sorry! No template Class found!', 'fluentform-pdf')
            ], 423);
        }
        $instance = new $class($this->app);

        wp_send_json_success([
            'feed' => $settings,
            'settings_fields' => $instance->getSettingsFields(),
            'appearance_fields' => $this->getGlobalFields()
        ], 200);


    }

    public function saveFeedAjax()
    {
        $formId = intval($_REQUEST['form_id']);

        $form = wpFluent()->table('fluentform_forms')
            ->where('id', $formId)
            ->first();

        $feedId = intval($_REQUEST['feed_id']);
        $feed = wp_unslash($_REQUEST['feed']);

        if(empty($feed['name'])) {
            wp_send_json_error([
                'message' => __('Feed name is required', 'fluentform-pdf')
            ], 423);
        }

        wpFluent()->table('fluentform_form_meta')
            ->where('id', $feedId)
            ->update([
                'value' => wp_json_encode($feed)
            ]);

        wp_send_json_success([
            'message' => __('Settings successfully updated', 'fluentform-pdf')
        ], 200);

    }

    public function deleteFeedAjax()
    {
        $feedId = intval($_REQUEST['feed_id']);
        wpFluent()->table('fluentform_form_meta')
            ->where('id', $feedId)
            ->where('meta_key', '_pdf_feeds')
            ->delete();

        wp_send_json_success([
            'message' => __('Feed successfully deleted', 'fluentform-pdf')
        ], 200);

    }
    /*
    * @return key => [ path, name]
    * To register a new template this filter must hook for path mapping
    * filter: fluentform_pdf_template_map
    */
    public function getAvailableTemplates($form)
    {
        return apply_filters(
            'fluentform_pdf_templates',
            array(
                "basic_template" => [
                    'name' => 'Basic Template',
                    'class' => '\FluentFormPdf\Classes\Templates\BasicTemplate',
                    'key' => 'basic_template',
                    'preview' => FLUENTFORM_PDF_URL . 'assets/images/basic_template.png'
                ],
                "another_template" => [
                    'name' => 'Another Template',
                    'class' => '\FluentFormPdf\Classes\Templates\BasicTemplate',
                    'key' => 'basic_template',
                    'preview' => FLUENTFORM_PDF_URL . 'assets/images/tabular.png'
                ]
            ), $form
        );
    }



    /*
    * @return [ key name]
    * global pdf setting fields
    */
    public function getGlobalFields()
    {
        return [
            [
                'key' => 'paper_size',
                'label' => 'Paper size',
                'component' => 'dropdown',
                'tips' => 'All available templates are shown here, select a default template',
                'options' => PdfOptions::getPaperSizes()
            ],
            [
                'key' => 'orientation',
                'label' => 'Orientation',
                'component' => 'dropdown',
                'options' => PdfOptions::getOrientations()
            ],
            [
                'key' => 'font',
                'label' => 'Font family',
                'component' => 'dropdown',
                'options' => PdfOptions::getFonts()
            ],
            [
                'key' => 'font_size',
                'label' => 'Font size',
                'component' => 'number'
            ],
            [
                'key' => 'font_color',
                'label' => 'Font color',
                'component' => 'color_picker'
            ],
            [
                'key' => 'accent_color',
                'label' => 'Accent color',
                'tips' => 'The accent color is used for the page, section titles and the border.',
                'component' => 'color_picker'
            ],
            [
                'key' => 'entry_view',
                'label' => 'Entry view',
                'component' => 'radio_choice',
                'options' => [
                    'view' => 'View',
                    'download' => 'Download'
                ]
            ],
            [
                'key' => 'reverse_text',
                'label' => 'Reverse text',
                'tips' => 'Script like Arabic and Hebrew are written right to left.',
                'component' => 'radio_choice',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ]
            ]
        ];
    }


    public function pushPdfButtons($widgets, $data)
    {
        $formId = $data['submission']->form_id;
        $feeds = $this->getFeeds($formId);
        if(!$feeds) {
            return $widgets;
        }
        $widgetData = [
            'title' => __('PDF Downloads', 'fluentform-pdf'),
            'type' => 'html_content'
        ];

        $contents = '<ul class="ff_list_items">';
        foreach ($feeds as $feed) {
            $contents .= '<li><a href="'.admin_url('admin-ajax.php?action=fluentform_pdf_admin_ajax_actions&route=download_pdf&id='.$feed['id']).'" target="_blank"><span style="font-size: 12px;" class="dashicons dashicons-arrow-down-alt"></span>'.$feed['name'].'</a></li>';
        }
        $contents .= '</ul>';
        $widgetData['content'] = $contents;

        $widgets['pdf_feeds'] = $widgetData;

        return $widgets;

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
    public function downloadPdf()
    {
        $feedId = intval($_REQUEST['id']);
        $feed = wpFluent()->table('fluentform_form_meta')
            ->where('id', $feedId)
            ->where('meta_key', '_pdf_feeds')
            ->first();

        $settings = json_decode($feed->value, true);

        vddd($settings);
    }
}
