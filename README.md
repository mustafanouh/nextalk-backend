# NexTalk — Backend Scaffold

هذا السكافولد كود جاهز (migrations, models, controllers, services, policies,
events, routes) مبني حسب الـ spec بتاعك بالظبط. مش مشروع Laravel كامل —
لازم تدمجه جوه مشروع Laravel حقيقي عندك محليًا. الخطوات:

## 1. إنشاء مشروع Laravel جديد

```bash
composer create-project laravel/laravel nextalk
cd nextalk
```

## 2. تثبيت الباكدجات المطلوبة

```bash
composer require laravel/sanctum laravel/reverb spatie/laravel-medialibrary
php artisan install:api        # ينشر ملفات Sanctum
php artisan reverb:install     # ينشر إعدادات Reverb
```

> **Spatie Media Library**: مفيش داعي تعمل `vendor:publish` للـ migration
> أو الـ config بتاعتها — نسختهم مكتوبين يدويًا في السكافولد ده بالفعل
> (`database/migrations/..._create_media_table.php` و
> `config/media-library.php`) لأن الشبكة هنا معندهاش وصول لـ packagist
> عشان أشغّل الأمر فعليًا. لو الإصدار اللي هيتنزل عندك مختلف وعايز تتأكد
> من التوافق، شغّل بعد التركيب:
> ```bash
> php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations" --force
> php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config" --force
> ```
> وقارن بالملفين الموجودين هنا.

## 3. نسخ ملفات السكافولد

انسخ محتوى كل فولدر من الملفات دي فوق مشروعك (استبدال أو دمج حسب الحالة):

```
database/migrations/   → database/migrations/
app/Models/             → app/Models/
app/Http/Controllers/   → app/Http/Controllers/
app/Http/Requests/      → app/Http/Requests/
app/Policies/           → app/Policies/
app/Services/           → app/Services/
app/Events/             → app/Events/
app/Exceptions/         → app/Exceptions/  (دمج مع اللي موجود)
app/Providers/          → app/Providers/   (دمج مع اللي موجود، أو استبدل AuthServiceProvider)
app/Http/Resources/     → app/Http/Resources/  (مجلد جديد، انسخه زي ما هو)
app/Notifications/      → app/Notifications/   (مجلد جديد، انسخه زي ما هو)
routes/api.php          → routes/api.php   (استبدال)
routes/channels.php     → routes/channels.php (استبدال)
.env.example            → .env (خد منه القيم اللي محتاجها وضيفها لـ .env بتاعك)
```

> ملحوظة: لو مشروعك Laravel 12 حديث، ممكن `AuthServiceProvider` يكون مش
> موجود افتراضيًا (الـ policies بتتسجل تلقائي). لو كده، سجّل الـ policies
> في `bootstrap/providers.php` بدل كده أو استخدم `Gate::policy()` جوه
> `AppServiceProvider::boot()`.

## 4. الإعدادات

- افتح `.env` وحط بيانات الداتابيز، Redis، وS3 (أو أي S3-compatible provider
  زي Cloudflare R2 أو Backblaze B2 — أرخص بكتير من S3 الأصلي لمشروع تدريبي).
- شغّل `php artisan key:generate`.
- في `config/filesystems.php` تأكد إن `s3` disk متظبط، ولو محتاج تجربة محلية
  بسيطة قبل ما تربط S3 فعلي، استخدم disk `local` مؤقتًا (بس متنساش ترجعها
  `s3` قبل الإنتاج، الـ spec بيمنع تخزين الملفات مباشر على السيرفر).

## 5. الداتابيز

```bash
php artisan migrate
```

## 6. تشغيل Reverb (WebSocket server)

```bash
php artisan reverb:start
```

في تيرمنال تاني، شغّل الـ queue worker (لازم عشان `MessageSent` و
`MessageDeleted` يتبعتوا — دول لسه `ShouldBroadcast` عادي لأن الرسائل
مش حساسة للـ latency بنفس درجة المكالمات. **كل أحداث المكالمات**
(`IncomingCall`, `CallAccepted`, `CallRejected`, `CallEnded`) **و**
WebRTC signaling (`WebRTCOffer/Answer/ICECandidate`) بقوا `ShouldBroadcastNow`
دلوقتي — بتتبعت فورًا من غير ما تعدي على الـ queue خالص، فمش هتفشل حتى
لو نسيت تشغّل الـ worker ده):

```bash
php artisan queue:work
```

> ⚠️ **لو المكالمات كانت مش بتوصل للطرف التاني قبل كده**: السبب كان إن
> `IncomingCall`/`CallAccepted`/`CallRejected`/`CallEnded` كانوا
> `ShouldBroadcast` (queued) — يعني لو نسيت تشغّل `queue:work` في
> تيرمنال منفصل، الحدث كان بيقعد في الـ queue من غير ما يتبعت خالص.
> اتصلحت في آخر تحديث للباك اند — دلوقتي مش محتاجين الـ worker عشان
> المكالمات تشتغل، بس لسه محتاجينه عشان الرسائل.

## 7. تشغيل السيرفر

```bash
php artisan serve
```

## اللي اتعمل ومش اتعمل

**اتعمل (جاهز يشتغل بمجرد الدمج):**
- كل الـ migrations والعلاقات بينهم
- Auth كامل (register/login/logout/me/forgot-reset password) — Sanctum
- User search + username lookup (route model binding بالـ username)
- Conversations (منع التكرار للمحادثات الخاصة)
- Messages (نص + مرفقات، pagination، soft delete، authorization)
- Attachments (تخزين خارجي فقط، MIME validation حقيقي مش بس بالامتداد)
- Calls (دورة حياة كاملة: ringing → active/rejected/missed → ended)
- كل الـ broadcasting events والـ channels (private + presence)
- WebRTC signaling endpoints (offer/answer/ICE) — دي إضافة مش موجودة
  صراحة في قسم الـ API بتاعك، لكن ضرورية عشان أحداث WebRTCOffer/Answer/
  ICECandidate يكون ليها مصدر. اتكتب كملاحظة جوه SignalingController.php.
- **Avatar عن طريق Spatie Media Library** — حذف تلقائي للصورة القديمة
  عند الاستبدال، thumbnail 256x256 بيتولد تلقائيًا، الرابط بيوصل
  للفرونت جاهز عن طريق `avatar_url` / `avatar_thumb_url` (appended
  attributes، مش عمود خام في الداتابيز).

**✅ البنود الحرجة الثلاثة من `BACKEND-UPDATE-PLAN.md` — اتنفذوا فعليًا
(مش مجرد خطة):**
1. **Custom notifications** (`app/Notifications/`) — `register` و
   `forgot-password` كانوا بيكسروا فعليًا (500 بعد ما اليوزر يتسجل في
   الداتابيز) لأن الـ notifications الافتراضية بتحتاج routes مش موجودة
   في مشروع API-only. اتحلت بـ notification classes مخصصة + route جديد
   (`GET /auth/email/verify/{id}/{hash}`, اسمه `verification.verify`،
   بره الـ `auth:sanctum` group لأن الرابط بيتفتح من الإيميل من غير
   token) + `FRONTEND_URL` في `.env` لرابط إعادة تعيين كلمة المرور.
2. **API Resources** (`app/Http/Resources/`) — كل الـ controllers دلوقتي
   بترجع `UserResource` / `PublicUserResource` / `ConversationResource` /
   `MessageResource` / `AttachmentResource` / `CallResource` بدل الـ
   Eloquent models الخام. ده هو اللي فعليًا بيصلح مشكلة "المرفقات ما
   بتفتحش" (`AttachmentResource` بترجع `url` موقّع بدل `path` الداخلي)،
   وبيمنع تسريب `email`/`phone` لغير صاحب الحساب.
3. **`broadcast()` بعد الـ commit** — `MessageService::send()` دلوقتي
   بيستخدم `DB::afterCommit(fn () => broadcast(...))` بدل ما ينادي
   `broadcast()` مباشرة جوه `DB::transaction()`، فمفيش احتمال إن الـ
   queue worker يدور على رسالة لسه مش متسجلة فعليًا.
4. **Bug إضافي اتلقى بالصدفة أثناء المراجعة**: `$user->only([...])` في
   `UserController` كانت هترمي 500 — `only()` مش method موجودة على
   Eloquent Model (method بتاعة Collection بس). اتصلحت تلقائيًا كنتيجة
   لاستخدام الـ Resources.

كل الـ 60 ملف PHP في السكافولد ده اتعملهم `php -l` (syntax check) فعليًا
وعدّوا بدون أي خطأ.

> **متطلب سيرفر**: الـ `thumb` conversion محتاجة إما GD أو Imagick PHP
> extension متركبة. اتأكد إن `php -m | grep -i gd` (أو imagick) بيرجع
> نتيجة قبل ما تجرب رفع avatar، وإلا هيرمي exception وقت التحويل.

**لسه محتاج منك (بنود 🟠 و🟡 من BACKEND-UPDATE-PLAN.md، لسه مش متنفذة):**
- منع تكرار المكالمات في نفس المحادثة (`CallAlreadyInProgressException`)
- التحقق من فشل رفع المرفقات على S3 (بند 5)
- `bootstrap/app.php` — exception handling صريح وموحّد لكل الـ app (بند 7)
- Rate limiting على messages/calls/signaling (بند 8)
- Tests (Pest) — بند 9، ومذكورة كمان في خطوة 18 بالـ dev order بتاعك
- إعداد Coturn فعليًا للإنتاج (الـ .env فيه أماكن جاهزة بس)

## الخطوة الجاية

قولّي لو عايز:
1. تنفيذ باقي البنود (🟠 منع تكرار المكالمات + فشل الرفع، 🟡 exception
   handling عام + rate limiting)
2. اختبارات Pest للباك اند ده
3. مراجعة/تعديل أي جزء من اللي فوق
