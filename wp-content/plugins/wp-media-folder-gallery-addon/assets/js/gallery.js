var wpmfGallery;
(function ($) {
    wpmfGallery = {
        wpmf_img_tags: '*',
        gallery_items: [],
        fancyBox: function (gallery, items, index) {
            //$.fancybox.destroy();
            if (!$.fancybox.getInstance()) {
                $.fancybox.open(items, gallery, index);
            }
        },

        /**
         * Get all items in gallery
         * @param gallery
         * @returns {Array}
         */
        wpmfGalleryGetItems: function (gallery) {
            var lightbox_items = gallery.data('lightbox-items');
            var items = [];
            if (typeof lightbox_items === "undefined") {
                var $item_elements;
                if (gallery.hasClass('wpmf-flipster')) {
                    $item_elements = gallery.find('.wpmf-gallery-item .flipster__item__content > a[data-swipe="1"]');
                } else {
                    $item_elements = gallery.find('.wpmf-gallery-icon > a[data-swipe="1"]');
                }

                $item_elements.each(function () {
                    var src = $(this).attr('href');
                    var type = 'image';
                    if ($(this).hasClass('isvideo')) {
                        type = 'iframe';
                    }

                    var pos = items.map(function (e) {
                        return e.src;
                    }).indexOf(src);
                    if (pos === -1) {
                        items.push({src: src, type: type, caption: $(this).data('title')});
                    }
                });
            } else {
                items = lightbox_items;
            }
            return items;
        },

        callPopup: function() {
            if ($.fancybox) {
                var index = 0;
                $('.wpmf-gallerys-addon .wpmf-gallery-icon > a').unbind('click').bind('click', function (e) {
                    if (parseInt($(this).data('swipe')) === 1) {
                        e.preventDefault();
                        var $this = $(this).closest('.gallery');
                        var parent_items;
                        if ($this.hasClass('wpmf-flipster')) {
                            parent_items = $this.find('.flipster__item__content > a[data-swipe="1"]').closest('.wpmf-gallery-item');
                        } else {
                            parent_items = $this.find('.wpmf-gallery-icon > a[data-swipe="1"]').closest('.wpmf-gallery-item');
                        }

                        index = parent_items.index($(this).closest('.wpmf-gallery-item'));
                        var items = wpmfGallery.wpmfGalleryGetItems($this);
                        wpmfGallery.fancyBox($this, items, index);
                    } else {
                        var target = $(this).attr('target');
                        if (target === '') {
                            target = '_self';
                        }

                        window.open($(this).attr('href'), target);
                    }
                });
            }
        },

        doGallery: function ($container, theme) {
            switch (theme) {
                case 'masonry':
                case 'portfolio':
                case 'square_grid':
                    var id = $container.data('id');
                    if ($container.find('.gallery').is(':hidden')) {
                        return;
                    }

                    if ($container.find('.gallery').hasClass('masonry')) {
                        return;
                    }
                    imagesLoaded($container.find('.gallery'), function () {
                        $container.closest('.wpmf_gallery_wrap').find('.loading_gallery').hide();
                        wpmfGallery.galleryRunMasonry(400, $container, id);
                        $container.find('.gallery').css('visibility', 'visible');
                        $container.find('.wpmf-gallery-item').addClass('wpmf-gallery-item-show');
                        wpmfGallery.callPopup();
                    });
                    break;
                case 'default':
                case 'material':
                    var id = $container.data('id');
                    var columns = $container.find('.glrdefault').data('wpmfcolumns');
                    imagesLoaded($container.find('.glrdefault'), function () {
                        $container.closest('.wpmf_gallery_wrap').find('.loading_gallery').hide();
                        $container.find('figure').each(function (j, v) {
                            if ((j + 1) % columns === 0) {
                                $container.find('figure:nth(' + (j) + ')').after('<hr class="wpmfglr-line-break" />');
                            }
                        });
                        wpmfGallery.wpmfAutobrowse(id, $container.find('.glrdefault'), 'default');
                        wpmfGallery.callPopup();
                    });
                    break;
                case 'flowslide':
                    imagesLoaded($container, function () {
                        $container.closest('.wpmf_gallery_wrap').find('.loading_gallery').hide();
                        var enableNavButtons = $container.data('button');
                        if (typeof enableNavButtons !== "undefined" && parseInt(enableNavButtons) === 1) {
                            $container.flipster({
                                style: 'coverflow',
                                buttons: 'custom',
                                spacing: 0,
                                loop: true,
                                autoplay: 5000,
                                buttonNext: '<i class="flipto-next material-icons"> keyboard_arrow_right </i>',
                                buttonPrev: '<i class="flipto-prev material-icons"> keyboard_arrow_left </i>',
                                onItemSwitch: function (currentItem, previousItem) {
                                    $container.find('.flipster__container').height($(currentItem).height());
                                },
                                onItemStart: function (currentItem) {
                                    $container.find('.flipster__container').height($(currentItem).height());
                                }
                            });
                        } else {
                            $container.flipster({
                                style: 'coverflow',
                                spacing: 0,
                                loop: true,
                                autoplay: 5000,
                                onItemSwitch: function (currentItem, previousItem) {
                                    $container.find('.flipster__container').height($(currentItem).height());
                                },
                                onItemStart: function (currentItem) {
                                    $container.find('.flipster__container').height($(currentItem).height());
                                }
                            });
                        }
                        wpmfGallery.callPopup();
                    });
                    break;

                case 'slider':
                    if (jQuery().flexslider) {
                        $('.icon-chevron-right').on('click', function () {
                            $(this).parent().find('.flex-next').click();
                        });

                        $('.icon-chevron-left').on('click', function () {
                            $(this).parent().find('.flex-prev').click();
                        });

                        if ($container.is(':hidden')) {
                            return;
                        }

                        if ($container.hasClass('flexslider-is-active')) {
                            return;
                        }

                        var animation = $container.data('animation');
                        var duration = parseInt($container.data('duration'));
                        var columns = parseInt($container.data('wpmfcolumns'));
                        var margin = parseInt($container.data('gutterwidth'));
                        var auto_animation = parseInt($container.data('auto_animation'));
                        var columns_width = ($container.width() - (columns - 1) * margin) / columns;
                        $container.addClass('flexslider-is-active');
                        imagesLoaded($container, function () {
                            $container.closest('.wpmf_gallery_wrap').find('.loading_gallery').hide();
                            if (columns > 1) {
                                $container.flexslider({
                                    animation: animation,
                                    animationLoop: true,
                                    slideshow: (auto_animation === 1),
                                    smoothHeight: (animation === 'fade'),
                                    itemWidth: (animation === 'fade') ? 0 : columns_width,
                                    itemMargin: margin,
                                    pauseOnHover: true,
                                    slideshowSpeed: duration,
                                    prevText: "",
                                    nextText: "",
                                    start: function () {
                                        $('.entry-content').removeClass('loading');
                                    }
                                });
                            } else {
                                $container.flexslider({
                                    animation: animation,
                                    animationLoop: true,
                                    itemWidth: $container.width(),
                                    slideshow: (auto_animation === 1),
                                    smoothHeight: true,
                                    pauseOnHover: true,
                                    slideshowSpeed: duration,
                                    prevText: "",
                                    nextText: "",
                                    start: function () {
                                        $('.entry-content').removeClass('loading');
                                    }
                                });
                            }

                            wpmfGallery.callPopup();
                        });
                    }
                    break;
            }
        },

        /* Init gallery */
        initGallery: function () {
            wpmfGallery.callPopup();
            /* re-call event with tags */
            wpmfGallery.wpmfEventGalleryTags();
            $('.wpmf_gallery_wrap .flipster').each(function () {
                var $flip = $(this);
                wpmfGallery.doGallery($flip, 'flowslide');
            });

            $('.wpmf-gallerys-addon').each(function () {
                var theme = $(this).data('theme');
                if (theme !== 'slider') {
                    wpmfGallery.doGallery($(this), theme);
                }
            });

            /* window load */
            $(window).load(function () {
                $('.flex-viewport').each(function () {
                    var first_image_height = $(this).find('ul.slides li:first-child img').css('height');
                    $(this).css('height', first_image_height + ' !important');
                });
            });

            /* init flexslider theme */
            $('.gallery_addon_flexslider').each(function () {
                wpmfGallery.doGallery($(this), 'slider');
            });
        },

        /**
         * get column width, gutter width, count columns
         * @param $container
         * @returns {{columnWidth: number, gutterWidth, columns: Number}}
         */
        calculateGrid: function ($container) {
            var columns = parseInt($container.data('wpmfcolumns'));
            var gutterWidth = $container.data('gutter-width');
            var containerWidth = $container.width();

            if (isNaN(gutterWidth)) {
                gutterWidth = 5;
            } else if (gutterWidth > 50 || gutterWidth < 0) {
                gutterWidth = 5;
            }

            if (parseInt(columns) < 2 || containerWidth <= 450) {
                columns = 2;
            }

            gutterWidth = parseInt(gutterWidth);

            var allGutters = gutterWidth * (columns - 1);
            var contentWidth = containerWidth - allGutters;

            var columnWidth = Math.floor(contentWidth / columns);
            return {columnWidth: columnWidth, gutterWidth: gutterWidth, columns: columns};
        },

        /**
         * Run masonry gallery
         * @param duration
         * @param $container
         * @param id
         */
        galleryRunMasonry: function (duration, $container, id) {
            if ($container.find('.gallery').hasClass('masonry')) {
                return;
            }
            if ($container.is(':hidden')) {
                return;
            }
            var container = $container.find('.gallery-masonry');
            var $postBox = container.children('.wpmf-gallery-item');
            var o = wpmfGallery.calculateGrid($(container));
            var padding = o.gutterWidth;
            $postBox.css({'width': o.columnWidth + 'px', 'margin-bottom': padding + 'px'});
            $(container).masonry({
                itemSelector: '.wpmf-gallery-item',
                columnWidth: o.columnWidth,
                gutter: padding,
                isAnimated: true,
                animationOptions: {
                    duration: duration,
                    easing: 'linear',
                    queue: false
                },
                isFitWidth: true
            });

            if ($(container).hasClass('gallery-portfolio')) {
                var w = $(container).find('.attachment-thumbnail').width();
                $(container).find('.wpmf-caption-text.wpmf-gallery-caption , .gallery-icon').css('max-width', w + 'px');
            }

            wpmfGallery.wpmfAutobrowse(id, container, 'masonry', o.columnWidth, padding);
        },

        /**
         * lazy load images in gallery
         * @param id theme id
         * @param container container parent of items
         * @param theme_type theme type
         * @param column_width item width
         * @param padding item padding
         */
        wpmfAutobrowse: function (id, container, theme_type, column_width, padding) {
            if (parseInt(wpmfgallery.progressive_loading) === 0) {
                return;
            }

            var count = $(container).data('count');
            var number = 8;
            var offset = 8;
            var current = 0;
            var theme = $(container).closest('.wpmf_gallery_box').data('theme');
            var settings = $(container).closest('.wpmf_gallery_wrap').data('top-gallery-settings');
            var tags = $(container).closest('.wpmf_gallery_wrap').find('.tab.filter-all-control.selected a').data('filter');
            container.autobrowse(
                {
                    url: function (offset) {
                        var url = wpmfgallery.ajaxurl + '?action=wpmf_get_gallery_item&gallery_id=' + id + '&theme=' + theme + '&offset=' + offset;
                        if (typeof tags !== 'undefined' && tags !== '*') {
                            url += '&tags=' + tags;
                        }
                        return url;
                    },
                    postData: {settings: JSON.stringify(settings)},
                    timeout: 100,
                    template: function (response) {
                        var elems = [];
                        if (response.status) {
                            for (var i = 0; i < number && i + current < response.items.length; i++) {
                                var el = $(response.items[i + current]);
                                elems[i] = $(el).get(0);
                                if (theme_type === 'masonry') {
                                    $($(el).get(0)).hide().appendTo(container);
                                } else {
                                    $($(el).get(0)).hide().appendTo(container).fadeIn(800);
                                }
                            }

                            current += 8;
                            if (theme_type === 'masonry') {
                                $(container).imagesLoaded(function () {
                                    $(elems).css({
                                        'width': column_width + 'px',
                                        'margin-bottom': padding + 'px',
                                        'opacity': 0
                                    }).show();

                                    $(container).masonry('appended', $(elems));
                                    $(elems).animate({
                                        opacity: 1,
                                    }, 100, function () {
                                        // Animation complete.
                                    });
                                    $(container).find('.wpmf-gallery-item').addClass('wpmf-gallery-item-show');
                                    wpmfGallery.callPopup();
                                });
                            }
                        } else {
                            current += 8;
                        }
                    },
                    itemsReturned: function (response) {
                        if (current >= count) {
                            return 0;
                        }
                        return number;
                    },
                    offset: offset
                }
            );
        },

        /* init tags event */
        wpmfEventGalleryTags: function () {
            $('.filter-all-control a').unbind('click').bind('click', function () {
                var $this = $(this);
                var galleryId = $this.closest('.wpmf_gallery_box').data('id');
                var $tree = $('.wpmf_gallery_tree[data-id="' + galleryId + '"]');
                var $container = $this.closest('.wpmf_gallery_wrap');
                var img_tags = $(this).data('filter');
                var settings = $container.data('top-gallery-settings');
                if (typeof img_tags !== "undefined") {
                    wpmfGallery.wpmf_img_tags = img_tags;
                }

                /* Load gallery */
                var data = {
                    action: "wpmf_load_gallery",
                    gallery_id: galleryId,
                    tags: wpmfGallery.wpmf_img_tags,
                    settings: settings,
                    wpmf_gallery_nonce: wpmfgallery.wpmf_gallery_nonce
                };

                data.selector = $this.closest('.wpmf_gallery_wrap').data('selector');
                if ($tree.length) {
                    var current = $tree.find('li.selected').data('id');
                    if (current === galleryId) {
                        data.settings = $this.closest('.wpmf_gallery_wrap').data('top-gallery-settings');
                    }
                    data.gallery_id = current;
                } else {
                    data.settings = $this.closest('.wpmf_gallery_wrap').data('top-gallery-settings');
                }

                $.ajax({
                    url: wpmfgallery.ajaxurl,
                    method: "POST",
                    dataType: 'json',
                    data: data,
                    beforeSend: function () {
                        $container.find('.wpmf_gallery_box *').hide();
                        $container.find('.wpmf_gallery_box .loading_gallery').show();
                    },
                    success: function (res) {
                        if (res.status) {
                            $this.closest('.wpmf_gallery_box').find('.loading_gallery').hide();
                            $container.find('.wpmf_gallery_box').html('').append(res.html);
                            wpmfGallery.initGallery();
                        }
                    }
                });
            });
        }
    };

    $(document).ready(function () {
        if (wpmfgallery.wpmf_current_theme === 'Gleam') {
            setTimeout(function () {
                wpmfGallery.initGallery();
            }, 1000);
        } else {
            wpmfGallery.initGallery();
        }

        setTimeout(function () {
            $('.responsive-tabs__list__item').on('click', function () {
                var target = $(this).attr('aria-controls');
                var container = $('#' + target).find('.wpmf-gallerys-addon');
                if (container.length) {
                    var id = container.data('id');
                    wpmfGallery.galleryRunMasonry(400, container, id);
                }
            });

            $('.tabtitle.responsive-tabs__heading').on('click', function () {
                var container = $(this).next('.tabcontent.responsive-tabs__panel').find('.wpmf-gallerys-addon');
                if (container.length) {
                    var id = container.data('id');
                    wpmfGallery.galleryRunMasonry(400, container, id);
                }
            });
        }, 1000);

        // click to tab of advanced tab Blocks
        $('.advgb-tab').on('click', function (event) {
            event.preventDefault();
            var bodyContainers = $(this).closest('.advgb-tabs-wrapper').find('.advgb-tab-body-container');
            setTimeout(function () {
                var currentTabActive = $(event.target).closest('.advgb-tab');
                var href = currentTabActive.find('a').attr('href');
                if (bodyContainers.find('.advgb-tab-body[aria-labelledby="' + href.replace(/^#/, "") + '"] .wpmf-gallerys').length) {
                    initGallery();
                }
            }, 200);
        });

        // click to tab of Kadence Blocks
        $('.kt-tabs-title-list .kt-title-item').on('click', function (event) {
            event.preventDefault();
            var href = $(this).attr('id');
            var bodyContainers = $(this).closest('.kt-tabs-wrap').find('.kt-tabs-content-wrap');
            setTimeout(function () {
                if (bodyContainers.find('.kt-tab-inner-content[aria-labelledby="' + href + '"] .wpmf-gallerys').length) {
                    initGallery();
                }
            }, 200);
        });

        // click to tab of Ultimate Blocks
        $('.wp-block-ub-tabbed-content-tab-title-wrap').on('click', function () {
            setTimeout(function () {
                var bodyContainers = $('.wp-block-ub-tabbed-content-tab-content-wrap.active');
                if (bodyContainers.find('.wpmf-gallerys').length) {
                    initGallery();
                }
            }, 200);
        });
    });

    $(document.body).on('post-load', function () {
        wpmfGallery.initGallery();
    });

    $(document.body).on('wpmfs-toggled', function () {
        wpmfGallery.initGallery();
    });
})(jQuery);
