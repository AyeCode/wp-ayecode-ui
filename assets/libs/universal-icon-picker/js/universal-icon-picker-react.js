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

    // --- Global cache for icon data ---
    window.myIconPickerCache = window.myIconPickerCache || {
        data: null,
        promise: null,
    };

    // Static Pro icon generation maps (memoized for performance)
    const PRO_FAMILIES = {
        'classic': '',
        'duotone': 'fa-duotone',
        'sharp': 'fa-sharp',
        'sharp-duotone': 'fa-sharp-duotone'
    };

    const PRO_WEIGHTS = {
        'solid': 'fa-solid',
        'regular': 'fa-regular',
        'light': 'fa-light',
        'thin': 'fa-thin'
    };

    /**
     * Search Pro icons via API (progressive enhancement)
     */
    async function searchProIconsAPI(searchTerm, styleFamily, weight, cache) {
        // Only search if wpApiSettings is available
        if (!window.wpApiSettings || !searchTerm) return [];

        const cacheKey = `${styleFamily}-${weight}-${searchTerm.toLowerCase()}`;

        // Return cached results if available
        if (cache[cacheKey]) {
            return cache[cacheKey];
        }

        try {
            const url = new URL(window.wpApiSettings.root + 'ayecode/fontawesome/v1/search');
            url.searchParams.append('style', styleFamily);
            url.searchParams.append('weight', weight);
            url.searchParams.append('search', searchTerm);
            url.searchParams.append('_wpnonce', window.wpApiSettings.nonce);

            const response = await fetch(url);
            if (!response.ok) return [];

            const data = await response.json();
            const icons = data.icons || [];

            // Cache results
            cache[cacheKey] = icons;

            return icons;
        } catch (error) {
            // Fail silently - graceful degradation to local search only
            return [];
        }
    }

    /**
     * The Modal Component (NOW WITH PORTAL LOGIC)
     */
    function IconPickerModal({ isOpen, onClose, onSelect }) {
        const [allIcons, setAllIcons] = useState(window.myIconPickerCache.data || {});
        const [isLoading, setIsLoading] = useState(!window.myIconPickerCache.data);
        const [activeLibrary, setActiveLibrary] = useState('all');
        const [searchTerm, setSearchTerm] = useState('');
        const [scrollTop, setScrollTop] = useState(0);
        const scrollContainerRef = wp.element.useRef(null);

        // Pro Mode State
        const [activeWeight, setActiveWeight] = useState('solid');
        const [activeStyleFamily, setActiveStyleFamily] = useState('classic');

        // Pro API Search State
        const [apiEnhancedIcons, setApiEnhancedIcons] = useState([]);
        const proSearchCache = wp.element.useRef({}).current;

        useEffect(() => {
            if (window.myIconPickerCache.data || window.myIconPickerCache.promise) {
                return;
            }
            async function fetchIcons() {
                try {
                    // Get libraries from global settings (set via PHP filter)
                    const librariesToLoad = window.ayecodeFASettings?.libraries || [];

                    if (!librariesToLoad || librariesToLoad.length === 0) {
                        throw new Error('No icon libraries configured. Please define window.ayecodeFASettings.libraries.');
                    }

                    const libraryPromises = librariesToLoad.map(async (libraryUrl) => {
                        const fileName = libraryUrl.split('/').pop();
                        const response = await fetch(libraryUrl);
                        if (!response.ok) throw new Error(`Failed to fetch ${libraryUrl}`);
                        const data = await response.json();

                        // Store filename for Pro detection
                        data._fileName = fileName;

                        // Generate key from icon-style or filename
                        const key = data['icon-style'] || fileName.replace('font-awesome-', '').replace('.min.json', '').replace('.json', '');

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

        // Detect Pro mode and get pro library
        const { isProMode, proLibrary } = useMemo(() => {
            for (const library of Object.values(allIcons)) {
                const fileName = library._fileName || '';
                const isProFile = fileName.includes('pro') || fileName.includes('Pro');

                // Pro mode requires BOTH modifiers === true AND filename contains "pro"
                if (library.modifiers === true && isProFile) {
                    return { isProMode: true, proLibrary: library };
                }
            }
            return { isProMode: false, proLibrary: null };
        }, [allIcons]);

        // Reset search term and Pro state when modal opens
        useEffect(() => {
            if (isOpen) {
                setSearchTerm('');
                setScrollTop(0);
                setApiEnhancedIcons([]); // Clear API results
                if (isProMode) {
                    setActiveLibrary('classic');
                    setActiveWeight('solid');
                    setActiveStyleFamily('classic');
                }
            }
        }, [isOpen, isProMode]);

        // API enhancement for Pro mode search (progressive enhancement)
        useEffect(() => {
            if (!isProMode || !searchTerm || !['classic', 'duotone', 'sharp', 'sharp-duotone'].includes(activeLibrary)) {
                setApiEnhancedIcons([]);
                return;
            }

            // Debounce API call
            const timer = setTimeout(async () => {
                const apiResults = await searchProIconsAPI(searchTerm, activeStyleFamily, activeWeight, proSearchCache);
                setApiEnhancedIcons(apiResults);
            }, 300);

            return () => clearTimeout(timer);
        }, [searchTerm, activeStyleFamily, activeWeight, isProMode, activeLibrary, proSearchCache]);

        // Reset scroll when library or search changes
        useEffect(() => {
            if (scrollContainerRef.current) {
                scrollContainerRef.current.scrollTop = 0;
                setScrollTop(0);
            }
        }, [activeLibrary, searchTerm]);

        const filteredIcons = useMemo(() => {
            if (isLoading || Object.keys(allIcons).length === 0) return [];
            let iconsToFilter = [];

            // Helper to generate Pro icon class (uses memoized constants)
            const generateProIconClass = (baseIconName, styleFamily, weight) => {
                const parts = [PRO_FAMILIES[styleFamily], PRO_WEIGHTS[weight], `fa-${baseIconName}`].filter(Boolean);
                return parts.join(' ');
            };

            // Pro Mode: Progressive enhancement (name-match first, then merge API results)
            if (isProMode && ['classic', 'duotone', 'sharp', 'sharp-duotone'].includes(activeLibrary)) {
                const currentStyleFamily = activeLibrary;

                if (searchTerm) {
                    // PHASE 1: Immediate name-match search (synchronous)
                    const searchLower = searchTerm.toLowerCase();
                    proLibrary.icons.forEach(iconData => {
                        if (typeof iconData === 'string') {
                            const iconName = iconData.split('|')[0];
                            // Filter by icon name for instant results
                            if (iconName.includes(searchLower)) {
                                iconsToFilter.push({
                                    type: 'font',
                                    name: iconName,
                                    searchTerm: iconName, // No local search terms available
                                    isPro: true,
                                    styleFamily: currentStyleFamily,
                                    weight: activeWeight
                                });
                            }
                        }
                    });

                    // PHASE 2: Merge API-enhanced results (from useEffect)
                    const existingNames = new Set(iconsToFilter.map(icon => icon.name));
                    apiEnhancedIcons.forEach(iconName => {
                        if (!existingNames.has(iconName)) {
                            iconsToFilter.push({
                                type: 'font',
                                name: iconName,
                                searchTerm: iconName,
                                isPro: true,
                                styleFamily: currentStyleFamily,
                                weight: activeWeight
                            });
                        }
                    });

                } else {
                    // No search: show all icons (browsing mode)
                    proLibrary.icons.forEach(iconData => {
                        if (typeof iconData === 'string') {
                            const iconName = iconData.split('|')[0];
                            iconsToFilter.push({
                                type: 'font',
                                name: iconName,
                                searchTerm: iconName,
                                isPro: true,
                                styleFamily: currentStyleFamily,
                                weight: activeWeight
                            });
                        }
                    });
                }
            }
            // Non-Pro Mode OR Brands/Custom tabs
            else {
                const processLibrary = (library) => {
                    // Skip the detected pro library (if any)
                    if (proLibrary && library === proLibrary) return [];

                    const hasNewFormat = library.icons.slice(0, 10).some(icon =>
                        typeof icon === 'string' && icon.includes('|')
                    );

                    return library.icons.map(iconData => {
                        if (typeof iconData === 'string') {
                            const iconName = iconData.split('|')[0];
                            const searchTerms = iconData;

                            return {
                                type: 'font',
                                name: iconName,
                                fullClass: `${library.prefix}${iconName}`,
                                searchTerm: searchTerms,
                                hasNewFormat: hasNewFormat
                            };
                        }
                        else if (typeof iconData === 'object' && iconData.slug) {
                            const uploadsUrl = window.ayecodeFASettings?.uploadsUrl || '';
                            const imageUrl = `${uploadsUrl}/${library['base-path']}/${iconData.file}`;
                            return {
                                type: 'image',
                                name: iconData.slug,
                                fullClass: `${library.prefix}${iconData.slug}`,
                                imageUrl: imageUrl,
                                searchTerm: iconData.slug,
                                hasNewFormat: false
                            };
                        }
                        return null;
                    }).filter(Boolean);
                };

                if (activeLibrary === 'all') {
                    Object.values(allIcons).forEach(library => {
                        const libraryIcons = processLibrary(library);
                        iconsToFilter.push(...libraryIcons);
                    });
                } else if (activeLibrary === 'brands' && isProMode) {
                    // Find brands library by icon-style
                    Object.values(allIcons).forEach(library => {
                        if (library['icon-style'] === 'fa-brands' || library['icon-style']?.includes('brand')) {
                            const libraryIcons = processLibrary(library);
                            iconsToFilter.push(...libraryIcons);
                        }
                    });
                } else if (activeLibrary === 'custom') {
                    // Find custom library by icon-style
                    Object.values(allIcons).forEach(library => {
                        if (library['icon-style'] === 'custom') {
                            const libraryIcons = processLibrary(library);
                            iconsToFilter.push(...libraryIcons);
                        }
                    });
                } else if (allIcons[activeLibrary]) {
                    iconsToFilter = processLibrary(allIcons[activeLibrary]);
                }
            }

            // For Pro mode with search, filtering is already done (name match + API results)
            // Skip the generic filter to avoid filtering out API-enhanced results
            if (!searchTerm) return iconsToFilter;

            let filtered;
            if (isProMode && ['classic', 'duotone', 'sharp', 'sharp-duotone'].includes(activeLibrary)) {
                // Pro mode: filtering already done, just use iconsToFilter
                filtered = iconsToFilter;
            } else {
                // Non-Pro mode: apply generic filter
                filtered = iconsToFilter.filter(icon => icon.searchTerm.toLowerCase().includes(searchTerm.toLowerCase()));
            }

            // Sort results: prioritize exact matches
            const searchTermLower = searchTerm.toLowerCase();
            filtered.sort((a, b) => {
                // Check if icon name is exact match
                const aNameExact = a.name.toLowerCase() === searchTermLower;
                const bNameExact = b.name.toLowerCase() === searchTermLower;

                if (aNameExact && !bNameExact) return -1;
                if (!aNameExact && bNameExact) return 1;

                // Only check for exact word matches if using new format
                if (a.hasNewFormat || b.hasNewFormat) {
                    // Check if search term is an exact word in the search terms (split by space or pipe)
                    const aTerms = a.hasNewFormat ? a.searchTerm.toLowerCase().split(/[\s|]+/) : [];
                    const bTerms = b.hasNewFormat ? b.searchTerm.toLowerCase().split(/[\s|]+/) : [];
                    const aHasExactTerm = aTerms.includes(searchTermLower);
                    const bHasExactTerm = bTerms.includes(searchTermLower);

                    if (aHasExactTerm && !bHasExactTerm) return -1;
                    if (!aHasExactTerm && bHasExactTerm) return 1;
                }

                return 0; // Keep original order
            });

            return filtered;
        }, [isLoading, allIcons, activeLibrary, searchTerm, isProMode, proLibrary, activeWeight, apiEnhancedIcons]);

        // Virtual scrolling configuration
        const ICON_SIZE = 60;
        const ICON_GAP = 8;
        const BUFFER_ROWS = 10; // Increased buffer to reduce re-renders during scroll

        // Calculate visible icons for virtual scrolling
        const visibleIcons = useMemo(() => {
            if (filteredIcons.length === 0) return { icons: [], topSpacer: 0, bottomSpacer: 0 };

            // Calculate icons per row (approximate based on 60px + 8px gap)
            const containerWidth = 800; // Approximate col-md-9 width
            const iconTotalWidth = ICON_SIZE + ICON_GAP;
            const iconsPerRow = Math.max(1, Math.floor((containerWidth + ICON_GAP) / iconTotalWidth));

            const totalRows = Math.ceil(filteredIcons.length / iconsPerRow);
            const rowHeight = ICON_SIZE + ICON_GAP;

            const viewportHeight = 600; // Approximate
            const firstVisibleRow = Math.max(0, Math.floor(scrollTop / rowHeight) - BUFFER_ROWS);
            const lastVisibleRow = Math.min(totalRows, Math.ceil((scrollTop + viewportHeight) / rowHeight) + BUFFER_ROWS);

            const startIndex = firstVisibleRow * iconsPerRow;
            const endIndex = Math.min(filteredIcons.length, lastVisibleRow * iconsPerRow);

            const topSpacer = firstVisibleRow * rowHeight;
            const remainingRows = totalRows - lastVisibleRow;
            const bottomSpacer = remainingRows > 0 ? remainingRows * rowHeight : 0;

            return {
                icons: filteredIcons.slice(startIndex, endIndex),
                topSpacer,
                bottomSpacer
            };
        }, [filteredIcons, scrollTop]);

        // Calculate library counts
        const libraryCounts = useMemo(() => {
            const counts = {};

            if (isProMode) {
                const proIconCount = proLibrary.icons ? proLibrary.icons.length : 0;
                // No multiplier needed - showing base icon count

                counts['classic'] = proIconCount;
                counts['duotone'] = proIconCount;
                counts['sharp'] = proIconCount;
                counts['sharp-duotone'] = proIconCount;

                // Add Brands count if exists
                Object.entries(allIcons).forEach(([key, library]) => {
                    if (library['icon-style'] === 'fa-brands' || key.includes('brand')) {
                        counts['brands'] = library.icons ? library.icons.length : 0;
                    }
                });

                // Add Custom count
                const customLibrary = Object.values(allIcons).find(lib => lib['icon-style'] === 'custom');
                counts['custom'] = customLibrary && customLibrary.icons ? customLibrary.icons.length : 0;
            } else {
                let totalCount = 0;
                Object.entries(allIcons).forEach(([key, library]) => {
                    const count = library.icons ? library.icons.length : 0;
                    counts[key] = count;
                    totalCount += count;
                });
                counts['all'] = totalCount;

                // Add Custom count
                const customLibrary = Object.values(allIcons).find(lib => lib['icon-style'] === 'custom');
                counts['custom'] = customLibrary && customLibrary.icons ? customLibrary.icons.length : 0;
            }

            return counts;
        }, [allIcons, isProMode, proLibrary, activeWeight]);

        // Throttle scroll updates with ref to track RAF
        const rafPending = wp.element.useRef(false);
        const handleScroll = useCallback((e) => {
            if (rafPending.current) return;
            rafPending.current = true;
            requestAnimationFrame(() => {
                setScrollTop(e.target.scrollTop);
                rafPending.current = false;
            });
        }, []);

        const handleClearSearch = () => {
            setSearchTerm('');
        };

        const renderCustomIconsEmptyState = () => {
            const settingsUrl = window.ayecodeFASettings?.customIconsSettingsUrl || '#';

            return createElement('div', {
                className: 'text-center py-5',
                style: { gridColumn: '1 / -1' }
            },
                createElement('div', { className: 'mb-3' },
                    createElement('i', {
                        className: 'fas fa-image',
                        style: { fontSize: '48px', color: '#6c757d' }
                    })
                ),
                createElement('h5', { className: 'mb-3' }, 'No custom icons added yet'),
                createElement('p', { className: 'text-muted mb-4' },
                    'Add your own custom SVG icons to use throughout your site.'
                ),
                createElement('a', {
                    href: settingsUrl,
                    className: 'btn btn-primary'
                },
                    createElement('i', { className: 'fas fa-plus me-2' }),
                    'Add Custom Icons'
                )
            );
        };

        const handleSelectAndClose = (iconObj) => {
            onClose();
            setTimeout(() => {
                // Construct HTML based on icon type
                const iconHtml = iconObj.type === 'font'
                    ? `<i class="${iconObj.fullClass}" style="font-size: 24px;"></i>`
                    : `<img src="${iconObj.imageUrl}" alt="${iconObj.name}" style="width: 24px; height: 24px; object-fit: contain;">`;

                onSelect({ iconClass: iconObj.fullClass, iconHtml });
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
                                        isProMode
                                            ? [
                                                createElement('button', {
                                                    key: 'classic',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'classic' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('classic')
                                                },
                                                    createElement('span', null, 'Classic'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['classic']?.toLocaleString() || '0')
                                                ),
                                                createElement('button', {
                                                    key: 'duotone',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'duotone' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('duotone')
                                                },
                                                    createElement('span', null, 'Duotone'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['duotone']?.toLocaleString() || '0')
                                                ),
                                                createElement('button', {
                                                    key: 'sharp',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'sharp' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('sharp')
                                                },
                                                    createElement('span', null, 'Sharp'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['sharp']?.toLocaleString() || '0')
                                                ),
                                                createElement('button', {
                                                    key: 'sharp-duotone',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'sharp-duotone' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('sharp-duotone')
                                                },
                                                    createElement('span', null, 'Sharp Duotone'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['sharp-duotone']?.toLocaleString() || '0')
                                                ),
                                                libraryCounts['brands'] && createElement('button', {
                                                    key: 'brands',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'brands' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('brands')
                                                },
                                                    createElement('span', null, 'Brands'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['brands']?.toLocaleString() || '0')
                                                ),
                                                createElement('button', {
                                                    key: 'custom',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'custom' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('custom')
                                                },
                                                    createElement('span', null, 'Custom'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['custom']?.toLocaleString() || '0')
                                                )
                                            ].filter(Boolean)
                                            : [
                                                createElement('button', {
                                                    key: 'all',
                                                    className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'all' ? 'active' : ''}`,
                                                    onClick: () => setActiveLibrary('all')
                                                },
                                                    createElement('span', null, 'All Icons'),
                                                    createElement('span', { className: 'badge bg-secondary' }, libraryCounts['all']?.toLocaleString() || '0')
                                                ),
                                                ...Object.entries(allIcons).map(([libKey, library]) => {
                                                    const displayName = library['display-name'] ||
                                                        (library['icon-style'] ? library['icon-style'].charAt(0).toUpperCase() + library['icon-style'].slice(1) : null) ||
                                                        libKey.charAt(0).toUpperCase() + libKey.slice(1);

                                                    return createElement('button', {
                                                        key: libKey,
                                                        className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === libKey ? 'active' : ''}`,
                                                        onClick: () => setActiveLibrary(libKey)
                                                    },
                                                        createElement('span', null, displayName),
                                                        createElement('span', { className: 'badge bg-secondary' }, libraryCounts[libKey]?.toLocaleString() || '0')
                                                    );
                                                }),
                                                !Object.keys(allIcons).some(key => allIcons[key]?.['icon-style'] === 'custom') &&
                                                    createElement('button', {
                                                        key: 'custom',
                                                        className: `nav-link text-start d-flex justify-content-between align-items-center ${activeLibrary === 'custom' ? 'active' : ''}`,
                                                        onClick: () => setActiveLibrary('custom')
                                                    },
                                                        createElement('span', null, 'Custom'),
                                                        createElement('span', { className: 'badge bg-secondary' }, '0')
                                                    )
                                            ].filter(Boolean)
                                    )
                                ),
                                createElement('div', { className: 'col-md-9 d-flex flex-column h-100' },
                                    createElement('div', { className: 'position-relative mb-3' },
                                        createElement('input', {
                                            type: 'text',
                                            className: 'form-control py-3 pe-5',
                                            placeholder: 'Search icons…',
                                            value: searchTerm,
                                            onChange: (e) => setSearchTerm(e.target.value),
                                            style: {maxHeight: '40px', minHeight: '40px'}
                                        }),
                                        searchTerm && createElement('button', {
                                            type: 'button',
                                            className: 'btn btn-link position-absolute top-50 end-0 translate-middle-y',
                                            onClick: handleClearSearch,
                                            style: { padding: '0.25rem 0.75rem' }
                                        },
                                            createElement('i', { className: 'fas fa-times' })
                                        )
                                    ),
                                    // Weight filter (Pro mode only, hidden for Brands/Custom)
                                    isProMode && !['brands', 'custom'].includes(activeLibrary) && createElement('div', { className: 'btn-group w-100 mb-3', role: 'group' },
                                        ['solid', 'regular', 'light', 'thin'].map(weight => {
                                            const checked = activeWeight === weight;
                                            const label = weight.charAt(0).toUpperCase() + weight.slice(1);
                                            return [
                                                createElement('input', {
                                                    key: `${weight}-input`,
                                                    type: 'radio',
                                                    className: 'btn-check',
                                                    name: 'icon-weight',
                                                    id: `weight-${weight}`,
                                                    value: weight,
                                                    checked: checked,
                                                    onChange: (e) => setActiveWeight(e.target.value),
                                                    autoComplete: 'off'
                                                }),
                                                createElement('label', {
                                                    key: `${weight}-label`,
                                                    className: `btn btn-outline-secondary ${checked ? 'active' : ''}`,
                                                    htmlFor: `weight-${weight}`
                                                }, label)
                                            ];
                                        }).flat()
                                    ),
                                    createElement('div', {
                                        ref: scrollContainerRef,
                                        className: 'flex-grow-1',
                                        style: { overflowY: 'auto' },
                                        onScroll: handleScroll
                                    },
                                        isLoading
                                            ? createElement('div', { className: 'd-flex justify-content-center pt-5' },
                                                createElement('div', { className: 'spinner-border', role: 'status' },
                                                    createElement('span', { className: 'visually-hidden' }, 'Loading...')
                                                )
                                            )
                                            : createElement('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(60px, 1fr))', gap: '8px', paddingRight: '15px' } },
                                                // Check for custom icons empty state
                                                (activeLibrary === 'custom' && !allIcons['custom'])
                                                    ? renderCustomIconsEmptyState()
                                                    : [
                                                        // Top spacer
                                                        visibleIcons.topSpacer > 0 && createElement('div', {
                                                            key: 'top-spacer',
                                                            style: {
                                                                gridColumn: '1 / -1',
                                                                height: `${visibleIcons.topSpacer}px`
                                                            }
                                                        }),
                                                        // Visible icons
                                                        ...visibleIcons.icons.map(iconObj => {
                                                            // Generate full class on-the-fly for Pro icons (uses memoized constants)
                                                            let fullClass = iconObj.fullClass;
                                                            if (iconObj.isPro && iconObj.styleFamily && iconObj.weight) {
                                                                const parts = [PRO_FAMILIES[iconObj.styleFamily], PRO_WEIGHTS[iconObj.weight], `fa-${iconObj.name}`].filter(Boolean);
                                                                fullClass = parts.join(' ');
                                                            }

                                                            return createElement('button', {
                                                                    key: fullClass || iconObj.name,
                                                                    className: 'btn btn-light border d-flex align-items-center justify-content-center',
                                                                    style: { height: '60px', width: '60px' },
                                                                    onClick: () => handleSelectAndClose({...iconObj, fullClass}),
                                                                    title: iconObj.name
                                                                },
                                                                iconObj.type === 'font'
                                                                    ? createElement('i', { className: fullClass, style: { fontSize: '24px' } })
                                                                    : createElement('img', {
                                                                        src: iconObj.imageUrl,
                                                                        alt: iconObj.name,
                                                                        style: { width: '24px', height: '24px', objectFit: 'contain' },
                                                                        loading: 'lazy'
                                                                    })
                                                            );
                                                        }),
                                                        // Bottom spacer
                                                        visibleIcons.bottomSpacer > 0 && createElement('div', {
                                                            key: 'bottom-spacer',
                                                            style: {
                                                                gridColumn: '1 / -1',
                                                                height: `${visibleIcons.bottomSpacer}px`
                                                            }
                                                        })
                                                    ].filter(Boolean)
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
        const [buttonContent, setButtonContent] = useState(null);

        // Render the current icon value (handles both font and custom SVG icons)
        useEffect(() => {
            if (props.value && window.aui_render_icon_from_class) {
                window.aui_render_icon_from_class(props.value, (html) => {
                    // Create a temporary div to parse HTML string
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    const element = temp.firstChild;

                    // Convert to React element
                    if (element.tagName === 'I') {
                        setButtonContent(createElement('i', { className: element.className }));
                    } else if (element.tagName === 'IMG') {
                        setButtonContent(createElement('img', {
                            src: element.src,
                            alt: element.alt,
                            style: { width: '24px', height: '24px', objectFit: 'contain' }
                        }));
                    }
                });
            } else if (props.value) {
                // Fallback: render as font icon
                setButtonContent(createElement('i', { className: props.value }));
            } else {
                // Default fallback icon
                setButtonContent(createElement('i', { className: 'fas fa-icons' }));
            }
        }, [props.value]);

        const handleSelect = (iconData) => {
            if (props.onSelect) {
                // If an onSelect callback is provided, use it.
                // Pass the entire object { iconClass, iconHtml }
                props.onSelect(iconData);
            } else {
                // Otherwise, fall back to the default Gutenberg behavior.
                // Only save the iconClass to attributes
                props.setAttributes({ [props.attributeName]: iconData.iconClass });
            }
        };

        return createElement(Fragment, null,
            createElement('div', { style: { display: 'inline-block', marginLeft: '-1px' } },
                createElement('button', {
                    id: `icon-picker-button-${props.uniqueId}`,
                    onClick: () => setIsModalOpen(true),
                    type: 'button',
                    className: 'components-button is-secondary is-compact'
                }, buttonContent || createElement('i', { className: 'fas fa-icons' }))
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