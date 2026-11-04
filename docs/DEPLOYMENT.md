# Сервер дээр байршуулах заавар

## Шаардлага

- PHP 8.2+, өргөтгөл: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `dom`
- MySQL 8.0+ (`utf8mb4`)
- Composer 2, Node.js 20+ (зөвхөн build хийхэд)
- Nginx эсвэл Apache, HTTPS гэрчилгээ
- Веб серверийн root нь төслийн `public/` хавтас байна.

## Алхмууд

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate
```

`.env` файлд заавал тохируулах:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://таны-домэйн
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=          # Reverse proxy-ийн ард бол түүний IP хаяг

DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...           # Зөвхөн энэ санд эрхтэй хэрэглэгч
DB_PASSWORD=...

MAIL_MAILER=smtp          # Тохируулаагүй бол нууц үг сэргээх имэйл очихгүй
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...

ADMIN_EMAIL=...
ADMIN_PASSWORD=...        # Анхны нэвтрэлтийн дараа системээс сольж, эндээс устгана
```

Дараа нь:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

`storage/` болон `bootstrap/cache/` хавтаст веб серверийн хэрэглэгч бичих эрхтэй байна. `.env` файл веб-ээр нээгдэхгүй байхыг шалгана.

## PHP-ийн файлын хязгаар

Видеонд `php.ini`:

```ini
upload_max_filesize=1024M
post_max_size=1100M
max_input_time=600
memory_limit=512M
```

Nginx-д `client_max_body_size 1100M;` нэмнэ. Цөөн хязгаар тавих бол `.env`-ийн `UPLOAD_VIDEO_MAX_KB`-ийг мөн багасгана.

## Япон хэлний агуулга

`db:seed` N5 агуулгыг оруулна. Хирагана, катакана нийтлэгдсэн, бусад нь ноорог. Агуулгыг мэргэжлийн багшаар шалгуулаад **Админ → Япон хэл** хэсгээс нийтэлнэ.

## Нөөцлөлт

Өдөр бүр хоёрыг нөөцөлнө:

- MySQL сан: `mysqldump`
- Файлууд: `storage/app/private` (видео, аудио, хадмал) болон `storage/app/public` (зураг)

Сэргээж туршсан нөөц л жинхэнэ нөөц гэдгийг анхаарна уу.

## Байршуулсны дараа шалгах

- [ ] `https://домэйн/up` нь 200 буцаана
- [ ] `http://` хаяг `https://` руу шилжинэ
- [ ] Алдааны хуудсанд техникийн мэдээлэл харагдахгүй (`APP_DEBUG=false`)
- [ ] Нууц үг сэргээх имэйл бодитоор ирнэ
- [ ] Админаар нэвтэрч видео оруулаад, суралцагчаар тоглуулна
- [ ] `https://домэйн/storage/lesson-media/` нээгдэхгүй

## Шинэчлэх

```bash
php artisan down
git pull                   # эсвэл файлуудаа хуулна
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan up
```
