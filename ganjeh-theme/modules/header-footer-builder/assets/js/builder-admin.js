/**
 * Header Footer Builder Admin JavaScript
 */

(function($) {
    'use strict';

    // Builder Object
    const DSTBuilder = {
        settings: {},
        elements: {},
        menus: {},
        currentTab: 'header',
        selectedElement: null,
        selectedRow: null,
        isDirty: false,
        previewTimeout: null,
        previewDebounceTime: 500,

        /**
         * Initialize
         */
        init: function() {
            this.settings = JSON.parse(JSON.stringify(dstBuilder.settings));
            this.elements = dstBuilder.elements;
            this.menus = dstBuilder.menus;

            this.bindEvents();
            this.renderCanvas('header');
            this.renderCanvas('footer');
            this.initDragDrop();
            this.initSortable();
            this.initGlobalSettings();
        },

        /**
         * Bind Events
         */
        bindEvents: function() {
            const self = this;

            // Accordion toggle
            $(document).on('click', '.accordion-header', function(e) {
                // Don't toggle if clicking on input inside header
                if ($(e.target).is('input')) return;

                const $panel = $(this).closest('.accordion-panel');
                $panel.toggleClass('collapsed');
            });

            // Tab switching
            $('.builder-tab').on('click', function() {
                const tab = $(this).data('tab');
                self.switchTab(tab);
            });

            // Device switcher
            $('.device-btn').on('click', function() {
                const device = $(this).data('device');
                self.switchDevice(device);
            });

            // Refresh preview
            $('.refresh-preview').on('click', function() {
                self.refreshPreview();
            });

            // Add row button
            $('.add-row-btn').on('click', function() {
                const type = $(this).data('type');
                self.openLayoutModal(type);
            });

            // Layout modal
            $('.layout-option').on('click', function() {
                const layout = $(this).data('layout');
                const columns = $(this).data('columns');
                self.addRow(self.pendingRowType, layout, columns);
                self.closeLayoutModal();
            });

            // Modal close
            $('.modal-close, .builder-modal').on('click', function(e) {
                if (e.target === this) {
                    self.closeLayoutModal();
                }
            });

            // Save button
            $('#save-builder').on('click', function() {
                self.save();
            });

            // Element search
            $('#element-search').on('input', function() {
                const term = $(this).val().toLowerCase();
                self.searchElements(term);
            });

            // Close settings panel
            $('.close-settings').on('click', function() {
                self.clearSelection();
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl+S to save
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    self.save();
                }
                // Delete selected element
                if (e.key === 'Delete' && self.selectedElement) {
                    self.deleteSelectedElement();
                }
                // Escape to clear selection
                if (e.key === 'Escape') {
                    self.clearSelection();
                }
            });

            // Warn on page leave if dirty
            $(window).on('beforeunload', function() {
                if (self.isDirty) {
                    return 'تغییرات ذخیره نشده دارید. آیا می‌خواهید صفحه را ترک کنید؟';
                }
            });

            // Handle iframe load - remove loading state
            $('#preview-frame').on('load', function() {
                $('.preview-frame-wrapper').removeClass('loading');
            });
        },

        /**
         * Switch Tab
         */
        switchTab: function(tab) {
            this.currentTab = tab;

            $('.builder-tab').removeClass('active');
            $(`.builder-tab[data-tab="${tab}"]`).addClass('active');

            $('.builder-canvas').addClass('hidden');
            $(`#${tab}-canvas`).removeClass('hidden');

            // Update global settings panel
            this.updateGlobalSettingsPanel(tab);

            this.clearSelection();
        },

        /**
         * Switch Device
         */
        switchDevice: function(device) {
            $('.device-btn').removeClass('active');
            $(`.device-btn[data-device="${device}"]`).addClass('active');
            $('.preview-frame-wrapper').attr('data-device', device);
        },

        /**
         * Refresh Preview
         */
        refreshPreview: function() {
            const iframe = document.getElementById('preview-frame');
            iframe.src = iframe.src;
        },

        /**
         * Render Canvas
         */
        renderCanvas: function(type) {
            const container = $(`#${type}-rows`);
            container.empty();

            const data = this.settings[type];
            if (!data || !data.rows) return;

            data.rows.forEach((row, index) => {
                container.append(this.createRowHTML(row, index, type));
            });
        },

        /**
         * Create Row HTML
         */
        createRowHTML: function(row, index, type) {
            const layout = row.layout || '1';
            const columns = row.columns || 1;

            let columnsHTML = '';
            const colKeys = Object.keys(row.elements || {});

            for (let i = 0; i < columns; i++) {
                const colKey = colKeys[i] || `col${i + 1}`;
                const elements = row.elements[colKey] || [];

                let elementsHTML = '';
                elements.forEach((el, elIndex) => {
                    elementsHTML += this.createElementHTML(el, elIndex, index, colKey, type);
                });

                columnsHTML += `
                    <div class="builder-column${elements.length === 0 ? ' empty' : ''}"
                         data-column="${colKey}"
                         data-row="${index}">
                        ${elementsHTML}
                    </div>
                `;
            }

            return `
                <div class="builder-row" data-row="${index}" data-layout="${layout}" data-type="${type}">
                    <div class="row-header" style="display:flex !important;align-items:center;justify-content:space-between;padding:6px 12px;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">
                        <div class="row-actions" style="display:flex !important;gap:5px;">
                            <button type="button" class="row-action-btn settings" data-row="${index}" title="تنظیمات" style="width:28px;height:28px;display:inline-flex !important;align-items:center;justify-content:center;border:1px solid #cbd5e1;background:#fff;border-radius:6px;cursor:pointer;font-size:14px;">⚙</button>
                            <button type="button" class="row-action-btn duplicate" data-row="${index}" title="کپی" style="width:28px;height:28px;display:inline-flex !important;align-items:center;justify-content:center;border:1px solid #cbd5e1;background:#fff;border-radius:6px;cursor:pointer;font-size:14px;">⧉</button>
                            <button type="button" class="row-action-btn delete" data-row="${index}" title="حذف" style="width:28px;height:28px;display:inline-flex !important;align-items:center;justify-content:center;border:1px solid #fca5a5;background:#fee2e2;border-radius:6px;cursor:pointer;font-size:14px;color:#dc2626;">✕</button>
                        </div>
                        <span class="row-label" style="font-weight:600;font-size:12px;color:#475569;">ردیف ${index + 1} (${this.getLayoutLabel(layout)})</span>
                    </div>
                    <div class="row-columns">
                        ${columnsHTML}
                    </div>
                </div>
            `;
        },

        /**
         * Create Element HTML
         */
        createElementHTML: function(element, elIndex, rowIndex, colKey, type) {
            const elData = this.elements[element.type] || {};
            const title = elData.title || element.type;
            const icon = this.getElementIcon(elData.icon || 'square');

            return `
                <div class="canvas-element"
                     data-type="${element.type}"
                     data-index="${elIndex}"
                     data-row="${rowIndex}"
                     data-column="${colKey}"
                     data-section="${type}">
                    <div class="canvas-element-header">
                        <div class="canvas-element-title">
                            ${icon}
                            <span>${title}</span>
                        </div>
                        <div class="canvas-element-actions">
                            <button type="button" class="element-action-btn settings" title="تنظیمات">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </button>
                            <button type="button" class="element-action-btn delete" title="حذف">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Get Layout Label
         */
        getLayoutLabel: function(layout) {
            const labels = {
                '1': '1 ستون',
                '1-1': '2 ستون',
                '1-2': '1/3 - 2/3',
                '2-1': '2/3 - 1/3',
                '1-1-1': '3 ستون',
                '1-2-1': '1/4 - 2/4 - 1/4',
                '1-1-1-1': '4 ستون',
                '1-1-1-1-1': '5 ستون',
                '1-1-1-1-1-1': '6 ستون'
            };
            return labels[layout] || layout;
        },

        /**
         * Get Element Icon
         */
        getElementIcon: function(icon) {
            const icons = {
                'image': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
                'menu': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
                'search': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
                'square': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/></svg>',
                'type': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>',
                'code': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
                'share-2': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
                'shopping-cart': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',
                'user': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                'heart': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
                'phone': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
                'copyright': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9.354a4 4 0 1 0 0 5.292"/></svg>',
                'minus': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>',
                'move-vertical': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 18 12 22 16 18"/><polyline points="8 6 12 2 16 6"/><line x1="12" y1="2" x2="12" y2="22"/></svg>'
            };
            return icons[icon] || icons['square'];
        },

        /**
         * Initialize Drag & Drop
         */
        initDragDrop: function() {
            const self = this;

            // Make elements draggable
            $('.element-item').on('dragstart', function(e) {
                e.originalEvent.dataTransfer.setData('element', $(this).data('element'));
                $(this).addClass('dragging');
            });

            $('.element-item').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.builder-column').removeClass('drag-over');
            });

            // Make columns droppable
            $(document).on('dragover', '.builder-column', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });

            $(document).on('dragleave', '.builder-column', function() {
                $(this).removeClass('drag-over');
            });

            $(document).on('drop', '.builder-column', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');

                const elementType = e.originalEvent.dataTransfer.getData('element');
                if (!elementType) return;

                const rowIndex = parseInt($(this).data('row'));
                const colKey = $(this).data('column');
                const section = $(this).closest('.builder-canvas').data('type');

                self.addElement(section, rowIndex, colKey, elementType);
            });

            // Click on element to select
            $(document).on('click', '.canvas-element', function(e) {
                e.stopPropagation();
                self.selectElement($(this));
            });

            // Click on row to select
            $(document).on('click', '.builder-row', function(e) {
                if ($(e.target).closest('.canvas-element').length) return;
                self.selectRow($(this));
            });

            // Element settings button
            $(document).on('click', '.canvas-element .element-action-btn.settings', function(e) {
                e.stopPropagation();
                self.selectElement($(this).closest('.canvas-element'));
            });

            // Element delete button
            $(document).on('click', '.canvas-element .element-action-btn.delete', function(e) {
                e.stopPropagation();
                const $el = $(this).closest('.canvas-element');
                self.deleteElement($el);
            });

            // Row settings button
            $(document).on('click', '.row-action-btn.settings', function(e) {
                e.stopPropagation();
                self.selectRow($(this).closest('.builder-row'));
            });

            // Row duplicate button
            $(document).on('click', '.row-action-btn.duplicate', function(e) {
                e.stopPropagation();
                const $row = $(this).closest('.builder-row');
                self.duplicateRow($row);
            });

            // Row delete button
            $(document).on('click', '.row-action-btn.delete', function(e) {
                e.stopPropagation();
                const $row = $(this).closest('.builder-row');
                self.deleteRow($row);
            });
        },

        /**
         * Initialize Sortable
         */
        initSortable: function() {
            const self = this;

            // Sortable rows
            $('#header-rows, #footer-rows').sortable({
                handle: '.row-header',
                placeholder: 'ui-sortable-placeholder',
                update: function(event, ui) {
                    self.updateRowOrder($(this).attr('id').replace('-rows', ''));
                }
            });

            // Sortable elements within columns
            $(document).on('sortable-init', function() {
                $('.builder-column').sortable({
                    connectWith: '.builder-column',
                    placeholder: 'ui-sortable-placeholder',
                    update: function(event, ui) {
                        if (this === ui.item.parent()[0]) {
                            self.updateElementOrder();
                        }
                    }
                });
            });

            $(document).trigger('sortable-init');
        },

        /**
         * Add Element
         */
        addElement: function(section, rowIndex, colKey, elementType) {
            const elData = this.elements[elementType];
            if (!elData) return;

            // Get default settings
            const defaultSettings = {};
            Object.keys(elData.settings || {}).forEach(key => {
                defaultSettings[key] = elData.settings[key].default;
            });

            const newElement = {
                type: elementType,
                settings: defaultSettings
            };

            // Add to settings
            if (!this.settings[section].rows[rowIndex].elements[colKey]) {
                this.settings[section].rows[rowIndex].elements[colKey] = [];
            }
            this.settings[section].rows[rowIndex].elements[colKey].push(newElement);

            // Update UI
            this.renderCanvas(section);
            this.initSortable();
            this.markDirty();

            this.showToast('المان اضافه شد', 'success');
        },

        /**
         * Delete Element
         */
        deleteElement: function($el) {
            const section = $el.data('section');
            const rowIndex = parseInt($el.data('row'));
            const colKey = $el.data('column');
            const elIndex = parseInt($el.data('index'));

            // Remove from settings
            this.settings[section].rows[rowIndex].elements[colKey].splice(elIndex, 1);

            // Update UI
            this.renderCanvas(section);
            this.clearSelection();
            this.markDirty();

            this.showToast('المان حذف شد');
        },

        /**
         * Delete Selected Element
         */
        deleteSelectedElement: function() {
            if (this.selectedElement) {
                this.deleteElement(this.selectedElement);
            }
        },

        /**
         * Select Element
         */
        selectElement: function($el) {
            this.clearSelection();
            this.selectedElement = $el;
            $el.addClass('selected');

            const section = $el.data('section');
            const rowIndex = parseInt($el.data('row'));
            const colKey = $el.data('column');
            const elIndex = parseInt($el.data('index'));

            const element = this.settings[section].rows[rowIndex].elements[colKey][elIndex];
            this.showElementSettings(element, section, rowIndex, colKey, elIndex);
        },

        /**
         * Select Row
         */
        selectRow: function($row) {
            this.clearSelection();
            this.selectedRow = $row;
            $row.addClass('selected');

            const section = $row.data('type');
            const rowIndex = parseInt($row.data('row'));
            const row = this.settings[section].rows[rowIndex];

            this.showRowSettings(row, section, rowIndex);
        },

        /**
         * Clear Selection
         */
        clearSelection: function() {
            this.selectedElement = null;
            this.selectedRow = null;
            $('.canvas-element, .builder-row').removeClass('selected');
            this.hideSettings();
        },

        /**
         * Show Element Settings
         */
        showElementSettings: function(element, section, rowIndex, colKey, elIndex) {
            const elData = this.elements[element.type];
            if (!elData) return;

            let html = `
                <div class="settings-section">
                    <div class="settings-section-title">${elData.title}</div>
            `;

            // Generate settings fields
            Object.keys(elData.settings || {}).forEach(key => {
                const setting = elData.settings[key];
                const value = element.settings[key] !== undefined ? element.settings[key] : setting.default;
                html += this.renderSettingField(key, setting, value, section, rowIndex, colKey, elIndex);
            });

            html += '</div>';

            $('#settings-content').html(html);

            // Bind settings change events
            this.bindSettingsEvents(section, rowIndex, colKey, elIndex, 'element');
        },

        /**
         * Show Row Settings
         */
        showRowSettings: function(row, section, rowIndex) {
            const self = this;
            const settings = row.settings || {};
            const currentLayout = row.layout || '1';

            // Layout options
            const layouts = [
                { layout: '1', columns: 1, label: '1 ستون' },
                { layout: '1-1', columns: 2, label: '2 ستون' },
                { layout: '1-2', columns: 2, label: '1/3 - 2/3' },
                { layout: '2-1', columns: 2, label: '2/3 - 1/3' },
                { layout: '1-1-1', columns: 3, label: '3 ستون' },
                { layout: '1-2-1', columns: 3, label: '1/4 - 2/4 - 1/4' },
                { layout: '1-1-1-1', columns: 4, label: '4 ستون' },
                { layout: '1-1-1-1-1', columns: 5, label: '5 ستون' },
                { layout: '1-1-1-1-1-1', columns: 6, label: '6 ستون' }
            ];

            let layoutOptionsHTML = '';
            layouts.forEach(item => {
                const active = item.layout === currentLayout ? ' active' : '';
                const colSpans = item.layout.split('-').map(n => `<span style="flex:${n}"></span>`).join('');
                layoutOptionsHTML += `
                    <button type="button" class="row-layout-btn${active}" data-layout="${item.layout}" data-columns="${item.columns}">
                        <div class="layout-cols">${colSpans}</div>
                        <span class="layout-name">${item.label}</span>
                    </button>
                `;
            });

            let html = `
                <div class="settings-section">
                    <div class="settings-section-title">چیدمان ستون‌ها</div>
                    <div class="row-layout-selector">
                        ${layoutOptionsHTML}
                    </div>
                </div>

                <div class="settings-section">
                    <div class="settings-section-title">استایل ردیف</div>

                    <div class="setting-field">
                        <label>رنگ پس‌زمینه</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="bg_color" value="${settings.bg_color || '#ffffff'}">
                            <input type="text" name="bg_color_text" value="${settings.bg_color || '#ffffff'}">
                        </div>
                    </div>

                    <div class="setting-field">
                        <label>رنگ متن</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="text_color" value="${settings.text_color || '#000000'}">
                            <input type="text" name="text_color_text" value="${settings.text_color || '#000000'}">
                        </div>
                    </div>

                    <div class="setting-field">
                        <label>پدینگ</label>
                        <input type="text" name="padding" value="${settings.padding || '15px 0'}" placeholder="15px 0">
                    </div>

                    <div class="setting-field setting-field-checkbox">
                        <input type="checkbox" name="sticky" id="row_sticky" ${settings.sticky ? 'checked' : ''}>
                        <label for="row_sticky">چسبنده (Sticky)</label>
                    </div>
                </div>

                <div class="settings-section">
                    <button type="button" class="delete-row-btn" data-section="${section}" data-row="${rowIndex}" style="width:100%;padding:10px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">
                        حذف این ردیف
                    </button>
                </div>
            `;

            $('#settings-content').html(html);

            // Bind delete row button
            $('.delete-row-btn').on('click', function() {
                const sec = $(this).data('section');
                const idx = $(this).data('row');
                if (confirm('آیا از حذف این ردیف مطمئن هستید؟')) {
                    self.settings[sec].rows.splice(idx, 1);
                    self.renderCanvas(sec);
                    self.clearSelection();
                    self.markDirty();
                }
            });

            // Bind layout change
            $('.row-layout-btn').on('click', function() {
                const newLayout = $(this).data('layout');
                const newColumns = $(this).data('columns');

                // Update button states
                $('.row-layout-btn').removeClass('active');
                $(this).addClass('active');

                // Update row settings
                self.changeRowLayout(section, rowIndex, newLayout, newColumns);
            });

            // Bind settings change events
            this.bindSettingsEvents(section, rowIndex, null, null, 'row');
        },

        /**
         * Change Row Layout
         */
        changeRowLayout: function(section, rowIndex, newLayout, newColumns) {
            const row = this.settings[section].rows[rowIndex];
            const oldColumns = row.columns || 1;
            const oldElements = row.elements || {};

            // Get new column keys
            const newColKeys = this.getColumnKeys(newColumns);
            const oldColKeys = Object.keys(oldElements);

            // Redistribute elements
            const newElements = {};
            newColKeys.forEach((key, i) => {
                newElements[key] = [];
            });

            // Move elements from old columns to new columns
            let allElements = [];
            oldColKeys.forEach(key => {
                allElements = allElements.concat(oldElements[key] || []);
            });

            // Distribute elements evenly across new columns
            if (allElements.length > 0) {
                const elementsPerCol = Math.ceil(allElements.length / newColumns);
                newColKeys.forEach((key, i) => {
                    const start = i * elementsPerCol;
                    const end = Math.min(start + elementsPerCol, allElements.length);
                    newElements[key] = allElements.slice(start, end);
                });
            }

            // Update row
            row.layout = newLayout;
            row.columns = newColumns;
            row.elements = newElements;

            // Re-render
            this.renderCanvas(section);
            this.initSortable();
            this.markDirty();

            // Re-select the row
            const $newRow = $(`.builder-row[data-type="${section}"][data-row="${rowIndex}"]`);
            this.selectRow($newRow);
        },

        /**
         * Render Setting Field
         */
        renderSettingField: function(key, setting, value, section, rowIndex, colKey, elIndex) {
            let html = '<div class="setting-field">';
            html += `<label>${setting.label}</label>`;

            switch (setting.type) {
                case 'text':
                case 'url':
                case 'email':
                    html += `<input type="${setting.type}" name="${key}" value="${this.escapeHtml(value || '')}">`;
                    break;

                case 'number':
                    html += `<input type="number" name="${key}" value="${value || ''}">`;
                    break;

                case 'textarea':
                    html += `<textarea name="${key}">${this.escapeHtml(value || '')}</textarea>`;
                    break;

                case 'select':
                    html += `<select name="${key}">`;
                    const options = setting.options === 'menus' ? this.menus : setting.options;
                    Object.keys(options).forEach(optKey => {
                        const selected = optKey === value ? ' selected' : '';
                        html += `<option value="${optKey}"${selected}>${options[optKey]}</option>`;
                    });
                    html += '</select>';
                    break;

                case 'checkbox':
                    html = '<div class="setting-field setting-field-checkbox">';
                    html += `<input type="checkbox" name="${key}" id="setting_${key}" ${value ? 'checked' : ''}>`;
                    html += `<label for="setting_${key}">${setting.label}</label>`;
                    break;

                case 'color':
                    html += `
                        <div class="color-input-wrapper">
                            <input type="color" name="${key}" value="${value || '#000000'}">
                            <input type="text" name="${key}_text" value="${value || '#000000'}">
                        </div>
                    `;
                    break;

                case 'image':
                    html += `
                        <div class="image-upload-field${value ? ' has-image' : ''}" data-key="${key}">
                            ${value ? `<img src="${value}"><button type="button" class="remove-image-btn">حذف تصویر</button>` : `
                                <div class="upload-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    <p>کلیک کنید یا بکشید</p>
                                </div>
                            `}
                            <input type="hidden" name="${key}" value="${value || ''}">
                        </div>
                    `;
                    break;
            }

            html += '</div>';
            return html;
        },

        /**
         * Bind Settings Events
         */
        bindSettingsEvents: function(section, rowIndex, colKey, elIndex, type) {
            const self = this;

            // Input change
            $('#settings-content input, #settings-content textarea, #settings-content select').on('change input', function() {
                const name = $(this).attr('name');
                if (!name || name.endsWith('_text')) return;

                let value = $(this).val();
                if ($(this).attr('type') === 'checkbox') {
                    value = $(this).is(':checked');
                }

                if (type === 'element') {
                    self.settings[section].rows[rowIndex].elements[colKey][elIndex].settings[name] = value;
                } else {
                    if (!self.settings[section].rows[rowIndex].settings) {
                        self.settings[section].rows[rowIndex].settings = {};
                    }
                    self.settings[section].rows[rowIndex].settings[name] = value;
                }

                self.markDirty();
            });

            // Color sync
            $('#settings-content input[type="color"]').on('input', function() {
                const name = $(this).attr('name');
                $(`input[name="${name}_text"]`).val($(this).val());
            });

            $('#settings-content input[name$="_text"]').on('input', function() {
                const name = $(this).attr('name').replace('_text', '');
                $(`input[name="${name}"]`).val($(this).val());
            });

            // Image upload
            $('#settings-content .image-upload-field').on('click', function(e) {
                if ($(e.target).hasClass('remove-image-btn')) return;

                const $field = $(this);
                const key = $field.data('key');

                const frame = wp.media({
                    title: 'انتخاب تصویر',
                    button: { text: 'انتخاب' },
                    multiple: false
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    $field.find('input[type="hidden"]').val(attachment.url).trigger('change');
                    $field.addClass('has-image');
                    $field.html(`
                        <img src="${attachment.url}">
                        <button type="button" class="remove-image-btn">حذف تصویر</button>
                        <input type="hidden" name="${key}" value="${attachment.url}">
                    `);
                });

                frame.open();
            });

            // Remove image
            $('#settings-content').on('click', '.remove-image-btn', function(e) {
                e.stopPropagation();
                const $field = $(this).closest('.image-upload-field');
                const key = $field.data('key');

                $field.removeClass('has-image');
                $field.html(`
                    <div class="upload-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <p>کلیک کنید یا بکشید</p>
                    </div>
                    <input type="hidden" name="${key}" value="">
                `);

                $field.find('input[type="hidden"]').trigger('change');
            });
        },

        /**
         * Hide Settings
         */
        hideSettings: function() {
            $('#settings-content').html(`
                <div class="empty-settings">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <p>یک المان یا ردیف را انتخاب کنید</p>
                </div>
            `);
        },

        /**
         * Open Layout Modal
         */
        openLayoutModal: function(type) {
            this.pendingRowType = type;
            $('#layout-modal').addClass('active');
        },

        /**
         * Close Layout Modal
         */
        closeLayoutModal: function() {
            $('#layout-modal').removeClass('active');
            this.pendingRowType = null;
        },

        /**
         * Add Row
         */
        addRow: function(type, layout, columns) {
            const colKeys = this.getColumnKeys(columns);
            const elements = {};
            colKeys.forEach(key => {
                elements[key] = [];
            });

            const newRow = {
                id: 'row_' + Date.now(),
                columns: columns,
                layout: layout,
                elements: elements,
                settings: {
                    bg_color: '#ffffff',
                    padding: '15px 0'
                }
            };

            this.settings[type].rows.push(newRow);
            this.renderCanvas(type);
            this.initSortable();
            this.markDirty();

            this.showToast('ردیف جدید اضافه شد', 'success');
        },

        /**
         * Get Column Keys
         */
        getColumnKeys: function(columns) {
            if (columns === 1) return ['col1'];
            if (columns === 2) return ['left', 'right'];
            if (columns === 3) return ['left', 'center', 'right'];

            const keys = [];
            for (let i = 1; i <= columns; i++) {
                keys.push(`col${i}`);
            }
            return keys;
        },

        /**
         * Duplicate Row
         */
        duplicateRow: function($row) {
            const section = $row.data('type');
            const rowIndex = parseInt($row.data('row'));
            const row = JSON.parse(JSON.stringify(this.settings[section].rows[rowIndex]));
            row.id = 'row_' + Date.now();

            this.settings[section].rows.splice(rowIndex + 1, 0, row);
            this.renderCanvas(section);
            this.initSortable();
            this.markDirty();

            this.showToast('ردیف کپی شد', 'success');
        },

        /**
         * Delete Row
         */
        deleteRow: function($row) {
            if (!confirm(dstBuilder.i18n.confirm_delete)) return;

            const section = $row.data('type');
            const rowIndex = parseInt($row.data('row'));

            this.settings[section].rows.splice(rowIndex, 1);
            this.renderCanvas(section);
            this.clearSelection();
            this.markDirty();

            this.showToast('ردیف حذف شد');
        },

        /**
         * Update Row Order
         */
        updateRowOrder: function(section) {
            const newOrder = [];
            $(`#${section}-rows .builder-row`).each(function() {
                const oldIndex = parseInt($(this).data('row'));
                newOrder.push(this.settings[section].rows[oldIndex]);
            }.bind(this));

            this.settings[section].rows = newOrder;
            this.renderCanvas(section);
            this.markDirty();
        },

        /**
         * Update Element Order
         */
        updateElementOrder: function() {
            const self = this;

            // Rebuild elements for each column
            $('.builder-row').each(function() {
                const section = $(this).data('type');
                const rowIndex = parseInt($(this).data('row'));

                $(this).find('.builder-column').each(function() {
                    const colKey = $(this).data('column');
                    const elements = [];

                    $(this).find('.canvas-element').each(function() {
                        const elSection = $(this).data('section');
                        const elRowIndex = parseInt($(this).data('row'));
                        const elColKey = $(this).data('column');
                        const elIndex = parseInt($(this).data('index'));

                        const element = self.settings[elSection].rows[elRowIndex].elements[elColKey][elIndex];
                        elements.push(element);
                    });

                    self.settings[section].rows[rowIndex].elements[colKey] = elements;
                });
            });

            this.markDirty();
        },

        /**
         * Search Elements
         */
        searchElements: function(term) {
            $('.element-item').each(function() {
                const title = $(this).find('span').text().toLowerCase();
                $(this).toggle(title.includes(term));
            });

            $('.element-category').each(function() {
                const visibleItems = $(this).find('.element-item:visible').length;
                $(this).toggle(visibleItems > 0);
            });
        },

        /**
         * Mark Dirty
         */
        markDirty: function() {
            this.isDirty = true;
            $('#save-builder span').text('ذخیره تغییرات *');

            // Trigger live preview with debounce
            this.triggerLivePreview();
        },

        /**
         * Trigger Live Preview (debounced)
         */
        triggerLivePreview: function() {
            const self = this;

            // Clear previous timeout
            if (this.previewTimeout) {
                clearTimeout(this.previewTimeout);
            }

            // Set new timeout
            this.previewTimeout = setTimeout(function() {
                self.updateLivePreview();
            }, this.previewDebounceTime);
        },

        /**
         * Update Live Preview
         * ذخیره تنظیمات موقت و رفرش iframe
         */
        updateLivePreview: function() {
            const self = this;
            const iframe = document.getElementById('preview-frame');
            if (!iframe) return;

            // Debug: Log settings
            console.log('📤 Sending preview settings:', JSON.stringify(this.settings, null, 2));

            // Show loading state
            $('.preview-frame-wrapper').addClass('loading');

            // Save settings temporarily via AJAX (use JSON to preserve nested structure)
            $.ajax({
                url: dstBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dst_builder_preview',
                    nonce: dstBuilder.nonce,
                    settings_json: JSON.stringify(this.settings)
                },
                success: function(response) {
                    console.log('📥 Preview response:', response);
                    if (response.success && response.data.preview_url) {
                        // Refresh iframe with new URL (cache busted)
                        iframe.src = response.data.preview_url;
                    } else {
                        console.error('Preview error:', response);
                        $('.preview-frame-wrapper').removeClass('loading');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    $('.preview-frame-wrapper').removeClass('loading');
                }
            });
        },

        /**
         * Save
         */
        save: function() {
            const self = this;
            const $btn = $('#save-builder');

            $btn.addClass('saving').find('span').text(dstBuilder.i18n.saving);

            $.ajax({
                url: dstBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dst_builder_save',
                    nonce: dstBuilder.nonce,
                    settings_json: JSON.stringify(this.settings)
                },
                success: function(response) {
                    if (response.success) {
                        $btn.removeClass('saving').addClass('saved').find('span').text(dstBuilder.i18n.saved);
                        self.isDirty = false;
                        self.showToast(response.data.message, 'success');
                        self.refreshPreview();

                        setTimeout(function() {
                            $btn.removeClass('saved').find('span').text(dstBuilder.i18n.save);
                        }, 2000);
                    } else {
                        $btn.removeClass('saving').find('span').text(dstBuilder.i18n.save);
                        self.showToast(dstBuilder.i18n.error, 'error');
                    }
                },
                error: function() {
                    $btn.removeClass('saving').find('span').text(dstBuilder.i18n.save);
                    self.showToast(dstBuilder.i18n.error, 'error');
                }
            });
        },

        /**
         * Show Toast
         */
        showToast: function(message, type = '') {
            const $toast = $('<div class="builder-toast">').text(message);
            if (type) $toast.addClass(type);

            $('body').append($toast);

            setTimeout(function() {
                $toast.addClass('show');
            }, 10);

            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 3000);
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        /**
         * Initialize Global Settings
         */
        initGlobalSettings: function() {
            const self = this;

            // Load initial values
            this.loadGlobalSettingsValues('header');
            this.loadGlobalSettingsValues('footer');

            // Helper to update settings
            const updateSetting = (target, key, value) => {
                if (!self.settings[target].settings) {
                    self.settings[target].settings = {};
                }
                self.settings[target].settings[key] = value;
                self.markDirty();
            };

            // Width option buttons
            $('.width-option').on('click', function() {
                const target = $(this).data('target');
                const width = $(this).data('width');
                $(`.width-option[data-target="${target}"]`).removeClass('active');
                $(this).addClass('active');
                updateSetting(target, 'width_type', width);
                if (width === 'contained') {
                    $(`#${target}-container-width-row`).removeClass('hidden');
                } else {
                    $(`#${target}-container-width-row`).addClass('hidden');
                }
            });

            // Container width input
            $('#header-container-width, #footer-container-width').on('change input', function() {
                const target = $(this).attr('id').replace('-container-width', '');
                updateSetting(target, 'container_width', parseInt($(this).val()) || 1200);
            });

            // Enabled toggles
            $('#header-enabled, #footer-enabled').on('change', function() {
                const target = $(this).attr('id').replace('-enabled', '');
                self.settings[target].enabled = $(this).is(':checked');
                self.markDirty();
            });

            // Sticky toggle (header only)
            $('#header-sticky').on('change', function() {
                updateSetting('header', 'sticky', $(this).is(':checked'));
            });

            // Background color inputs
            $('#header-bg-color, #footer-bg-color').on('input change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                const value = $(this).val();
                $(`#${target}-bg-color-text`).val(value);
                updateSetting(target, 'bg_color', value);
            });

            $('#header-bg-color-text, #footer-bg-color-text').on('input change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                const value = $(this).val();
                if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^rgba?\(/.test(value)) {
                    $(`#${target}-bg-color`).val(value.startsWith('#') ? value : '#ffffff');
                    updateSetting(target, 'bg_color', value);
                }
            });

            // Shadow enabled toggle (header only)
            $('#header-shadow-enabled').on('change', function() {
                const isChecked = $(this).is(':checked');
                updateSetting('header', 'shadow_enabled', isChecked);
                if (isChecked) {
                    $('#header-shadow-settings').removeClass('hidden');
                } else {
                    $('#header-shadow-settings').addClass('hidden');
                }
            });

            // Shadow color
            $('#header-shadow-color').on('input change', function() {
                $('#header-shadow-color-text').val($(this).val());
            });

            $('#header-shadow-color-text').on('input change', function() {
                const value = $(this).val();
                updateSetting('header', 'shadow_color', value);
            });

            // Shadow values
            $('#header-shadow-x, #header-shadow-y, #header-shadow-blur, #header-shadow-spread').on('input change', function() {
                const prop = $(this).attr('id').replace('header-shadow-', 'shadow_');
                updateSetting('header', prop, parseInt($(this).val()) || 0);
            });

            // Border enabled toggles
            $('#header-border-enabled, #footer-border-enabled').on('change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                const isChecked = $(this).is(':checked');
                updateSetting(target, 'border_enabled', isChecked);
                if (isChecked) {
                    $(`#${target}-border-settings`).removeClass('hidden');
                } else {
                    $(`#${target}-border-settings`).addClass('hidden');
                }
            });

            // Border color inputs
            $('#header-border-color, #footer-border-color').on('input change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                const value = $(this).val();
                $(`#${target}-border-color-text`).val(value);
                updateSetting(target, 'border_color', value);
            });

            $('#header-border-color-text, #footer-border-color-text').on('input change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                const value = $(this).val();
                $(`#${target}-border-color`).val(value);
                updateSetting(target, 'border_color', value);
            });

            // Border width
            $('#header-border-width, #footer-border-width').on('input change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                updateSetting(target, 'border_width', parseInt($(this).val()) || 1);
            });

            // Border style
            $('#header-border-style, #footer-border-style').on('change', function() {
                const target = $(this).attr('id').includes('header') ? 'header' : 'footer';
                updateSetting(target, 'border_style', $(this).val());
            });
        },

        /**
         * Load Global Settings Values
         */
        loadGlobalSettingsValues: function(type) {
            const data = this.settings[type];
            const settings = data.settings || {};

            // Enabled
            $(`#${type}-enabled`).prop('checked', data.enabled !== false);

            // Width type
            const widthType = settings.width_type || 'contained';
            $(`.width-option[data-target="${type}"]`).removeClass('active');
            $(`.width-option[data-target="${type}"][data-width="${widthType}"]`).addClass('active');

            // Container width
            $(`#${type}-container-width`).val(settings.container_width || 1200);
            if (widthType !== 'contained') {
                $(`#${type}-container-width-row`).addClass('hidden');
            }

            // Background color
            const bgColor = settings.bg_color || (type === 'header' ? '#ffffff' : '#1f2937');
            $(`#${type}-bg-color`).val(bgColor.startsWith('#') ? bgColor : '#ffffff');
            $(`#${type}-bg-color-text`).val(bgColor);

            // Header-specific settings
            if (type === 'header') {
                // Sticky
                $('#header-sticky').prop('checked', settings.sticky === true);

                // Shadow
                const shadowEnabled = settings.shadow_enabled !== false;
                $('#header-shadow-enabled').prop('checked', shadowEnabled);
                if (!shadowEnabled) {
                    $('#header-shadow-settings').addClass('hidden');
                }
                $('#header-shadow-color-text').val(settings.shadow_color || 'rgba(0,0,0,0.08)');
                $('#header-shadow-x').val(settings.shadow_x ?? 0);
                $('#header-shadow-y').val(settings.shadow_y ?? 2);
                $('#header-shadow-blur').val(settings.shadow_blur ?? 10);
                $('#header-shadow-spread').val(settings.shadow_spread ?? 0);
            }

            // Border
            const borderEnabled = settings.border_enabled === true;
            $(`#${type}-border-enabled`).prop('checked', borderEnabled);
            if (borderEnabled) {
                $(`#${type}-border-settings`).removeClass('hidden');
            }
            const borderColor = settings.border_color || (type === 'header' ? '#e5e7eb' : '#374151');
            $(`#${type}-border-color`).val(borderColor);
            $(`#${type}-border-color-text`).val(borderColor);
            $(`#${type}-border-width`).val(settings.border_width || 1);
            $(`#${type}-border-style`).val(settings.border_style || 'solid');
        },

        /**
         * Update Global Settings Panel
         */
        updateGlobalSettingsPanel: function(tab) {
            // Update title
            $('#global-settings-title').text(tab === 'header' ? 'تنظیمات هدر' : 'تنظیمات فوتر');

            // Toggle settings groups
            $('.global-settings-group').addClass('hidden');
            $(`#${tab}-global-settings`).removeClass('hidden');
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.dst-builder-wrap').length) {
            DSTBuilder.init();
        }
    });

})(jQuery);
