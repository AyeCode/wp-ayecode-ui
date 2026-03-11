/**
 * Inspiration taken from https://github.com/migliori/universal-icon-picker
 * Built to reduce wait times when working with react compared non react version.
 *
 * PORTAL VERSION: This version uses a React Portal to ensure the modal
 * displays correctly over the entire editor, including within the Site Editor's iframe.
 */
(function(wp) {
    // We add `createPortal` to our list of functions from wp.element
    const { createElement, useState, useEffect, useMemo, useCallback, Fragment, createPortal } = wp.element;

    // --- Dynamic Path Detection ---
    const scriptUrl = new URL(document.currentScript.src);
    const basePath = scriptUrl.pathname.substring(0, scriptUrl.pathname.lastIndexOf('/'));

    // --- Global cache for icon data ---
    window.myIconPickerCache = window.myIconPickerCache || {
        data: null,
        promise: null,
    };

    /**
     * The Modal Component (NOW WITH PORTAL LOGIC)
     */
    function IconPickerModal({ isOpen, onClose, onSelect }) {
        const [allIcons, setAllIcons] = useState(window.myIconPickerCache.data || {});
        const [isLoading, setIsLoading] = useState(!window.myIconPickerCache.data);
        const [activeLibrary, setActiveLibrary] = useState('all');
        const [searchTerm, setSearchTerm] = useState('');

        const iconLibraries = {
            solid: 'font-awesome-solid.min.json',
            regular: 'font-awesome-regular.min.json',
            brands: 'font-awesome-brands.min.json',
        };

        useEffect(() => {
            if (window.myIconPickerCache.data || window.myIconPickerCache.promise) {
                return;
            }
            async function fetchIcons() {
                try {
                    const libraryPromises = Object.entries(iconLibraries).map(async ([key, fileName]) => {
                        const response = await fetch(`${basePath}/../icons-libraries/${fileName}`);
                        if (!response.ok) throw new Error(`Failed to fetch ${fileName}`);
                        const data = await response.json();
                        return [key, data];
                    });
                    const loadedLibraries = await Promise.all(libraryPromises);
                    const iconData = Object.fromEntries(loadedLibraries);
                    window.myIconPickerCache.data = iconData;
                    setAllIcons(iconData);
                } catch (error) {
                    console.error("Error loading icon libraries:", error);
                } finally {
                    setIsLoading(false);
                    window.myIconPickerCache.promise = null;
                }
            }
            window.myIconPickerCache.promise = fetchIcons();
        }, []);

        const filteredIcons = useMemo(() => {
            if (isLoading || Object.keys(allIcons).length === 0) return [];
            let iconsToFilter = [];
            if (activeLibrary === 'all') {
                Object.values(allIcons).forEach(library => {
                    const libraryIcons = library.icons.map(iconName => ({ name: iconName, fullClass: `${library.prefix}${iconName}` }));
                    iconsToFilter.push(...libraryIcons);
                });
            } else if (allIcons[activeLibrary]) {
                const library = allIcons[activeLibrary];
                iconsToFilter = library.icons.map(iconName => ({ name: iconName, fullClass: `${library.prefix}${iconName}` }));
            }
            if (!searchTerm) return iconsToFilter;
            return iconsToFilter.filter(icon => icon.name.toLowerCase().includes(searchTerm.toLowerCase()));
        }, [isLoading, allIcons, activeLibrary, searchTerm]);

        const handleSelectAndClose = (iconClass) => {
            onClose();
            setTimeout(() => {
                onSelect(iconClass);
            }, 0);
        };

        if (!isOpen) {
            return null;
        }

        // The full markup for the modal and its backdrop
        const modalWithBackdrop = createElement(
            Fragment, null,
            // The dark backdrop
            createElement('div', {
                className: 'modal-backdrop fade show',
                style: { zIndex: 999990 }
            }),
            // The modal window
            createElement('div',{ className: 'bsui'},
            createElement('div', { className: 'modal fade show', style: { display: 'block', zIndex: 999999 }, role: 'dialog' },
                createElement('div', { className: 'modal-dialog modal-xl' },
                    createElement('div', { className: 'modal-content', style: { height: '85vh', display: 'flex', flexDirection: 'column' } },
                        createElement('div', { className: 'modal-header' },
                            createElement('h5', { className: 'modal-title' }, 'Select an Icon'),
                            createElement('button', { type: 'button', className: 'btn-close', onClick: onClose })
                        ),
                        createElement('div', { className: 'modal-body', style: { flex: '1 1 auto', overflow: 'hidden' } },
                            createElement('div', { className: 'row h-100' },
                                createElement('div', { className: 'col-md-3' },
                                    createElement('div', { className: 'nav flex-column nav-pills' },
                                        createElement('button', {
                                            className: `nav-link text-start ${activeLibrary === 'all' ? 'active' : ''}`,
                                            onClick: () => setActiveLibrary('all')
                                        }, 'All Icons'),
                                        Object.keys(iconLibraries).map(libKey =>
                                            createElement('button', {
                                                key: libKey,
                                                className: `nav-link text-start ${activeLibrary === libKey ? 'active' : ''}`,
                                                onClick: () => setActiveLibrary(libKey)
                                            }, libKey.charAt(0).toUpperCase() + libKey.slice(1))
                                        )
                                    )
                                ),
                                createElement('div', { className: 'col-md-9 d-flex flex-column h-100' },
                                    createElement('input', {
                                        type: 'text',
                                        className: 'form-control mb-3 py-3',
                                        placeholder: 'Search icons…',
                                        value: searchTerm,
                                        onChange: (e) => setSearchTerm(e.target.value)
                                    }),
                                    createElement('div', { className: 'flex-grow-1', style: { overflowY: 'auto' } },
                                        isLoading
                                            ? createElement('div', { className: 'd-flex justify-content-center pt-5' },
                                                createElement('div', { className: 'spinner-border', role: 'status' },
                                                    createElement('span', { className: 'visually-hidden' }, 'Loading...')
                                                )
                                            )
                                            : createElement('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(60px, 1fr))', gap: '8px', paddingRight: '15px' } },
                                                filteredIcons.map(iconObj =>
                                                    createElement('button', {
                                                            key: iconObj.fullClass,
                                                            className: 'btn btn-light border d-flex align-items-center justify-content-center',
                                                            style: { height: '60px' },
                                                            onClick: () => handleSelectAndClose(iconObj.fullClass),
                                                            title: iconObj.name
                                                        },
                                                        createElement('i', { className: iconObj.fullClass, style: { fontSize: '24px' } })
                                                    )
                                                )
                                            )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ));

        // Use the Portal to render the markup into the main document body
        return createPortal(modalWithBackdrop, document.body);
    }

    /**
     * The Picker Component (The Button)
     */
    function IconPickerWrapper(props) {
        const [isModalOpen, setIsModalOpen] = useState(false);

        const handleSelect = (iconClass) => {
            if (props.onSelect) {
                // If an onSelect callback is provided, use it.
                props.onSelect(iconClass);
            } else {
                // Otherwise, fall back to the default Gutenberg behavior.
                props.setAttributes({ [props.attributeName]: iconClass });
            }
        };

        return createElement(Fragment, null,
            createElement('div', { style: { display: 'inline-block', marginLeft: '-1px' } },
                createElement('button', {
                    id: `icon-picker-button-${props.uniqueId}`,
                    onClick: () => setIsModalOpen(true),
                    type: 'button',
                    className: 'components-button is-secondary is-compact'
                }, createElement('i', { className: props.value || 'fas fa-icons' }))
            ),

            // The modal now handles its own backdrop, so we only need to render the modal component itself.
            isModalOpen && createElement(IconPickerModal, {
                isOpen: isModalOpen,
                onClose: () => setIsModalOpen(false),
                onSelect: handleSelect,
            })
        );
    }

    // Expose the necessary components to the window object
    window.sdBlockTools = window.sdBlockTools || {};
    window.sdBlockTools.IconPickerButton = IconPickerWrapper;
    window.sdBlockTools.IconPickerModal = IconPickerModal; // Also expose modal directly

})(window.wp);