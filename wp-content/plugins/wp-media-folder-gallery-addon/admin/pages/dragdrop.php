<div class="fileupload-container" style="width:100%;max-width:100%">
    <div>
        <div class="fileuploadform">
            <input type="hidden" name="acceptfiletypes" value=".(.)$">
            <div class="fileupload-drag-drop">
                <span class="msg_no_files">
                    <?php esc_html_e('Use Drag & Drop or your WordPress images', 'wp-media-folder-gallery-addon') ?>
                </span>
            </div>

            <div class="fileupload-list">
                <div role="presentation">
                    <div class="files"></div>
                </div>
                <input type="hidden" name="fileupload-filelist" id="fileupload-filelist"
                       class="fileupload-filelist" value="">
            </div>
        </div>
    </div>
    <div class="template-row">
        <div class="upload-thumbnail">
            <img class="" src="">
        </div>

        <div class="upload-file-info">
            <div class="upload-img-defails">
                <div class="file-name"></div>
                <div class="file-size"></div>
            </div>

            <div class="process-right-wrap">
                <div class="upload-progress">
                    <div
                            class="progress progress-striped active ui-progressbar
                             ui-widget ui-widget-content ui-corner-all"
                            role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                        <div class="ui-progressbar-value ui-widget-header ui-corner-left"></div>
                    </div>
                </div>
            </div>
            <div class="upload-error"></div>
        </div>
    </div>
</div>
