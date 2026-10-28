# Warehouse SaaS

نظام SaaS لإدارة مخزون الشركات، مبني على Laravel 12 وPHP 8.2+. يوفّر لكل شركة كتالوجاً وأطرافاً وفواتير شراء وبيع مع سجل حركات للمخزون، إضافة إلى إدارة الاشتراكات وتقارير PDF وتبادل البيانات.

## المتطلبات

- PHP 8.2 أو أحدث، وComposer.
- قاعدة بيانات يدعمها Laravel؛ يستخدم المشروع SQLite افتراضياً، كما يمكن ضبط MySQL أو PostgreSQL عبر `.env`. تستخدم الاختبارات SQLite في الذاكرة.
- إضافة PHP `zip` مفعّلة في PHP CLI وفي PHP المستخدم لخادم الويب، لاستيراد/تصدير ملفات XLSX. استيراد CSV لا يحتاج إلى إنشاء ملف XLSX.
- Node.js وnpm لبناء ملفات الواجهة (ويفضّل استخدام إصدار Node.js مدعوم من إصدار Vite المثبّت).

## التقنيات المستخدمة

- **الخلفية:** PHP 8.2+ وLaravel 12، مع Eloquent ORM وواجهات Blade وخدمات Artisan.
- **الواجهة:** صفحات Blade مع Tailwind CSS 4 وAlpine.js 3، وتُبنى عبر Vite 7 وLaravel Vite Plugin. يستخدم Axios ضمن إعداد JavaScript.
- **قاعدة البيانات:** SQLite افتراضياً (`database/database.sqlite`)؛ إعدادات Laravel تتضمن أيضاً MySQL/MariaDB وPostgreSQL وSQL Server. المخطط يُدار عبر Laravel migrations.
- **المصادقة والصلاحيات:** مصادقة Laravel للويب، وSpatie Laravel Permission للأدوار والصلاحيات. Sanctum موجود كاعتمادية، لكن لا توجد حالياً مسارات JSON في `routes/api.php`.
- **الاستيراد والتصدير:** `rap2hpoutre/fast-excel` لقراءة وتوليد ملفات CSV/ODS/XLSX وتصدير Excel.
- **PDF:** TCPDF لإنشاء فواتير وتقارير PDF.
- **الصفوف والمهام الخلفية:** Laravel Queue باستخدام قاعدة البيانات افتراضياً، مع مهمة إنشاء تقارير PDF.
- **الاختبارات وأدوات التطوير:** Pest 3 وPHPUnit، وLaravel Pint للتنسيق، وFaker وMockery للاختبارات، وLaravel Sail وPail ضمن اعتمادات التطوير.

## خطوات الإعداد المحلي

الأوامر التالية مكتوبة لـ PowerShell على Windows:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite | Out-Null }
php artisan key:generate
```

افتراضياً، `.env.example` يختار SQLite؛ ملف قاعدة البيانات الذي أنشأناه هو `database\database.sqlite`. إذا كنت تستخدم MySQL أو PostgreSQL، عدّل `DB_CONNECTION` وحقول `DB_HOST` و`DB_PORT` و`DB_DATABASE` و`DB_USERNAME` و`DB_PASSWORD` لتناسب قاعدة البيانات، وأنشئ قاعدة البيانات قبل تشغيل migrations. بعد ضبط `.env`، ثبّت اعتمادات الواجهة وأنشئ المخطط والبيانات التجريبية:

```powershell
npm install
php artisan migrate --seed
npm run build
php artisan storage:link
php artisan serve
```

توفّر بيانات البذور شركات وأدواراً ومستخدمين وبيانات مخزون تجريبية للتطوير. لا تستخدم حسابات أو كلمات مرور البذور في بيئة الإنتاج.

للتطوير مع إعادة تحميل الواجهة وتشغيل خادم الطوابير، يمكن استخدام سكربت Composer بدلاً من تشغيل الخادم وVite والعامل كلٌّ على حدة:

```powershell
composer run dev
```

يعرض التطبيق محلياً عادةً على `http://127.0.0.1:8000`. في حال استخدام `npm run dev` يدوياً، شغّله في نافذة طرفية مستقلة عن `php artisan serve`.

## الوظائف الرئيسية

- عزل بيانات كل شركة، مع أدوار مدير الشركة ومدير المنصة وصلاحيات Spatie.
- إدارة المنتجات والفئات والعملاء والموردين، وقراءة المخزون من سجل العمليات. تظهر قائمة المنتجات الكمية الحالية وتنبيهاً عندما تصل مادة نشطة إلى حدها الأدنى أو تنخفض عنه.
- فواتير شراء وبيع مؤكدة أو مسودات. لا تغيّر المسودة المخزون حتى تأكيدها.
- إنشاء مرتجع من فاتورة مؤكدة أصلية فقط. مرتجع البيع يزيد المخزون، ومرتجع الشراء ينقصه. يمكن إرجاع جزء من كل بند، ولا يسمح النظام بتجاوز الكمية المتبقية. تُستخدم أسعار الفاتورة الأصلية ويُوزّع خصمها على قيمة المرتجع بالتناسب.
- عملة افتراضية للشركة، وعملة مستقلة لكل فاتورة، وخصم ثابت أو نسبي.
- استيراد المنتجات من CSV أو ODS أو XLSX حتى 10 MB. تتضمن الأعمدة `name`, `barcode`, `category`, `unit`, `price`, `initial_quantity`. السعر المستورد هو سعر البيع، والكمية الأولية تسجّل كرصد مخزون افتتاحي دون تكلفة شراء. تُتخطى الصفوف غير الصالحة والباركودات المكررة مع إظهار تفاصيل الأخطاء.
- تصدير المنتجات وحركات المخزون إلى Excel. تدعم ملفات XLSX إضافة PHP `zip`.
- تصدير PDF للفواتير مباشرة، وطلب تقارير PDF للحركات في الخلفية. التقرير يُتاح للتنزيل بعد أن يعالجه عامل الطوابير، ويكون متاحاً لمستخدمي الشركة المالكة فقط.
- تعليق التجارب المنتهية بأمر `subscriptions:check-expiring` المجدول يومياً، وإدارة الاشتراكات وتمديد التجربة من لوحة Super Admin.

## العمال والجدولة

إعداد Queue الافتراضي يستخدم قاعدة البيانات. شغّل عاملاً دائماً في بيئة الإنتاج:

```powershell
php artisan queue:work --tries=3 --timeout=300
```

يجب أن تكون قيمة `DB_QUEUE_RETRY_AFTER` في `.env` أطول من مهلة المهمة الأطول؛ القيمة الافتراضية في الإعدادات هي 360 ثانية مقابل مهلة تقرير PDF البالغة 300 ثانية. راقب المهام الفاشلة وسجلات Laravel، ولا تعتمد على صفحة التقارير وحدها للتأكد من نجاح العامل.

Laravel يعرّف فحص الاشتراكات مرة يومياً. يجب على نظام التشغيل استدعاء `php artisan schedule:run` كل دقيقة. في Windows، أضف مهمة في Task Scheduler لتشغيل الأمر كل دقيقة باستخدام مسار PHP وملف `artisan` الخاصين بالمشروع. تحقّق من المهام المسجلة عبر:

```powershell
php artisan schedule:list
```

إذا كان ملف ZIP متوفراً لكن PHP لا يحمّله، استخدم `php --ini` لمعرفة ملف إعداد PHP CLI، ثم فعّل `extension=zip` فيه وأعد فتح الطرفية. على Windows/Laragon يمكن التحقق من التحميل عبر `php -m` والبحث عن `zip`.

## قاعدة البيانات والنشر

- تنفّذ migrations إنشاء جداول الشركات والكتالوج والأطراف والفواتير والعمليات والصلاحيات والطوابير والتقارير. وتضيف migration الفهارس المركبة المستخدمة في الاستعلامات متعددة الشركات.
- قبل ترقية قاعدة بيانات قائمة، خذ نسخة احتياطية وراجع migrations المراد تشغيلها؛ لا تستخدم `migrate:fresh` على بيانات حقيقية.
- خزّن ملفات PDF على القرص الخاص، وقيّد الوصول إليها عبر مسار التنزيل الموثق، وجهّز الاحتفاظ بالملفات والمساحة التخزينية وفق سياسة التشغيل.
- إعدادات العملة الحالية تحفظ رموز العملات ولا تنفذ تحويل أسعار الصرف؛ افصل الإجماليات بحسب العملة عند إعداد تقارير مالية موحدة.

## بنية المشروع

```text
app/
  Console/Commands/       أوامر Artisan، ومنها فحص التجارب المنتهية
  Http/Controllers/       وحدات التحكم للكتالوج والفواتير والإدارة
  Http/Middleware/        التحقق من دور المستخدم واشتراك الشركة
  Jobs/                   المهام الخلفية، مثل إنشاء PDF للتقارير
  Models/                 نماذج Eloquent ونطاق عزل بيانات الشركات
  Services/               خدمات استيراد المنتجات وإنشاء ملفات PDF
bootstrap/                تهيئة Laravel وتعريف middleware aliases
config/                   إعدادات Laravel وقاعدة البيانات والطوابير
database/
  factories/              مصانع بيانات الاختبارات
  migrations/             مخطط قاعدة البيانات وتطوراته
  seeders/                بيانات التطوير والأدوار والصلاحيات
resources/
  css/ js/                مصادر الواجهة التي يبنيها Vite
  views/                  قوالب Blade، بما فيها لوحات الإدارة وPDF
routes/
  web.php                 مسارات صفحات الويب والإجراءات
  api.php                 لا يحتوي حالياً على مسارات API
  console.php             تعريف جدولة أوامر Artisan
storage/                  الملفات والسجلات وملفات PDF الخاصة
tests/
  Feature/ Unit/           اختبارات Pest/PHPUnit
```

## المسارات الرئيسية

هذا المشروع حالياً تطبيق ويب يعتمد صفحات Blade ونماذج HTTP وجلسات Laravel. المسارات التالية موجز للمسارات المعرّفة في `routes/web.php`؛ المسارات التشغيلية للشركة تتطلب تسجيل الدخول ودور مدير الشركة واشتراكاً فعالاً، بينما `/admin/*` مخصصة لمدير المنصة.

| الطريقة | المسار | الغرض |
|---|---|---|
| `GET`, `POST` | `/login` | عرض نموذج الدخول وإرساله |
| `GET`, `POST` | `/register` | عرض نموذج التسجيل وإرساله |
| `GET`, `POST` | `/forgot-password` | عرض طلب إعادة التعيين وإرسال رابط إعادة التعيين |
| `GET` | `/reset-password/{token}` | عرض نموذج تعيين كلمة مرور جديدة |
| `POST` | `/reset-password` | حفظ كلمة المرور الجديدة |
| `POST` | `/logout` | إنهاء جلسة المستخدم |
| `GET` | `/dashboard` | لوحة مدير الشركة |
| `GET` | `/categories`, `/categories/create`, `/categories/{category}/edit` | عرض الفئات ونموذجي الإضافة والتعديل |
| `POST`, `PUT`, `PATCH` | `/categories`, `/categories/{category}`, `/categories/{category}/toggle-status` | إنشاء فئة وتعديلها وتغيير حالتها |
| `GET` | `/products`, `/products/create`, `/products/{product}/edit` | عرض المنتجات ونموذجي الإضافة والتعديل |
| `POST`, `PUT`, `PATCH` | `/products`, `/products/{product}`, `/products/{product}/toggle-status` | إنشاء منتج وتعديله وتغيير حالته |
| `POST` | `/products/import` | استيراد CSV أو ODS أو XLSX (حتى 10 MB) |
| `GET` | `/products/export/excel` | تصدير المنتجات إلى Excel |
| `GET` | `/parties` | عرض العملاء والموردين |
| `POST`, `PUT`, `PATCH` | `/parties`, `/parties/{party}`, `/parties/{party}/toggle-status` | إنشاء طرف وتعديله وتغيير حالته |
| `GET` | `/settings` | عرض إعدادات الشركة |
| `PUT` | `/settings/company` | حفظ بيانات الشركة |
| `GET`, `PUT` | `/settings/pricing` | عرض إعدادات التسعير وحفظها |
| `GET` | `/movements`, `/movements/create` | عرض الحركات ونموذج إنشاء فاتورة |
| `POST` | `/movements` | إنشاء فاتورة شراء أو بيع |
| `POST` | `/movements/{movement}/confirm` | تأكيد مسودة فاتورة |
| `GET`, `POST` | `/movements/{movement}/returns/create`, `/movements/{movement}/returns` | إنشاء مرتجع جزئي أو كلي لفاتورة مؤكدة |
| `GET` | `/movements/{movement}/pdf` | تنزيل PDF لفاتورة |
| `GET` | `/movements/export/excel` | تصدير الحركات إلى Excel |
| `POST` | `/movements/export/pdf` | طلب تقرير PDF غير متزامن |
| `GET` | `/reports`, `/reports/{report}/download` | متابعة تقارير PDF الجاهزة وتنزيلها |
| `GET` | `/admin/dashboard`, `/admin/companies`, `/admin/users` | لوحات الشركات والمستخدمين لمدير المنصة |
| `GET` | `/admin/companies/{company}/edit` | عرض نموذج تعديل الشركة |
| `PUT` | `/admin/companies/{company}` | حفظ تغييرات الشركة |
| `POST` | `/admin/companies/{company}/extend-trial`, `/admin/companies/{company}/terminate-subscription` | تمديد التجربة أو إنهاء الاشتراك |
| `GET` | `/up` | مسار فحص صحة Laravel |

لا تمثل هذه المسارات واجهة REST/JSON عامة؛ `routes/api.php` فارغ حالياً. لاستخدام Sanctum أو إضافة API لاحقاً، تُعرّف المسارات وتوثيقها وسياسات الوصول بشكل صريح.

## الاختبارات

```powershell
php artisan test
```

تعمل الاختبارات باستخدام SQLite في الذاكرة وفق `phpunit.xml`، وتغطي Feature Tests المصادقة والكتالوج والإدارة والفواتير والمخزون والاشتراكات والاستيراد والتصدير والتقارير. يلزم تفعيل `zip` في بيئة PHP التي تشغّل الاختبارات كي ينجح اختبار إنشاء ملفات Excel.
