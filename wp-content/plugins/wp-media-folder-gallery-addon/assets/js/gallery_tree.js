/**
 * Folder tree for WP Media Folder
 */
var wpmfGalleryTreeModule;
(function ($) {
    /**
     * Main folder tree function
     * @type {{options: {root: string, showroot: string, onclick: onclick, oncheck: oncheck, usecheckboxes: boolean, expandSpeed: number, collapseSpeed: number, expandEasing: null, collapseEasing: null, canselect: boolean}, init: init, glropengallery, glrclosedir: glrclosedir, glrsetevents: glrsetevents}}
     */
    wpmfGalleryTreeModule = {
        options: {
            'root': '/',
            'showroot': '',
            'onclick': function (elem, type, file) {
            },
            'oncheck': function (elem, checked, type, file) {
            },
            'usecheckboxes': true, //can be true files dirs or false
            'expandSpeed': 500,
            'collapseSpeed': 500,
            'expandEasing': null,
            'collapseEasing': null,
            'canselect': true
        },
        categories : [], // categories
        folders_states : [], // Contains open or closed status of galleries
        /**
         * Folder tree init
         */
        init: function () {
            wpmfGalleryTreeModule.categories_order = wpmf_glraddon.vars.categories_order;
            wpmfGalleryTreeModule.categories = wpmf_glraddon.vars.categories;

            wpmfGalleryTreeModule.importCategories();
            $gallerylist = $('.gallerylist');
            if ($gallerylist.length === 0) {
                return;
            }

            wpmfGalleryTreeModule.getTreeElement().html(wpmfGalleryTreeModule.getRendering());
            var data_params = $('#gallerylist').data('edited');
            var first_id = data_params.gallery_id;
            if (parseInt(first_id) === 0) {
                first_id = $('#gallerylist').find('.tree_view ul li:nth-child(2)').data('id');
            }

            if (first_id !== 0) {
                $('.row_jao[data-id="'+ first_id +'"]').parents('li').removeClass('closed');
                wpmfGalleryTreeModule.glrTitleopengallery(first_id);
            }

            wpmfGalleryModule.galleryEvent();
            wpmfGalleryTreeModule.dragGallery($(".tree_view ul"));
            wpmfGalleryTreeModule.dropGallery();
        },

        /**
         * import gallery category
         */
        importCategories: function () {
            var galleries_ordered = [];

            // Add each category
            $(wpmfGalleryTreeModule.categories_order).each(function (i, v) {
                galleries_ordered.push(wpmfGalleryTreeModule.categories[this]);
            });
            galleries_ordered = galleries_ordered.sort(function(a, b){return a.order - b.order});
            // Reorder array based on children
            var galleries_ordered_deep = [];
            var processed_ids = [];
            const loadChildren = function (id) {
                if (processed_ids.indexOf(id) < 0) {
                    processed_ids.push(id);
                    for (var ij = 0; ij < galleries_ordered.length; ij++) {
                        if (galleries_ordered[ij].parent_id === id) {
                            galleries_ordered_deep.push(galleries_ordered[ij]);
                            loadChildren(galleries_ordered[ij].id);
                        }
                    }
                }
            };
            loadChildren(0);

            // Finally save it to the global var
            wpmfGalleryTreeModule.categories = galleries_ordered_deep;
            if (wpmfGalleryTreeModule.categories.length <= 1) {
                $('.form_edit_gallery').hide();
            } else {
                $('.form_edit_gallery').show();
            }
        },

        /**
         * Get the html resulting tree view
         * @return {string}
         */
        getRendering: function () {
            var ij = 0;
            var content = ''; // Final tree view content
            /**
             * Recursively print list of folders
             * @return {boolean}
             */
            const generateList = function () {
                content += '<ul>';
                var lists = wpmfGalleryTreeModule.categories;
                while (ij < lists.length) {
                    var className = 'closed';
                    // Open li tag
                    content += '<li class="' + className + ' row_jao" data-id="' + lists[ij].id + '" data-parent_id="' + lists[ij].parent_id + '" >';
                    content += '<div class="row-tree">';
                    if (parseInt(lists[ij].id) === 0) {
                        content += '<a class="title-folder top_level" data-id="' + lists[ij].id + '">';
                    } else {
                        const a_tag = '<a class="title-folder" onclick="wpmfGalleryTreeModule.glrTitleopengallery(' + lists[ij].id + ')" data-id="' + lists[ij].id + '">';

                        if (lists[ij + 1] && lists[ij + 1].depth > lists[ij].depth) { // The next element is a sub folder
                            content += '<a class="icon_toggle" onclick="wpmfGalleryTreeModule.toggle(' + lists[ij].id + ')"><i class="material-icons wpmf-arrow">keyboard_arrow_down</i></a>';
                            content += a_tag;
                            content += '<i class="material-icons-outlined grlfolder">photo_album</i>';
                        } else {
                            content += a_tag;
                            content += '<i class="material-icons-outlined grlfolder wpmf-no-arrow">photo_album</i>';
                        }
                    }

                    // Add current category name
                    content += '<span data-id="' + lists[ij].id + '" data-parent_id="' + lists[ij].parent_id + '">' + lists[ij].label + '</span>';
                    content += '</a>';
                    if (parseInt(lists[ij].id) !== 0) {
                        content += '<i class="material-icons wpmficon-delete-gallery"  onclick="wpmfGalleryTreeModule.deleteGallery(' + lists[ij].id + ')" data-id="' + lists[ij].id + '" data-parent_id="' + lists[ij].parent_id + '" "> delete_outline </i>';
                    }
                    content += '</div>';

                    // This is the end of the array
                    if (lists[ij + 1] === undefined) {
                        // Let's close all opened tags
                        for (var ik = lists[ij].depth; ik >= 0; ik--) {
                            content += '</li>';
                            content += '</ul>';
                        }

                        // We are at the end don't continue to process array
                        return false;
                    }


                    if (lists[ij + 1].depth > lists[ij].depth) { // The next element is a sub folder
                        // Recursively list it
                        ij++;
                        if (generateList() === false) {
                            // We have reached the end, let's recursively end
                            return false;
                        }
                    } else if (lists[ij + 1].depth < lists[ij].depth) { // The next element don't have the same parent
                        // Let's close opened tags
                        for (var ik = lists[ij].depth; ik > lists[ij + 1].depth; ik--) {
                            content += '</li>';
                            content += '</ul>';
                        }

                        // We're not at the end of the array let's continue processing it
                        return true;
                    }

                    // Close the current element
                    content += '</li>';
                    ij++;
                }
            };

            // Start generation
            generateList();
            return content;
        },

        /**
         * Toggle the open / closed state of a gallery
         * @param gallery_id
         */
        toggle : function(gallery_id) {
            // Check is gallery has closed class
            if (wpmfGalleryTreeModule.getTreeElement().find('li[data-id="' + gallery_id + '"]').hasClass('closed')) {
                // Open the gallery
                wpmfGalleryTreeModule.glropengallery(gallery_id);
            } else {
                // Close the gallery
                wpmfGalleryTreeModule.glrclosedir(gallery_id);
                // close all sub gallery
                $('li[data-id="' + gallery_id + '"]').find('li').addClass('closed');
            }
        },

        /**
         * open gallery tree by dir name
         * @param gallery_id
         */
        glropengallery : function(gallery_id) {
            wpmfGalleryTreeModule.getTreeElement().find('li[data-id="' + gallery_id + '"]').removeClass('closed');
            wpmfGalleryTreeModule.folders_states[gallery_id] = 'open';
        },

        /**
         * open gallery tree by dir name
         */
        glrTitleopengallery : function(gallery_id, reload = false) {
            if (parseInt(gallery_id) === 0 || (wpmfGalleryModule.wpmf_current_gallery === gallery_id && !reload)) {
                return;
            }

            wpmfGalleryTreeModule.getTreeElement().find('li').removeClass('selected');
            wpmfGalleryTreeModule.getTreeElement().find('li[data-id="' + gallery_id + '"]').removeClass('closed').addClass('selected');
            wpmfGalleryTreeModule.folders_states[gallery_id] = 'open';
            wpmfGalleryModule.changeGallery(gallery_id);

            wpmfGalleryModule.wpmf_current_gallery = gallery_id;
            $('.select_gallery_id').val(gallery_id);
        },

        /**
         * Close a gallery and hide children
         * @param gallery_id
         */
        glrclosedir : function(gallery_id) {
            wpmfGalleryTreeModule.getTreeElement().find('li[data-id="' + gallery_id + '"]').addClass('closed');
            wpmfGalleryTreeModule.folders_states[gallery_id] = 'close';
        },

        /**
         * Retrieve the Jquery tree view element
         * of the current frame
         * @return jQuery
         */
        getTreeElement : function() {
            return $('.tree_view');
        },

        /**
         * init event click to open/close gallery tree
         */
        deleteGallery: function (id) {
            /* Delete gallery */
            showDialog({
                title: wpmf_glraddon.l18n.delete_gallery,
                negative: {
                    title: wpmf_glraddon.l18n.cancel
                },
                positive: {
                    title: wpmf_glraddon.l18n.delete,
                    onClick: function () {
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "wpmfgallery",
                                task: "delete_gallery",
                                id: id,
                                wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                            },
                            success: function (res) {
                                /* remove gallery html */
                                if (res.status) {
                                    $('#gallerylist').find('[data-id="' + id + '"]').remove();
                                    $('.wpmf-gallery-categories option[value="' + id + '"]').remove();
                                    var first_id = $('#gallerylist').find('.tree_view ul li:nth-child(2)').data('id');
                                    wpmfGalleryTreeModule.glrTitleopengallery(first_id);

                                    /* display notification */
                                    wpmfSnackbarModule.show({
                                        id: 'gallery_deleted',
                                        content : wpmf_glraddon.l18n.delete_glr,
                                        auto_close_delay: 2000
                                    });
                                }
                            }
                        });
                    }
                }
            });
        },

        /**
         * droppable Gallery
         */
        dropGallery: function () {
            // Initialize dropping gallery on tree view
            wpmfGalleryTreeModule.getTreeElement().find('ul li .title-folder').droppable({
                hoverClass: "wpmf-hover-folder",
                tolerance: 'pointer',
                over: function (event, ui) {
                    $('.tree_view ul').sortable('disable');
                    $('.wpmfgallery_drop_sort').hide();
                },
                out: function (event, ui) {
                    $('.tree_view ul').sortable('enable');
                    $('.wpmfgallery_drop_sort').show();
                },
                drop: function (event, ui) {
                    event.stopPropagation();
                    if (($(ui.draggable).hasClass('row_jao'))) {
                        // Transfer the event to the wpmf main module
                        var parent = $(this).data('id');
                        var id_gallery = $(ui.draggable).closest('li').data('id');
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "wpmfgallery",
                                task: "update_parent_gallery",
                                parent: parent,
                                id_gallery: id_gallery,
                                wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                            },
                            success: function (response) {
                                /* remove gallery html */
                                if (response.status) {
                                    // update dropdown lists gallery
                                    $('#wpmf-gallery-categories').remove();
                                    $('.gallery_theme').after(response.dropdown_gallery);

                                    // Update the categories variables
                                    wpmfGalleryModule.renderListstree(response, wpmfGalleryModule.wpmf_current_gallery, false);
                                }
                            }
                        });
                    }
                }
            });
        },

        /**
         * draggable gallery
         */
        dragGallery: function ($tree) {
            $tree.sortable({
                placeholder: 'wpmfgallery_drop_sort',
                items: '.row_jao:not([data-id="0"])',
                delay: 500,
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
                    $.each($('.tree_view li'), function (i, val) {
                        var id = $(val).data('id');
                        if (id !== 0) {
                            if (order !== '') {
                                order += ',';
                            }wpmfSnackbarModule.show
                            order += '"' + i + '":' + id;
                        }
                    });
                    order = '{' + order + '}';

                    // do re-order gallery
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: "wpmfgallery",
                            task: "reordergallery",
                            order: order,
                            wpmf_gallery_nonce: wpmf_glraddon.vars.wpmf_gallery_nonce
                        },
                        success: function (res) {
                            // Show snackbar
                            wpmfSnackbarModule.show({
                                id: 'reorder_gallery',
                                content : wpmf_glraddon.l18n.reordergallery,
                                auto_close : true
                            });
                        }
                    });
                }
            });

            $tree.disableSelection();
        }
    };

    // initialize WPMF gallery tree features
    $(document).ready(function () {
        wpmfGalleryTreeModule.init();
    });
})(jQuery);


