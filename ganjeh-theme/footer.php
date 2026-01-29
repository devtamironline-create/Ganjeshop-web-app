    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pb-20">
        <div class="px-4 py-6">
            <!-- Copyright -->
            <p class="text-center text-xs text-gray-400">
                <?php printf(__('© %s گنجه مارکت. تمامی حقوق محفوظ است.', 'ganjeh'), date('Y')); ?>
            </p>
        </div>
    </footer>

    <!-- Bottom Navigation (hide on single product pages) -->
    <?php if (!is_singular('product')) : ?>
        <?php get_template_part('template-parts/components/bottom-nav'); ?>
    <?php endif; ?>

    <!-- Cart Toast Notification -->
    <div id="cart-toast" class="cart-toast" x-data="cartToast()" @show-cart-toast.window="show($event.detail)">
        <div class="cart-toast-content" x-show="visible" x-transition:enter="toast-enter" x-transition:leave="toast-leave">
            <div class="toast-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="toast-message" x-text="message"></span>
            <a :href="cartUrl" class="toast-btn"><?php _e('مشاهده سبد', 'ganjeh'); ?></a>
            <button type="button" class="toast-close" @click="hide()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <style>
    .cart-toast {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 32px);
        max-width: 480px;
        z-index: 9998;
        pointer-events: none;
    }
    .cart-toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #1f2937;
        color: white;
        padding: 14px 16px;
        border-radius: 14px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        pointer-events: auto;
    }
    .toast-icon {
        width: 28px;
        height: 28px;
        background: #4CB050;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .toast-icon svg {
        color: white;
    }
    .toast-message {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
    }
    .toast-btn {
        padding: 8px 16px;
        background: white;
        color: #1f2937;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
    }
    .toast-enter { animation: slideUp 0.3s ease; }
    .toast-leave { animation: slideDown 0.3s ease; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideDown {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(20px); }
    }
    </style>

    <script>
    function cartToast() {
        return {
            visible: false,
            message: '',
            cartUrl: '<?php echo wc_get_cart_url(); ?>',
            timeout: null,

            show(data) {
                this.message = data.message || '<?php _e('به سبد خرید اضافه شد', 'ganjeh'); ?>';
                if (data.cart_url) this.cartUrl = data.cart_url;
                this.visible = true;

                if (this.timeout) clearTimeout(this.timeout);
                this.timeout = setTimeout(() => this.hide(), 5000);
            },

            hide() {
                this.visible = false;
            }
        };
    }

    // Global function to show cart toast
    window.showCartToast = function(data) {
        window.dispatchEvent(new CustomEvent('show-cart-toast', { detail: data }));
    };
    </script>

    <!-- Login/Register Modal -->
    <?php if (!is_user_logged_in()) : ?>
    <div id="auth-modal" class="auth-modal" x-data="authModal()" @open-auth.window="openModal($event.detail)">
        <div class="auth-overlay" @click="closeModal()"></div>
        <div class="auth-content">
            <div class="auth-header">
                <h3 x-text="step === 'phone' ? 'ورود / ثبت نام' : 'کد تایید'"></h3>
                <button type="button" @click="closeModal()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="auth-body">
                <!-- Step 1: Phone Number -->
                <div x-show="step === 'phone'" x-transition>
                    <p class="auth-desc">شماره موبایل خود را وارد کنید</p>
                    <div class="auth-input-group">
                        <input type="tel" x-model="mobile" @keyup.enter="sendOtp()" placeholder="09123456789" maxlength="11" dir="ltr" class="auth-input">
                    </div>
                    <button type="button" @click="sendOtp()" :disabled="loading" class="auth-btn">
                        <span x-show="!loading">دریافت کد تایید</span>
                        <svg x-show="loading" class="auth-spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Step 2: OTP Verification -->
                <div x-show="step === 'otp'" x-transition>
                    <p class="auth-desc">کد ارسال شده به <span x-text="mobile" dir="ltr"></span> را وارد کنید</p>

                    <!-- 4-digit OTP boxes -->
                    <div class="otp-boxes" dir="ltr">
                        <template x-for="(digit, index) in otpDigits" :key="index">
                            <input
                                type="text"
                                maxlength="1"
                                class="otp-box"
                                :value="digit"
                                @input="handleOtpInput($event, index)"
                                @keydown="handleOtpKeydown($event, index)"
                                @paste="handleOtpPaste($event)"
                                x-ref="otpInput"
                            >
                        </template>
                    </div>

                    <!-- Name field for new users -->
                    <div x-show="isNewUser" class="auth-input-group" style="margin-top: 16px;">
                        <input type="text" x-model="fullName" placeholder="نام و نام خانوادگی" class="auth-input">
                    </div>

                    <div class="auth-timer" x-show="timer > 0">
                        <span>ارسال مجدد تا</span>
                        <span x-text="formatTimer(timer)"></span>
                    </div>
                    <button type="button" x-show="timer === 0" @click="sendOtp()" class="auth-resend">ارسال مجدد کد</button>
                    <button type="button" @click="verifyOtp()" :disabled="loading" class="auth-btn">
                        <span x-show="!loading">تایید</span>
                        <svg x-show="loading" class="auth-spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    <button type="button" @click="step = 'phone'" class="auth-back">تغییر شماره</button>
                </div>

                <!-- Message -->
                <div class="auth-message" x-show="message" :class="messageType" x-text="message"></div>
            </div>
        </div>
    </div>

    <style>
    .auth-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }
    .auth-modal.show { display: flex; }
    .auth-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
    }
    .auth-content {
        position: relative;
        width: 100%;
        max-width: 400px;
        background: white;
        border-radius: 20px 20px 0 0;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    .auth-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    .auth-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
    .auth-header button {
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #6b7280;
    }
    .auth-body {
        padding: 24px 20px 32px;
    }
    .auth-desc {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 20px;
        text-align: center;
    }
    .auth-input-group {
        margin-bottom: 16px;
    }
    .auth-input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        text-align: center;
        transition: all 0.2s;
    }
    .auth-input:focus {
        outline: none;
        border-color: #4CB050;
        box-shadow: 0 0 0 3px rgba(76, 176, 80, 0.1);
    }
    .otp-boxes {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 16px;
    }
    .otp-box {
        width: 56px;
        height: 56px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
        transition: all 0.2s;
    }
    .otp-box:focus {
        outline: none;
        border-color: #4CB050;
        box-shadow: 0 0 0 3px rgba(76, 176, 80, 0.1);
    }
    .auth-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #4CB050, #3d9142);
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 8px;
    }
    .auth-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .auth-spinner {
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .auth-timer {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }
    .auth-resend {
        display: block;
        width: 100%;
        background: none;
        border: none;
        font-size: 13px;
        color: #4CB050;
        cursor: pointer;
        margin-bottom: 12px;
    }
    .auth-back {
        display: block;
        width: 100%;
        background: none;
        border: none;
        font-size: 13px;
        color: #6b7280;
        cursor: pointer;
        margin-top: 16px;
    }
    .auth-message {
        padding: 12px;
        border-radius: 10px;
        font-size: 13px;
        text-align: center;
        margin-top: 16px;
    }
    .auth-message.success {
        background: #d1fae5;
        color: #065f46;
    }
    .auth-message.error {
        background: #fee2e2;
        color: #991b1b;
    }
    </style>

    <script>
    function authModal() {
        return {
            step: 'phone',
            mobile: '',
            otpDigits: ['', '', '', ''],
            fullName: '',
            loading: false,
            message: '',
            messageType: '',
            timer: 0,
            timerInterval: null,
            isNewUser: false,
            pendingAction: null,

            openModal(actionData) {
                document.getElementById('auth-modal').classList.add('show');
                this.reset();
                if (actionData) {
                    this.pendingAction = actionData;
                }
            },

            closeModal() {
                document.getElementById('auth-modal').classList.remove('show');
            },

            reset() {
                this.step = 'phone';
                this.otpDigits = ['', '', '', ''];
                this.fullName = '';
                this.message = '';
                this.timer = 0;
                this.isNewUser = false;
                this.pendingAction = null;
                if (this.timerInterval) clearInterval(this.timerInterval);
            },

            handleOtpInput(event, index) {
                const value = event.target.value.replace(/[^0-9]/g, '');
                this.otpDigits[index] = value.slice(-1);
                event.target.value = this.otpDigits[index];

                if (value && index < 3) {
                    const inputs = document.querySelectorAll('.otp-box');
                    inputs[index + 1].focus();
                }
            },

            handleOtpKeydown(event, index) {
                if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                    const inputs = document.querySelectorAll('.otp-box');
                    inputs[index - 1].focus();
                }
            },

            handleOtpPaste(event) {
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/[^0-9]/g, '').slice(0, 4).split('');
                const inputs = document.querySelectorAll('.otp-box');

                digits.forEach((digit, i) => {
                    this.otpDigits[i] = digit;
                    if (inputs[i]) inputs[i].value = digit;
                });

                if (digits.length > 0 && inputs[Math.min(digits.length, 3)]) {
                    inputs[Math.min(digits.length, 3)].focus();
                }
            },

            formatTimer(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return `${m}:${s.toString().padStart(2, '0')}`;
            },

            startTimer() {
                this.timer = 120;
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    this.timer--;
                    if (this.timer <= 0) {
                        clearInterval(this.timerInterval);
                    }
                }, 1000);
            },

            sendOtp() {
                if (!this.mobile || this.mobile.length < 10) {
                    this.message = 'شماره موبایل را وارد کنید';
                    this.messageType = 'error';
                    return;
                }

                this.loading = true;
                this.message = '';

                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ganjeh_send_otp',
                        mobile: this.mobile,
                        nonce: ganjeh.nonce
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.isNewUser = data.data.is_new_user;
                        this.step = 'otp';
                        this.startTimer();
                        this.message = data.data.message;
                        this.messageType = 'success';
                        setTimeout(() => this.message = '', 3000);
                    } else {
                        this.message = data.data.message;
                        this.messageType = 'error';
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.message = 'خطا در ارسال. لطفاً دوباره تلاش کنید';
                    this.messageType = 'error';
                });
            },

            verifyOtp() {
                const otp = this.otpDigits.join('');
                if (otp.length < 4) {
                    this.message = 'کد تایید را کامل وارد کنید';
                    this.messageType = 'error';
                    return;
                }

                if (this.isNewUser && !this.fullName.trim()) {
                    this.message = 'نام و نام خانوادگی را وارد کنید';
                    this.messageType = 'error';
                    return;
                }

                this.loading = true;
                this.message = '';

                const params = {
                    action: 'ganjeh_verify_otp',
                    mobile: this.mobile,
                    otp: otp,
                    nonce: ganjeh.nonce
                };

                if (this.isNewUser && this.fullName.trim()) {
                    params.full_name = this.fullName.trim();
                }

                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(params)
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.message = data.data.message;
                        this.messageType = 'success';

                        const action = this.pendingAction;
                        setTimeout(() => {
                            this.closeModal();

                            // Execute pending action after login
                            if (action && action.type === 'add_to_cart') {
                                if (action.isVariable) {
                                    // Open variation sheet for variable products
                                    const sheet = document.getElementById('variation-sheet');
                                    if (sheet) {
                                        sheet.classList.add('show');
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    // Add simple product to cart directly
                                    this.addToCartAfterLogin(action.productId, action.quantity || 1);
                                }
                            } else {
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        this.message = data.data.message;
                        this.messageType = 'error';
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.message = 'خطا در تایید. لطفاً دوباره تلاش کنید';
                    this.messageType = 'error';
                });
            },

            addToCartAfterLogin(productId, quantity) {
                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ganjeh_add_to_cart',
                        product_id: productId,
                        quantity: quantity,
                        nonce: ganjeh.nonce
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.querySelector('.ganjeh-cart-count');
                        if (cartCount) cartCount.textContent = data.data.cart_count;
                        window.showCartToast && window.showCartToast(data.data);
                    } else {
                        alert(data.data.message);
                    }
                })
                .catch(() => {
                    location.reload();
                });
            },

            };
    }

    // Global function to open auth modal
    window.openAuthModal = function(actionData) {
        window.dispatchEvent(new CustomEvent('open-auth', { detail: actionData || null }));
    };
    </script>
    <?php endif; ?>

</div><!-- #app -->

<?php wp_footer(); ?>
</body>
</html>
