<script>
    /**
     * An AUI bootstrap adaptation of GreedyNav.js ( by Luke Jackson ).
     *
     * Simply add the class `greedy` to any <nav> menu and it will do the rest.
     * Licensed under the MIT license - http://opensource.org/licenses/MIT
     * @ver 0.0.1
     */
    function aui_init_greedy_nav() {
        document.querySelectorAll('nav.greedy').forEach((nav) => {
            if (nav.classList.contains('being-greedy')) return;
            nav.classList.add('being-greedy', 'navbar-expand');

            nav.addEventListener('shown.bs.tab', (e) => {
                const menu = e.target.closest('.dropdown-menu.greedy-links');
                if (menu) {
                    const greedy = e.target.closest('.greedy');
                    greedy.querySelector('.greedy-btn.dropdown')
                        .setAttribute('aria-expanded', 'false');
                    menu.classList.replace('show', 'd-none');
                }
            });

            document.addEventListener('mousemove', (e) => {
                if (e.target.closest('.greedy-btn')) {
                    document
                        .querySelectorAll('.dropdown-menu.greedy-links')
                        .forEach((m) => m.classList.remove('d-none'));
                }
            });

            // find our <ul>
            let vlinks = nav.querySelector('.navbar-nav');
            let extraDD = '', ddItemClass = 'greedy-nav-item';
            if (vlinks) {
                if (vlinks.classList.contains('being-greedy')) return;
                vlinks.classList.add('being-greedy', 'w-100');
                vlinks.classList.remove('overflow-hidden');
            } else {
                vlinks = nav.querySelector('.nav');
                if (!vlinks || vlinks.classList.contains('being-greedy')) return;
                vlinks.classList.add('being-greedy', 'w-100');
                vlinks.classList.remove('overflow-hidden');
                extraDD = ' mt-0 p-0 zi-5';
                ddItemClass += ' mt-0 me-0';
            }

            // stash originals
            Array.from(vlinks.children).forEach((li) => {
                li.dataset.originalItemClass = li.className;
                const a = li.querySelector('.nav-link');
                if (a) a.dataset.originalLinkClass = a.className;
            });

            // add the “more” button
            const moreLi = document.createElement('li');
            moreLi.className = 'nav-item list-unstyled ms-auto greedy-btn d-none dropdown';
            moreLi.innerHTML = `
      <button data-bs-toggle="dropdown"
              class="nav-link greedy-nav-link dropdown-toggle"
              role="button"
              aria-expanded="false">
        <i class="fas fa-ellipsis-h"></i>
        <span class="greedy-count badge bg-dark rounded-pill"></span>
      </button>
      <ul class="greedy-links dropdown-menu dropdown-menu-end${extraDD}"></ul>
    `;
            vlinks.appendChild(moreLi);

            const hlinks = nav.querySelector('ul.greedy-links');
            const btnLi  = moreLi;
            const btn    = moreLi.querySelector('button');

            // measure breakpoints
            let total = 0, breakWidths = [];
            Array.from(vlinks.children).forEach((li) => {
                total += li.getBoundingClientRect().width;
                breakWidths.push(total);
            });
            const numItems = breakWidths.length;

            function check() {
                const avail = vlinks.getBoundingClientRect().width - 10;
                let visible = vlinks.children.length;
                const needed = breakWidths[visible - 1];

                // overflow → push one into dropdown
                if (visible > 1 && needed > avail) {
                    const li = vlinks.children[visible - 2];
                    li.classList.remove('nav-item');
                    li.classList.add(...ddItemClass.split(' '));

                    if (!hlinks.children.length) {
                        li.querySelector('a')
                            .classList.add('w-100','dropdown-item','rounded-0','rounded-bottom','justify-content-start');
                    } else {
                        hlinks.querySelectorAll('a').forEach(x => x.classList.remove('rounded-top'));
                        li.querySelector('a')
                            .classList.add('w-100','dropdown-item','rounded-0','rounded-top','justify-content-start');
                    }

                    hlinks.insertBefore(li, hlinks.firstChild);
                    visible--;
                    return check();
                }

                // underflow → pop one back out
                if (avail > (breakWidths[visible] || 0)) {
                    const li = hlinks.firstElementChild;
                    if (li) {
                        // restore <li> classes
                        li.className = li.dataset.originalItemClass || '';
                        // restore <a> classes
                        const a = li.querySelector('a');
                        if (a && a.dataset.originalLinkClass) {
                            a.className = a.dataset.originalLinkClass;
                        }
                        vlinks.insertBefore(li, btnLi);
                        visible++;
                        return check();
                    }
                }

                // update button count + toggle
                btn.querySelector('.greedy-count').textContent = numItems - visible;
                btnLi.classList.toggle('d-none', visible === numItems);
            }

            window.addEventListener('resize', check);
            check();
        });
    }



    function aui_select2_locale() {
        var aui_select2_params = <?php echo self::select2_locale(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;

        return {
            theme: "bootstrap-5",
            width: jQuery( this ).data( 'width' ) ? jQuery( this ).data( 'width' ) : jQuery( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: jQuery( this ).data( 'placeholder' ),
            'language': {
                errorLoading: function() {
                    // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
                    return aui_select2_params.i18n_searching;
                },
                inputTooLong: function(args) {
                    var overChars = args.input.length - args.maximum;
                    if (1 === overChars) {
                        return aui_select2_params.i18n_input_too_long_1;
                    }
                    return aui_select2_params.i18n_input_too_long_n.replace('%item%', overChars);
                },
                inputTooShort: function(args) {
                    var remainingChars = args.minimum - args.input.length;
                    if (1 === remainingChars) {
                        return aui_select2_params.i18n_input_too_short_1;
                    }
                    return aui_select2_params.i18n_input_too_short_n.replace('%item%', remainingChars);
                },
                loadingMore: function() {
                    return aui_select2_params.i18n_load_more;
                },
                maximumSelected: function(args) {
                    if (args.maximum === 1) {
                        return aui_select2_params.i18n_selection_too_long_1;
                    }
                    return aui_select2_params.i18n_selection_too_long_n.replace('%item%', args.maximum);
                },
                noResults: function() {
                    return aui_select2_params.i18n_no_matches;
                },
                searching: function() {
                    return aui_select2_params.i18n_searching;
                }
            }
        };
    }

    /**
     * Builds a configuration object for a single Choices.js instance.
     * @param {HTMLElement} select - The <select> element to configure.
     * @returns {object} The configuration object for Choices.js.
     */
    function aui_get_choices_config(select) {
        const defaultOptions = {
            allowHTML: true,
            searchPlaceholderValue: 'Search...',
            removeItemButton: true,
            editItems: true,
            searchEnabled: false,
            shouldSort: false,
            itemSelectText: '',
            classNames: {
                containerInner: 'form-select',
            },
        };

        let userOptions = {};
        const dataAttr = select.getAttribute('data-select');
        if (dataAttr) {
            try {
                userOptions = JSON.parse(dataAttr);
            } catch (e) {
                console.error('Invalid JSON in data-select attribute:', e);
            }
        }

        let finalOptions = { ...defaultOptions, ...userOptions };

        const template = select.getAttribute('data-select-template');
        if (template) {
            finalOptions.callbackOnCreateTemplates = function (templateFn) {
                return {
                    item: ({ classNames }, data) => {
                        return templateFn(`
                        <div class="${classNames.item} ${data.highlighted ? classNames.highlightedState : classNames.itemSelectable} ${data.placeholder ? classNames.placeholder : ''}" data-item data-id="${data.id}" data-value="${data.value}" ${data.active ? 'aria-selected="true"' : ''} ${data.disabled ? 'aria-disabled="true"' : ''} ${data.placeholder ? 'data-placeholder' : ''}>
                            ${data.placeholder || !data.customProperties?.selected ? data.label : data.customProperties.selected}
                            ${userOptions.removeItemButton === false ? '' : `<button type="button" class="choices__button" aria-label="Remove item" data-button></button>`}
                        </div>
                    `);
                    },
                    choice: ({ classNames }, data) => {
                        return templateFn(`
                        <div class="${classNames.item} ${classNames.itemChoice} ${data.disabled ? classNames.itemDisabled : classNames.itemSelectable} ${data.placeholder ? classNames.placeholder : ''}" data-select-text="${this.config.itemSelectText}" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'} data-id="${data.id}" data-value="${data.value}" ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}>
                            <div>
                                ${data.label}
                                ${(() => {
                            let output = '';
                            if (data.customProperties) {
                                for (const key in data.customProperties) {
                                    if (Object.prototype.hasOwnProperty.call(data.customProperties, key) && key !== 'selected') {
                                        output += data.customProperties[key];
                                    }
                                }
                            }
                            return output;
                        })()}
                            </div>
                        </div>
                    `);
                    },
                };
            };
        }

        return finalOptions;
    }

    /**
     * Initiate Select2 items.  @todo we need to check and replicate functionality in current use cases.
     */
    /**
     * Finds and initializes all Choices.js select elements on the page.
     */
    function aui_init_choices() {
        const selects = document.querySelectorAll('select.aui-select2:not([data-choice="active"])');
        if (selects.length === 0) return;

        selects.forEach((select) => {
            const config = aui_get_choices_config(select);
            new Choices(select, config);
        });
    }

    /**
     * A function to convert a time value to a "ago" time text.
     *
     * @param selector string The .class selector
     */
    function aui_time_ago(selector) {
        var aui_timeago_params = <?php echo self::timeago_locale(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;

        var templates = {
            prefix: aui_timeago_params.prefix_ago,
            suffix: aui_timeago_params.suffix_ago,
            seconds: aui_timeago_params.seconds,
            minute: aui_timeago_params.minute,
            minutes: aui_timeago_params.minutes,
            hour: aui_timeago_params.hour,
            hours: aui_timeago_params.hours,
            day: aui_timeago_params.day,
            days: aui_timeago_params.days,
            month: aui_timeago_params.month,
            months: aui_timeago_params.months,
            year: aui_timeago_params.year,
            years: aui_timeago_params.years
        };
        var template = function (t, n) {
            return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
        };

        var timer = function (time) {
            if (!time)
                return;
            time = time.replace(/\.\d+/, ""); // remove milliseconds
            time = time.replace(/-/, "/").replace(/-/, "/");
            time = time.replace(/T/, " ").replace(/Z/, " UTC");
            time = time.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400
            time = new Date(time * 1000 || time);

            var now = new Date();
            var seconds = ((now.getTime() - time) * .001) >> 0;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;

            return templates.prefix + (
                seconds < 45 && template('seconds', seconds) ||
                seconds < 90 && template('minute', 1) ||
                minutes < 45 && template('minutes', minutes) ||
                minutes < 90 && template('hour', 1) ||
                hours < 24 && template('hours', hours) ||
                hours < 42 && template('day', 1) ||
                days < 30 && template('days', days) ||
                days < 45 && template('month', 1) ||
                days < 365 && template('months', days / 30) ||
                years < 1.5 && template('year', 1) ||
                template('years', years)
            ) + templates.suffix;
        };

        var elements = document.getElementsByClassName(selector);
        if (selector && elements && elements.length) {
            for (var i in elements) {
                var $el = elements[i];
                if (typeof $el === 'object') {
                    $el.innerHTML = '<i class="far fa-clock"></i> ' + timer($el.getAttribute('title') || $el.getAttribute('datetime'));
                }
            }
        }

        // update time every minute
        setTimeout(function() {
            aui_time_ago(selector);
        }, 60000);

    }

    /**
     * Initiate tooltips on the page.
     */
    function aui_init_tooltips() {
        // initialize all tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });

        // initialize all popovers
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
            new bootstrap.Popover(el);
        });

        // initialize all HTML‐enabled popovers
        document.querySelectorAll('[data-bs-toggle="popover-html"]').forEach(function(el) {
            new bootstrap.Popover(el, {
                html: true,
                sanitize: false
            });
        });

        // fix popover container compatibility
        document.querySelectorAll('[data-bs-toggle="popover"],[data-bs-toggle="popover-html"]').forEach(function(el) {
            el.addEventListener('inserted.bs.popover', function () {
                // collect all direct‐child .popover elements of <body>
                const popovers = Array.from(document.body.querySelectorAll(':scope > .popover'));
                if (!popovers.length) return;

                // wrap them in a single .bsui container
                const wrapper = document.createElement('div');
                wrapper.className = 'bsui';
                popovers.forEach(function(p) {
                    wrapper.appendChild(p);
                });
                document.body.appendChild(wrapper);
            });
        });
    }


    /**
     * Initiate flatpickrs on the page.
     */
    $aui_doing_init_flatpickr = false;
    function aui_init_flatpickr(){
        if ( typeof flatpickr === "function" && !$aui_doing_init_flatpickr) {
            $aui_doing_init_flatpickr = true;
            <?php if ( ! empty( $flatpickr_locale ) ) { ?>try{flatpickr.localize(<?php echo $flatpickr_locale; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>);}catch(err){console.log(err.message);}<?php } ?>
            document
                .querySelectorAll('input[data-aui-init="flatpickr"]:not(.flatpickr-input)')
                .forEach(function(el){
                    flatpickr(el);
                });
        }
        $aui_doing_init_flatpickr = false;
    }


    // keep track of instances for cleanup
    var _aui_iconPickers = [];

    /**
     * Initializes all icon pickers on the page.
     * @param {string} [wrapperSelector] defaults to '.input-group'
     * @param {boolean} [force] destroy & re-init if true
     */
    function aui_init_iconpicker(wrapperSelector, force) {
        // Ensure the new picker class is available
        if (typeof AyeCodeIconPicker === 'undefined') return;

        var wrapSel = wrapperSelector || '.input-group';

        // Destroy existing if forced
        if (force) {
            _aui_iconPickers.forEach(function(p) { p.destroy && p.destroy(); });
            _aui_iconPickers = [];
            document.querySelectorAll('input[data-aui-init="iconpicker"]')
                .forEach(function(el) {
                    el.classList.remove('iconpicker-input');
                    delete el._iconPicker;
                });
        }

        // Init any new ones
        document.querySelectorAll('input[data-aui-init="iconpicker"]:not(.iconpicker-input)')
            .forEach(function(el) {
                // Find wrapper & addon trigger
                var wrapper = el.closest(wrapSel);
                var addon = wrapper && wrapper.querySelector('.input-group-addon, .input-group-text');
                if (!addon) return; // nothing to click

                // Give the trigger addon a stable ID for the picker to attach to
                if (!addon.id) {
                    addon.id = 'iconpicker-trigger-' + Math.random().toString(36).substr(2, 9);
                }

                // Show initial icon or a fallback
                addon.innerHTML = el.value.trim()
                    ? '<i class="' + el.value + '"></i>'
                    : '<i class="fas fa-icons"></i>'; // Fallback icon
                addon.classList.add('c-pointer');

                // Instantiate our new AyeCodeIconPicker on the addon
                var picker = new AyeCodeIconPicker('#' + addon.id, {
                    // IMPORTANT: Provide the correct path to your icons-libraries folder
                    iconPickerUrl: '<?php echo $this->url;?>/assets-v5-dm/libs/universal-icon-picker/icons-libraries/',

                    // These are the default libraries, can be overridden if needed
                    iconLibraries: [
                        'font-awesome-solid.min.json',
                        'font-awesome-regular.min.json',
                        'font-awesome-brands.min.json'
                    ],

                    // Define what happens when an icon is selected
                    onSelect: function(jsonIconData) {
                        // Update the hidden input's value
                        el.value = jsonIconData.iconClass;
                        // Update the visible addon's icon
                        addon.innerHTML = jsonIconData.iconHtml;
                        // Optional: Trigger a change event for other scripts to listen to
                        el.dispatchEvent(new Event('change'));
                    }

                    // The onReset functionality is handled by selecting an empty icon if you were to add one,
                    // or you could add a "Reset" button to the modal and call an `onReset` callback.
                });

                // Mark as initialized
                el.classList.add('iconpicker-input');
                el._iconPicker = picker;
                _aui_iconPickers.push(picker);
            });
    }


    function aui_modal_iframe($title,$url,$footer,$dismissible,$class,$dialog_class,$body_class,responsive){
        if(!$body_class){$body_class = 'p-0';}
        var wClass = 'text-center position-absolute w-100 text-dark overlay overlay-white p-0 m-0 d-flex justify-content-center align-items-center';
        var wStyle = '';
        var sStyle = '';
        var $body = "", sClass = "w-100 p-0 m-0";
        if (responsive) {
            $body += '<div class="embed-responsive embed-responsive-16by9 ratio ratio-16x9">';
            wClass += ' h-100';
            sClass += ' embed-responsive-item';
        } else {
            wClass += ' vh-100';
            sClass += ' vh-100';
            wStyle += ' height: 90vh !important;';
            sStyle += ' height: 90vh !important;';
        }
        $body += '<div class="ac-preview-loading ' + wClass + '" style="left:0;top:0;' + wStyle + '"><div class="spinner-border" role="status"></div></div>';
        $body += '<iframe id="embedModal-iframe" class="' + sClass + '" style="' + sStyle + '" src="" width="100%" height="100%" frameborder="0" allowtransparency="true"></iframe>';
        if (responsive) {
            $body += '</div>';
        }
        $m = aui_modal($title,$body,$footer,$dismissible,$class,$dialog_class,$body_class);

        const auiModal = document.getElementById('aui-modal');
        auiModal.addEventListener( 'shown.bs.modal', function ( e ) {
            // Get references to the iframe and loading indicator(s)
            const iframe = document.getElementById('embedModal-iframe');
            const loaders = document.querySelectorAll('.ac-preview-loading');

            // Show the loading indicator
            loaders.forEach(el => el.classList.add('d-flex'));
            // Set the iframe’s src
            iframe.src = $url;
            // Once the iframe has finished loading…
            iframe.addEventListener('load', () => {
                // Hide the loading indicator
                loaders.forEach(el => {
                    el.classList.remove('d-flex');
                    el.classList.add('d-none');
                });
            });
        });

        return $m;
    }

    function aui_modal($title, $body, $footer, $dismissible, $class, $dialog_class, $body_class) {
        // defaults
        if (!$class)        { $class = ''; }
        if (!$dialog_class) { $dialog_class = ''; }
        if (!$body)         { $body = '<div class="text-center"><div class="spinner-border" role="status"></div></div>'; }

        // remove any existing modal + backdrop
        document.querySelectorAll('.aui-modal').forEach(el => el.remove());
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.style.overflow     = '';
        document.body.style.paddingRight = '';

        // build the modal HTML
        var $modal = '';
        $modal += '<div id="aui-modal" class="modal aui-modal fade shadow bsui ' + $class + '" tabindex="-1">';
        $modal +=   '<div class="modal-dialog modal-dialog-centered ' + $dialog_class + '">';
        $modal +=     '<div class="modal-content border-0 shadow">';

        // header
        if ($title) {
            $modal += '<div class="modal-header">';
            $modal +=   '<h5 class="modal-title">' + $title + '</h5>';
            if ($dismissible) {
                $modal += '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            }
            $modal += '</div>';
        }

        // body
        $modal += '<div class="modal-body ' + $body_class + '">';
        $modal +=   $body;
        $modal += '</div>';

        // footer
        if ($footer) {
            $modal += '<div class="modal-footer">';
            $modal +=   $footer;
            $modal += '</div>';
        }

        $modal +=     '</div>'; // .modal-content
        $modal +=   '</div>';   // .modal-dialog
        $modal += '</div>';     // #aui-modal

        // append to body
        document.body.insertAdjacentHTML('beforeend', $modal);

        // init & show via Bootstrap 5
        const ayeModal = new bootstrap.Modal(document.querySelector('.aui-modal'), {});
        ayeModal.show();
    }


    /**
     * Show / hide fields depending on conditions.
     */
    function aui_conditional_fields(form) {
        // allow `form` to be either a selector string or a DOM node
        const root = (typeof form === 'string')
            ? document.querySelector(form)
            : form;
        if (!root) return;

        // find all conditional fields
        root.querySelectorAll('.aui-conditional-field').forEach((el) => {
            // read the data-element-require attribute
            let $element_require = el.getAttribute('data-element-require');

            if ($element_require) {
                // replace HTML-encoded quotes
                $element_require = $element_require
                    .replace("&#039;", "'")
                    .replace("&quot;", '"');

                // test the condition
                if (aui_check_form_condition($element_require, form)) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            }
        });
    }


    /**
     * Check form condition
     */
    function aui_check_form_condition(condition,form) {
        if (form) {
            condition = condition.replace(/\(form\)/g, "('"+form+"')");
        }
        return new Function("return " + condition+";")();
    }

    /**
     * A function to determine if a element is on screen.
     */
    // attach to Element.prototype so you can call element.aui_isOnScreen()
    Element.prototype.aui_isOnScreen = function() {

        var win = window;
        // get scroll offsets (fallback for older browsers)
        var scrollTop  = win.scrollY || document.documentElement.scrollTop;
        var scrollLeft = win.scrollX || document.documentElement.scrollLeft;

        // build the viewport bounds
        var viewport = {
            top:    scrollTop,
            left:   scrollLeft
        };
        viewport.right  = viewport.left + (win.innerWidth || document.documentElement.clientWidth);
        viewport.bottom = viewport.top  + (win.innerHeight || document.documentElement.clientHeight);

        // get element bounds (like offset() + outerWidth/outerHeight)
        var rect = this.getBoundingClientRect();
        var bounds = {
            left: rect.left + scrollLeft,
            top:  rect.top  + scrollTop
        };
        bounds.right  = bounds.left   + rect.width;
        bounds.bottom = bounds.top    + rect.height;


        return !(
            viewport.right  < bounds.left   ||
            viewport.left   > bounds.right  ||
            viewport.bottom < bounds.top    ||
            viewport.top    > bounds.bottom
        );
    };


    /**
     * Maybe show multiple carousel items if set to do so.
     */
    function aui_carousel_maybe_show_multiple_items($carousel) {
        var $items = {};
        var $item_count = 0;

        // maybe backup original slides
        if (!$carousel.querySelector('.carousel-inner-original')) {
            var origHTML = $carousel
                .querySelector('.carousel-inner')
                .innerHTML
                .replaceAll('carousel-item', 'not-carousel-item');
            $carousel.insertAdjacentHTML(
                'beforeend',
                '<div class="carousel-inner-original d-none">' +
                origHTML +
                '</div>'
            );
        }

        // collect each “not-carousel-item” into $items
        $carousel.querySelectorAll('.carousel-inner-original .not-carousel-item')
            .forEach(function(el) {
                $items[$item_count] = el.innerHTML;
                $item_count++;
            });

        // nothing to do?
        if (!$item_count) return;

        // SMALL SCREENS: restore original
        var vw = window.innerWidth || document.documentElement.clientWidth;
        if (vw <= 576) {
            var carouselInner = $carousel.querySelector('.carousel-inner');
            if (
                carouselInner.classList.contains('aui-multiple-items') &&
                $carousel.querySelector('.carousel-inner-original')
            ) {
                carouselInner.classList.remove('aui-multiple-items');
                var restored = $carousel
                    .querySelector('.carousel-inner-original')
                    .innerHTML
                    .replaceAll('not-carousel-item', 'carousel-item');
                carouselInner.innerHTML = restored;
                $carousel
                    .querySelectorAll('.carousel-indicators li')
                    .forEach(li => li.classList.remove('d-none'));
            }

            // LARGER SCREENS: rebuild grouped slides
        } else {
            var $md_count      = parseInt($carousel.getAttribute('data-limit_show'), 10)  || 0;
            var $md_cols_count = parseInt($carousel.getAttribute('data-cols_show'), 10)   || 0;
            var $new_items     = '';
            var $new_items_count = 0;
            var $new_item_count  = 0;
            var $closed = true;

            // loop through each original item by index
            for (var index = 0; index < $item_count; index++) {
                // close previous group?
                if (index !== 0 && Number.isInteger(index / $md_count)) {
                    $new_items += '</div></div>';
                    $closed = true;
                }
                // open a new carousel-item + row
                if (index === 0 || Number.isInteger(index / $md_count)) {
                    var $row_cols_class = $md_cols_count
                        ? ' g-lg-4 g-3 row-cols-1 row-cols-lg-' + $md_cols_count
                        : '';
                    var $active = index === 0 ? 'active' : '';
                    $new_items +=
                        '<div class="carousel-item ' + $active + '">' +
                        '<div class="row' + $row_cols_class + '">';
                    $closed = false;
                    $new_items_count++;
                    $new_item_count = 0;
                }
                // add the actual content column
                $new_items += '<div class="col">' + $items[index] + '</div>';
                $new_item_count++;
            }

            // pad empty cols and close final group
            if (!$closed) {
                var $placeholder_count = $md_count - $new_item_count;
                while ($placeholder_count > 0) {
                    $new_items += '<div class="col"></div>';
                    $placeholder_count--;
                }
                $new_items += '</div></div>';
            }

            // replace inner with grouped slides
            var carouselInner = $carousel.querySelector('.carousel-inner');
            carouselInner.classList.add('aui-multiple-items');
            carouselInner.innerHTML = $new_items;

            // fix any lazy-load images in the active slide
            $carousel.querySelectorAll('.carousel-item.active img')
                .forEach(function(img) {
                    var real_srcset = img.getAttribute('data-srcset');
                    if (real_srcset && !img.getAttribute('srcset')) {
                        img.setAttribute('srcset', real_srcset);
                    }
                    var real_src = img.getAttribute('data-src');
                    if (real_src && !img.getAttribute('srcset')) {
                        img.setAttribute('src', real_src);
                    }
                });

            // hide extra indicators beyond the last slide
            var $hide_count = $new_items_count - 1;
            $carousel.querySelectorAll('.carousel-indicators li')
                .forEach(function(li, i) {
                    if (i > $hide_count) li.classList.add('d-none');
                });
        }

        // fire the same custom event
        var evt = new Event('aui_carousel_multiple');
        window.dispatchEvent(evt);
    }


    /**
     * Init Multiple item carousels.
     */
    function aui_init_carousel_multiple_items() {
        // on resize, rerun for each carousel
        window.addEventListener('resize', function() {
            document.querySelectorAll('.carousel-multiple-items').forEach(function(el) {
                aui_carousel_maybe_show_multiple_items(el);
            });
        });

        // run now once on init
        document.querySelectorAll('.carousel-multiple-items').forEach(function(el) {
            aui_carousel_maybe_show_multiple_items(el);
        });
    }

    /**
     * Converts Bootstrap 5 dropdowns to open on hover, while maintaining full keyboard
     * accessibility. This script waits for the DOM to be fully loaded before running
     * to prevent race conditions with Bootstrap's own initialization.
     */
    function init_nav_sub_menus() {
        // Only run on non-touch devices
        if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
            return;
        }

        const dropdownTriggerList = document.querySelectorAll('[data-bs-toggle="dropdown"][data-bs-trigger="hover"]');

        dropdownTriggerList.forEach(dropdownTriggerEl => {
            // Get the Bootstrap instance *safely*
            const bsDropdown = bootstrap.Dropdown.getOrCreateInstance(dropdownTriggerEl);
            const parentNode = dropdownTriggerEl.parentNode;

            // --- Event Listeners ---

            dropdownTriggerEl.addEventListener('click', e => {
                if (e.currentTarget.getAttribute('href') === '#') {
                    e.preventDefault();
                }
            });

            // Show on mouseover and call .blur() to prevent the focus ring
            dropdownTriggerEl.addEventListener('mouseover', () => {
                bsDropdown.show();
                dropdownTriggerEl.blur();
            });

            // Show on focus for keyboard users
            dropdownTriggerEl.addEventListener('focus', () => {
                bsDropdown.show();
            });

            // Hide when mouse leaves the entire dropdown component
            parentNode.addEventListener('mouseleave', () => {
                bsDropdown.hide();
            });

            // Hide when focus moves to another element outside the component
            parentNode.addEventListener('focusout', (e) => {
                // The check for e.relatedTarget prevents the programmatic .blur() from closing the dropdown
                if (e.relatedTarget && !parentNode.contains(e.relatedTarget)) {
                    bsDropdown.hide();
                }
            });
        });

        // Add a single, global escape key handler
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    const instance = bootstrap.Dropdown.getInstance(menu.previousElementSibling);
                    if (instance) {
                        instance.hide();
                    }
                });
            }
        });
    }



    /**
     * Open a lightbox when an embed item is clicked.
     */
    function aui_lightbox_embed(link, ele) {
        ele.preventDefault();

        // remove existing modal
        document.querySelectorAll('.aui-carousel-modal').forEach(el => el.remove());

        // create and append modal wrapper
        const modalHTML = `
      <div class="modal fade aui-carousel-modal bsui" id="aui-carousel-modal" tabindex="-1" role="dialog" aria-labelledby="aui-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl mw-100">
          <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header">
              <h5 class="modal-title" id="aui-modal-title"></h5>
            </div>
            <div class="modal-body text-center">
              <i class="fas fa-circle-notch fa-spin fa-3x"></i>
            </div>
          </div>
        </div>
      </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // initialize Bootstrap modal
        const ayeModal = new bootstrap.Modal(document.querySelector('.aui-carousel-modal'), {});

        // clear iframe src on hide
        const myModalEl = document.getElementById('aui-carousel-modal');
        myModalEl.addEventListener('hidden.bs.modal', () => {
            document.querySelectorAll('.aui-carousel-modal iframe').forEach(iframe => {
                iframe.src = '';
            });
        });

        // find gallery container & hrefs
        const $container = link.closest('.aui-gallery');
        const $clicked_href = link.getAttribute('href');
        const $images = [];
        $container.querySelectorAll('.aui-lightbox-image, .aui-lightbox-iframe').forEach(a => {
            const href = a.getAttribute('href');
            if (href) $images.push(href);
        });

        if ($images.length) {
            let $carousel = `<div id="aui-embed-slider-modal" class="carousel slide">`;

            // indicators
            if ($images.length > 1) {
                $carousel += `<ol class="carousel-indicators position-fixed">`;
                $container.querySelectorAll('.aui-lightbox-image, .aui-lightbox-iframe').forEach((el, i) => {
                    const active = ($clicked_href === el.getAttribute('href')) ? 'active' : '';
                    $carousel += `<li data-bs-target="#aui-embed-slider-modal" data-bs-slide-to="${i}" class="${active}"></li>`;
                });
                $carousel += `</ol>`;
            }

            // determine RTL
            const rtlClass = document.documentElement.dir === 'rtl'
                ? 'justify-content-end'
                : 'justify-content-start';

            // carousel-inner start
            $carousel += `<div class="carousel-inner d-flex align-items-center ${rtlClass}">`;

            // image slides
            $container.querySelectorAll('.aui-lightbox-image').forEach(el => {
                const href = el.getAttribute('href');
                const active = ($clicked_href === href) ? 'active' : '';
                const cssHeight = window.innerWidth > window.innerHeight ? '90vh' : 'auto';

                // build srcset/sizes if present
                const imgElem = el.querySelector('img');
                const srcset = imgElem.getAttribute('srcset') || '';
                let sizes = '';
                if (srcset) {
                    const sources = srcset.split(',').map(s => {
                        const [url, desc] = s.trim().split(' ');
                        return {
                            width: parseInt(desc.replace('w',''), 10),
                            descriptor: desc.replace('w','px')
                        };
                    }).sort((a, b) => b.width - a.width);

                    sizes = sources
                        .map((src, idx, arr) =>
                            idx === 0
                                ? `${src.descriptor}`
                                : `(max-width: ${src.width - 1}px) ${arr[idx-1].descriptor}`
                        )
                        .reverse()
                        .join(', ');
                }

                // image tag
                $carousel += `
          <div class="carousel-item ${active}">
            <div>
              <img
                src="${href}"
                ${srcset ? `srcset="${srcset}" sizes="${sizes}"` : ''}
                class="mx-auto d-block w-auto rounded"
                style="max-height:${cssHeight};max-width:98%;"
              >
        `;

                // captions
                const cap = el.parentElement.querySelector('.carousel-caption');
                const fig = el.parentElement.querySelector('.figure-caption');
                if (cap) {
                    $carousel += cap.cloneNode(true).outerHTML.replace(/sr-only|visually-hidden/g, '');
                } else if (fig) {
                    const cloned = fig.cloneNode(true);
                    cloned.classList.add('carousel-caption');
                    cloned.classList.remove('sr-only', 'visually-hidden');
                    $carousel += cloned.outerHTML;
                }

                $carousel += `</div></div>`;
            });

            // iframe slides
            $container.querySelectorAll('.aui-lightbox-iframe').forEach(el => {
                const href = el.getAttribute('href');
                const active = ($clicked_href === href) ? 'active' : '';
                const cssHeight = window.innerWidth > window.innerHeight ? '90vh' : 'auto';
                const styleWidth = $images.length > 1 ? 'max-width:70%;' : '';

                $carousel += `
          <div class="carousel-item ${active}">
            <div class="modal-xl mx-auto ratio ratio-16x9" style="max-height:${cssHeight};${styleWidth}">
              <div class="ac-preview-loading text-light d-none" style="left:0;top:0;height:${cssHeight}">
                <div class="spinner-border m-auto" role="status"></div>
              </div>
              <iframe
                class="aui-carousel-iframe"
                style="height:${cssHeight}"
                src=""
                data-src="${href}?rel=0&amp;showinfo=0&amp;modestbranding=1&amp;autoplay=1"
                allow="autoplay"
              ></iframe>
            </div>
          </div>`;
            });

            $carousel += `</div>`; // end .carousel-inner

            // controls
            if ($images.length > 1) {
                $carousel += `
          <a class="carousel-control-prev" href="#aui-embed-slider-modal" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </a>
          <a class="carousel-control-next" href="#aui-embed-slider-modal" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </a>`;
            }

            $carousel += `</div>`; // end #aui-embed-slider-modal

            // inject content & show
            const closeBtn = `
        <button
          type="button"
          class="btn-close btn-close-white text-end position-fixed"
          style="right:20px;top:10px;z-index:1055;"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>`;
            document.querySelector('.aui-carousel-modal .modal-content').innerHTML = closeBtn + $carousel;

            ayeModal.show();

            // enable touch swipe
            if ('ontouchstart' in document.documentElement || navigator.maxTouchPoints > 0) {
                try {
                    new bootstrap.Carousel('#aui-embed-slider-modal');
                } catch (e) { /* ignore */ }
            }
        }
    }


    /**
     * Init lightbox embed.
     */
    function aui_init_lightbox_embed() {
        document
            .querySelectorAll('.aui-lightbox-image, .aui-lightbox-iframe')
            .forEach(el => {
                // overwrite any previous click handler exactly like `.off('click').on('click',…)`
                el.onclick = function(event) {
                    aui_lightbox_embed(this, event);
                };
            });
    }


    /**
     * Init modal iframe.
     */
    function aui_init_modal_iframe() {
        document
            .querySelectorAll('.aui-has-embed, [data-aui-embed="iframe"]')
            .forEach(el => {
                // only bind once, and only if there's a data-embed-url
                const url = el.dataset.embedUrl;
                if (url && !el.classList.contains('aui-modal-iframed')) {
                    el.classList.add('aui-modal-iframed');
                    el.addEventListener('click', function(e) {
                        aui_modal_iframe(
                            '',
                            url,
                            '',
                            true,
                            '',
                            'modal-lg',
                            'aui-modal-iframe p-0',
                            true
                        );
                        e.preventDefault();
                        e.stopPropagation();
                    });
                }
            });
    }


    /**
     * Show a toast.
     */
        // global flag
    var $aui_doing_toast = false;

    function aui_toast($id, $type, $title, $title_small, $body, $time, $can_close) {
        if ($aui_doing_toast) {
            setTimeout(function() {
                aui_toast($id, $type, $title, $title_small, $body, $time, $can_close);
            }, 500);
            return;
        }
        $aui_doing_toast = true;

        // defaults
        if ($can_close == null) { $can_close = false; }
        if (!$time) { $time = 3000; }

        // if already exists, remove it before creating the new one
        if ($id && document.getElementById($id)) {
            var existing = document.getElementById($id);
            existing.remove();
        }

        // unique id
        var uniqid = $id || String(Date.now());

        // styling vars
        var op = '', tClass = '', thClass = '', icon = '';
        switch ($type) {
            case 'success':
                op = 'opacity:.92;';
                tClass = 'alert bg-success w-auto';
                thClass = 'bg-transparent border-0 text-white';
                icon = "<div class='fs-5 m-0 p-0'><i class='fas fa-check-circle me-2'></i></div>";
                break;
            case 'error':
            case 'danger':
                op = 'opacity:.92;';
                tClass = 'alert bg-danger w-auto';
                thClass = 'bg-transparent border-0 text-white';
                icon = "<div class='s-5 m-0 p-0'><i class='far fa-times-circle me-2'></i></div>";
                break;
            case 'info':
                op = 'opacity:.92;';
                tClass = 'alert bg-info w-auto';
                thClass = 'bg-transparent border-0 text-white';
                icon = "<div class='fs-5 m-0 p-0'><i class='fas fa-info-circle me-2'></i></div>";
                break;
            case 'warning':
                op = 'opacity:.92;';
                tClass = 'alert bg-warning w-auto';
                thClass = 'bg-transparent border-0 text-dark';
                icon = "<div class='fs-5 m-0 p-0'><i class='fas fa-exclamation-triangle me-2'></i></div>";
                break;
        }

        // ensure container exists
        if (!document.getElementById('aui-toasts')) {
            var outer = document.createElement('div');
            outer.id = 'aui-toasts';
            outer.classList.add('bsui');
            var inner = document.createElement('div');
            inner.className = 'position-fixed aui-toast-bottom-right pr-3 pe-3 mb-1';
            inner.setAttribute('style', 'z-index:500000;right:0;bottom:0;' + op);
            outer.appendChild(inner);
            document.body.appendChild(outer);
        }
        var container = document.querySelector('.aui-toast-bottom-right');

        // build toast element
        var toastEl = document.createElement('div');
        toastEl.id = uniqid;
        toastEl.className = 'toast fade hide shadow hover-shadow ' + tClass;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        // header
        if ($type || $title || $title_small) {
            var header = document.createElement('div');
            header.className = 'toast-header ' + thClass;

            if (icon) {
                header.insertAdjacentHTML('beforeend', icon);
            }

            if ($title) {
                var strong = document.createElement('strong');
                strong.className = 'me-auto';
                strong.innerHTML = $title;
                header.appendChild(strong);
            }

            if ($title_small) {
                var small = document.createElement('small');
                small.textContent = $title_small;
                header.appendChild(small);
            }

            if ($can_close) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ms-2 mb-1 btn-close';
                btn.setAttribute('data-bs-dismiss', 'toast');
                btn.setAttribute('aria-label', 'Close');
                header.appendChild(btn);
            }

            toastEl.appendChild(header);
        }

        // The rest of the function continues as before to create and show the new element
        if ($body) {
            var bodyEl = document.createElement('div');
            bodyEl.className = 'toast-body';
            bodyEl.innerHTML = $body;
            toastEl.appendChild(bodyEl);
        }

        container.appendChild(toastEl);

        var toast = bootstrap.Toast.getOrCreateInstance(toastEl, {
            animation: true,
            autohide: ($time > 0),
            delay: $time
        });

        toast.show();
        setTimeout(function() { $aui_doing_toast = false; }, 500);
    }


    /**
     * Animate a number.
     */
    function aui_init_counters() {

        const animNum = EL => {
            if (EL._isAnimated) return;   // Animate only once!
            EL._isAnimated = true;

            const end      = parseFloat(EL.dataset.auiend);
            const start    = parseFloat(EL.dataset.auistart);
            const duration = EL.dataset.auiduration
                ? Math.abs(parseInt(EL.dataset.auiduration, 10))
                : 2000;
            const seperator= EL.dataset.auisep || '';

            // “swing” easing ≈ easeInOut
            const easeSwing = p => 0.5 - Math.cos(p * Math.PI) / 2;

            let startTime = null;
            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                const elapsed = timestamp - startTime;
                const progress= Math.min(elapsed / duration, 1);
                const eased   = easeSwing(progress);
                const nowVal  = start + (end - start) * eased;
                // build text + HTML
                const text    = seperator
                    ? Math.ceil(nowVal).toLocaleString('en-US')
                    : Math.ceil(nowVal);
                let html      = seperator
                    ? text.split(',').map(n => `<span class="count">${n}</span>`).join(',')
                    : text;
                if (seperator && seperator !== ',') {
                    html.replace(',', seperator);
                }
                EL.innerHTML = html;

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
        };

        const inViewport = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animNum(entry.target);
                }
            });
        };

        // Observe all elements with [data-auicounter]
        document
            .querySelectorAll('[data-auicounter]')
            .forEach(EL => {
                const obs = new IntersectionObserver(inViewport);
                obs.observe(EL);
            });
    }



    /**
     * Initiate all AUI JS.
     */
    function aui_init(){

        // init counters
        aui_init_counters();

        // nav menu submenus
       // init_nav_sub_menus();

        // init tooltips
        aui_init_tooltips();

        // init select2
       // aui_init_select2();

        //init choices
        aui_init_choices();

        // init flatpickr
        aui_init_flatpickr();

        // init iconpicker
        aui_init_iconpicker();

        // init Greedy nav
        aui_init_greedy_nav();

        // Set times to time ago
        aui_time_ago('timeago');

        // init multiple item carousels
        aui_init_carousel_multiple_items();

        // init lightbox embeds
        aui_init_lightbox_embed();

        /* Init modal iframe */
        aui_init_modal_iframe();
    }

    // run on window loaded
    window.addEventListener('load', function() {
        aui_init();
    });



    window.addEventListener('DOMContentLoaded', () => {
        // nav menu submenus
        init_nav_sub_menus();
        init_nav_sub_menus(); // for some reason this only fully works when called twice, we need to check this // @todo
    });

    /* Fix modal background scroll on iOS mobile device */
    (function() {
        let auiInitIosDone = false;

        function aui_init_ios_modal_scroll() {
            if (auiInitIosDone) return;
            auiInitIosDone = true;

            const ua = navigator.userAgent.toLowerCase();
            const isiOS = /(iphone|ipod|ipad)/.test(ua);
            if (isiOS) {
                let pS = 0;
                const pM = parseFloat(getComputedStyle(document.body).marginTop);

                document.addEventListener('show.bs.modal', () => {
                    pS = window.scrollY;
                    Object.assign(document.body.style, {
                        marginTop: `-${pS}px`,
                        overflow: 'hidden',
                        position: 'fixed'
                    });
                });

                document.addEventListener('hidden.bs.modal', () => {
                    Object.assign(document.body.style, {
                        marginTop: `${pM}px`,
                        overflow: 'visible',
                        position: 'inherit'
                    });
                    window.scrollTo(0, pS);
                });
            }

            document.addEventListener('slide.bs.carousel', event => {
                const related = event.relatedTarget;
                if (!related) return;

                const modalEl = related.closest('.aui-carousel-modal');
                if (
                    !modalEl ||
                    modalEl.offsetParent === null ||
                    !modalEl.querySelector('iframe.aui-carousel-iframe')
                ) return;

                // clear old iframe
                modalEl
                    .querySelectorAll('.carousel-item.active iframe.aui-carousel-iframe')
                    .forEach(iframe => {
                        if (iframe.src) {
                            iframe.dataset.src = iframe.src;
                            iframe.src = '';
                        }
                    });

                // show loader + set new src
                const loading = related.querySelector('.ac-preview-loading');
                if (loading) {
                    loading.classList.replace('d-none','d-flex');
                }
                const cIframe = related.querySelector('iframe.aui-carousel-iframe');
                if (cIframe && !cIframe.src && cIframe.dataset.src) {
                    cIframe.src = cIframe.dataset.src;
                }
                if (cIframe) {
                    cIframe.addEventListener('load', () => {
                        setTimeout(() =>
                                modalEl
                                    .querySelectorAll('.ac-preview-loading')
                                    .forEach(el => el.classList.replace('d-flex','d-none')),
                            1250);
                    });
                }
            });
        }

        // Self‐execute on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', aui_init_ios_modal_scroll);
        } else {
            aui_init_ios_modal_scroll();
        }
    })();


    /**
     * Show a "confirm" dialog
     *
     * @param {string} message The message to display to the user
     * @param {string} okButtonText OPTIONAL - The OK button text, defaults to "Yes"
     * @param {string} cancelButtonText OPTIONAL - The Cancel button text, defaults to "No"
     * @param isDelete
     * @param large
     * @returns {Q.Promise<boolean>} A promise of a boolean value
     */
    var aui_confirm = function (message, okButtonText, cancelButtonText, isDelete, large) {
        okButtonText     = okButtonText     || 'Yes';
        cancelButtonText = cancelButtonText || 'Cancel';
        message          = message          || 'Are you sure?';
        var sizeClass    = large ? '' : 'modal-sm';
        var btnClass     = isDelete ? 'btn-danger' : 'btn-primary';

        // Simple “deferred” replacement
        deferred = {};
        deferred.promise = new Promise(function (resolve, reject) {
            deferred.resolve = resolve;
            deferred.reject  = reject;
        });

        // build the same markup
        var $body = '';
        $body += "<h3 class='h4 py-3 text-center text-dark'>" + message + "</h3>";
        $body += "<div class='d-flex'>";
        $body +=
            "<button class='btn btn-outline-secondary w-50 btn-round' data-bs-dismiss='modal' " +
            "onclick='deferred.resolve(false);'>" +
            cancelButtonText +
            '</button>';
        $body +=
            "<button class='btn " +
            btnClass +
            " ms-2 w-50 btn-round' data-bs-dismiss='modal' " +
            "onclick='deferred.resolve(true);'>" +
            okButtonText +
            '</button>';
        $body += '</div>';

        // call your existing function to show the modal
        aui_modal('', $body, '', false, '', sizeClass);

        // return the native Promise so callers can await it
        return deferred.promise;
    };


    /**
     * Flip the color scheem on scroll
     * @param $value
     * @param $iframe
     */
    function aui_flip_color_scheme_on_scroll($value, $iframe){
        if(!$value) $value = window.scrollY;

        var navbar = $iframe ?  $iframe.querySelector('.color-scheme-flip-on-scroll') : document.querySelector('.color-scheme-flip-on-scroll');
        if (navbar == null) return;

        let cs_original = navbar.dataset.cso;
        let cs_scroll = navbar.dataset.css;

        if (!cs_scroll && !cs_original) {
            if( navbar.classList.contains('navbar-light') ){
                cs_original = 'navbar-light';
                cs_scroll  = 'navbar-dark';
            }else if( navbar.classList.contains('navbar-dark') ){
                cs_original = 'navbar-dark';
                cs_scroll  = 'navbar-light';
            }

            navbar.dataset.cso = cs_original;
            navbar.dataset.css = cs_scroll;
        }

        if($value > 0 || navbar.classList.contains('nav-menu-open') ){
            navbar.classList.remove(cs_original);
            navbar.classList.add(cs_scroll);
        }else{
            navbar.classList.remove(cs_scroll);
            navbar.classList.add(cs_original);
        }
    }

    /**
     * Add a window scrolled data element.
     */
    window.onscroll = function () {
        aui_set_data_scroll();
        aui_flip_color_scheme_on_scroll();
    };

    /**
     * Set scroll data element.
     */
    function aui_set_data_scroll(){
        document.documentElement.dataset.scroll = window.scrollY;
    }

    // call data scroll function ASAP.
    aui_set_data_scroll();
    aui_flip_color_scheme_on_scroll();

	<?php
	// FSE tweaks.
	if(!empty($_REQUEST['postType']) || !empty($_REQUEST['canvas']) ){ ?>
    function aui_fse_set_data_scroll() {
        console.log('init scroll');
        let Iframe = document.getElementsByClassName("edit-site-visual-editor__editor-canvas");
        if( Iframe[0] === undefined ){ return; }
        let iframe_doc = Iframe[0].contentWindow ? Iframe[0].contentWindow.document : Iframe[0].contentDocument;
        Iframe[0].contentWindow.onscroll = function () {
            iframe_doc.documentElement.dataset.scroll = Iframe[0].contentWindow.scrollY;
            aui_flip_color_scheme_on_scroll(Iframe[0].contentWindow.scrollY,iframe_doc);
        };
    }

    setTimeout(function(){
        aui_fse_set_data_scroll();
    }, 3000);

    // fire when URL changes also.
    let FSElastUrl = location.href;
    new MutationObserver(() => {
        const url = location.href;
        if (url !== FSElastUrl) {
            FSElastUrl = url;
            aui_fse_set_data_scroll();
            // fire a second time incase of load delays.
            setTimeout(function(){
                aui_fse_set_data_scroll();
            }, 2000);
        }
    }).observe(document, {subtree: true, childList: true});


    /**
     * Convert hex color to rgb values.
     *
     * @param hex
     * @returns {string|null}
     */
    function aui_fse_hexToRgb(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex.trim().substring(0, 7));
        return result ? parseInt(result[1], 16) +','+parseInt(result[2], 16)+','+parseInt(result[3], 16) : null;
    }

    /**
     * update colors as the style colour pallet is changed
     * @param $color
     */
    function aui_fse_sync_site_colors($color) {
        // helper to grab the CSS custom-property from inside the site editor iframe
        const getColorHex = () => {
            const canvas = document.querySelector('.edit-site-visual-editor__editor-canvas');
            if (!canvas || !canvas.contentDocument) return '';
            const wrapper = canvas.contentDocument.querySelector('.editor-styles-wrapper');
            return wrapper
                ? window.getComputedStyle(wrapper).getPropertyValue(`--wp--preset--color--${$color}`)
                : '';
        };

        // initial value
        let colorHex = getColorHex();

        // subscribe to WP data store changes
        wp.data.subscribe(() => {
            const newColorHex = getColorHex();

            // only update when it actually changes
            if (newColorHex && newColorHex !== colorHex) {
                const canvas = document.querySelector('.edit-site-visual-editor__editor-canvas');
                if (canvas && canvas.contentDocument) {
                    const body = canvas.contentDocument.querySelector('body');
                    if (body) {
                        body.style.setProperty(
                            `--bs-${$color}-rgb`,
                            aui_fse_hexToRgb(newColorHex)
                        );
                    }
                }
            }

            // store for next comparison
            colorHex = newColorHex;
        });
    }


    /**
     * update colors as the style colour pallet is changed
     * @param $color
     */
    function aui_fse_sync_site_typography(){
        const getGlobalStyles = () => {
            const { select } = wp.data;
            const settings = select('core/block-editor').getSettings();

            return ( settings && settings.styles && settings.styles[3].css ? settings.styles[3].css : null );
        };

        // set the initial styles
        let Styles = getGlobalStyles();

        wp.data.subscribe(() => {
            // get the current styles
            const newStyles = getGlobalStyles();

            // only do something if newStyles has changed.
            if( newStyles && Styles !== newStyles ) {
                // heading sizes
                aui_updateCssRule('body.editor-styles-wrapper h1', 'font-size', aui_parseCSS(newStyles, 'h1', 'font-size'));
                aui_updateCssRule('body.editor-styles-wrapper h2', 'font-size', aui_parseCSS(newStyles, 'h2', 'font-size'));
                aui_updateCssRule('body.editor-styles-wrapper h3', 'font-size', aui_parseCSS(newStyles, 'h3', 'font-size'));
                aui_updateCssRule('body.editor-styles-wrapper h4', 'font-size', aui_parseCSS(newStyles, 'h4', 'font-size'));
                aui_updateCssRule('body.editor-styles-wrapper h5', 'font-size', aui_parseCSS(newStyles, 'h5', 'font-size'));
                aui_updateCssRule('body.editor-styles-wrapper h6', 'font-size', aui_parseCSS(newStyles, 'h6', 'font-size'));

                // ALl Headings
               aui_updateCssRule('body.editor-styles-wrapper h1, body.editor-styles-wrapper h2, body.editor-styles-wrapper h3, body.editor-styles-wrapper h4, body.editor-styles-wrapper h5, body.editor-styles-wrapper h6', 'font-family', aui_parseCSS(newStyles, 'h1, h2, h3, h4, h5, h6', 'font-family'));

                // individual headings
                aui_updateCssRule('body.editor-styles-wrapper h1', 'font-family', aui_parseCSS(newStyles, 'h1{', 'font-family'));
                aui_updateCssRule('body.editor-styles-wrapper h2', 'font-family', aui_parseCSS(newStyles, 'h2{', 'font-family'));
                aui_updateCssRule('body.editor-styles-wrapper h3', 'font-family', aui_parseCSS(newStyles, 'h3{', 'font-family'));
                aui_updateCssRule('body.editor-styles-wrapper h4', 'font-family', aui_parseCSS(newStyles, 'h4{', 'font-family'));
                aui_updateCssRule('body.editor-styles-wrapper h5', 'font-family', aui_parseCSS(newStyles, 'h5{', 'font-family'));
                aui_updateCssRule('body.editor-styles-wrapper h6', 'font-family', aui_parseCSS(newStyles, 'h6{', 'font-family'));

                // console.log(aui_parseCSS(newStyles, 'h2{', 'font-family'));

                // color
                aui_updateCssRule('body.editor-styles-wrapper h1, body.editor-styles-wrapper h2, body.editor-styles-wrapper h3, body.editor-styles-wrapper h4, body.editor-styles-wrapper h5, body.editor-styles-wrapper h6', 'color', aui_parseCSS(newStyles, 'h1, h2, h3, h4, h5, h6', 'color'));

                aui_updateCssRule('body.editor-styles-wrapper h1', 'color', aui_parseCSS(newStyles, 'h1{', 'color'));
                aui_updateCssRule('body.editor-styles-wrapper h2', 'color', aui_parseCSS(newStyles, 'h2{', 'color'));
                aui_updateCssRule('body.editor-styles-wrapper h3', 'color', aui_parseCSS(newStyles, 'h3{', 'color'));
                aui_updateCssRule('body.editor-styles-wrapper h4', 'color', aui_parseCSS(newStyles, 'h4{', 'color'));
                aui_updateCssRule('body.editor-styles-wrapper h5', 'color', aui_parseCSS(newStyles, 'h5{', 'color'));
                aui_updateCssRule('body.editor-styles-wrapper h6', 'color', aui_parseCSS(newStyles, 'h6{', 'color'));



                //background
                aui_updateCssRule('body.editor-styles-wrapper h1, body.editor-styles-wrapper h2, body.editor-styles-wrapper h3, body.editor-styles-wrapper h4, body.editor-styles-wrapper h5, body.editor-styles-wrapper h6', 'background', aui_parseCSS(newStyles, 'h1, h2, h3, h4, h5, h6', 'background'));

                aui_updateCssRule('body.editor-styles-wrapper h1', 'background', aui_parseCSS(newStyles, 'h1{', 'background'));
                aui_updateCssRule('body.editor-styles-wrapper h2', 'background', aui_parseCSS(newStyles, 'h2{', 'background'));
                aui_updateCssRule('body.editor-styles-wrapper h3', 'background', aui_parseCSS(newStyles, 'h3{', 'background'));
                aui_updateCssRule('body.editor-styles-wrapper h4', 'background', aui_parseCSS(newStyles, 'h4{', 'background'));
                aui_updateCssRule('body.editor-styles-wrapper h5', 'background', aui_parseCSS(newStyles, 'h5{', 'background'));
                aui_updateCssRule('body.editor-styles-wrapper h6', 'background', aui_parseCSS(newStyles, 'h6{', 'background'));



                //                console.log('Font size of h2 is:', fontSize);
            }

            // update the newStyles variable.
            Styles = newStyles;


        });
    }

    setTimeout(function(){
        aui_sync_admin_styles();
    }, 10000);

    function aui_sync_admin_styles(){
        aui_fse_sync_site_colors('primary');
        aui_fse_sync_site_colors('danger');
        aui_fse_sync_site_colors('warning');
        aui_fse_sync_site_colors('info');
        aui_fse_sync_site_typography();
    }



    function aui_parseCSS(cssString, selector, property) {
        // Split the CSS string on closing braces
        const rules = cssString.split('}');

        // Search for the selector and property
        for (let rule of rules) {
            if (rule.includes(selector) && rule.includes(property)) {
                // Extract the rule's content
                const ruleContent = rule.split('{')[1];

                // Split properties and search for the desired property
                const properties = ruleContent.split(';');
                for (let prop of properties) {
                    if (prop.includes(property)) {
                        // Extract and return the property value
                        return prop.split(':')[1].trim();
                    }
                }
            }
        }

        return null;
    }

    // Function to update a CSS rule
    function aui_updateCssRule(selector, property, value) {
        // find the inline‐CSS <style> in the site editor iframe
        var aui_inline_css, aui_inline_css_stylebook, styleSheet;

        var editorCanvas = document.querySelector('.edit-site-visual-editor__editor-canvas');
        if (editorCanvas && editorCanvas.contentDocument) {
            aui_inline_css = editorCanvas.contentDocument.getElementById('ayecode-ui-fse-inline-css');
        }

        // if not in the editor, try the style‐book iframe
        var styleBook = document.querySelector('.edit-site-style-book__iframe');
        if (styleBook && styleBook.contentDocument) {
            aui_inline_css_stylebook = styleBook.contentDocument.getElementById('ayecode-ui-fse-inline-css');
        }

        // choose the sheet
        if (aui_inline_css && aui_inline_css.sheet) {
            styleSheet = aui_inline_css.sheet;
        } else if (aui_inline_css_stylebook && aui_inline_css_stylebook.sheet) {
            styleSheet = aui_inline_css_stylebook.sheet;
        } else {
            return; // nothing to update
        }

        // get existing rules
        var rules = styleSheet.cssRules || styleSheet.rules;

        // try to find and update
        for (var i = 0; i < rules.length; i++) {
            if (rules[i].selectorText === selector) {
                rules[i].style[property] = value;
                return; // done
            }
        }

        // not found → insert new rule
        styleSheet.insertRule(
            selector + ' { ' + property + ': ' + value + '; }',
            rules.length
        );
    }


    <?php } ?>


</script>
