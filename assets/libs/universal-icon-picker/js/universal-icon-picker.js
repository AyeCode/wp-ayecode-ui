/**
 * Inspiration taken from https://github.com/migliori/universal-icon-picker
 */
class AyeCodeIconPicker {
    // Static Pro icon generation maps (memoized for performance)
    static PRO_FAMILIES = {
        'classic': '',
        'duotone': 'fa-duotone',
        'sharp': 'fa-sharp',
        'sharp-duotone': 'fa-sharp-duotone'
    };

    static PRO_WEIGHTS = {
        'solid': 'fa-solid',
        'regular': 'fa-regular',
        'light': 'fa-light',
        'thin': 'fa-thin'
    };

    constructor(triggerSelector, options) {
        this.triggerElement = document.querySelector(triggerSelector);
        if (!this.triggerElement) {
            console.error(`AeroIconPicker: Trigger element "${triggerSelector}" not found.`);
            return;
        }
        if (typeof aui_modal !== 'function') {
            console.error(`AeroIconPicker: The global function 'aui_modal' is not available.`);
            return;
        }

        // Default configuration
        this.defaultLibraries = [
            'font-awesome-solid.min.json',
            'font-awesome-regular.min.json',
            'font-awesome-brands.min.json'
        ];

        // Get base URL from global settings or options
        // If neither is provided, the picker won't work - settings MUST be defined
        this.defaultBaseUrl = '';

        // This sets up default options and merges them with any options the user passes in.
        this.options = Object.assign({
            iconPickerUrl: this.defaultBaseUrl,
            iconLibraries: this.defaultLibraries,
            onSelect: () => {},
        }, options);

        // --- State ---
        this.allIcons = {};
        this.activeLibrary = 'all';
        this.searchTerm = '';
        this.isLoading = true;
        this.modalInstance = null;
        window.myIconPickerCache = window.myIconPickerCache || { data: null, promise: null };

        // --- Pro Mode State ---
        this.isProMode = false;
        this.proLibrary = null;
        this.activeWeight = 'solid'; // 'solid', 'regular', 'light', 'thin' (removed 'all')
        this.activeStyleFamily = 'classic'; // 'classic', 'duotone', 'sharp', 'sharp-duotone'

        // --- Pro API Search State ---
        this.proSearchCache = {}; // Cache API results per session
        this.pendingSearchAPI = null; // Track pending API request for cancellation

        // --- Virtual Scroll Configuration ---
        this.ICON_SIZE = 60; // Height of each icon button in pixels
        this.ICON_GAP = 8; // Gap between icons in pixels
        this.ICONS_PER_ROW = 0; // Calculated dynamically based on container width
        this.BUFFER_ROWS = 10; // Number of extra rows to render above/below viewport (increased to reduce re-renders)
        this.filteredIcons = []; // Store filtered icons for virtual scrolling
        this.lastRenderedRange = { start: -1, end: -1 }; // Track what's currently rendered

        // --- Bind Methods ---
        this.open = this.open.bind(this);
        this.close = this.close.bind(this);
        // _handleIconSelect: No longer needs binding (using event delegation)
        this._handleSearch = this._debounce(this._handleSearch.bind(this), 250); // Debounce search for better performance
        this._handleLibraryChange = this._handleLibraryChange.bind(this);
        this._handleWeightChange = this._handleWeightChange.bind(this);
        this._handleKeyDown = this._handleKeyDown.bind(this);
        this._updateVisibleIcons = this._updateVisibleIcons.bind(this); // RAF throttling handles timing

        this._init();
    }

    _init() {
        this.triggerElement.addEventListener('click', this.open);
        this._fetchIcons();
    }

    open() {
        const modalBody = `
            <div class="row" style="height: 85vh;">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="aip-library-tabs"></div>
                </div>
                <div class="col-md-9 d-flex flex-column h-100">
                    <div class="position-relative mb-3">
                        <input type="text" class="form-control py-3 pe-5" id="aip-search-input" placeholder="Search icons…" style="max-height: 40px;min-height: 40px;">
                        <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y d-none" id="aip-clear-search" style="padding: 0.25rem 0.75rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="aip-weight-filter" class="d-none mb-3"></div>
                    <div class="flex-grow-1" style="overflow-y: auto;">
                        <div id="aip-icon-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 8px; padding-right: 15px;"></div>
                        <div id="aip-loader" class="d-flex justify-content-center pt-5">
                            <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        </div>
                    </div>
                </div>
            </div>`;

        aui_modal('Select an Icon', modalBody, null, true, '', 'modal-xl', 'h-100');

        this.modalInstance = document.getElementById('aui-modal');

        if (!this.modalInstance) {
            console.error("AeroIconPicker: Could not find element with ID '#aui-modal' after calling aui_modal.");
            return;
        }

        this.search_input = this.modalInstance.querySelector('#aip-search-input');
        this.clear_search_btn = this.modalInstance.querySelector('#aip-clear-search');
        this.weight_filter_container = this.modalInstance.querySelector('#aip-weight-filter');
        this.icon_grid = this.modalInstance.querySelector('#aip-icon-grid');
        this.loader = this.modalInstance.querySelector('#aip-loader');
        this.library_tabs_container = this.modalInstance.querySelector('#aip-library-tabs');

        // Reset search term and input value when opening
        this.searchTerm = '';
        this.search_input.value = '';

        // Reset Pro mode state when opening
        if (this.isProMode) {
            this.activeLibrary = 'classic';
            this.activeWeight = 'solid';
            this.activeStyleFamily = 'classic';
        }

        // Search input with immediate clear button update, debounced filtering
        this.search_input.addEventListener('input', (e) => {
            // Update clear button immediately (no debounce)
            if (e.target.value) {
                this.clear_search_btn.classList.remove('d-none');
            } else {
                this.clear_search_btn.classList.add('d-none');
            }
            // Debounced search happens via _handleSearch
            this._handleSearch(e);
        });
        this.clear_search_btn.addEventListener('click', this._handleClearSearch.bind(this));
        document.addEventListener('keydown', this._handleKeyDown);

        // Event delegation: Single listener for all icon clicks
        this.icon_grid.addEventListener('click', (e) => {
            const button = e.target.closest('button[data-icon-class]');
            if (button) {
                this._handleIconSelect(button);
            }
        });

        this.modalInstance.addEventListener('hidden.bs.modal', () => {
            document.removeEventListener('keydown', this._handleKeyDown);
        }, { once: true });

        this._renderLibraryTabs();
        this._renderWeightFilter();
        this._renderIcons();
        this.search_input.focus();
    }

    close() {
        if (!this.modalInstance) return;
        const modal = bootstrap.Modal.getInstance(this.modalInstance);
        if (modal) {
            modal.hide();
        }
        // Reset search term when closing
        this.searchTerm = '';
    }

    async _fetchIcons() {
        if (window.myIconPickerCache.data) {
            this.allIcons = window.myIconPickerCache.data;
            this._detectProMode();
            this.isLoading = false;
            return;
        }
        if (window.myIconPickerCache.promise) {
            await window.myIconPickerCache.promise;
            this.allIcons = window.myIconPickerCache.data;
            this._detectProMode();
            this.isLoading = false;
            return;
        }
        const fetchPromise = (async () => {
            try {
                // Determine which libraries to load
                // Priority: window.ayecodeFASettings.libraries > this.options.iconLibraries
                let librariesToLoad = window.ayecodeFASettings?.libraries || this.options.iconLibraries;

                // Handle case where libraries might be a JSON string instead of array
                if (typeof librariesToLoad === 'string') {
                    try {
                        librariesToLoad = JSON.parse(librariesToLoad);
                    } catch (e) {
                        console.error('Failed to parse libraries JSON:', e);
                    }
                }

                // Validate that we have libraries configured
                if (!librariesToLoad || !Array.isArray(librariesToLoad) || librariesToLoad.length === 0) {
                    throw new Error('No icon libraries configured. Please define window.ayecodeFASettings.libraries as an array.');
                }

                const libraryPromises = librariesToLoad.map(async (libraryUrl) => {
                    // libraryUrl should be a full URL (set via PHP filter)
                    // Extract filename for key generation
                    const fileName = libraryUrl.split('/').pop();

                    const response = await fetch(libraryUrl);
                    if (!response.ok) throw new Error(`Failed to fetch ${libraryUrl}`);
                    const data = await response.json();

                    // Store filename with library data for Pro detection
                    data._fileName = fileName;

                    // Generate library key using the new universal method
                    const key = this._generateLibraryKey(fileName, data);

                    return [key, data];
                });

                const loadedLibraries = await Promise.all(libraryPromises);
                window.myIconPickerCache.data = Object.fromEntries(loadedLibraries);
                this.allIcons = window.myIconPickerCache.data;

                // Detect Pro mode
                this._detectProMode();
            } catch (error) {
                console.error("Error loading icon libraries:", error);
            } finally {
                this.isLoading = false;
                window.myIconPickerCache.promise = null;
            }
        })();
        window.myIconPickerCache.promise = fetchPromise;
        await fetchPromise;
    }

    /**
     * Detect if Pro mode is active by checking for modifiers flag AND filename
     */
    _detectProMode() {
        for (const [key, library] of Object.entries(this.allIcons)) {
            const fileName = library._fileName || '';
            const isProFile = fileName.includes('pro') || fileName.includes('Pro');

            // Pro mode requires BOTH modifiers === true AND filename contains "pro"
            if (library.modifiers === true && isProFile) {
                this.isProMode = true;
                this.proLibrary = library;
                break;
            }
        }
    }

    /**
     * Generate Pro icon class from components (uses static memoized maps)
     */
    _generateProIconClass(baseIconName, styleFamily, weight) {
        const parts = [
            AyeCodeIconPicker.PRO_FAMILIES[styleFamily],
            AyeCodeIconPicker.PRO_WEIGHTS[weight],
            `fa-${baseIconName}`
        ].filter(Boolean);
        return parts.join(' ');
    }

    /**
     * Search Pro icons via API (progressive enhancement)
     */
    async _searchProIconsAPI(searchTerm, styleFamily, weight) {
        // Only search if wpApiSettings is available
        if (!window.wpApiSettings || !searchTerm) return [];

        const cacheKey = `${styleFamily}-${weight}-${searchTerm.toLowerCase()}`;

        // Return cached results if available
        if (this.proSearchCache[cacheKey]) {
            return this.proSearchCache[cacheKey];
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
            this.proSearchCache[cacheKey] = icons;

            return icons;
        } catch (error) {
            // Fail silently - graceful degradation to local search only
            return [];
        }
    }

    /**
     * Enhance Pro search results with API data (async)
     */
    async _enhanceProSearchResults(searchTerm, styleFamily, weight) {
        // Get API results
        const apiIconNames = await this._searchProIconsAPI(searchTerm, styleFamily, weight);

        if (!apiIconNames || apiIconNames.length === 0) return; // No results

        // Verify the search context hasn't changed
        if (this.searchTerm !== searchTerm || this.activeStyleFamily !== styleFamily || this.activeWeight !== weight) {
            return; // User has changed search/filters, discard stale results
        }

        // Get current displayed icon names
        const currentIconNames = new Set(this.filteredIcons.map(icon => icon.name));

        // Find new icons from API (not already displayed)
        const newIcons = apiIconNames
            .filter(iconName => !currentIconNames.has(iconName))
            .map(iconName => ({
                type: 'font',
                name: iconName,
                searchTerm: iconName,
                isPro: true,
                styleFamily: styleFamily,
                weight: weight
            }));

        if (newIcons.length === 0) return; // No new icons to add

        // Merge with existing results (API results at end)
        this.filteredIcons = [...this.filteredIcons, ...newIcons];

        // Force re-render by resetting last rendered range
        this.lastRenderedRange = { start: -1, end: -1 };

        // Re-render to show enhanced results
        this._renderVirtualIcons();
    }

    _renderLibraryTabs() {
        const libs = {};

        if (this.isProMode) {
            // Pro Mode: Show style family tabs
            const proIconCount = this.proLibrary.icons ? this.proLibrary.icons.length : 0;
            // No multiplier needed - showing base icon count

            libs['classic'] = { name: 'Classic', count: proIconCount };
            libs['duotone'] = { name: 'Duotone', count: proIconCount };
            libs['sharp'] = { name: 'Sharp', count: proIconCount };
            libs['sharp-duotone'] = { name: 'Sharp Duotone', count: proIconCount };

            // Add Brands tab if exists
            Object.entries(this.allIcons).forEach(([key, library]) => {
                if (library['icon-style'] === 'fa-brands' || key.includes('brand')) {
                    libs['brands'] = {
                        name: 'Brands',
                        count: library.icons ? library.icons.length : 0
                    };
                }
            });

            // Add Custom tab (check if custom library exists, otherwise show empty state)
            const customLibrary = Object.values(this.allIcons).find(lib => lib['icon-style'] === 'custom');
            libs['custom'] = {
                name: 'Custom',
                count: customLibrary && customLibrary.icons ? customLibrary.icons.length : 0
            };
        } else {
            // Non-Pro Mode: Original behavior
            libs['all'] = { name: 'All Icons', count: 0 };

            // Calculate total icon count
            let totalIcons = 0;
            Object.values(this.allIcons).forEach(library => {
                totalIcons += library.icons ? library.icons.length : 0;
            });
            libs['all'].count = totalIcons;

            // Build library names from metadata
            Object.entries(this.allIcons).forEach(([key, library]) => {
                const displayName = library['display-name'] ||
                    (library['icon-style'] ? library['icon-style'].charAt(0).toUpperCase() + library['icon-style'].slice(1) : null) ||
                    key.charAt(0).toUpperCase() + key.slice(1);

                libs[key] = {
                    name: displayName,
                    count: library.icons ? library.icons.length : 0
                };
            });

            // Add Custom tab (check if custom library exists, otherwise show empty state)
            const customLibrary = Object.values(this.allIcons).find(lib => lib['icon-style'] === 'custom');
            libs['custom'] = {
                name: 'Custom',
                count: customLibrary && customLibrary.icons ? customLibrary.icons.length : 0
            };
        }

        let tabsHtml = '';
        for (const [key, data] of Object.entries(libs)) {
            const activeClass = this.activeLibrary === key ? 'active' : '';
            tabsHtml += `<button type="button" class="nav-link text-start d-flex justify-content-between align-items-center ${activeClass}" data-library-key="${key}">
                <span>${data.name}</span>
                <span class="badge bg-secondary">${data.count.toLocaleString()}</span>
            </button>`;
        }
        this.library_tabs_container.innerHTML = tabsHtml;
        this.library_tabs_container.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('click', this._handleLibraryChange);
        });
    }

    /**
     * Render weight filter (Pro mode only)
     */
    _renderWeightFilter() {
        if (!this.isProMode) {
            this.weight_filter_container.classList.add('d-none');
            return;
        }

        // Hide weight filter for Brands and Custom tabs
        if (this.activeLibrary === 'brands' || this.activeLibrary === 'custom') {
            this.weight_filter_container.classList.add('d-none');
            return;
        }

        this.weight_filter_container.classList.remove('d-none');

        // Check if we need to initialize the HTML (first time)
        if (!this.weight_filter_container.querySelector('.btn-group')) {
            const weights = [
                { value: 'solid', label: 'Solid' },
                { value: 'regular', label: 'Regular' },
                { value: 'light', label: 'Light' },
                { value: 'thin', label: 'Thin' }
            ];

            let filterHtml = '<div class="btn-group w-100" role="group">';
            weights.forEach(weight => {
                const checked = this.activeWeight === weight.value ? 'checked' : '';
                filterHtml += `
                    <input type="radio" class="btn-check" name="icon-weight" id="weight-${weight.value}" value="${weight.value}" ${checked} autocomplete="off">
                    <label class="btn btn-outline-secondary" for="weight-${weight.value}">${weight.label}</label>
                `;
            });
            filterHtml += '</div>';

            this.weight_filter_container.innerHTML = filterHtml;

            // Add event listeners
            this.weight_filter_container.querySelectorAll('input[name="icon-weight"]').forEach(radio => {
                radio.addEventListener('change', this._handleWeightChange);
            });
        }

        // Update checked state and active class
        this.weight_filter_container.querySelectorAll('input[name="icon-weight"]').forEach(radio => {
            const label = this.weight_filter_container.querySelector(`label[for="${radio.id}"]`);

            if (radio.value === this.activeWeight) {
                radio.checked = true;
                if (label) label.classList.add('active');
            } else {
                radio.checked = false;
                if (label) label.classList.remove('active');
            }
        });
    }

    _renderIcons() {
        if (this.isLoading) {
            // Show the loader by removing the 'd-none' class
            this.loader.classList.remove('d-none');
            this.icon_grid.style.display = 'none';
            return;
        }

        // Hide the loader by adding the 'd-none' class
        this.loader.classList.add('d-none');
        this.icon_grid.style.display = 'grid';

        // Check if "Custom" tab is selected but no custom library exists
        if (this.activeLibrary === 'custom') {
            const customLibrary = Object.values(this.allIcons).find(lib => lib['icon-style'] === 'custom');
            if (!customLibrary) {
                this._renderCustomIconsEmptyState();
                return;
            }
        }

        let iconsToFilter = [];

        // Pro Mode: Progressive enhancement search (name-match first, then API enhancement)
        if (this.isProMode && ['classic', 'duotone', 'sharp', 'sharp-duotone'].includes(this.activeLibrary)) {
            this.activeStyleFamily = this.activeLibrary;

            if (this.searchTerm) {
                // PHASE 1: Immediate name-match search (synchronous)
                const searchLower = this.searchTerm.toLowerCase();
                this.proLibrary.icons.forEach(iconData => {
                    if (typeof iconData === 'string') {
                        const iconName = iconData.split('|')[0];
                        // Filter by icon name for instant results
                        if (iconName.includes(searchLower)) {
                            iconsToFilter.push({
                                type: 'font',
                                name: iconName,
                                searchTerm: iconName, // No local search terms available
                                isPro: true,
                                styleFamily: this.activeStyleFamily,
                                weight: this.activeWeight
                            });
                        }
                    }
                });

                // PHASE 2: Enhanced API search (asynchronous, non-blocking)
                // Cancel any pending API request
                if (this.pendingSearchAPI) {
                    clearTimeout(this.pendingSearchAPI);
                }
                // Trigger API search after debounce to avoid excessive requests
                this.pendingSearchAPI = setTimeout(() => {
                    this._enhanceProSearchResults(this.searchTerm, this.activeStyleFamily, this.activeWeight);
                }, 300);

            } else {
                // No search: show all icons (browsing mode)
                this.proLibrary.icons.forEach(iconData => {
                    if (typeof iconData === 'string') {
                        const iconName = iconData.split('|')[0];
                        iconsToFilter.push({
                            type: 'font',
                            name: iconName,
                            searchTerm: iconName,
                            isPro: true,
                            styleFamily: this.activeStyleFamily,
                            weight: this.activeWeight
                        });
                    }
                });
            }
        }
        // Non-Pro Mode OR Brands/Custom tabs
        else {
            let libraries;

            if (this.activeLibrary === 'all') {
                libraries = Object.values(this.allIcons);
            } else if (this.activeLibrary === 'brands' && this.isProMode) {
                // Find brands library by icon-style
                libraries = Object.values(this.allIcons).filter(lib =>
                    lib['icon-style'] === 'fa-brands' || lib['icon-style']?.includes('brand')
                );
            } else if (this.activeLibrary === 'custom') {
                // Find custom library by icon-style
                libraries = Object.values(this.allIcons).filter(lib =>
                    lib['icon-style'] === 'custom'
                );
            } else {
                libraries = [this.allIcons[this.activeLibrary]];
            }

            for (const library of libraries) {
                if (!library) continue;

                // Skip the detected pro library (if any)
                if (this.proLibrary && library === this.proLibrary) continue;

                // Detect if library uses new format
                const hasNewFormat = library.icons.slice(0, 10).some(icon =>
                    typeof icon === 'string' && icon.includes('|')
                );

                library.icons.forEach(iconData => {
                    // Font-based icons
                    if (typeof iconData === 'string') {
                        const iconName = iconData.split('|')[0];
                        const searchTerms = iconData;

                        iconsToFilter.push({
                            type: 'font',
                            name: iconName,
                            fullClass: `${library.prefix}${iconName}`,
                            searchTerm: searchTerms,
                            hasNewFormat: hasNewFormat
                        });
                    }
                    // Image-based icons (Custom)
                    else if (typeof iconData === 'object' && iconData.slug) {
                        const imageUrl = this._resolveIconUrl(library, iconData);
                        if (imageUrl) {
                            iconsToFilter.push({
                                type: 'image',
                                name: iconData.slug,
                                fullClass: `${library.prefix}${iconData.slug}`,
                                imageUrl: imageUrl,
                                searchTerm: iconData.slug,
                                hasNewFormat: false
                            });
                        }
                    }
                });
            }
        }

        const searchTermLower = this.searchTerm.toLowerCase();
        this.filteredIcons = !this.searchTerm
            ? iconsToFilter
            : iconsToFilter.filter(icon => icon.searchTerm.toLowerCase().includes(searchTermLower));

        // Sort results: prioritize exact matches
        if (this.searchTerm) {
            this.filteredIcons.sort((a, b) => {
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
        }

        // Setup virtual scrolling and render visible icons only
        this._setupVirtualScroll();

        // Reset scroll position to top when filter changes
        if (this.scrollContainer) {
            this.scrollContainer.scrollTop = 0;
        }

        // Reset rendered range so we force a re-render
        this.lastRenderedRange = { start: -1, end: -1 };

        this._renderVirtualIcons();
    }

    _handleSearch(e) {
        this.searchTerm = e.target.value;
        this._renderIcons();
    }

    _handleClearSearch() {
        this.searchTerm = '';
        this.search_input.value = '';
        this.clear_search_btn.classList.add('d-none');
        this.search_input.focus();
        this._renderIcons();
    }

    _handleLibraryChange(e) {
        const libraryKey = e.target.dataset.libraryKey || e.currentTarget.dataset.libraryKey;

        if (!libraryKey) {
            console.error('No libraryKey found! Aborting.');
            return;
        }

        this.activeLibrary = libraryKey;

        // Remove active class from previously active tab (if exists)
        const previousActive = this.library_tabs_container.querySelector('.active');
        if (previousActive) {
            previousActive.classList.remove('active');
        }

        // Add active to clicked element (or its parent button)
        const targetButton = e.target.classList.contains('nav-link') ? e.target : e.target.closest('.nav-link');
        if (targetButton) {
            targetButton.classList.add('active');
        }

        // Update weight filter visibility when tab changes
        this._renderWeightFilter();
        this._renderIcons();
    }

    _handleWeightChange(e) {
        this.activeWeight = e.target.value;

        // Update active class immediately
        this.weight_filter_container.querySelectorAll('input[name="icon-weight"]').forEach(radio => {
            const label = this.weight_filter_container.querySelector(`label[for="${radio.id}"]`);
            if (radio.value === this.activeWeight) {
                if (label) label.classList.add('active');
            } else {
                if (label) label.classList.remove('active');
            }
        });

        // Update tab counts when weight changes
        this._renderLibraryTabs();
        this._renderIcons();
    }

    _handleIconSelect(button) {
        const iconClass = button.dataset.iconClass;

        // Detect if this is a font icon or image icon
        const fontIcon = button.querySelector('i');
        const imageIcon = button.querySelector('img');

        let iconHtml;
        if (fontIcon) {
            // Font-based icon (Font Awesome)
            iconHtml = fontIcon.outerHTML;
        } else if (imageIcon) {
            // Image-based icon (Custom SVG)
            iconHtml = imageIcon.outerHTML;
        }

        this.options.onSelect({ iconClass, iconHtml });
        this.close();
    }

    _handleKeyDown(e) {
        if (e.key === "Escape") {
            this.close();
        }
    }

    /**
     * Render empty state when Custom tab is selected but no custom icons exist
     */
    _renderCustomIconsEmptyState() {
        const settingsUrl = window.ayecodeFASettings?.customIconsSettingsUrl || '#';

        this.icon_grid.style.display = 'flex';
        this.icon_grid.style.gridTemplateColumns = '1fr';
        this.icon_grid.style.justifyContent = 'center';
        this.icon_grid.style.alignItems = 'center';

        this.icon_grid.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-image" style="font-size: 48px; color: #6c757d;"></i>
                </div>
                <h5 class="mb-3">No custom icons added yet</h5>
                <p class="text-muted mb-4">Add your own custom SVG icons to use throughout your site.</p>
                <a href="${settingsUrl}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Custom Icons
                </a>
            </div>
        `;
    }

    /**
     * Generate a unique key for a library based on filename and JSON metadata
     * @param {string} fileName - The library filename (e.g., 'font-awesome-solid.min.json')
     * @param {object} jsonData - The parsed JSON data from the library
     * @returns {string} - A unique key for the library
     */
    _generateLibraryKey(fileName, jsonData) {
        // Priority 1: Use icon-style from JSON if available (e.g., "custom", "solid")
        if (jsonData['icon-style']) {
            return jsonData['icon-style'];
        }

        // Priority 2: Derive from filename
        return fileName
            .replace('font-awesome-', '')
            .replace('.min.json', '')
            .replace('.json', '');
    }

    /**
     * Resolve the full URL for an image-based icon
     * @param {object} library - The library object containing metadata
     * @param {object} iconData - The icon data object (e.g., {slug: "ayecode", file: "ayecode.svg"})
     * @returns {string|null} - The full URL to the icon image, or null if not resolvable
     */
    _resolveIconUrl(library, iconData) {
        // If icon has a direct URL, use it
        if (iconData.url) {
            return iconData.url;
        }

        // If library has a base-path and icon has a file, construct the URL
        if (library['base-path'] && iconData.file) {
            const uploadsUrl = window.ayecodeFASettings?.uploadsUrl || '';
            if (!uploadsUrl) {
                console.warn('ayecodeFASettings.uploadsUrl not set - cannot resolve icon URL for', iconData);
                return null;
            }
            return `${uploadsUrl}/${library['base-path']}/${iconData.file}`;
        }

        return null;
    }

    /**
     * Debounce utility function to limit how often a function is called
     * @param {function} func - The function to debounce
     * @param {number} wait - The delay in milliseconds
     * @returns {function} - Debounced function
     */
    _debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Setup virtual scrolling - calculate icons per row and attach scroll listener
     */
    _setupVirtualScroll() {
        // Calculate icons per row based on grid width
        const gridWidth = this.icon_grid.offsetWidth || 800; // Fallback width
        const iconTotalWidth = this.ICON_SIZE + this.ICON_GAP;
        this.ICONS_PER_ROW = Math.max(1, Math.floor((gridWidth + this.ICON_GAP) / iconTotalWidth));

        // Cache computed values for performance
        this.rowHeight = this.ICON_SIZE + this.ICON_GAP;
        this.iconTotalWidth = iconTotalWidth;

        // Get scroll container (parent of icon grid)
        this.scrollContainer = this.icon_grid.parentElement;

        // Remove existing scroll listener if any
        if (this.scrollContainer._scrollListener) {
            this.scrollContainer.removeEventListener('scroll', this.scrollContainer._scrollListener);
        }

        // Add new scroll listener with requestAnimationFrame throttling
        this._rafPending = false;
        this.scrollContainer._scrollListener = () => {
            if (this._rafPending) return;
            this._rafPending = true;
            requestAnimationFrame(() => {
                this._updateVisibleIcons();
                this._rafPending = false;
            });
        };
        this.scrollContainer.addEventListener('scroll', this.scrollContainer._scrollListener);
    }

    /**
     * Update visible icons when scrolling
     */
    _updateVisibleIcons() {
        if (!this.filteredIcons || this.filteredIcons.length === 0) return;
        this._renderVirtualIcons();
    }

    /**
     * Render only the visible portion of icons (virtual scrolling)
     */
    _renderVirtualIcons() {
        if (this.filteredIcons.length === 0) {
            this.icon_grid.innerHTML = '<p class="text-muted">No icons found.</p>';
            this.lastRenderedRange = { start: -1, end: -1 };
            return;
        }

        // Calculate dimensions (rowHeight cached as property)
        const totalIcons = this.filteredIcons.length;
        const totalRows = Math.ceil(totalIcons / this.ICONS_PER_ROW);
        const rowHeight = this.rowHeight; // Use cached value

        // Calculate visible range
        const scrollTop = this.scrollContainer.scrollTop || 0;
        const viewportHeight = this.scrollContainer.offsetHeight || 600;

        const firstVisibleRow = Math.max(0, Math.floor(scrollTop / rowHeight) - this.BUFFER_ROWS);
        const lastVisibleRow = Math.min(totalRows, Math.ceil((scrollTop + viewportHeight) / rowHeight) + this.BUFFER_ROWS);

        const startIndex = firstVisibleRow * this.ICONS_PER_ROW;
        const endIndex = Math.min(totalIcons, lastVisibleRow * this.ICONS_PER_ROW);

        // Check if we can avoid re-rendering by checking if range is close enough
        // Only re-render if we've scrolled at least 3 full rows worth
        const rowThreshold = this.ICONS_PER_ROW * 3;
        if (this.lastRenderedRange.start !== -1 &&
            Math.abs(this.lastRenderedRange.start - startIndex) < rowThreshold &&
            Math.abs(this.lastRenderedRange.end - endIndex) < rowThreshold) {
            return; // Skip re-render, range is close enough
        }

        this.lastRenderedRange = { start: startIndex, end: endIndex };

        // Create document fragment for better performance
        const fragment = document.createDocumentFragment();

        // Add top padding spacer to maintain scroll position
        if (startIndex > 0) {
            const spacer = document.createElement('div');
            spacer.style.cssText = `
                grid-column: 1 / -1;
                height: ${firstVisibleRow * rowHeight}px;
            `;
            fragment.appendChild(spacer);
        }

        // Render visible icons
        for (let i = startIndex; i < endIndex; i++) {
            const icon = this.filteredIcons[i];

            // Generate full class on-the-fly for Pro icons
            let fullClass = icon.fullClass;
            if (icon.isPro && icon.styleFamily && icon.weight) {
                fullClass = this._generateProIconClass(icon.name, icon.styleFamily, icon.weight);
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-light border d-flex align-items-center justify-content-center';
            button.title = icon.name;
            button.dataset.iconClass = fullClass;
            button.style.cssText = `height: ${this.ICON_SIZE}px; width: ${this.ICON_SIZE}px;`;

            if (icon.type === 'font' || icon.isPro) {
                button.innerHTML = `<i class="${fullClass}" style="font-size: 24px;"></i>`;
            } else if (icon.type === 'image') {
                button.dataset.iconUrl = icon.imageUrl;
                button.innerHTML = `<img src="${icon.imageUrl}" alt="${icon.name}" style="width: 24px; height: 24px; object-fit: contain;" loading="lazy">`;
            }

            fragment.appendChild(button);
        }

        // Add bottom padding spacer to maintain total height
        const renderedRows = lastVisibleRow - firstVisibleRow;
        const remainingRows = totalRows - lastVisibleRow;
        if (remainingRows > 0) {
            const spacer = document.createElement('div');
            spacer.style.cssText = `
                grid-column: 1 / -1;
                height: ${remainingRows * rowHeight}px;
            `;
            fragment.appendChild(spacer);
        }

        // Clear and render
        this.icon_grid.innerHTML = '';
        this.icon_grid.appendChild(fragment);
    }

    destroy() {
        this.triggerElement.removeEventListener('click', this.open);

        // Clean up scroll listener
        if (this.scrollContainer && this.scrollContainer._scrollListener) {
            this.scrollContainer.removeEventListener('scroll', this.scrollContainer._scrollListener);
        }
    }
}