@extends('layouts.app')

@section('title', 'Хүртээмжийн тохиргоо')

@section('content')
    <x-page-header title="Хүртээмжийн тохиргоо"
                   lead="Тохиргоо таны бүртгэлд хадгалагдаж, дараагийн нэвтрэхэд автоматаар хэрэгжинэ." />

    <x-form.errors />

    <div class="grid-split">
        <form method="POST" action="{{ route('settings.update') }}" class="card-surface card-pad" novalidate>
            @csrf
            @method('PUT')

            <fieldset class="mb-4">
                <legend class="h5">Харагдах байдал</legend>
                <x-form.select name="font_size_percent" label="Текстийн хэмжээ" :value="$settings->font_size_percent"
                               :options="collect($fontSizes)->mapWithKeys(fn ($size) => [$size => $size.'%'])->all()" />
                <x-form.select name="font_style" label="Үсгийн хэлбэр" :value="$settings->font_style" :options="\App\Models\UserSetting::FONT_STYLES" />
                <x-form.select name="line_spacing" label="Мөр хоорондын зай" :value="$settings->line_spacing" :options="\App\Models\UserSetting::LINE_SPACINGS" />
                <x-form.check name="accessibility_mode" label="Хүртээмжийн горим" :checked="$settings->accessibility_mode"
                              hint="Товчлуурыг томруулж, чимэглэлийн зургийг нууж, сургалтуудыг нэг баганаар харуулна." />
                <x-form.check name="high_contrast" label="Өндөр контраст горим" :checked="$settings->high_contrast"
                              hint="Хар дэвсгэр дээр цагаан, шар текстээр харуулна." />
                <x-form.check name="reduce_motion" label="Хөдөлгөөнт эффектийг багасгах" :checked="$settings->reduce_motion" />
            </fieldset>

            <fieldset class="mb-4">
                <legend class="h5">Видео хичээл</legend>
                <x-form.check name="audio_description_enabled" label="Аудио тайлбарыг анхнаас асаах" :checked="$settings->audio_description_enabled"
                              hint="Зөвхөн аудио тайлбар оруулсан хичээлд хамаарна." />
                <x-form.check name="subtitles_enabled" label="Хадмалыг анхнаас асаах" :checked="$settings->subtitles_enabled"
                              hint="Зөвхөн хадмал оруулсан хичээлд хамаарна." />
                <x-form.select name="caption_size" label="Хадмалын хэмжээ" :value="$settings->caption_size" :options="\App\Models\UserSetting::CAPTION_SIZES" />
            </fieldset>

            <fieldset class="mb-4">
                <legend class="h5">Дуугаар унших</legend>
                <x-form.check name="tts_enabled" label="Хуудсыг дуугаар унших самбарыг харуулах" :checked="$settings->tts_enabled"
                              hint="NVDA, JAWS зэрэг дэлгэц уншигч ашигладаг бол унтраалттай байлгахыг зөвлөж байна: хоёр дуу зэрэг гарахаас сэргийлнэ." />
                <x-form.check name="tts_highlight" label="Уншиж буй хэсгийг тодруулах" :checked="$settings->tts_highlight" />
                <x-form.check name="tts_read_japanese" label="Япон хэлний уншлагыг унших" :checked="$settings->tts_read_japanese" />
                <x-form.check name="tts_read_mongolian" label="Монгол хэлний тайлбарыг унших" :checked="$settings->tts_read_mongolian" />

                <x-form.select name="tts_rate" label="Унших хурд" :value="rtrim(rtrim(number_format((float) $settings->tts_rate, 2, '.', ''), '0'), '.')"
                               :options="['0.5' => '0.5 дахин', '0.75' => '0.75 дахин', '1' => '1 дахин', '1.25' => '1.25 дахин', '1.5' => '1.5 дахин', '2' => '2 дахин']" />

                <div class="mb-3">
                    <label for="tts_volume" class="form-label">Дууны түвшин</label>
                    <input id="tts_volume" name="tts_volume" type="range" class="form-range" min="0" max="1" step="0.1" value="{{ $settings->tts_volume }}">
                </div>

                {{-- Төхөөрөмж дээр байгаа дуу хоолойгоор JavaScript дүүргэнэ. --}}
                @foreach ([['tts_voice', 'mn', 'Монгол дуу хоолой', $settings->tts_voice], ['tts_voice_ja', 'ja', 'Япон дуу хоолой', $settings->tts_voice_ja]] as [$field, $lang, $label, $current])
                    <div class="mb-3" data-voice-field="{{ $lang }}">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <select id="{{ $field }}" name="{{ $field }}" class="form-select" data-voice-select="{{ $lang }}" data-current="{{ $current }}" disabled hidden></select>
                        <p class="field-hint mb-0" data-voice-empty="{{ $lang }}">Энэ төхөөрөмжид {{ $label }} олдсонгүй.</p>
                    </div>
                @endforeach
            </fieldset>

            <button type="submit" class="btn btn-primary">Хадгалах</button>
        </form>

        <div class="d-grid gap-3">
            <section class="card-surface card-pad" aria-labelledby="sr-guide">
                <h2 id="sr-guide" class="h5">Дэлгэц уншигч ашиглах заавар</h2>
                <ul class="mb-0">
                    <li>Систем NVDA, JAWS, Narrator, VoiceOver, TalkBack-тэй ажиллах зорилгоор бүтээгдсэн.</li>
                    <li>Гарчгаар шилжихэд гарчгийн товч (ихэнхэд H), хэсгээр шилжихэд landmark товчийг ашиглана.</li>
                    <li>Япон текст Япон хэлээр тэмдэглэгдсэн тул дэлгэц уншигчид Япон дуу хоолой суулгавал түүгээр уншина.</li>
                </ul>
            </section>

            <section class="card-surface card-pad" aria-labelledby="shortcuts">
                <h2 id="shortcuts" class="h5">Гарын товчлол</h2>
                <dl class="jp-facts mb-0">
                    <div><dt>Tab, Shift + Tab</dt><dd>Дараагийн, өмнөх элемент</dd></div>
                    <div><dt>Enter, Space</dt><dd>Товчлуур ажиллуулах</dd></div>
                    <div><dt>Escape</dt><dd>Цэс, цонх хаах</dd></div>
                    <div><dt>Сум дээш, доош</dt><dd>Үсгийн жагсаалт, сонголтод шилжих</dd></div>
                    <div><dt>Home, End</dt><dd>Жагсаалтын эхлэл, төгсгөл</dd></div>
                    <div><dt>Alt + Shift + R</dt><dd>Дуугаар унших (самбар асаалттай үед)</dd></div>
                    <div><dt>Alt + Shift + P</dt><dd>Түр зогсоох, үргэлжлүүлэх</dd></div>
                    <div><dt>Alt + Shift + S</dt><dd>Уншилтыг зогсоох</dd></div>
                    <div><dt>Alt + Shift + N, B</dt><dd>Дараагийн, өмнөх хэсэг</dd></div>
                </dl>
            </section>
        </div>
    </div>
@endsection
