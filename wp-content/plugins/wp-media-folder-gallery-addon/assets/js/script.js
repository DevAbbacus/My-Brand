/**
 * Main WP Media Gallery addon script
 */
var wpmfGalleryModule;
(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = wpmf.vars.ajaxurl;
    }

    wpmfGalleryModule = {
        upload_from_pc: false,
        wpmf_current_gallery: 0, // current gallery selected
        current_page_nav: 1, // current page for images gallery selection
        gallery_details: {},
        events : [], // event handling
        init: function () {
            // tabs
            $('.gallery-ju-top-tabs li').click(function(){
                var tab_id = $(this).attr('data-tab');
                $('.gallery-ju-top-tabs li').removeClass('current');
                $('.gallery-tab-content').removeClass('current');
                $(this).addClass('current');
                $("#"+tab_id).addClass('current');
            });

            // show popup inline
            if ($().magnificPopup) {
                $('.new-gallery-popup').magnificPopup({
                    type: 'inline',
                    closeBtnInside: true,
                    midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
                });
            }

            wpmfGalleryModule.uploadImages();
            /* Show tooltip for some icon */
            if ($().qtip) {
                $('.wpmf_open_qtip').qtip({
                    content: {
                        attr: 'data-for'
                    },
                    position: {
                        my: 'bottom center',
                        at: 'top center'
                    },
                    style: {
                        tip: {
                            corner: true
                        },
                        classes: 'wpmf-qtip qtip-rounded'
                    },
                    show: 'hover',
                    hide: {
                        fixed: true,
                        delay: 10
                    }
                });
            }

            $('.ju-left-panel-toggle').unbind('click').click(function () {
                var leftPanel = $('.ju-left-panel');
                var wpLeftPanel = $('#adminmenuwrap');
                var rtl = $('body').hasClass('rtl');

                if (leftPanel.is(':visible')) {
                    if (wpLeftPanel.is(':visible')) {
                        if (!rtl) {
                            $(this).css('left', 35);
                        } else {
                            $(this).css('right', 35);
                        }
                    } else {
                        if (!rtl) {
                            $(this).css('left', 0);
                        } else {
                            $(this).css('right', 0);
                        }
                    }
                } else {
                    if (wpLeftPanel.is(':visible')) {
                        if (!rtl) {
                            $(this).css('left', 335);
                        } else {
                            $(this).css('right', 335);
                        }
                    } else {
                        if (!rtl) {
                            $(this).css('left', 290);
                        } else {
                            $(this).css('right', 290);
                        }
                    }
                }

                leftPanel.toggle()
            });

            wpmfGalleryModule.bindEvent();
            wpmfGalleryModule.eventImages();

            var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
            var eventer = window[eventMethod];
            var messageEvent = eventMethod === "attachEvent" ? "onmessage" : "message";

            // Listen to message from child window
            eventer(messageEvent, function (e) {
                var res = e.data;
                if (typeof res !== "undefined" && typeof res.type !== "undefined" && res.type === "wpmf_google_photo_gallery_import") {
                    tb_remove();
                    wpmfGalleryModule.changeGallery(wpmfGalleryModule.wpmf_current_gallery);
                }
            }, false);
        },

        updateThemeSelection: function(theme, type = 'edit') {
            if (type === 'edit') {
                $('.edit-gallery-theme').val(theme);
                $('.form_edit_gallery .wpmf-theme-item').removeClass('selected');
                $('.form_edit_gallery .wpmf-theme-item[data-theme="'+ theme +'"]').addClass('selected');
                $('#main-gallery-settings').attr('data-theme', theme);
            } else {
                $('.new-gallery-theme').val(theme);
                $('.form_add_gallery .wpmf-theme-item').removeClass('selected');
                $('.form_add_gallery .wpmf-theme-item[data-theme="'+ theme +'"]').addClass('selected');
            }
        },

        fileUpload: function() {
            $('.WpmfGalleryList').each(function () {
                $(this).fileupload({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    autoUpload: true,
                    maxFileSize: 104857600,
                    acceptFileTypes: new RegExp($(this).find('input[name="acceptfiletypes"]').val(), "i"),
                    dropZone: $(this).closest('.WpmfGalleryList'),
                    messages: {
                        maxNumberOfFiles: wpmf_glraddon.vars.maxNumberOfFiles,
                        acceptFileTypes: wpmf_glraddon.vars.acceptFileTypes,
                        maxFileSize: wpmf_glraddon.vars.maxFileSize,
                        minFileSize: wpmf_glraddon.vars.minFileSize
                    },
                    limitConcurrentUploads: 3,
                    disableImageLoad: true,
                    disableImageResize: true,
                    disableImagePreview: true,
                    disableAudioPreview: true,
                    disableVideoPreview: true,
                    uploadTemplateId: null,
                    downloadTemplateId: null,
                    add: function (e, data) {
                        if (wpmfGalleryModule.upload_from_pc) {
                            return;
                        }

                        $('.wpmf-drop-overlay').removeClass('in');
                        if (!$('.fileupload-container').length) {
                            return;
                        }
                        $.each(data.files, function (index, file) {
                            file.hash = file.name.hashCode() + '_' + Math.floor(Math.random() * 1000000);
                            file = wpmfGalleryModule.validateFile(file);
                            var row = wpmfGalleryModule.renderFileUploadRow(file);
                            if (file.error !== false) {
                                data.files.splice(index, 1);
                            }
                        });

                        if (data.files.length > 0) {
                            data.process().done(function () {
                                data.submit();
                            });
                        }

                    },
                    done: function (e, data) {
                        if (data.result !== false) {
                            if (!$('.template-upload').length) {
                                wpmfGalleryModule.changeGallery(wpmfGalleryModule.wpmf_current_gallery);
                            }
                        }
                    }
                }).on('fileuploadsubmit', function (e, data) {
                    $.each(data.files, function (index, file) {
                        wpmfGalleryModule.uploadStart(file);
                    });

                    data.formData = {
                        action: 'wpmfgallery',
                        task: 'gallery_uploadfile',
                        up_gallery_id: wpmfGalleryModule.wpmf_current_gallery,
                        wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                    };

                }).on('fileuploadprogress', function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $.each(data.files, function (index, file) {
                        wpmfGalleryModule.uploadProgress(file, {percentage: 100});
                    });

                }).on('fileuploadstopped', function () {
                }).on('fileuploaddone', function (e, data) {
                    wpmfGalleryModule.uploadFinished(data.files[0]);
                }).on('fileuploadalways', function (e, data) {

                }).on('fileuploaddrop', function (e, data) {});
            });
        },

        /**
         * Start upload file
         * @param file
         */
        uploadStart: function (file) {
            var row = $(".WpmfGalleryList .fileupload-list [data-id='" + file.hash + "']");
            row.find('.upload-progress').slideDown();
        },

        /**
         * Helper functions
         * @param bytes
         * @param si
         * @returns {string}
         */
        humanFileSize: function (bytes, si) {
            var thresh = si ? 1000 : 1024;
            if (Math.abs(bytes) < thresh) {
                return bytes + ' B';
            }
            var units = si
                ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
                : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
            var u = -1;
            do {
                bytes /= thresh;
                ++u;
            } while (Math.abs(bytes) >= thresh && u < units.length - 1);
            return bytes.toFixed(1) + ' ' + units[u];
        },

        /**
         * Validate File for Upload
         * @param file
         * @returns {*}
         */
        validateFile: function (file) {
            var acceptFileType = new RegExp($(".WpmfGalleryList").find('input[name="acceptfiletypes"]').val(), "i");
            file.error = false;
            if (file.name.length && !acceptFileType.test(file.name)) {
                file.error = wpmf_glraddon.vars.acceptFileTypes;
            }

            if (wpmf_glraddon.vars.maxsize !== '' && file.size > 0 && file.size > wpmf_glraddon.vars.maxsize) {
                file.error = wpmf_glraddon.vars.maxFileSize;
            }
            return file;
        },

        /**
         * Get thumbnail for local and cloud files
         * @param file
         * @returns {*}
         */
        getThumbnail: function (file) {
            if (typeof file.thumbnail === 'undefined' || file.thumbnail === null || file.thumbnail === '') {
                var icon = 'file_default';
                if (file.type.indexOf("image") >= 0) {
                    return URL.createObjectURL(file);
                }

                return wpmf_glraddon.vars.plugin_url_image + icon + '.png';
            } else {
                return file.thumbnail;
            }
        },

        /**
         * Render file in upload list
         * @param file
         */
        renderFileUploadRow: function (file) {
            var row = ($(".WpmfGalleryList").find('.template-row').clone().removeClass('template-row'));

            row.attr('data-file', file.name).attr('data-id', file.hash);
            row.find('.file-name').text(file.name);
            if (file.size !== 'undefined' && file.size > 0) {
                row.find('.file-size').text(wpmfGalleryModule.humanFileSize(file.size, true));
            }
            row.find('.upload-thumbnail img').attr('src', wpmfGalleryModule.getThumbnail(file));

            row.addClass('template-upload');

            $(".WpmfGalleryList .fileupload-list .files").append(row);
            return row;
        },

        /**
         * Render the progress of uploading cloud files
         * @param file
         * @param status
         */
        uploadProgress: function (file, status) {
            var row = $(".WpmfGalleryList .fileupload-list [data-id='" + file.hash + "']");
            row.find('.progress')
                .attr('aria-valuenow', status.percentage)
                .children().first().fadeIn()
                .animate({
                    width: status.percentage + '%'
                }, 'slow', function () {});
        },

        /**
         * when upload file finish
         * @param file
         */
        uploadFinished: function (file) {
            var row = $(".WpmfGalleryList .fileupload-list [data-id='" + file.hash + "']");

            row.addClass('template-download').removeClass('template-upload');
            row.find('.file-name').text(file.name);
            row.find('.upload-thumbnail img').attr('src', wpmfGalleryModule.getThumbnail(file));
            row.find('.upload-progress').slideUp();
            row.animate({"opacity": "0"}, "slow", function () {
                if ($(this).parent().find('.template-upload').length <= 1) {
                    $(this).closest('.fileuploadform').find('div.fileupload-drag-drop').fadeIn();

                    /* Update Filelist */
                    var formData = {
                        listtoken: file.listtoken
                    };
                }

                $(this).remove();
            });
        },

        /**
         * Change gallery function
         * @param id id of gallery
         */
        changeGallery: function (id) {
            if (typeof id === 'undefined') {
                return;
            }

            if (parseInt(id) === 0) {
                return;
            }

            if ($('.btn_import_from_google_photos').length) {
                var url = wpmf_glraddon.vars.admin_url + 'upload.php?page=wpmf-google-photos&noheader=1';
                var body_width = $('body').width();
                var body_height = $('body').height();
                var google_photo_page_width = body_width * 80 / 100;
                var google_photo_page_height = body_height * 80 / 100;
                url += '&width=' + google_photo_page_width;
                url += '&height=' + google_photo_page_height;
                url += '&gallery_id=' + id;
                $('.btn_import_from_google_photos').attr('href', url);
            }

            var data_params = $('#gallerylist').data('edited');
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmfgallery",
                    task: "change_gallery",
                    id: id,
                    wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                },
                beforeSend: function () {
                    $('.wpmf-gallery-selection-wrap').addClass('loading');
                },
                success: function (res) {
                    $('.wpmf-gallery-selection-wrap').removeClass('loading');
                    if ($('.btn_modal_import_image_fromwp').length > 0) {
                        var url_modal = $('.btn_modal_import_image_fromwp').attr('href');
                        var new_url = url_modal + '&gallery_id=' + id;
                        $('.btn_modal_import_image_fromwp').attr('href', new_url);
                    }

                    $('.up_gallery_id').val(id);
                    $('.form_edit_gallery .wpmf_gallery_selection').html(res.images_html);

                    if (res.status) {
                        wpmfGalleryModule.sortAbleImages('.wpmf_gallery_selection');
                        wpmfGalleryModule.eventImages();
                    } else {
                        $(document).bind('dragover', function (e) {
                            if (!$('.fileupload-container').length) {
                                return;
                            }

                            $('.wpmf-drop-overlay').addClass('in');
                        });

                        $(document).bind('dragleave', function (e) {
                            $('.wpmf-drop-overlay').removeClass('in');
                        });

                        wpmfGalleryModule.fileUpload();
                    }

                    /* Load image template */
                    wpmfGalleryModule.gallery_details[id] = res.glr;
                    $('.form_edit_gallery .gallery_name').val(wpmfGalleryModule.gallery_details[id].name);
                    $('.edit-gallery-parent option[value="' + wpmfGalleryModule.gallery_details[id].parent + '"]').prop('selected', true).change();

                    if (parseInt(data_params.gallery_id) !== 0 && parseInt(data_params.gallery_id) === parseInt(id)) {
                        wpmfGalleryModule.updateThemeSelection(data_params.display, 'edit');
                        $('.edit-gallery-columns').val(data_params.columns);
                        $('.edit-gallery-size').val(data_params.size);
                        $('.edit-gallery-targetsize').val(data_params.targetsize);
                        $('.edit-gallery-link').val(data_params.link);
                        $('.edit-gallery-orderby').val(data_params.wpmf_orderby);
                        $('.edit-gallery-order').val(data_params.wpmf_order);
                        $('.edit-gallery-animation').val(data_params.animation);
                        $('.edit-gallery-duration').val(data_params.duration);
                        $('.edit-gallery-auto_animation').val(data_params.auto_animation);

                        if (parseInt(data_params.display_tree) === 1) {
                            $('.gallery_display_tree').prop('checked', true);
                        } else {
                            $('.gallery_display_tree').prop('checked', false);
                        }

                        if (parseInt(data_params.display_tag) === 1) {
                            $('.gallery_display_tag').prop('checked', true);
                        } else {
                            $('.gallery_display_tag').prop('checked', false);
                        }

                        if (parseInt(data_params.show_buttons) === 1) {
                            $('.gallery_flow_show-buttons').prop('checked', true);
                        } else {
                            $('.gallery_flow_show-buttons').prop('checked', false);
                        }
                    } else {
                        wpmfGalleryModule.updateThemeSelection(wpmfGalleryModule.gallery_details[id].theme, 'edit');
                        $('.edit-gallery-columns').val(res.glr.params.columns);
                        $('.edit-gallery-size').val(res.glr.params.size);
                        $('.edit-gallery-targetsize').val(res.glr.params.targetsize);
                        $('.edit-gallery-link').val(res.glr.params.link);
                        $('.edit-gallery-orderby').val(res.glr.params.wpmf_orderby);
                        $('.edit-gallery-order').val(res.glr.params.wpmf_order);
                        $('.edit-gallery-animation').val(res.glr.params.animation);
                        $('.edit-gallery-duration').val(res.glr.params.duration);
                        $('.edit-gallery-auto_animation').val(res.glr.params.auto_animation);

                        if (parseInt(res.glr.params.display_tree) === 1) {
                            $('.gallery_display_tree').prop('checked', true);
                        } else {
                            $('.gallery_display_tree').prop('checked', false);
                        }

                        if (parseInt(res.glr.params.display_tag) === 1) {
                            $('.gallery_display_tag').prop('checked', true);
                        } else {
                            $('.gallery_display_tag').prop('checked', false);
                        }

                        if (parseInt(res.glr.params.show_buttons) === 1) {
                            $('.gallery_flow_show-buttons').prop('checked', true);
                        } else {
                            $('.gallery_flow_show-buttons').prop('checked', false);
                        }
                    }

                    wpmfGalleryModule.updateNav(res);
                    wpmfGalleryModule.bindEvent();
                }
            });
        },

        /**
         * sortable image in gallery
         */
        sortAbleImages: function (selector) {
            $(selector).sortable({
                revert: true,
                helper: function(e, item){
                    return $(item).clone();
                },
                /** Prevent firefox bug positionnement **/
                start: function (event, ui) {
                },
                stop: function (event, ui) {
                },
                beforeStop: function (event, ui) {
                    var userAgent = navigator.userAgent.toLowerCase();
                    if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                        ui.helper.css('margin-top', 0);
                    }
                },
                update: function () {
                    var order = '';
                    $.each($(selector + ' .gallery-attachment'), function (i, val) {
                        if (order !== '') {
                            order += ',';
                        }
                        order += '"' + i + '":' + $(val).data('id');
                    });
                    order = '{' + order + '}';

                    // do re-order file
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: "wpmfgallery",
                            task: "reorder_image_gallery",
                            order: order,
                            wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                        },
                        success: function () {
                            /* display notification */
                            wpmfSnackbarModule.show({
                                id: 'save_gallery',
                                content : wpmf_glraddon.l18n.save_glr,
                                auto_close_delay: 2000
                            });
                        }
                    });
                }
            });

            $(selector).disableSelection();
        },

        /**
         * Escape string
         * @param s string
         */
        wpmfescapeScripts: function (s) {
            return s
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        },

        /**
         * action edit and remove image
         */
        eventImages: function () {
            $('.edit_image_selection').unbind('click').bind('click', function () {
                var id = $(this).data('id');
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    dataType: 'json',
                    data: {
                        action: "wpmfgallery",
                        task: "image_details",
                        id: id,
                        wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                    },
                    success: function (res) {
                        if (res.status) {
                            showDialog({
                                text: res.html,
                                closeicon: true,
                                negative: {
                                    title: wpmf_glraddon.l18n.cancel,
                                    id: 'wpmf-dl-cancel-edit-image'
                                },
                                positive: {
                                    title: wpmf_glraddon.l18n.save,
                                    id: 'wpmf-dl-save-image',
                                    onClick: function () {
                                        var title = wpmfGalleryModule.wpmfescapeScripts($('.form_image_details_popup .img_title').val());
                                        var excerpt = wpmfGalleryModule.wpmfescapeScripts($('.form_image_details_popup .img_excerpt').val());
                                        var alt = wpmfGalleryModule.wpmfescapeScripts($('.form_image_details_popup .img_alt').val());
                                        var content = wpmfGalleryModule.wpmfescapeScripts($('.form_image_details_popup .img_content').val());
                                        var link_to = wpmfGalleryModule.wpmfescapeScripts($('.form_image_details_popup .custom_image_link').val());
                                        var link_target = $('.form_image_details_popup .image_link_target').val();
                                        var img_tags = wpmfGalleryModule.wpmfescapeScripts($('.form_image_details_popup .img_tags').val());

                                        /* Run ajax update image */
                                        $.ajax({
                                            url: ajaxurl,
                                            method: "POST",
                                            dataType: 'json',
                                            data: {
                                                action: "wpmfgallery",
                                                task: "update_image",
                                                id: id,
                                                title: title,
                                                excerpt: excerpt,
                                                alt: alt,
                                                content: content,
                                                link_to: link_to,
                                                link_target: link_target,
                                                img_tags: img_tags,
                                                wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                                            },
                                            success: function (res) {
                                                if (res.status) {
                                                    /* display notification */
                                                    wpmfSnackbarModule.show({
                                                        id: 'save_image',
                                                        content: wpmf_glraddon.l18n.save_img,
                                                        auto_close_delay: 2000
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }
                            });

                            wpmfGalleryModule.linkAction('form_image_details_popup');
                        }
                    }
                });
            });

            /* Delete image gallery selection */
            $('.delete_image_selection').unbind('click').bind('click', function () {
                var id = $(this).data('id');
                showDialog({
                    title: wpmf_glraddon.l18n.delete_image_gallery,
                    negative: {
                        title: wpmf_glraddon.l18n.cancel
                    },
                    positive: {
                        title: wpmf_glraddon.l18n.delete,
                        onClick: function () {
                            $.ajax({
                                url: ajaxurl,
                                method: "POST",
                                dataType: 'json',
                                data: {
                                    action: "wpmfgallery",
                                    task: "image_selection_delete",
                                    id: id,
                                    id_gallery: wpmfGalleryModule.wpmf_current_gallery,
                                    wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                                },
                                success: function (res) {
                                    if (res.status) {
                                        $('.gallery-attachment[data-id="' + id + '"]').remove();
                                        /* display notification */
                                        wpmfSnackbarModule.show({
                                            id: 'delete_image',
                                            content : wpmf_glraddon.l18n.delete_img,
                                            auto_close_delay: 2000
                                        });
                                        wpmfGalleryModule.changeGallery(wpmfGalleryModule.wpmf_current_gallery);
                                    }
                                }
                            });
                        }
                    }
                });
            });
        },

        /**
         * render lists galleries tree
         * @param res
         * @param open_id
         * @param type
         */
        renderListstree: function (res,open_id,type) {
            wpmfGalleryTreeModule.categories = res.categories;
            wpmfGalleryTreeModule.categories_order = res.categories_order;
            wpmfGalleryTreeModule.importCategories();
            wpmfGalleryTreeModule.getTreeElement().html(wpmfGalleryTreeModule.getRendering());
            wpmfGalleryTreeModule.dragGallery($(".tree_view ul"));
            wpmfGalleryTreeModule.dropGallery();
            if (type) {
                open_id = $('#gallerylist').find('.tree_view ul li:nth-child(2)').data('id');
            }
            wpmfGalleryTreeModule.glrTitleopengallery(open_id, true);
        },
        /**
         * Open link dialog
         * @param selector
         */
        linkAction: function (selector) {
            $('.link-btn').on('click', function () {
                if (typeof wpLink !== "undefined") {
                    wpLink.open('link-btn');
                    /* Bind to open link editor! */
                    $('#wp-link-backdrop').show();
                    $('#wp-link-wrap').show();
                    $('#url-field,#wp-link-url').closest('div').find('span').html('Link To');
                    $('#link-title-field').closest('div').hide();
                    $('.wp-link-text-field').hide();

                    $('#url-field,#wp-link-url').val($('.compat-field-wpmf_gallery_custom_image_link input.text').val());
                    if ($('.compat-field-gallery_link_target select').val() === '_blank') {
                        $('#link-target-checkbox,#wp-link-target').prop('checked', true);
                    } else {
                        $('#link-target-checkbox,#wp-link-target').prop('checked', false);
                    }
                }
            });

            /* Update link  */
            $('#wp-link-submit').on('click', function () {
                var link = $('#url-field').val();
                if (typeof link === "undefined") {
                    link = $('#wp-link-url').val();
                } // version 4.2+

                var link_target = $('#link-target-checkbox:checked').val();
                if (typeof link_target === "undefined") {
                    link_target = $('#wp-link-target:checked').val();
                } // version 4.2+

                if (link_target === 'on') {
                    link_target = '_blank';
                } else {
                    link_target = '';
                }

                $('.' + selector + ' .custom_image_link').val(link);
                $('.' + selector + ' .image_link_target option[value="' + link_target + '"]').prop('selected', true).change();
            });
        },

        /* update nav */
        updateNav: function (res) {
            $('.wpmf-gallery-image-pagging').html(res.nav);
            wpmfGalleryModule.bindEvent();
        },

        /* action for gallery */
        galleryEvent: function () {
            /* import image from wordpress */
            $('.btn_import_image_fromwp').unbind('click').bind('click', function () {
                if (typeof frame !== "undefined") {
                    frame.open();
                    return;
                }
                // Create the media frame.
                var frame = wp.media({
                    // Tell the modal to show only images.
                    library: {
                        type: 'image'
                    },
                    title: wpmf_glraddon.l18n.iframe_import_label,
                    button: {
                        text: wpmf_glraddon.l18n.import
                    },
                    multiple: true
                });

                // When an image is selected, run a callback.
                frame.on('select', function () {
                    // Grab the selected attachment.
                    var attachments = frame.state().get('selection').toJSON();
                    var percent = Math.ceil(100 / (attachments.length));
                    $('.wpmf-process-bar').data('w', 0).css('width', '0%');
                    $('.wpmf-process-bar-full').show();
                    $.each(attachments, function (i, v) {
                        $.ajax({
                            url: ajaxurl,
                            method: "POST",
                            dataType: 'json',
                            data: {
                                action: "wpmfgallery",
                                task: "import_images_from_wp",
                                id: v.id,
                                gallery_id: wpmfGalleryModule.wpmf_current_gallery,
                                mime: v.mime,
                                title: v.title,
                                filename: v.filename,
                                wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                            },
                            success: function (res) {
                                var w = $('.wpmf-process-bar').data('w');
                                var new_w = parseFloat(w) + parseFloat(percent);
                                if (new_w > 100)
                                    new_w = 100;
                                $('.wpmf-process-bar').data('w', new_w).css('width', new_w + '%');
                                if (parseInt(new_w) === 100) {
                                    wpmfGalleryModule.changeGallery(wpmfGalleryModule.wpmf_current_gallery);
                                    $('.wpmf-process-bar-full').fadeOut(3000);
                                }
                            }
                        });
                    });
                });

                // let's open up the frame.
                frame.open();
            });
        },

        /**
         * Get images selection
         */
        getImgSelection: function () {
            $('.WpmfGalleryList #current-page-selector').val(wpmfGalleryModule.current_page_nav);
            $.ajax({
                url: ajaxurl,
                method: "POST",
                dataType: 'json',
                data: {
                    action: "wpmfgallery",
                    task: "get_imgselection",
                    id_gallery: wpmfGalleryModule.wpmf_current_gallery,
                    current_page_nav: wpmfGalleryModule.current_page_nav,
                    wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                },
                beforeSend: function () {
                    $('.wpmf-gallery-selection-wrap').addClass('loading');
                },
                success: function (res) {
                    $('.wpmf-gallery-selection-wrap').removeClass('loading');
                    if (res.status) {
                        $('.wpmf_gallery_selection').html(res.html);
                        wpmfGalleryModule.updateNav(res);
                        wpmfGalleryModule.bindEvent();
                        wpmfGalleryModule.eventImages();
                    }
                }
            });
        },

        /**
         * search key by value
         * @param arr
         * @param val
         * @returns {*}
         */
        arraySearch: function (arr, val) {
            for (var i = 0; i < arr.length; i++)
                if (arr[i] === val)
                    return i;
            return false;
        },

        /**
         * Upload function
         */
        uploadImages: function () {
            /* Upload image */
            $('#wpmf_gallery_file').unbind('change').bind('change', function () {
                wpmfGalleryModule.upload_from_pc = true;
                jQuery('#wpmf_progress_upload').hide();
                $('#wpmf_bar').width(0);
                $('#wpmfglr_form_upload').submit();
            });

            $('.btn_upload_from_pc').unbind('click').bind('click', function () {
                $('#wpmf_gallery_file').click();
            });

            var wpmf_bar = jQuery('.wpmf-process-bar');
            var wpmf_percentValue = '0%';
            jQuery('#wpmfglr_form_upload').ajaxForm({
                beforeSend: function () {
                    wpmf_percentValue = '0%';
                    wpmf_bar.width(wpmf_percentValue);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    jQuery('.wpmf-process-bar-full').show();
                    var wpmf_percentValue = percentComplete + '%';
                    wpmf_bar.width(wpmf_percentValue);
                },
                success: function () {
                    var wpmf_percentValue = '100%';
                    wpmf_bar.width(wpmf_percentValue);
                },
                complete: function (xhr) {
                    jQuery('.wpmf-process-bar-full').hide();
                    try {
                        var ob = JSON.parse(xhr.responseText);
                        if (typeof xhr.responseText !== "undefined") {
                            wpmfGalleryModule.upload_from_pc = false;
                            if (ob.status) {
                                /* display notification */
                                wpmfSnackbarModule.show({
                                    id: 'gallery_image_uploaded',
                                    content : wpmf_glraddon.l18n.upload_img,
                                    auto_close_delay: 2000
                                });
                                wpmfGalleryModule.changeGallery(wpmfGalleryModule.wpmf_current_gallery);
                            } else {
                                alert(ob.msg);
                            }
                        }
                    } catch(err) {
                        wpmfSnackbarModule.show({
                            id: 'gallery_image_upload_error',
                            content : wpmf_glraddon.l18n.upload_error,
                            error: true,
                            auto_close_delay: 5000
                        });
                    }
                }
            });
        },

        updateGalleryShortcode: function () {
            var display = $('.wpmf-theme-item.selected').data('theme');
            var display_tag = 0;
            var display_tree = 0;
            var columns = $('.edit-gallery-columns').val();
            var size = $('.edit-gallery-size').val();
            var targetsize = $('.edit-gallery-targetsize').val();
            var link = $('.edit-gallery-link').val();
            var wpmf_orderby = $('.edit-gallery-orderby').val();
            var wpmf_order = $('.edit-gallery-order').val();
            var animation = $('.edit-gallery-animation').val();
            var duration = $('.edit-gallery-duration').val();
            var auto_animation = $('.edit-gallery-auto_animation').val();
            var show_buttons = 0;

            var gallery_shortcode = '[wpmfgallery';
            gallery_shortcode += ' gallery_id="' + wpmfGalleryModule.wpmf_current_gallery + '"';
            gallery_shortcode += ' display="' + display + '"';
            gallery_shortcode += ' customlink="0"';
            gallery_shortcode += ' bottomspace="default"';
            gallery_shortcode += ' columns="' + columns + '"';
            gallery_shortcode += ' size="' + size + '"';
            gallery_shortcode += ' targetsize="' + targetsize + '"';
            gallery_shortcode += ' link="' + link + '"';
            gallery_shortcode += ' wpmf_orderby="' + wpmf_orderby + '"';
            gallery_shortcode += ' wpmf_order="' + wpmf_order + '"';
            gallery_shortcode += ' animation="' + animation + '"';
            gallery_shortcode += ' duration="' + duration + '"';
            gallery_shortcode += ' auto_animation="' + auto_animation + '"';

            if ($('.gallery_display_tree').is(':checked')) {
                gallery_shortcode += ' display_tree="1"';
                display_tree = 1;
            } else {
                gallery_shortcode += ' display_tree="0"';
            }

            if ($('.gallery_display_tag').is(':checked')) {
                gallery_shortcode += ' display_tag="1"';
                display_tag = 1;
            } else {
                gallery_shortcode += ' display_tag="0"';
            }

            if ($('.gallery_flow_show-buttons').is(':checked')) {
                gallery_shortcode += ' show_buttons="1"';
                show_buttons = 1;
            } else {
                gallery_shortcode += ' show_buttons="0"';
            }

            gallery_shortcode += ']';
            if ($('#WpmfGalleryList').hasClass('wpmfgutenberg')) {
                var datas = $('#gallerylist').data('edited');
                parent.postMessage({
                    'galleryId': wpmfGalleryModule.wpmf_current_gallery,
                    'display': display,
                    'idblock': datas.idblock,
                    'type': 'wpmfgalleryinsert',
                    'display_tree': display_tree,
                    'display_tag': display_tag,
                    'columns': columns,
                    'size': size,
                    'targetsize': targetsize,
                    'link': link,
                    'wpmf_orderby': wpmf_orderby,
                    'wpmf_order': wpmf_order,
                    'animation': animation,
                    'duration': duration,
                    'auto_animation': auto_animation,
                    'show_buttons': show_buttons
                }, wpmf_glraddon.vars.admin_url);
            } else {
                var win = window.dialogArguments || opener || parent || top;
                win.send_to_editor(gallery_shortcode);
                // Refocus in window
                var ed = parent.tinymce.editors[0];
                ed.windowManager.windows[0].close();
            }
        },

        /**
         * all event
         */
        bindEvent: function () {
            $('.form_add_gallery .wpmf-theme-item').unbind('click').bind('click', function () {
                var theme = $(this).data('theme');
                wpmfGalleryModule.updateThemeSelection(theme, 'new');
            });

            $('.form_edit_gallery .wpmf-theme-item').unbind('click').bind('click', function () {
                var theme = $(this).data('theme');
                wpmfGalleryModule.updateThemeSelection(theme, 'edit');
            });

            $('.glr-next-page').unbind('click').bind('click', function () {
                wpmfGalleryModule.current_page_nav++;
                var page_count = $(this).data('page_count');
                if (wpmfGalleryModule.current_page_nav > parseInt(page_count)) wpmfGalleryModule.current_page_nav = page_count;
                wpmfGalleryModule.getImgSelection();
            });

            $('.glr-prev-page').unbind('click').bind('click', function () {
                wpmfGalleryModule.current_page_nav--;
                if (wpmfGalleryModule.current_page_nav < 1) wpmfGalleryModule.current_page_nav = 1;
                wpmfGalleryModule.getImgSelection();
            });

            $('.glr-first-page').unbind('click').bind('click', function () {
                wpmfGalleryModule.current_page_nav = 1;
                wpmfGalleryModule.getImgSelection();
            });

            $('.glr-last-page').unbind('click').bind('click', function () {
                wpmfGalleryModule.current_page_nav = $(this).data('page_count');
                wpmfGalleryModule.getImgSelection();
            });

            $('.glr-current-page').unbind('change').bind('change', function () {
                var page_count = $('.glr-next-page').data('page_count');
                if ($(this).val() > parseInt(page_count)) {
                    wpmfGalleryModule.current_page_nav = page_count;
                    $(this).val(wpmfGalleryModule.current_page_nav);
                } else if ($(this).val() < 1) {
                    wpmfGalleryModule.current_page_nav = 1;
                    $(this).val(wpmfGalleryModule.current_page_nav);
                } else {
                    wpmfGalleryModule.current_page_nav = $(this).val();
                }

                wpmfGalleryModule.getImgSelection();
            });

            $('.img_per_page').unbind('change').bind('change', function () {
                var img_per_page = $(this).val();
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    dataType: 'json',
                    data: {
                        action: "wpmfgallery",
                        task: "update_img_per_page",
                        img_per_page: img_per_page,
                        wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                    },
                    success: function (res) {
                        if (res.status) {
                            wpmfGalleryModule.getImgSelection();
                        }
                    }
                });

            });

            /* insert shortcode gallery */
            $('.btn_insert_gallery').unbind('click').bind('click', function () {
                wpmfGalleryModule.updateGalleryShortcode();
            });

            /* Select images */
            var singleIndex;
            $('.wpmf_gallery_selection .gallery-attachment').unbind('click').bind('click', function (e) {
                var $this = $(this);
                if (!$(e.target).hasClass('material-icons')) {
                    var nodes = Array.prototype.slice.call( document.getElementById('wpmf_gallery_selection').children );
                    if (!$('.gallery-attachment.selected').length) {
                        singleIndex = nodes.indexOf( this );
                    }

                    // select multiple image use ctrl key or shift key
                    if ( e.ctrlKey || e.shiftKey ) {
                        if (!$('.gallery-attachment.selected').length) {
                            $this.addClass('selected');
                        } else {
                            var modelIndex  = nodes.indexOf( this ), i;
                            if ( singleIndex < modelIndex ) {
                                for (i = singleIndex; i<= (modelIndex + 1); i++) {
                                    $('.gallery-attachment:nth-child('+ i +')').addClass('selected');
                                }
                            } else {
                                for (i = modelIndex; i <= (singleIndex + 1); i++) {
                                    $('.gallery-attachment:nth-child('+ (i + 1) +')').addClass('selected');
                                }
                            }
                        }
                    } else {
                        if ($this.hasClass('selected')) {
                            $this.removeClass('selected');
                        } else {
                            $this.addClass('selected');
                        }
                    }

                    if ($('.gallery-attachment.selected').length) {
                        $('.wpmf-remove-imgs-btn').show();
                    } else {
                        $('.wpmf-remove-imgs-btn').hide();
                    }
                }
            });

            /* Create gallery */
            $('.btn_create_gallery').unbind('click').bind('click', function () {
                var $this = $(this);
                var title = $('.new-gallery-name').val();
                var theme = $('.new-gallery-theme').val();
                var parent = $('.new-gallery-parent').val();

                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    dataType: 'json',
                    data: {
                        action: "wpmfgallery",
                        task: "create_gallery",
                        title: title,
                        theme: theme,
                        parent: parent,
                        wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                    },
                    beforeSend: function () {
                        $this.closest('.wpmf-gallery-fields').find('.spinner').css('visibility', 'visible').show();
                    },
                    success: function (res) {
                        if (res.status) {
                            wpmfGalleryModule.gallery_details[res.items.term_id] = res.items;
                            $this.closest('.wpmf-gallery-fields').find('.spinner').hide();
                            $.magnificPopup.close();
                            var ret = '<li class="closed row_jao ui-draggable ui-draggable-handle ui-droppable" data-id="' + res.items.term_id + '" data-parent_id="' + res.items.parent + '">';
                            ret += '<a class="title-folder" onclick="wpmfGalleryTreeModule.glrTitleopengallery(' + res.items.term_id + ')" data-id="' + res.items.term_id + '">';
                            ret += '<i class="material-icons-outlined grlfolder wpmf-no-arrow">photo_album</i>';
                            ret += '<span data-id="' + res.items.term_id + '" data-parent_id="' + res.items.parent + '">' + res.items.name + '</span>';
                            ret += '</a>';
                            ret += '<i onclick="wpmfGalleryTreeModule.deleteGallery(' + res.items.term_id + ')" data-id="' + res.items.term_id + '" data-parent_id="' + res.items.parent + '" class="wpmficon-delete-gallery material-icons">delete_outline</i>';
                            ret += '</li>';

                            if (res.items.parent === 0) {
                                $('#gallerylist').find('.tree_view > ul').append(ret);
                            } else {
                                $('#gallerylist').find('li[data-id="' + res.items.parent + '"] > ul').append(ret);
                            }

                            // Update the categories variables
                            wpmfGalleryModule.updateDropdownParent(res.dropdown_gallery);
                            wpmfGalleryModule.renderListstree(res,res.items.term_id,false);

                            /* display notification */
                            wpmfSnackbarModule.show({
                                id: 'gallery_added',
                                content : wpmf_glraddon.l18n.add_gallery,
                                auto_close_delay: 2000
                            });
                        }
                    }
                });
            });

            /* Delete selected images gallery */
            $('.wpmf-remove-imgs-btn').unbind('click').bind('click', function () {
                var ids = [];
                $('.wpmf_gallery_selection .gallery-attachment.selected').each(function (i, v) {
                    var id = $(v).data('id');
                    ids.push(id);
                });

                showDialog({
                    title: wpmf_glraddon.l18n.delete_selected_image,
                    negative: {
                        title: wpmf_glraddon.l18n.cancel
                    },
                    positive: {
                        title: wpmf_glraddon.l18n.delete,
                        onClick: function () {
                            $.ajax({
                                url: ajaxurl,
                                method: "POST",
                                dataType: 'json',
                                data: {
                                    action: "wpmfgallery",
                                    task: "delete_imgs_selected",
                                    ids: ids.join(),
                                    id_gallery: wpmfGalleryModule.wpmf_current_gallery,
                                    wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                                },
                                success: function (res) {
                                    if (res.status) {
                                        $.each(ids, function (i, id) {
                                            $('.gallery-attachment[data-id="' + id + '"]').remove();
                                        });

                                        /* display notification */
                                        wpmfSnackbarModule.show({
                                            id: 'image_deleted',
                                            content : wpmf_glraddon.l18n.delete_img,
                                            auto_close_delay: 2000
                                        });

                                        wpmfGalleryModule.changeGallery(wpmfGalleryModule.wpmf_current_gallery);
                                    }
                                }
                            });
                        }
                    }
                });
            });

            /* Create gallery */
            $('.btn_edit_gallery').unbind('click').bind('click', function () {
                var $this = $(this);
                var title = $('.edit-gallery-name').val();
                var theme = $('.edit-gallery-theme').val();
                var parent = $('.edit-gallery-parent').val();
                var columns = $('.edit-gallery-columns').val();
                var size = $('.edit-gallery-size').val();
                var targetsize = $('.edit-gallery-targetsize').val();
                var link = $('.edit-gallery-link').val();
                var orderby = $('.edit-gallery-orderby').val();
                var order = $('.edit-gallery-order').val();
                var animation = $('.edit-gallery-animation').val();
                var duration = $('.edit-gallery-duration').val();
                var auto_animation = $('.edit-gallery-auto_animation').val();
                var display_tree = 0;
                var display_tag = 0;
                var show_buttons = 0;
                var gallery_editor = $('#gallerylist').data('edited');
                if ($('.gallery_display_tree').is(':checked')) {
                    display_tree = 1;
                }

                if ($('.gallery_display_tag').is(':checked')) {
                    display_tag = 1;
                }

                if ($('.gallery_flow_show-buttons').is(':checked')) {
                    show_buttons = 1;
                }

                /* Ajax edit gallery */
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    dataType: 'json',
                    data: {
                        action: "wpmfgallery",
                        task: "edit_gallery",
                        id: wpmfGalleryModule.wpmf_current_gallery,
                        title: title,
                        theme: theme,
                        parent: parent,
                        columns: columns,
                        size: size,
                        targetsize: targetsize,
                        link: link,
                        wpmf_orderby: orderby,
                        wpmf_order: order,
                        display_tree: display_tree,
                        display_tag: display_tag,
                        animation: animation,
                        duration: duration,
                        auto_animation: auto_animation,
                        show_buttons: show_buttons,
                        wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                    },
                    beforeSend: function () {
                        wpmfSnackbarModule.show({
                            id: 'wpmf-gallery-saving',
                            content: wpmf_glraddon.l18n.gallery_saving,
                            is_progress: true,
                            auto_close: false
                        });
                    },
                    success: function (res) {
                        if (res.status) {
                            wpmfSnackbarModule.close('wpmf-gallery-saving');
                            if ($this.hasClass('wpmf-modal-save') && parseInt(gallery_editor.gallery_id) === parseInt(wpmfGalleryModule.wpmf_current_gallery)) {
                                // set data params on element
                                $('#gallerylist').data('edited', {
                                    'gallery_id': gallery_editor.gallery_id,
                                    'idblock':  gallery_editor.idblock,
                                    'display': theme,
                                    'display_tree': display_tree,
                                    'display_tag': display_tag,
                                    'columns': columns,
                                    'size': size,
                                    'targetsize': targetsize,
                                    'link': link,
                                    'wpmf_orderby': orderby,
                                    'wpmf_order': order,
                                    'animation': animation,
                                    'duration': duration,
                                    'auto_animation': auto_animation,
                                    'show_buttons': show_buttons,
                                });

                                wpmfSnackbarModule.show({
                                    id: 'save_gallery_modal',
                                    content : wpmf_glraddon.l18n.save_glr_modal,
                                    auto_close_delay: 5000
                                });
                            } else {
                                wpmfSnackbarModule.show({
                                    id: 'save_gallery',
                                    content : wpmf_glraddon.l18n.save_glr,
                                    auto_close_delay: 1000
                                });
                            }

                            // Update the categories variables
                            wpmfGalleryModule.gallery_details[res.items.term_id] = res.items;
                            wpmfGalleryModule.updateDropdownParent(res.dropdown_gallery);
                            wpmfGalleryModule.renderListstree(res, wpmfGalleryModule.wpmf_current_gallery,false);
                        }
                    }
                });

            });
        },

        updateDropdownParent: function (dropdown_gallery) {
            $('.sl-gallery-parent-wrap').html(dropdown_gallery);
            $('.form_edit_gallery .wpmf-gallery-categories').addClass('edit-gallery-parent');
            $('.form_add_gallery .wpmf-gallery-categories').addClass('new-gallery-parent');
        }
    };

    // initialize WPMF gallery features
    $(document).ready(function () {
        wpmfGalleryModule.init();
    });
})(jQuery);
String.prototype.hashCode = function () {
    var hash = 0, i, char;
    if (this.length === 0)
        return hash;
    for (i = 0, l = this.length; i < l; i++) {
        char = this.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash |= 0; // Convert to 32bit integer
    }
    return Math.abs(hash);
};