<?php defined('ABSPATH') or die; ?>

<?php if(count($downloadableFiles)): ?>
<div style="text-align: center" class="font_downloader_wrapper">
    <h3>Fonts are required for PDF Generation</h3>
    <p>This module requires to download fonts for PDF generation. Please click on the bellow button and it will download the required font files. This is one time job</p>
    <button id="ff_download_fonts" class="button-primary">Install Fonts</button>
    <div class="ff_download_loading"></div>
    <div style="text-align: left; line-height: 130%; display: none;max-width: 500px; border: 2px solid gray; margin: 20px auto;background: white;padding: 20px; max-height: 200px;overflow: scroll;" class="ff_download_logs"></div>
</div>
<?php else: ?>

<div class="ff_pdf_system_status">
    <h3>Fluent Forms PDF Module is now active <?php if(!$statuses['status']): ?><span style="color: red;">But Few Server Extensions are missing</span><?php endif; ?></h3>
    <ul>
        <?php foreach ($statuses['extensions'] as $status): ?>
        <li>
            <?php if($status['status']): ?><span class="dashicons dashicons-yes"></span>
            <?php else: ?><span class="dashicons dashicons-no-alt"></span><?php endif; ?>
            <?php echo $status['label']; ?>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if($statuses['status']): ?>
    <p>All Looks good! You can now use Fluent Forms PDF Addon. <a href="<?php echo $globalSettingsUrl; ?>">Clicke Here</a> to check your global PDF feed settings</p>
    <?php endif; ?>
</div>
<?php endif; ?>