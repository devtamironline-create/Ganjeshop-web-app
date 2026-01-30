# 🧱 ACF Blocks Module

سیستم ساخت بلوک‌های سفارشی با Advanced Custom Fields

---

## 📋 فهرست

- [نصب](#نصب)
- [ساختار](#ساختار)
- [بلوک‌های موجود](#بلوک‌های-موجود)
- [ساخت بلوک جدید](#ساخت-بلوک-جدید)
- [استفاده](#استفاده)

---

## 🚀 نصب

### پیش‌نیاز:
```
✅ WordPress 6.0+
✅ Advanced Custom Fields Pro 6.0+
✅ PHP 7.4+
```

### فعال‌سازی:
```php
// در module.json
"enabled": true
```

---

## 📁 ساختار

```
modules/acf-blocks/
├── init.php                 ← کلاس اصلی
├── module.json              ← تنظیمات
├── assets/
│   ├── css/
│   │   └── editor.css      ← استایل ادیتور
│   └── js/
│       └── editor.js       ← اسکریپت ادیتور
└── blocks/
    ├── hero/               ← بلوک Hero
    │   ├── block.php      ← تعریف + فیلدها
    │   └── render.php     ← Template
    ├── features/          ← بلوک Features
    └── pricing/           ← بلوک Pricing
```

---

## 🧱 بلوک‌های موجود

### 1️⃣ Hero Block
**نام:** `acf/hero`  
**آیکون:** 📸  
**دسته:** Developer Blocks

**فیلدها:**
- عنوان (text)
- توضیحات (textarea)
- دکمه اصلی (group)
  - متن
  - لینک
  - استایل
- دکمه ثانویه (group)
- تصویر (image)
- تنظیمات (group)
  - ارتفاع
  - تراز
  - رنگ پس‌زمینه

**مثال:**
```
[Hero Section]
عنوان: میزبانی وب حرفه‌ای
توضیحات: بهترین سرویس میزبانی
دکمه: شروع کنید
تصویر: [انتخاب تصویر]
```

---

### 2️⃣ Features Block
**نام:** `acf/features`  
**آیکون:** ⚡  
**دسته:** Developer Blocks

**فیلدها:**
- عنوان بخش (text)
- توضیحات (textarea)
- ویژگی‌ها (repeater)
  - آیکون
  - عنوان
  - توضیحات
- تنظیمات (group)
  - تعداد ستون (2/3/4)
  - استایل (card/minimal/boxed)

**مثال:**
```
[Features Grid]
عنوان: ویژگی‌های ما
ستون‌ها: 3

ویژگی 1:
- آیکون: ⚡
- عنوان: سرعت بالا
- توضیح: ...

ویژگی 2:
- آیکون: 🛡️
- عنوان: امنیت
- توضیح: ...
```

---

### 3️⃣ Pricing Block
**نام:** `acf/pricing`  
**آیکون:** 💰  
**دسته:** Developer Blocks

**فیلدها:**
- عنوان بخش (text)
- پلن‌ها (repeater)
  - نام پلن
  - قیمت
  - دوره
  - توضیحات
  - ویژگی‌ها (textarea)
  - دکمه (text + url)
  - پلن محبوب (true/false)
  - Badge

**مثال:**
```
[Pricing Plans]

پلن 1:
- نام: پایه
- قیمت: $9
- دوره: ماهانه
- ویژگی‌ها:
  1GB فضا
  10GB ترافیک
  1 دامنه

پلن 2 (محبوب):
- نام: حرفه‌ای
- قیمت: $19
- پلن محبوب: بله
```

---

## 🛠️ ساخت بلوک جدید

### مرحله 1: ساخت پوشه
```bash
mkdir -p modules/acf-blocks/blocks/my-block
```

### مرحله 2: ساخت block.php
```php
<?php
/**
 * My Block
 */

// ثبت بلوک
add_action('acf/init', function() {
    
    if (!function_exists('acf_register_block_type')) {
        return;
    }
    
    acf_register_block_type([
        'name'              => 'my-block',
        'title'             => __('My Block', 'dst'),
        'description'       => __('توضیحات بلوک', 'dst'),
        'render_template'   => dirname(__FILE__) . '/render.php',
        'category'          => 'dst-blocks',
        'icon'              => 'star-filled',
        'keywords'          => ['my', 'block'],
        'supports'          => [
            'align' => ['wide', 'full'],
            'mode'  => true,
        ],
    ]);
});

// ثبت فیلدها
add_action('acf/init', function() {
    
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    acf_add_local_field_group([
        'key'    => 'group_my_block',
        'title'  => 'My Block Settings',
        'fields' => [
            // فیلدهای بلوک
            [
                'key'   => 'field_my_title',
                'label' => 'عنوان',
                'name'  => 'title',
                'type'  => 'text',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/my-block',
                ],
            ],
        ],
    ]);
});
```

### مرحله 3: ساخت render.php
```php
<?php
/**
 * My Block - Template
 */

// دریافت فیلدها
$title = get_field('title');

// کلاس‌ها
$class_name = 'dst-my-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
?>

<section class="<?php echo esc_attr($class_name); ?>">
    <h2><?php echo esc_html($title); ?></h2>
</section>

<style>
.dst-my-block {
    padding: 60px 20px;
}
</style>
```

### مرحله 4: تست
1. رفرش کنید
2. بروید به ادیتور
3. بلوک جدید رو پیدا کنید
4. استفاده کنید!

---

## 💡 نکات مهم

### ساختار فایل:
```php
blocks/BLOCK_NAME/
├── block.php    ← ثبت + فیلدها
└── render.php   ← Template + Style
```

### نام‌گذاری:
- **Block Name:** حروف کوچک + tire (my-block)
- **Field Key:** `field_BLOCK_FIELD` (field_hero_title)
- **Group Key:** `group_BLOCK` (group_hero_block)

### استایل:
- **Inline CSS:** در render.php
- **External CSS:** در assets/css/

### کلاس‌ها:
```php
$class_name = 'dst-BLOCK-block';
$class_name .= ' ' . $block['className']; // Custom classes
$class_name .= ' align' . $block['align']; // Alignment
```

---

## 🎨 تنظیمات رایج

### Align Support:
```php
'supports' => [
    'align' => ['wide', 'full'], // یا true برای همه
]
```

### Mode Support:
```php
'supports' => [
    'mode' => true, // Auto/Preview/Edit modes
]
```

### Preview Image:
```php
'example' => [
    'attributes' => [
        'mode' => 'preview',
        'data' => [
            'preview_image' => 'path/to/preview.jpg',
        ],
    ],
],
```

---

## 📚 منابع

- [ACF Blocks Documentation](https://www.advancedcustomfields.com/resources/blocks/)
- [Block Registration](https://www.advancedcustomfields.com/resources/acf_register_block_type/)
- [Field Types](https://www.advancedcustomfields.com/resources/)

---

## ✅ چک‌لیست ساخت بلوک

```markdown
⬜ ساخت پوشه block
⬜ ساخت block.php
⬜ ثبت بلوک با acf_register_block_type
⬜ تعریف فیلدها با acf_add_local_field_group
⬜ ساخت render.php
⬜ دریافت فیلدها با get_field
⬜ رندر HTML
⬜ اضافه کردن CSS
⬜ تست در ادیتور
⬜ بررسی Responsive
```

---

**حالا می‌تونی بلوک بسازی! 🚀**
