<?php

return [
    /*
     * Анхны админ хэрэглэгч. Утгыг зөвхөн .env файлаас уншина,
     * нууц үгийг кодод бичихгүй.
     */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Системийн админ'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
     * Production орчинд HTTPS-ийг албадна. Reverse proxy-ийн ард ажиллах бол
     * TRUSTED_PROXIES-д proxy-ийн IP хаягийг таслалаар тусгаарлаж бичнэ.
     */
    'force_https' => (bool) env('FORCE_HTTPS', false),
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'reports' => [
        'pdf_max_rows' => (int) env('REPORT_PDF_MAX_ROWS', 500),
    ],

    /*
     * Файлын дээд хэмжээ (килобайт). PHP-ийн upload_max_filesize болон
     * post_max_size нь эдгээрээс багагүй байх ёстой.
     */
    'uploads' => [
        'video_kb' => (int) env('UPLOAD_VIDEO_MAX_KB', 1024 * 1024),
        'audio_kb' => (int) env('UPLOAD_AUDIO_MAX_KB', 300 * 1024),
        'subtitle_kb' => (int) env('UPLOAD_SUBTITLE_MAX_KB', 2 * 1024),
        'image_kb' => (int) env('UPLOAD_IMAGE_MAX_KB', 2 * 1024),
    ],
];
