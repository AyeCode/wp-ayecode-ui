/**
 * Inspiration taken from https://github.com/migliori/universal-icon-picker
 */
class AyeCodeIconPicker {
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

        // --- *** THE MISSING LINE IS HERE *** ---
        // This sets up default options and merges them with any options the user passes in.
        this.options = Object.assign({
            iconPickerUrl: '', // A default to prevent errors, should be overridden
            iconLibraries: [
                'font-awesome-solid.min.json',
                'font-awesome-regular.min.json',
                'font-awesome-brands.min.json'
            ],
            onSelect: () => {},
        }, options);

        // --- State ---
        this.allIcons = {};
        this.activeLibrary = 'all';
        this.searchTerm = '';
        this.isLoading = true;
        this.modalInstance = null;
        window.myIconPickerCache = window.myIconPickerCache || { data: null, promise: null };

        // --- Bind Methods ---
        this.open = this.open.bind(this);
        this.close = this.close.bind(this);
        this._handleIconSelect = this._handleIconSelect.bind(this);
        this._handleSearch = this._handleSearch.bind(this);
        this._handleLibraryChange = this._handleLibraryChange.bind(this);
        this._handleKeyDown = this._handleKeyDown.bind(this);

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
                    <input type="text" class="form-control mb-3 py-3" id="aip-search-input" placeholder="Search icons…">
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
        this.icon_grid = this.modalInstance.querySelector('#aip-icon-grid');
        this.loader = this.modalInstance.querySelector('#aip-loader');
        this.library_tabs_container = this.modalInstance.querySelector('#aip-library-tabs');

        this.search_input.addEventListener('input', this._handleSearch);
        document.addEventListener('keydown', this._handleKeyDown);

        this.modalInstance.addEventListener('hidden.bs.modal', () => {
            document.removeEventListener('keydown', this._handleKeyDown);
        }, { once: true });

        this._renderLibraryTabs();
        this._renderIcons();
        this.search_input.focus();
    }

    close() {
        if (!this.modalInstance) return;
        const modal = bootstrap.Modal.getInstance(this.modalInstance);
        if (modal) {
            modal.hide();
        }
    }

    async _fetchIcons() {
        if (window.myIconPickerCache.data) {
            this.allIcons = window.myIconPickerCache.data;
            this.isLoading = false;
            return;
        }
        if (window.myIconPickerCache.promise) {
            await window.myIconPickerCache.promise;
            this.allIcons = window.myIconPickerCache.data;
            this.isLoading = false;
            return;
        }
        const fetchPromise = (async () => {
            try {
                const libraryPromises = this.options.iconLibraries.map(async (fileName) => {
                    const key = fileName.replace('font-awesome-', '').replace('.min.json', '');
                    const response = await fetch(`${this.options.iconPickerUrl}/${fileName}`);
                    if (!response.ok) throw new Error(`Failed to fetch ${fileName}`);
                    const data = await response.json();
                    return [key, data];
                });
                const loadedLibraries = await Promise.all(libraryPromises);
                window.myIconPickerCache.data = Object.fromEntries(loadedLibraries);
                this.allIcons = window.myIconPickerCache.data;
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

    _renderLibraryTabs() {
        const libs = { 'all': 'All Icons', ...Object.fromEntries(Object.keys(this.allIcons).map(k => [k, k.charAt(0).toUpperCase() + k.slice(1)])) };
        let tabsHtml = '';
        for (const [key, name] of Object.entries(libs)) {
            const activeClass = this.activeLibrary === key ? 'active' : '';
            tabsHtml += `<button type="button" class="nav-link text-start ${activeClass}" data-library-key="${key}">${name}</button>`;
        }
        this.library_tabs_container.innerHTML = tabsHtml;
        this.library_tabs_container.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('click', this._handleLibraryChange);
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

        let iconsToFilter = [];
        const libraries = this.activeLibrary === 'all'
            ? Object.values(this.allIcons)
            : [this.allIcons[this.activeLibrary]];

        for (const library of libraries) {
            if (!library) continue;
            library.icons.forEach(iconName => {
                iconsToFilter.push({ name: iconName, fullClass: `${library.prefix}${iconName}` });
            });
        }

        const searchTermLower = this.searchTerm.toLowerCase();
        const filteredIcons = !this.searchTerm
            ? iconsToFilter
            : iconsToFilter.filter(icon => icon.name.toLowerCase().includes(searchTermLower));

        let gridHtml = '';
        if (filteredIcons.length > 0) {
            filteredIcons.forEach(icon => {
                gridHtml += `
                <button type="button" class="btn btn-light border d-flex align-items-center justify-content-center" title="${icon.name}" data-icon-class="${icon.fullClass}" style="height: 60px;">
                    <i class="${icon.fullClass}" style="font-size: 24px;"></i>
                </button>
            `;
            });
        } else {
            gridHtml = '<p class="text-muted">No icons found.</p>';
        }

        this.icon_grid.innerHTML = gridHtml;
        this.icon_grid.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', this._handleIconSelect);
        });
    }

    _handleSearch(e) {
        this.searchTerm = e.target.value;
        this._renderIcons();
    }

    _handleLibraryChange(e) {
        this.activeLibrary = e.target.dataset.libraryKey;
        this.library_tabs_container.querySelector('.active').classList.remove('active');
        e.target.classList.add('active');
        this._renderIcons();
    }

    _handleIconSelect(e) {
        const iconClass = e.currentTarget.dataset.iconClass;
        const iconHtml = e.currentTarget.querySelector('i').outerHTML;
        this.options.onSelect({ iconClass, iconHtml });
        this.close();
    }

    _handleKeyDown(e) {
        if (e.key === "Escape") {
            this.close();
        }
    }

    destroy() {
        this.triggerElement.removeEventListener('click', this.open);
    }
}