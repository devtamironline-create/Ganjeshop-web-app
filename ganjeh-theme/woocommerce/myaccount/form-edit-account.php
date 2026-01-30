<?php
/**
 * Edit Account Form Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$user = wp_get_current_user();
?>

<div class="edit-account-page" x-data="editAccountForm()">
    <!-- Header -->
    <header class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php _e('ویرایش حساب', 'ganjeh'); ?></h1>
        <div class="spacer"></div>
    </header>

    <div class="form-content">
        <form method="post" @submit.prevent="submitForm">
            <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>

            <!-- Avatar Section -->
            <div class="avatar-section">
                <div class="avatar-circle">
                    <?php echo get_avatar($user->ID, 80); ?>
                </div>
                <div class="avatar-info">
                    <span class="user-name"><?php echo esc_html($user->display_name ?: $user->user_login); ?></span>
                    <span class="user-phone"><?php echo esc_html($user->billing_phone ?: $user->user_email); ?></span>
                </div>
            </div>

            <!-- Personal Info Card -->
            <div class="form-card">
                <h3 class="card-title"><?php _e('اطلاعات شخصی', 'ganjeh'); ?></h3>

                <div class="form-group">
                    <label for="account_first_name"><?php _e('نام', 'ganjeh'); ?></label>
                    <input type="text"
                           name="account_first_name"
                           id="account_first_name"
                           x-model="form.first_name"
                           placeholder="<?php esc_attr_e('نام خود را وارد کنید', 'ganjeh'); ?>">
                </div>

                <div class="form-group">
                    <label for="account_last_name"><?php _e('نام خانوادگی', 'ganjeh'); ?></label>
                    <input type="text"
                           name="account_last_name"
                           id="account_last_name"
                           x-model="form.last_name"
                           placeholder="<?php esc_attr_e('نام خانوادگی خود را وارد کنید', 'ganjeh'); ?>">
                </div>

                <div class="form-group">
                    <label for="account_display_name"><?php _e('نام نمایشی', 'ganjeh'); ?></label>
                    <input type="text"
                           name="account_display_name"
                           id="account_display_name"
                           x-model="form.display_name"
                           placeholder="<?php esc_attr_e('نام نمایشی', 'ganjeh'); ?>">
                    <span class="field-hint"><?php _e('این نام در سایت نمایش داده می‌شود', 'ganjeh'); ?></span>
                </div>

                <div class="form-group">
                    <label for="account_email"><?php _e('ایمیل', 'ganjeh'); ?></label>
                    <input type="email"
                           name="account_email"
                           id="account_email"
                           x-model="form.email"
                           placeholder="<?php esc_attr_e('ایمیل خود را وارد کنید', 'ganjeh'); ?>">
                </div>

                <div class="form-group">
                    <label for="billing_phone"><?php _e('شماره موبایل', 'ganjeh'); ?></label>
                    <input type="tel"
                           name="billing_phone"
                           id="billing_phone"
                           x-model="form.phone"
                           class="ltr-input"
                           placeholder="09xxxxxxxxx"
                           readonly>
                    <span class="field-hint"><?php _e('شماره موبایل قابل تغییر نیست', 'ganjeh'); ?></span>
                </div>
            </div>

            <!-- Password Card -->
            <div class="form-card">
                <h3 class="card-title"><?php _e('تغییر رمز عبور', 'ganjeh'); ?></h3>
                <p class="card-desc"><?php _e('در صورت تمایل به تغییر رمز عبور، فیلدهای زیر را پر کنید', 'ganjeh'); ?></p>

                <div class="form-group">
                    <label for="password_current"><?php _e('رمز عبور فعلی', 'ganjeh'); ?></label>
                    <div class="password-input-wrap">
                        <input :type="showCurrentPass ? 'text' : 'password'"
                               name="password_current"
                               id="password_current"
                               x-model="form.password_current"
                               autocomplete="current-password">
                        <button type="button" class="toggle-pass" @click="showCurrentPass = !showCurrentPass">
                            <svg x-show="!showCurrentPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showCurrentPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_1"><?php _e('رمز عبور جدید', 'ganjeh'); ?></label>
                    <div class="password-input-wrap">
                        <input :type="showNewPass ? 'text' : 'password'"
                               name="password_1"
                               id="password_1"
                               x-model="form.password_1"
                               autocomplete="new-password">
                        <button type="button" class="toggle-pass" @click="showNewPass = !showNewPass">
                            <svg x-show="!showNewPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNewPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_2"><?php _e('تکرار رمز عبور جدید', 'ganjeh'); ?></label>
                    <div class="password-input-wrap">
                        <input :type="showConfirmPass ? 'text' : 'password'"
                               name="password_2"
                               id="password_2"
                               x-model="form.password_2"
                               autocomplete="new-password">
                        <button type="button" class="toggle-pass" @click="showConfirmPass = !showConfirmPass">
                            <svg x-show="!showConfirmPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirmPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <template x-if="message">
                <div class="form-message" :class="messageType">
                    <span x-text="message"></span>
                </div>
            </template>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit" :disabled="loading" :class="{ 'loading': loading }">
                <span x-show="!loading"><?php _e('ذخیره تغییرات', 'ganjeh'); ?></span>
                <span x-show="loading" class="btn-loading">
                    <svg viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <?php _e('در حال ذخیره...', 'ganjeh'); ?>
                </span>
            </button>
        </form>
    </div>
</div>

<script>
function editAccountForm() {
    return {
        loading: false,
        message: '',
        messageType: 'success',
        showCurrentPass: false,
        showNewPass: false,
        showConfirmPass: false,
        form: {
            first_name: '<?php echo esc_js($user->first_name); ?>',
            last_name: '<?php echo esc_js($user->last_name); ?>',
            display_name: '<?php echo esc_js($user->display_name); ?>',
            email: '<?php echo esc_js($user->user_email); ?>',
            phone: '<?php echo esc_js($user->billing_phone); ?>',
            password_current: '',
            password_1: '',
            password_2: ''
        },

        submitForm() {
            this.message = '';

            // Validate passwords match
            if (this.form.password_1 && this.form.password_1 !== this.form.password_2) {
                this.message = '<?php echo esc_js(__('رمز عبور جدید و تکرار آن یکسان نیستند', 'ganjeh')); ?>';
                this.messageType = 'error';
                return;
            }

            // Validate current password if changing
            if (this.form.password_1 && !this.form.password_current) {
                this.message = '<?php echo esc_js(__('لطفا رمز عبور فعلی را وارد کنید', 'ganjeh')); ?>';
                this.messageType = 'error';
                return;
            }

            this.loading = true;

            const formData = new FormData();
            formData.append('action', 'ganjeh_update_account');
            formData.append('nonce', ganjeh.nonce);
            formData.append('first_name', this.form.first_name);
            formData.append('last_name', this.form.last_name);
            formData.append('display_name', this.form.display_name);
            formData.append('email', this.form.email);
            formData.append('password_current', this.form.password_current);
            formData.append('password_1', this.form.password_1);
            formData.append('password_2', this.form.password_2);

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.message = data.data.message;
                    this.messageType = 'success';
                    // Clear password fields
                    this.form.password_current = '';
                    this.form.password_1 = '';
                    this.form.password_2 = '';
                } else {
                    this.message = data.data.message || '<?php echo esc_js(__('خطا در ذخیره اطلاعات', 'ganjeh')); ?>';
                    this.messageType = 'error';
                }
            })
            .catch(() => {
                this.loading = false;
                this.message = '<?php echo esc_js(__('خطا در ارتباط با سرور', 'ganjeh')); ?>';
                this.messageType = 'error';
            });
        }
    }
}
</script>

<style>
.edit-account-page {
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

.back-btn, .spacer {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    text-decoration: none;
}

/* Avatar Section */
.avatar-section {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px 16px;
    background: linear-gradient(135deg, #4CB050, #3D9142);
    margin: 16px;
    border-radius: 16px;
}

.avatar-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,0.3);
}

.avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.avatar-info .user-name {
    font-size: 16px;
    font-weight: 700;
    color: white;
}

.avatar-info .user-phone {
    font-size: 13px;
    color: rgba(255,255,255,0.8);
    direction: ltr;
    text-align: right;
}

/* Form Content */
.form-content {
    padding: 0 16px;
}

/* Form Card */
.form-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
}

.card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px;
}

.card-desc {
    font-size: 12px;
    color: #9ca3af;
    margin: 0 0 16px;
}

/* Form Group */
.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
    color: #1f2937;
    background: #f9fafb;
    transition: all 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #4CB050;
    background: white;
}

.form-group input[readonly] {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.form-group input.ltr-input {
    direction: ltr;
    text-align: left;
}

.field-hint {
    display: block;
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
}

/* Password Input */
.password-input-wrap {
    position: relative;
}

.password-input-wrap input {
    padding-left: 44px;
}

.toggle-pass {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #9ca3af;
}

.toggle-pass svg {
    width: 20px;
    height: 20px;
}

/* Message */
.form-message {
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 12px;
}

.form-message.success {
    background: #f0fdf4;
    color: #166534;
}

.form-message.error {
    background: #fef2f2;
    color: #991b1b;
}

/* Submit Button */
.btn-submit {
    width: 100%;
    padding: 14px;
    background: #4CB050;
    color: white;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-submit:hover {
    background: #3D9142;
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-loading {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-loading svg {
    width: 18px;
    height: 18px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
