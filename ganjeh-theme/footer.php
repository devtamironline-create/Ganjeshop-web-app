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

    <!-- Login/Register Modal -->
    <?php if (!is_user_logged_in()) : ?>
    <div id="auth-modal" class="auth-modal" x-data="authModal()" @open-auth.window="openModal()">
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

            openModal() {
                document.getElementById('auth-modal').classList.add('show');
                this.reset();
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
                        setTimeout(() => {
                            this.closeModal();
                            location.reload();
                        }, 1500);
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

            };
    }

    // Global function to open auth modal
    window.openAuthModal = function() {
        window.dispatchEvent(new CustomEvent('open-auth'));
    };
    </script>
    <?php endif; ?>

</div><!-- #app -->

<?php wp_footer(); ?>
</body>
</html>
