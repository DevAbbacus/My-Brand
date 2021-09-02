(function (wpI18n, wpBlocks, wpElement, wpEditor, wpComponents) {
    const {__} = wpI18n;
    const {Component, Fragment} = wpElement;
    const {registerBlockType} = wpBlocks;
    const {BlockControls, BlockAlignmentToolbar, InspectorControls, PanelColorSettings} = wpEditor;
    const {PanelBody, Modal, FocusableIframe, IconButton, Toolbar, SelectControl, ToggleControl, RangeControl, Placeholder} = wpComponents;
    const $ = jQuery;
    const el = wpElement.createElement;
    const iconblock = el('svg', {width: 24, height: 24},
        el('path', {d: "M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 2v5l-1-.75L9 9V4h2zm7 16H6V4h1v9l3-2.25L13 13V4h5v16zm-6.72-2.04L9.5 15.81 7 19h10l-3.22-4.26z"})
    );
    let save_params = {};
    class WpmfGallery extends Component {
        constructor() {
            super(...arguments);
            this.state = {
                isOpen: false,
                preview: false,
                title: '',
                gallery_items: []
            };

            this.openModal = this.openModal.bind(this);
            this.closeModal = this.closeModal.bind(this);
            this.addEventListener = this.addEventListener.bind(this);
            this.componentDidMount = this.componentDidMount.bind(this);
        }

        componentWillMount() {
            const {attributes} = this.props;
            this.initLoadTheme();
        }

        componentDidMount() {
            window.addEventListener("message", this.addEventListener, false);
        }

        componentDidUpdate(prevProps) {
            const {attributes} = this.props;
            if ((attributes.html !== '' && (prevProps.attributes.display_tree !== attributes.display_tree
                || prevProps.attributes.display_tag !== attributes.display_tag
                || prevProps.attributes.show_buttons !== attributes.show_buttons
                || prevProps.attributes.display !== attributes.display
                || prevProps.attributes.img_border_radius !== attributes.img_border_radius
                || prevProps.attributes.borderWidth !== attributes.borderWidth
                || prevProps.attributes.borderColor !== attributes.borderColor
                || prevProps.attributes.borderStyle !== attributes.borderStyle
                || prevProps.attributes.gutterwidth !== attributes.gutterwidth
                || prevProps.attributes.hoverShadowH !== attributes.hoverShadowH
                || prevProps.attributes.hoverShadowV !== attributes.hoverShadowV
                || prevProps.attributes.hoverShadowBlur !== attributes.hoverShadowBlur
                || prevProps.attributes.hoverShadowSpread !== attributes.hoverShadowSpread
                || prevProps.attributes.hoverShadowColor !== attributes.hoverShadowColor
                || prevProps.attributes.size !== attributes.size
                || prevProps.attributes.columns !== attributes.columns
                || prevProps.attributes.wpmf_orderby !== attributes.wpmf_orderby
                || prevProps.attributes.wpmf_order !== attributes.wpmf_order)) || prevProps.attributes.galleryId !== attributes.galleryId) {

                this.setState({
                    preview: false
                });
                this.initLoadTheme();
            }

            if (this.state.preview) {
                this.initTheme();
            }
        }

        getTree(categories, trees, parent) {
            let ij = 0;
            while (ij < categories.length) {
                if (categories[ij].parent === parent) {
                    trees.push(categories[ij]);
                    this.getTree(categories, trees, categories[ij].term_id);
                }
                ij++;
            }
            return trees;
        }

        /**
         * Get the html resulting tree view
         * @return {string}
         */
        getRendering() {
            const {attributes} = this.props;
            const {galleryId} = attributes;
            let k = 0;
            let all_galleries = wpmfgalleryblocks.vars.galleries;
            all_galleries = all_galleries.sort(function(a, b){return a.order - b.order});
            let galleries;
            galleries = [];
            while (k < all_galleries.length) {
                if (all_galleries[k].term_id === galleryId) {

                    galleries = [all_galleries[k]];
                    galleries = this.getTree(all_galleries, galleries, galleryId);
                }
                k++;
            }
            
            let ij = 0;
            let content = '';
            /**
             * Recursively print list of folders
             * @return {boolean}
             */
            const generateList = function () {
                content += '<ul>';

                while (ij < galleries.length) {
                    let className = '';
                    if (galleries[ij].term_id === parseInt(galleryId)) {
                        className += 'open selected ';
                    } else {
                        className += 'closed ';
                    }

                    // Open li tag
                    content += '<li class="' + className + '" data-id="' + galleries[ij].term_id + '" >';
                    const a_tag = '<a data-id="' + galleries[ij].term_id + '">';
                    if (galleries[ij + 1] && galleries[ij + 1].depth > galleries[ij].depth) { // The next element is a sub folder
                        content += '<span class="material-icons wpmf-arrow">keyboard_arrow_down</span>';
                        content += a_tag;
                        content += '<i class="material-icons-outlined">photo_album</i>';
                    } else {
                        content += a_tag;
                        content += '<span class="wpmf-no-arrow"></span><i class="material-icons-outlined">photo_album</i>';
                    }

                    // Add current category name
                    content += '<span>' + galleries[ij].name + '</span>';
                    content += '</a>';

                    // This is the end of the array
                    if (galleries[ij + 1] === undefined) {
                        // Let's close all opened tags
                        for (let ik = galleries[ij].depth; ik >= 0; ik--) {
                            content += '</li>';
                            content += '</ul>';
                        }

                        // We are at the end don't continue to process array
                        return false;
                    }

                    if (galleries[ij + 1].depth > galleries[ij].depth) { // The next element is a sub folder
                        // Recursively list it
                        ij++;
                        if (generateList() === false) {
                            // We have reached the end, let's recursively end
                            return false;
                        }
                    } else if (galleries[ij + 1].depth < galleries[ij].depth) { // The next element don't have the same parent
                        // Let's close opened tags
                        for (let ik = galleries[ij].depth; ik > galleries[ij + 1].depth; ik--) {
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
        }

        /**
         * Render tree view inside content
         */
        loadTreeView() {
            const {clientId} = this.props;
            $(`#block-${clientId} .wpmf_gallery_tree`).html(this.getRendering());
        }

        initLoadTheme() {
            const {attributes, setAttributes} = this.props;
            const {
                galleryId,
                display,
                display_tree,
                display_tag,
                columns,
                size,
                targetsize,
                link,
                wpmf_orderby,
                wpmf_order,
                animation,
                duration,
                auto_animation,
                show_buttons,
                gutterwidth,
                img_border_radius,
                borderWidth,
                borderColor,
                borderStyle,
                hoverShadowH,
                hoverShadowV,
                hoverShadowBlur,
                hoverShadowSpread,
                hoverShadowColor
            } = attributes;

            if (parseInt(galleryId) !== 0) {
                let params = {
                    gallery_id: galleryId,
                    display: display,
                    display_tree: display_tree,
                    display_tag: display_tag,
                    columns: columns,
                    size: size,
                    targetsize: targetsize,
                    link: link,
                    orderby: wpmf_orderby,
                    order: wpmf_order,
                    animation: animation,
                    duration: duration,
                    auto_animation: auto_animation,
                    show_buttons: show_buttons,
                    gutterwidth: gutterwidth,
                    img_border_radius: img_border_radius,
                    border_style: borderStyle,
                    border_width: borderWidth,
                    border_color: borderColor.replace('#', ''),
                    hoverShadowH: hoverShadowH,
                    hoverShadowV: hoverShadowV,
                    hoverShadowBlur: hoverShadowBlur,
                    hoverShadowSpread: hoverShadowSpread,
                    hoverShadowColor: hoverShadowColor.replace('#', ''),
                };

                fetch(encodeURI(wpmfgalleryblocks.vars.ajaxurl + `?action=wpmf_load_gallery_html&datas=${JSON.stringify(params)}&wpmf_gallery_nonce=${wpmfgalleryblocks.vars.wpmf_gallery_nonce}`))
                    .then(res => res.json())
                    .then(
                        (result) => {
                            if (result.status) {
                                setAttributes({
                                    html: result.html,
                                    theme: result.theme
                                });

                                this.setState({
                                    preview: true,
                                    title: result.title
                                });
                            }
                        },
                        // errors
                        (error) => {
                        }
                    );
            }
        }

        initTheme() {
            const {attributes, clientId} = this.props;
            const {theme, columns, display_tree} = attributes;

            if (parseInt(display_tree) === 1) {
                this.loadTreeView();
            }

            let $container = $(`#block-${clientId} .wpmf-gallery-addon-block-preview`);
            let $masonry = $container.find('.gallery-masonry');
            imagesLoaded($masonry, function () {
                if (theme === 'slider') {
                    let $slider_container = $(`#block-${clientId} .gallery_addon_flexslider`);
                    let gutterwidth = 15;
                    if ($slider_container.is(':hidden')) {
                        return;
                    }

                    if ($slider_container.hasClass('flexslider-is-active')) {
                        let columns_width = ($slider_container.width() - (columns - 1) * gutterwidth) / columns;
                        $slider_container.find('.wpmf-gallery-item').width(columns_width);
                        return;
                    }

                    if (jQuery().flexslider) {
                        let columns_width = ($slider_container.width() - (columns - 1) * gutterwidth) / columns;
                        let animation = $slider_container.data('animation');
                        let duration = parseInt($slider_container.data('duration'));
                        let auto_animation = parseInt($slider_container.data('auto_animation'));
                        $slider_container.addClass('flexslider-is-active');
                        /* call flexslider function */
                        if (columns > 1) {
                            $slider_container.flexslider({
                                animation: animation,
                                animationLoop: true,
                                slideshow: (auto_animation === 1),
                                smoothHeight: (animation === 'fade'),
                                itemWidth: (animation === 'fade') ? 0 : columns_width,
                                itemMargin: 15,
                                pauseOnHover: true,
                                slideshowSpeed: duration,
                                prevText: "",
                                nextText: ""
                            });
                        } else {
                            $slider_container.flexslider({
                                animation: animation,
                                animationLoop: true,
                                slideshow: (auto_animation === 1),
                                smoothHeight: true,
                                pauseOnHover: true,
                                slideshowSpeed: duration,
                                prevText: "",
                                nextText: "",
                            });
                        }
                    }
                }

                if (theme === 'flowslide') {
                    let $flow_container = $(`#block-${clientId} .flipster`);
                    let enableNavButtons = $flow_container.data('button');
                    if (typeof enableNavButtons !== "undefined" && parseInt(enableNavButtons) === 1) {
                        $flow_container.flipster({
                            style: 'coverflow',
                            buttons: 'custom',
                            spacing: 0,
                            loop: true,
                            autoplay: 5000,
                            buttonNext: '<i class="flipto-next material-icons"> keyboard_arrow_right </i>',
                            buttonPrev: '<i class="flipto-prev material-icons"> keyboard_arrow_left </i>',
                            onItemSwitch: function (currentItem, previousItem) {
                                $flow_container.find('.flipster__container').height($(currentItem).height());
                            },
                            onItemStart: function (currentItem) {
                                $flow_container.find('.flipster__container').height($(currentItem).height());
                            }
                        });
                    } else {
                        $flow_container.flipster({
                            style: 'coverflow',
                            spacing: 0,
                            loop: true,
                            autoplay: 5000,
                            onItemSwitch: function (currentItem, previousItem) {
                                $flow_container.find('.flipster__container').height($(currentItem).height());
                            },
                            onItemStart: function (currentItem) {
                                $flow_container.find('.flipster__container').height($(currentItem).height());
                            }
                        });
                    }
                }

                if (theme === 'masonry' || theme === 'portfolio' || theme === 'square_grid') {
                    $masonry.masonry({
                        itemSelector: '.wpmf-gallery-item',
                        isAnimated: true,
                        animationOptions: {
                            duration: 400,
                            easing: 'linear',
                            queue: false
                        }
                    });
                }
            });
        }

        openModal() {
            if (!this.state.isOpen) {
                this.setState({isOpen: true});
            }
        }

        closeModal() {
            if (this.state.isOpen) {
                this.setState({isOpen: false});
                const {attributes, setAttributes} = this.props;
                const {
                    galleryId,
                    display,
                    display_tree,
                    display_tag,
                    columns,
                    size,
                    targetsize,
                    link,
                    wpmf_orderby,
                    wpmf_order,
                    animation,
                    duration,
                    auto_animation,
                    show_buttons,
                } = attributes;

                if (typeof save_params.galleryId === 'undefined') {
                    return;
                }

                if (parseInt(save_params.galleryId) !== parseInt(galleryId)
                    || save_params.display !== display
                    || save_params.display_tree !== display_tree
                    || save_params.display_tag !== display_tag
                    || save_params.columns !== columns
                    || save_params.link !== link
                    || save_params.wpmf_order !== wpmf_order
                    || save_params.wpmf_orderby !== wpmf_orderby
                    || save_params.size !== size
                    || save_params.targetsize !== targetsize
                    || save_params.animation !== animation
                    || parseInt(save_params.duration) !== parseInt(duration)
                    || parseInt(save_params.auto_animation) !== parseInt(auto_animation)
                    || parseInt(save_params.show_buttons) !== parseInt(show_buttons)
                ) {
                    this.setState({
                        preview: false
                    });

                    setAttributes({
                        galleryId: parseInt(save_params.galleryId),
                        display: save_params.display,
                        display_tree: parseInt(save_params.display_tree),
                        display_tag: parseInt(save_params.display_tag),
                        columns: save_params.columns,
                        link: save_params.link,
                        wpmf_order: save_params.wpmf_order,
                        wpmf_orderby: save_params.wpmf_orderby,
                        size: save_params.size,
                        targetsize: save_params.targetsize,
                        animation: save_params.animation,
                        duration: parseInt(save_params.duration),
                        auto_animation: parseInt(save_params.auto_animation),
                        show_buttons: parseInt(save_params.show_buttons)
                    });
                }
            }
        }

        addEventListener(e) {
            if (!e.data.galleryId) {
                return;
            }

            if (e.data.type !== 'wpmfgalleryinsert') {
                return;
            }

            if (e.data.idblock !== this.props.clientId) {
                return;
            }

            save_params = e.data;
            this.closeModal();
        }

        render() {
            const listBorderStyles = [
                {label: __('None', 'wp-media-folder-gallery-addon'), value: 'none'},
                {label: __('Solid', 'wp-media-folder-gallery-addon'), value: 'solid'},
                {label: __('Dotted', 'wp-media-folder-gallery-addon'), value: 'dotted'},
                {label: __('Dashed', 'wp-media-folder-gallery-addon'), value: 'dashed'},
                {label: __('Double', 'wp-media-folder-gallery-addon'), value: 'double'},
                {label: __('Groove', 'wp-media-folder-gallery-addon'), value: 'groove'},
                {label: __('Ridge', 'wp-media-folder-gallery-addon'), value: 'ridge'},
                {label: __('Inset', 'wp-media-folder-gallery-addon'), value: 'inset'},
                {label: __('Outset', 'wp-media-folder-gallery-addon'), value: 'outset'},
            ];
            
            const {attributes, setAttributes} = this.props;
            const {
                align,
                galleryId,
                display,
                display_tree,
                display_tag,
                columns,
                size,
                targetsize,
                link,
                wpmf_orderby,
                wpmf_order,
                img_border_radius,
                borderWidth,
                borderStyle,
                borderColor,
                hoverShadowH,
                hoverShadowV,
                hoverShadowBlur,
                hoverShadowSpread,
                hoverShadowColor,
                gutterwidth,
                animation,
                duration,
                auto_animation,
                show_buttons,
                html,
                cover
            } = attributes;

            const list_sizes = Object.keys(wpmfgalleryblocks.vars.sizes).map((key, label) => {
                return {
                    label: wpmfgalleryblocks.vars.sizes[key],
                    value: key
                }
            });

            return (
                <Fragment>
                    {
                        typeof cover !== "undefined" && <div className="wpmf-cover"><img src={cover} /></div>
                    }

                    {typeof cover === "undefined" && galleryId !== 0 && (
                        <Fragment>
                            <BlockControls>
                                <Toolbar>
                                    <BlockAlignmentToolbar value={align}
                                                           onChange={(align) => setAttributes({align: align})}/>
                                    <IconButton
                                        className="components-toolbar__control"
                                        label={wpmfgalleryblocks.l18n.edit}
                                        icon={'edit'}
                                        onClick={() => this.setState({isOpen: true})}
                                    />

                                    <IconButton
                                        className="components-toolbar__control"
                                        label={wpmfgalleryblocks.l18n.remove}
                                        icon={'no'}
                                        onClick={() => setAttributes({galleryId: 0})}
                                    />

                                    <IconButton
                                        className="components-toolbar__control"
                                        label={__('Refresh', 'wp-media-folder-gallery-addon')}
                                        icon="update"
                                        onClick={() => this.initLoadTheme()}
                                    />
                                </Toolbar>
                            </BlockControls>
                            <InspectorControls>
                                <PanelBody title={__('Gallery Settings', 'wp-media-folder-gallery-addon')}>
                                    <ToggleControl
                                        label={__('Gallery navigation', 'wp-media-folder-gallery-addon')}
                                        checked={display_tree}
                                        onChange={() => setAttributes({display_tree: (display_tree === 1) ? 0 : 1})}
                                    />

                                    <ToggleControl
                                        label={__('Display images tags', 'wp-media-folder-gallery-addon')}
                                        checked={display_tag}
                                        onChange={() => setAttributes({display_tag: (display_tag === 1) ? 0 : 1})}
                                    />

                                    <SelectControl
                                        label={__('Theme', 'wp-media-folder-gallery-addon')}
                                        value={display}
                                        options={[
                                            {label: __('Use theme setting', 'wp-media-folder-gallery-addon'), value: ''},
                                            {label: __('Default', 'wp-media-folder-gallery-addon'), value: 'default'},
                                            {label: __('Masonry', 'wp-media-folder-gallery-addon'), value: 'masonry'},
                                            {label: __('Portfolio', 'wp-media-folder-gallery-addon'), value: 'portfolio'},
                                            {label: __('Slider', 'wp-media-folder-gallery-addon'), value: 'slider'},
                                            {label: __('Flow slide', 'wp-media-folder-gallery-addon'), value: 'flowslide'},
                                            {label: __('Square grid', 'wp-media-folder-gallery-addon'), value: 'square_grid'},
                                            {label: __('Material', 'wp-media-folder-gallery-addon'), value: 'material'}
                                        ]}
                                        onChange={(value) => setAttributes({display: value})}
                                    />

                                    <SelectControl
                                        label={__('Columns', 'wp-media-folder-gallery-addon')}
                                        value={columns}
                                        options={[
                                            {label: 1, value: '1'},
                                            {label: 2, value: '2'},
                                            {label: 3, value: '3'},
                                            {label: 4, value: '4'},
                                            {label: 5, value: '5'},
                                            {label: 6, value: '6'},
                                            {label: 7, value: '7'},
                                            {label: 8, value: '8'},
                                            {label: 9, value: '9'},
                                        ]}
                                        onChange={(value) => setAttributes({columns: value})}
                                    />

                                    <SelectControl
                                        label={__('Gallery image size', 'wp-media-folder-gallery-addon')}
                                        value={size}
                                        options={list_sizes}
                                        onChange={(value) => setAttributes({size: value})}
                                    />

                                    <SelectControl
                                        label={__('Lightbox size', 'wp-media-folder-gallery-addon')}
                                        value={targetsize}
                                        options={list_sizes}
                                        onChange={(value) => setAttributes({targetsize: value})}
                                    />

                                    <SelectControl
                                        label={__('Action on click', 'wp-media-folder-gallery-addon')}
                                        value={link}
                                        options={[
                                            {label: __('Lightbox', 'wp-media-folder-gallery-addon'), value: 'file'},
                                            {label: __('Attachment Page', 'wp-media-folder-gallery-addon'), value: 'post'},
                                            {label: __('None', 'wp-media-folder-gallery-addon'), value: 'none'},
                                        ]}
                                        onChange={(value) => setAttributes({link: value})}
                                    />

                                    <SelectControl
                                        label={__('Order by', 'wp-media-folder-gallery-addon')}
                                        value={wpmf_orderby}
                                        options={[
                                            {label: __('Custom', 'wp-media-folder-gallery-addon'), value: 'post__in'},
                                            {label: __('Random', 'wp-media-folder-gallery-addon'), value: 'rand'},
                                            {label: __('Title', 'wp-media-folder-gallery-addon'), value: 'title'},
                                            {label: __('Date', 'wp-media-folder-gallery-addon'), value: 'date'}
                                        ]}
                                        onChange={(value) => setAttributes({wpmf_orderby: value})}
                                    />

                                    <SelectControl
                                        label={__('Order', 'wp-media-folder-gallery-addon')}
                                        value={wpmf_order}
                                        options={[
                                            {label: __('Ascending', 'wp-media-folder-gallery-addon'), value: 'ASC'},
                                            {label: __('Descending', 'wp-media-folder-gallery-addon'), value: 'DESC'}
                                        ]}
                                        onChange={(value) => setAttributes({wpmf_order: value})}
                                    />

                                    {
                                        (display === 'slider') &&
                                        <Fragment>
                                            <SelectControl
                                                label={__('Transition Type', 'wp-media-folder-gallery-addon')}
                                                value={wpmf_order}
                                                options={[
                                                    {label: __('Slide', 'wp-media-folder-gallery-addon'), value: 'slide'},
                                                    {label: __('Fade', 'wp-media-folder-gallery-addon'), value: 'fade'}
                                                ]}
                                                onChange={(value) => setAttributes({animation: value})}
                                            />

                                            <RangeControl
                                                label={__('Transition Duration (ms)', 'wp-media-folder-gallery-addon')}
                                                value={duration || 0}
                                                onChange={(value) => setAttributes({duration: value})}
                                                min={0}
                                                max={10000}
                                                step={1000}
                                            />

                                            <ToggleControl
                                                label={__('Automatic Animation', 'wp-media-folder-gallery-addon')}
                                                checked={auto_animation}
                                                onChange={() => setAttributes({auto_animation: (auto_animation === 1) ? 0 : 1})}
                                            />
                                        </Fragment>
                                    }

                                    {
                                        (display === 'flowslide') &&
                                        <Fragment>
                                            <ToggleControl
                                                label={__('Show Buttons', 'wp-media-folder-gallery-addon')}
                                                checked={show_buttons}
                                                onChange={() => setAttributes({show_buttons: (show_buttons === 1) ? 0 : 1})}
                                            />
                                        </Fragment>
                                    }
                                </PanelBody>

                                <PanelBody title={__('Border', 'wp-media-folder-gallery-addon')} initialOpen={false}>
                                    <RangeControl
                                        label={__('Border radius', 'wp-media-folder-gallery-addon')}
                                        aria-label={__('Add rounded corners to the gallery items.', 'wp-media-folder-gallery-addon')}
                                        value={img_border_radius}
                                        onChange={(value) => setAttributes({img_border_radius: value})}
                                        min={0}
                                        max={20}
                                        step={1}
                                    />
                                    <SelectControl
                                        label={__('Border style', 'wp-media-folder-gallery-addon')}
                                        value={borderStyle}
                                        options={listBorderStyles}
                                        onChange={(value) => setAttributes({borderStyle: value})}
                                    />
                                    {borderStyle !== 'none' && (
                                        <Fragment>
                                            <PanelColorSettings
                                                title={__('Border Color', 'wp-media-folder-gallery-addon')}
                                                initialOpen={false}
                                                colorSettings={[
                                                    {
                                                        label: __('Border Color', 'wp-media-folder-gallery-addon'),
                                                        value: borderColor,
                                                        onChange: (value) => setAttributes({borderColor: value === undefined ? '#2196f3' : value}),
                                                    },
                                                ]}
                                            />
                                            <RangeControl
                                                label={__('Border width', 'wp-media-folder-gallery-addon')}
                                                value={borderWidth || 0}
                                                onChange={(value) => setAttributes({borderWidth: value})}
                                                min={0}
                                                max={10}
                                            />
                                        </Fragment>
                                    )}
                                </PanelBody>
                                <PanelBody title={__('Margin', 'wp-media-folder-gallery-addon')} initialOpen={false}>
                                    <RangeControl
                                        label={__('Gutter', 'wp-media-folder-gallery-addon')}
                                        value={gutterwidth}
                                        onChange={(value) => setAttributes({gutterwidth: value})}
                                        min={0}
                                        max={50}
                                        step={5}
                                    />
                                </PanelBody>
                                <PanelBody title={__('Shadow', 'wp-media-folder-gallery-addon')} initialOpen={false}>
                                    <RangeControl
                                        label={__('Shadow H offset', 'wp-media-folder-gallery-addon')}
                                        value={hoverShadowH || 0}
                                        onChange={(value) => setAttributes({hoverShadowH: value})}
                                        min={-50}
                                        max={50}
                                    />
                                    <RangeControl
                                        label={__('Shadow V offset', 'wp-media-folder-gallery-addon')}
                                        value={hoverShadowV || 0}
                                        onChange={(value) => setAttributes({hoverShadowV: value})}
                                        min={-50}
                                        max={50}
                                    />
                                    <RangeControl
                                        label={__('Shadow blur', 'wp-media-folder-gallery-addon')}
                                        value={hoverShadowBlur || 0}
                                        onChange={(value) => setAttributes({hoverShadowBlur: value})}
                                        min={0}
                                        max={50}
                                    />
                                    <RangeControl
                                        label={__('Shadow spread', 'wp-media-folder-gallery-addon')}
                                        value={hoverShadowSpread || 0}
                                        onChange={(value) => setAttributes({hoverShadowSpread: value})}
                                        min={0}
                                        max={50}
                                    />

                                    <PanelColorSettings
                                        title={__('Color Settings', 'wp-media-folder-gallery-addon')}
                                        initialOpen={false}
                                        colorSettings={[
                                            {
                                                label: __('Shadow Color', 'wp-media-folder-gallery-addon'),
                                                value: hoverShadowColor,
                                                onChange: (value) => setAttributes({hoverShadowColor: value === undefined ? '#ccc' : value}),
                                            }
                                        ]}
                                    />
                                </PanelBody>
                            </InspectorControls>
                        </Fragment>
                    )}

                    {typeof cover === "undefined" && galleryId === 0 &&
                        <Placeholder
                            icon={iconblock}
                            label={__('WP Media Folder Gallery Addon', 'wp-media-folder-gallery-addon')}
                            instructions={__('Select or create a WP Media Folder Addon image gallery', 'wp-media-folder-gallery-addon')}
                        >
                            <button className="components-button is-button is-default is-primary is-large aligncenter"
                                    onClick={this.openModal}>{wpmfgalleryblocks.l18n.select_gallery_title}</button>
                        </Placeholder>}
                    {typeof cover === "undefined" && this.state.isOpen ?
                        <Modal
                            className="wpmfGalleryModal"
                            title={wpmfgalleryblocks.l18n.gallery_title}
                            onRequestClose={this.closeModal}
                            shouldCloseOnClickOutside={false}>
                            <FocusableIframe
                                src={wpmfgalleryblocks.vars.admin_gallery_page + `&idblock=${this.props.clientId}&gallery_id=${galleryId}&display=${display}&display_tree=${display_tree}&display_tag=${display_tag}&columns=${columns}&size=${size}&targetsize=${targetsize}&link=${link}&wpmf_orderby=${wpmf_orderby}&wpmf_order=${wpmf_order}&animation=${animation}&duration=${duration}&auto_animation=${auto_animation}&show_buttons=${show_buttons}`}
                            />
                        </Modal>
                        : null}
                    {
                        typeof cover === "undefined" && this.state.title !== '' && <div className="wpmf_glraddon_title_block">{__('Gallery title: ', 'wp-media-folder-gallery-addon') + this.state.title }</div>
                    }

                    {
                        typeof cover === "undefined" && this.state.preview && <div className="wpmf-gallery-addon-block-preview" dangerouslySetInnerHTML={{__html: html}}></div>
                    }

                    {
                        typeof cover === "undefined" && !this.state.preview && parseInt(galleryId) !== 0 && <div className="wpmf-gallery-addon-block-preview" dangerouslySetInnerHTML={{__html: `<p class="wpmf_glraddon_block_loading">${__('Loading...', 'wp-media-folder-gallery-addon')}</p>`}}></div>
                    }
                </Fragment>
            );
        }
    }

    registerBlockType('wpmf/block-gallery', {
        title: wpmfgalleryblocks.l18n.gallery_title,
        icon: iconblock,
        category: 'wp-media-folder',
        keywords: [
            __('gallery', 'wp-media-folder-gallery-addon'),
            __('file', 'wp-media-folder-gallery-addon')
        ],
        example: {
            attributes: {
                cover: wpmfgalleryblocks.vars.block_cover
            }
        },
        attributes: {
            galleryId: {
                type: 'number',
                default: 0
            },
            display: {
                type: 'string',
                default: ''
            },
            display_tree: {
                type: 'number',
                default: 0
            },
            display_tag: {
                type: 'number',
                default: 0
            },
            columns: {
                type: 'string',
                default: '3'
            },
            size: {
                type: 'string',
                default: 'medium'
            },
            targetsize: {
                type: 'string',
                default: 'large'
            },
            link: {
                type: 'string',
                default: 'file'
            },
            wpmf_orderby: {
                type: 'string',
                default: 'post__in'
            },
            wpmf_order: {
                type: 'string',
                default: 'ASC'
            },
            animation: {
                type: 'string',
                default: 'slide'
            },
            duration: {
                type: 'number',
                default: 4000
            },
            auto_animation: {
                type: 'number',
                default: 1
            },
            show_buttons: {
                type: 'number',
                default: 1
            },
            align: {
                type: 'string',
                default: 'center'
            },
            img_border_radius: {
                type: 'number',
                default: 0
            },
            borderWidth: {
                type: 'number',
                default: 1,
            },
            borderColor: {
                type: 'string',
                default: 'transparent'
            },
            borderStyle: {
                type: 'string',
                default: 'none'
            },
            hoverShadowH: {
                type: 'number',
                default: 0
            },
            hoverShadowV: {
                type: 'number',
                default: 0
            },
            hoverShadowBlur: {
                type: 'number',
                default: 0
            },
            hoverShadowSpread: {
                type: 'number',
                default: 0
            },
            hoverShadowColor: {
                type: 'string',
                default: '#ccc'
            },
            gutterwidth: {
                type: 'number',
                default: 15
            },
            theme: {
                type: 'default',
                default: ''
            },
            html: {
                type: 'string',
                default: ''
            },
            cover: {
                type: 'string',
                source: 'attribute',
                selector: 'img',
                attribute: 'src',
            }
        },
        edit: WpmfGallery,
        save: ({attributes}) => {
            const {galleryId, display, display_tree, display_tag, animation, duration, auto_animation, show_buttons, columns, size, targetsize, link, wpmf_orderby, wpmf_order, img_border_radius, gutterwidth, hoverShadowH, hoverShadowV, hoverShadowBlur, hoverShadowSpread, hoverShadowColor, borderWidth, borderStyle, borderColor} = attributes;
            let gallery_shortcode = '[wpmfgallery';
            gallery_shortcode += ' gallery_id="' + galleryId + '"';
            gallery_shortcode += ' size="' + size + '"';
            gallery_shortcode += ' columns="' + columns + '"';
            gallery_shortcode += ' targetsize="' + targetsize + '"';
            gallery_shortcode += ' link="' + link + '"';
            gallery_shortcode += ' wpmf_orderby="' + wpmf_orderby + '"';
            gallery_shortcode += ' wpmf_order="' + wpmf_order + '"';
            gallery_shortcode += ' display_tree="' + display_tree + '"';
            gallery_shortcode += ' display_tag="' + display_tag + '"';


            if (display !== '') {
                gallery_shortcode += ' display="' + display + '"';
            }

            if (parseInt(img_border_radius) !== 0) {
                gallery_shortcode += ' img_border_radius="' + img_border_radius + '"';
            }

            if (parseInt(gutterwidth) !== 5) {
                gallery_shortcode += ' gutterwidth="' + gutterwidth + '"';
            }

            if (typeof hoverShadowH !== "undefined" && typeof hoverShadowV !== "undefined" && typeof hoverShadowBlur !== "undefined" && typeof hoverShadowSpread !== "undefined" && (parseInt(hoverShadowH) !== 0 || parseInt(hoverShadowV) !== 0 || parseInt(hoverShadowBlur) !== 0 || parseInt(hoverShadowSpread) !== 0)) {
                gallery_shortcode += ` img_shadow="${hoverShadowH}px ${hoverShadowV}px ${hoverShadowBlur}px ${hoverShadowSpread}px ${hoverShadowColor}"`;
            }

            if (borderStyle !== 'none') {
                gallery_shortcode += ' border_width="' + borderWidth + '"';
                gallery_shortcode += ' border_style="' + borderStyle + '"';
                gallery_shortcode += ' border_color="' + borderColor + '"';
            }

            if (animation !== 'slide') {
                gallery_shortcode += ' animation="' + animation + '"';
            }

            if (parseInt(duration) !== 4000) {
                gallery_shortcode += ' duration="' + duration + '"';
            }

            if (parseInt(auto_animation) !== 1) {
                gallery_shortcode += ' auto_animation="' + auto_animation + '"';
            }

            if (parseInt(show_buttons) !== 1) {
                gallery_shortcode += ' show_buttons="' + show_buttons + '"';
            }
            gallery_shortcode += ']';
            return gallery_shortcode;
        },
        getEditWrapperProps(attributes) {
            const {align} = attributes;
            const props = {'data-resized': true};

            if ('left' === align || 'right' === align || 'center' === align) {
                props['data-align'] = align;
            }

            return props;
        }
    });
})(wp.i18n, wp.blocks, wp.element, wp.editor, wp.components);