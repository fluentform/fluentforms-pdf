jQuery(document).ready(function ($) {
    const pdfDownloader = {
        initDownloadFonts() {
            $('#ff_download_fonts').text('Downloading...').attr('disabled', true);
            $('.ff_download_loading').html('Please do not close this window when downloading the fonts');
            this.ajaxLoadFonts();
        },

        ajaxLoadFonts() {
            jQuery.post(window.fluentform_pdf_admin.ajaxUrl, {
                action: 'fluentform_pdf_admin_ajax_actions',
                route: 'downloadFonts'
            })
                .then(response => {
                    if(response.data.downloaded_files && response.data.downloaded_files.length) {
                        $('.ff_download_logs').prepend(response.data.downloaded_files.join('<br />')).show();
                        this.ajaxLoadFonts();
                    } else {
                        // All Done
                        window.location.reload();
                    }
                })
                .fail(error => {
                    window.location.reload();
                });
        },

        init() {
            $('#ff_download_fonts').on('click', (e) => {
                e.preventDefault();
                this.initDownloadFonts();
            });
        }
    };

    pdfDownloader.init();
});