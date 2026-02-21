# گنجه مارکت - قالب وردپرس

قالب فروشگاهی موبایل‌فرست برای ووکامرس

## ویژگی‌ها

- طراحی موبایل‌فرست (حتی در دسکتاپ)
- پشتیبانی کامل RTL فارسی
- سازگاری کامل با ووکامرس
- سرعت بالا و بهینه‌سازی شده
- Tailwind CSS + Alpine.js
- فونت وزیرمتن

## نصب

1. فایل‌های تم را در `wp-content/themes/ganjeh-theme` کپی کنید
2. از پنل وردپرس، تم را فعال کنید
3. WooCommerce را نصب و فعال کنید

## توسعه

```bash
# نصب وابستگی‌ها
npm install

# حالت توسعه
npm run dev

# بیلد پروداکشن
npm run build
```

## فونت‌ها

فونت وزیرمتن را از [این لینک](https://github.com/rastikerdar/vazirmatn/releases) دانلود کرده و در مسیر زیر قرار دهید:

```
assets/fonts/vazirmatn/
├── Vazirmatn-Regular.woff2
├── Vazirmatn-Medium.woff2
├── Vazirmatn-Bold.woff2
└── Vazirmatn[wght].woff2 (Variable)
```

## کتابخانه‌های جاوااسکریپت

برای پروداکشن، فایل‌های اصلی را دانلود کنید:

- [Alpine.js](https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js)
- [Swiper.js](https://cdn.jsdelivr.net/npm/swiper@11.0.5/swiper-bundle.min.js)

## ساختار فایل‌ها

```
ganjeh-theme/
├── assets/
│   ├── css/
│   │   ├── tailwind.css
│   │   ├── style.min.css
│   │   └── swiper.min.css
│   ├── js/
│   │   ├── alpine.min.js
│   │   ├── swiper.min.js
│   │   └── main.js
│   ├── fonts/
│   │   └── vazirmatn/
│   └── images/
├── inc/
│   ├── theme-setup.php
│   ├── woocommerce.php
│   ├── customizer.php
│   └── ajax-handlers.php
├── template-parts/
│   ├── components/
│   │   ├── bottom-nav.php
│   │   ├── category-grid.php
│   │   ├── hero-slider.php
│   │   └── product-card.php
│   └── header/
│       └── promo-banner.php
├── woocommerce/
│   ├── archive-product.php
│   ├── single-product.php
│   ├── content-product.php
│   ├── cart/
│   │   └── cart.php
│   └── myaccount/
│       ├── my-account.php
│       └── form-login.php
├── functions.php
├── header.php
├── footer.php
├── front-page.php
├── index.php
├── 404.php
├── style.css
├── tailwind.config.js
└── package.json
```

## تنظیمات

از بخش **سفارشی‌سازی** وردپرس می‌توانید:

- رنگ‌های اصلی و ثانویه را تغییر دهید
- بنر تبلیغاتی را مدیریت کنید
- اسلایدر صفحه اصلی را تنظیم کنید
- لینک‌های شبکه‌های اجتماعی را اضافه کنید

## مجوز

GPL v2 or later
