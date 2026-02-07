<?php
/**
 * Edit/Manage Addresses Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$user_id = get_current_user_id();
$addresses = ganjeh_get_user_addresses($user_id);
?>

<div class="addresses-page" x-data="addressesManager()">
    <!-- Header -->
    <header class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php _e('آدرس‌های من', 'ganjeh'); ?></h1>
        <button type="button" class="add-btn" @click="openAddModal()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
            </svg>
        </button>
    </header>

    <!-- Addresses List -->
    <div class="addresses-content">
        <template x-if="addresses.length > 0">
            <div class="addresses-list">
                <template x-for="(address, index) in addresses" :key="index">
                    <div class="address-card" :class="{ 'is-default': address.is_default }">
                        <div class="address-header">
                            <div class="address-title-wrap">
                                <span class="address-title" x-text="address.title || '<?php _e('آدرس', 'ganjeh'); ?> ' + (index + 1)"></span>
                                <span class="default-badge" x-show="address.is_default"><?php _e('پیش‌فرض', 'ganjeh'); ?></span>
                            </div>
                            <div class="address-actions">
                                <button type="button" class="action-btn edit" @click="editAddress(index)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button" class="action-btn delete" @click="deleteAddress(index)" x-show="!address.is_default">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="address-body">
                            <div class="address-row">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span x-text="address.receiver_name"></span>
                            </div>
                            <div class="address-row">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="ltr-text" x-text="address.phone"></span>
                            </div>
                            <div class="address-row full">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span x-text="address.state + '، ' + address.city + '، ' + address.address"></span>
                            </div>
                            <template x-if="address.postal_code">
                                <div class="address-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="ltr-text" x-text="address.postal_code"></span>
                                </div>
                            </template>
                        </div>
                        <div class="address-footer" x-show="!address.is_default">
                            <button type="button" class="set-default-btn" @click="setDefault(index)">
                                <?php _e('تنظیم به عنوان پیش‌فرض', 'ganjeh'); ?>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Empty State -->
        <template x-if="addresses.length === 0">
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2><?php _e('آدرسی ثبت نشده', 'ganjeh'); ?></h2>
                <p><?php _e('برای ثبت سفارش، ابتدا آدرس خود را اضافه کنید.', 'ganjeh'); ?></p>
                <button type="button" class="btn-add-first" @click="openAddModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                    </svg>
                    <?php _e('افزودن آدرس', 'ganjeh'); ?>
                </button>
            </div>
        </template>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" x-show="showModal" x-cloak @click.self="closeModal()" x-transition:enter="fade-enter" x-transition:leave="fade-leave">
        <div class="modal-content" x-show="showModal" x-transition:enter="slide-enter" x-transition:leave="slide-leave">
            <div class="modal-header">
                <h3 x-text="editingIndex !== null ? '<?php _e('ویرایش آدرس', 'ganjeh'); ?>' : '<?php _e('افزودن آدرس جدید', 'ganjeh'); ?>'"></h3>
                <button type="button" class="close-btn" @click="closeModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?php _e('عنوان آدرس', 'ganjeh'); ?></label>
                    <input type="text" x-model="form.title" placeholder="<?php esc_attr_e('مثلا: خانه، محل کار', 'ganjeh'); ?>">
                </div>
                <div class="form-group">
                    <label><?php _e('نام گیرنده', 'ganjeh'); ?> <span class="required">*</span></label>
                    <input type="text" x-model="form.receiver_name" placeholder="<?php esc_attr_e('نام و نام خانوادگی گیرنده', 'ganjeh'); ?>">
                </div>
                <div class="form-group">
                    <label><?php _e('شماره موبایل', 'ganjeh'); ?> <span class="required">*</span></label>
                    <input type="tel" x-model="form.phone" class="ltr-input" placeholder="09xxxxxxxxx">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e('استان', 'ganjeh'); ?> <span class="required">*</span></label>
                        <input type="text" x-model="form.state" placeholder="<?php esc_attr_e('استان', 'ganjeh'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php _e('شهر', 'ganjeh'); ?> <span class="required">*</span></label>
                        <input type="text" x-model="form.city" placeholder="<?php esc_attr_e('شهر', 'ganjeh'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php _e('آدرس کامل', 'ganjeh'); ?> <span class="required">*</span></label>
                    <textarea x-model="form.address" rows="3" placeholder="<?php esc_attr_e('خیابان، کوچه، پلاک، واحد', 'ganjeh'); ?>"></textarea>
                </div>
                <div class="form-group">
                    <label><?php _e('کد پستی', 'ganjeh'); ?></label>
                    <input type="text" x-model="form.postal_code" class="ltr-input" placeholder="<?php esc_attr_e('کد پستی 10 رقمی', 'ganjeh'); ?>">
                </div>
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" x-model="form.is_default">
                        <span><?php _e('تنظیم به عنوان آدرس پیش‌فرض', 'ganjeh'); ?></span>
                    </label>
                </div>

                <template x-if="errorMessage">
                    <div class="error-message" x-text="errorMessage"></div>
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" @click="closeModal()"><?php _e('انصراف', 'ganjeh'); ?></button>
                <button type="button" class="btn-save" @click="saveAddress()" :disabled="saving">
                    <span x-show="!saving" x-text="editingIndex !== null ? '<?php _e('ذخیره تغییرات', 'ganjeh'); ?>' : '<?php _e('افزودن آدرس', 'ganjeh'); ?>'"></span>
                    <span x-show="saving"><?php _e('در حال ذخیره...', 'ganjeh'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" x-show="showDeleteModal" x-cloak @click.self="showDeleteModal = false">
        <div class="confirm-modal">
            <div class="confirm-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3><?php _e('حذف آدرس', 'ganjeh'); ?></h3>
            <p><?php _e('آیا از حذف این آدرس اطمینان دارید؟', 'ganjeh'); ?></p>
            <div class="confirm-actions">
                <button type="button" class="btn-cancel" @click="showDeleteModal = false"><?php _e('انصراف', 'ganjeh'); ?></button>
                <button type="button" class="btn-delete" @click="confirmDelete()"><?php _e('حذف', 'ganjeh'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
function addressesManager() {
    return {
        addresses: <?php echo json_encode(array_values($addresses)); ?>,
        showModal: false,
        showDeleteModal: false,
        editingIndex: null,
        deleteIndex: null,
        saving: false,
        errorMessage: '',
        form: {
            title: '',
            receiver_name: '',
            phone: '',
            state: '',
            city: '',
            address: '',
            postal_code: '',
            is_default: false
        },

        openAddModal() {
            this.editingIndex = null;
            this.form = {
                title: '',
                receiver_name: '<?php echo esc_js(wp_get_current_user()->display_name); ?>',
                phone: '<?php echo esc_js(wp_get_current_user()->billing_phone); ?>',
                state: '',
                city: '',
                address: '',
                postal_code: '',
                is_default: this.addresses.length === 0
            };
            this.errorMessage = '';
            this.showModal = true;
        },

        editAddress(index) {
            this.editingIndex = index;
            this.form = { ...this.addresses[index] };
            this.errorMessage = '';
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingIndex = null;
        },

        validateForm() {
            if (!this.form.receiver_name.trim()) {
                this.errorMessage = '<?php echo esc_js(__('نام گیرنده را وارد کنید', 'ganjeh')); ?>';
                return false;
            }
            if (!this.form.phone.trim() || !/^09\d{9}$/.test(this.form.phone.trim())) {
                this.errorMessage = '<?php echo esc_js(__('شماره موبایل معتبر وارد کنید', 'ganjeh')); ?>';
                return false;
            }
            if (!this.form.state.trim()) {
                this.errorMessage = '<?php echo esc_js(__('استان را وارد کنید', 'ganjeh')); ?>';
                return false;
            }
            if (!this.form.city.trim()) {
                this.errorMessage = '<?php echo esc_js(__('شهر را وارد کنید', 'ganjeh')); ?>';
                return false;
            }
            if (!this.form.address.trim()) {
                this.errorMessage = '<?php echo esc_js(__('آدرس کامل را وارد کنید', 'ganjeh')); ?>';
                return false;
            }
            return true;
        },

        saveAddress() {
            if (!this.validateForm()) return;

            this.saving = true;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('action', 'ganjeh_save_address');
            formData.append('nonce', ganjeh.nonce);
            formData.append('address_index', this.editingIndex !== null ? this.editingIndex : -1);
            formData.append('title', this.form.title);
            formData.append('receiver_name', this.form.receiver_name);
            formData.append('phone', this.form.phone);
            formData.append('state', this.form.state);
            formData.append('city', this.form.city);
            formData.append('address', this.form.address);
            formData.append('postal_code', this.form.postal_code);
            formData.append('is_default', this.form.is_default ? '1' : '0');

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                this.saving = false;
                if (data.success) {
                    this.addresses = data.data.addresses;
                    this.closeModal();
                } else {
                    this.errorMessage = data.data.message || '<?php echo esc_js(__('خطا در ذخیره آدرس', 'ganjeh')); ?>';
                }
            })
            .catch(() => {
                this.saving = false;
                this.errorMessage = '<?php echo esc_js(__('لطفا اینترنت خود را چک کنید', 'ganjeh')); ?>';
            });
        },

        deleteAddress(index) {
            this.deleteIndex = index;
            this.showDeleteModal = true;
        },

        confirmDelete() {
            const formData = new FormData();
            formData.append('action', 'ganjeh_delete_address');
            formData.append('nonce', ganjeh.nonce);
            formData.append('address_index', this.deleteIndex);

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.addresses = data.data.addresses;
                }
                this.showDeleteModal = false;
            });
        },

        setDefault(index) {
            // Set default locally first
            this.addresses.forEach((addr, i) => {
                addr.is_default = (i === index);
            });

            // Save to server
            const formData = new FormData();
            formData.append('action', 'ganjeh_save_address');
            formData.append('nonce', ganjeh.nonce);
            formData.append('address_index', index);
            formData.append('title', this.addresses[index].title);
            formData.append('receiver_name', this.addresses[index].receiver_name);
            formData.append('phone', this.addresses[index].phone);
            formData.append('state', this.addresses[index].state);
            formData.append('city', this.addresses[index].city);
            formData.append('address', this.addresses[index].address);
            formData.append('postal_code', this.addresses[index].postal_code);
            formData.append('is_default', '1');

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.addresses = data.data.addresses;
                }
            });
        }
    }
}
</script>

<style>
.addresses-page {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 100px;
}

.page-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f3f4f6;
}

.page-header h1 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.back-btn, .add-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
}

.add-btn {
    color: #4CB050;
}

/* Content */
.addresses-content {
    padding: 16px;
}

.addresses-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Address Card */
.address-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.address-card.is-default {
    border-color: #4CB050;
    border-width: 2px;
}

.address-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
}

.address-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.address-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.default-badge {
    font-size: 10px;
    padding: 2px 8px;
    background: #f0fdf4;
    color: #166534;
    border-radius: 6px;
    font-weight: 500;
}

.address-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.action-btn svg {
    width: 16px;
    height: 16px;
}

.action-btn.edit {
    background: #eff6ff;
    color: #2563eb;
}

.action-btn.delete {
    background: #fef2f2;
    color: #dc2626;
}

.address-body {
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.address-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: #374151;
}

.address-row svg {
    width: 18px;
    height: 18px;
    color: #9ca3af;
    flex-shrink: 0;
    margin-top: 1px;
}

.address-row.full {
    line-height: 1.6;
}

.ltr-text {
    direction: ltr;
    text-align: left;
}

.address-footer {
    padding: 12px 16px;
    border-top: 1px solid #f3f4f6;
}

.set-default-btn {
    width: 100%;
    padding: 10px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: 10px;
    font-size: 13px;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.set-default-btn:hover {
    background: #f0fdf4;
    border-color: #4CB050;
    color: #4CB050;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 16px;
    color: #d1d5db;
}

.empty-icon svg {
    width: 100%;
    height: 100%;
}

.empty-state h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}

.empty-state p {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 24px;
}

.btn-add-first {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    background: #4CB050;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.btn-add-first svg {
    width: 20px;
    height: 20px;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 24px 24px 0 0;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    position: sticky;
    top: 0;
    background: white;
}

.modal-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.close-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.close-btn svg {
    width: 18px;
    height: 18px;
    color: #6b7280;
}

.modal-body {
    padding: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.required {
    color: #ef4444;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
    color: #1f2937;
    background: #f9fafb;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4CB050;
    background: white;
}

.form-group input.ltr-input {
    direction: ltr;
    text-align: left;
}

.form-group textarea {
    resize: none;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.form-row .form-group {
    margin-bottom: 16px;
}

.checkbox-group {
    margin-bottom: 0;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    accent-color: #4CB050;
}

.checkbox-label span {
    font-size: 13px;
    color: #374151;
}

.error-message {
    background: #fef2f2;
    color: #991b1b;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    margin-top: 12px;
}

.modal-footer {
    display: flex;
    gap: 12px;
    padding: 16px;
    border-top: 1px solid #f3f4f6;
    position: sticky;
    bottom: 0;
    background: white;
}

.btn-cancel {
    flex: 1;
    padding: 14px;
    background: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
}

.btn-save {
    flex: 2;
    padding: 14px;
    background: #4CB050;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.btn-save:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Confirm Modal */
.confirm-modal {
    background: white;
    border-radius: 20px;
    padding: 24px;
    margin: auto 16px;
    text-align: center;
    max-width: 320px;
}

.confirm-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #fef3c7;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.confirm-icon svg {
    width: 28px;
    height: 28px;
    color: #d97706;
}

.confirm-modal h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}

.confirm-modal p {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 20px;
}

.confirm-actions {
    display: flex;
    gap: 12px;
}

.confirm-actions .btn-cancel {
    flex: 1;
}

.btn-delete {
    flex: 1;
    padding: 12px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

/* Animations */
[x-cloak] { display: none !important; }

.fade-enter { animation: fadeIn 0.2s ease-out; }
.fade-leave { animation: fadeOut 0.2s ease-out; }
.slide-enter { animation: slideUp 0.3s ease-out; }
.slide-leave { animation: slideDown 0.2s ease-out; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
@keyframes slideDown {
    from { transform: translateY(0); }
    to { transform: translateY(100%); }
}
</style>
