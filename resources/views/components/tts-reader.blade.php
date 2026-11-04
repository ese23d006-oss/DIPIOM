{{-- Системийн өөрийн уншигч. Хэрэглэгч тохиргооноос асаасан үед л харагдана, өөрөө эхлэхгүй. --}}
@php $settings = auth()->user()->settings; @endphp

<section class="tts-reader" aria-label="Хуудсыг дуугаар унших" data-reader data-tts-skip>
    <p class="tts-status mb-2" role="status" data-reader-status>Дуу хоолойг шалгаж байна.</p>

    <div class="tts-controls" data-reader-controls hidden>
        <button type="button" class="btn btn-sm btn-primary" data-reader-action="read"><i class="bi bi-play-fill" aria-hidden="true"></i> Дуугаар унших</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-reader-action="pause" aria-disabled="true">Түр зогсоох</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-reader-action="resume" aria-disabled="true">Үргэлжлүүлэх</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-reader-action="stop" aria-disabled="true">Уншилтыг зогсоох</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-reader-action="previous">Өмнөх хэсэг</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-reader-action="next">Дараагийн хэсэг</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-reader-action="selection">Сонгосон текстийг унших</button>
        <label class="tts-rate">
            Унших хурд
            <select class="form-select" data-reader-rate>
                @foreach (['0.5', '0.75', '1', '1.25', '1.5', '2'] as $speed)
                    <option value="{{ $speed }}" @selected((float) $speed === (float) $settings->tts_rate)>{{ $speed }} дахин</option>
                @endforeach
            </select>
        </label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="reader-ja" data-reader-lang="ja" @checked($settings->tts_read_japanese)>
            <label class="form-check-label" for="reader-ja">Япон хэлийг унших</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="reader-mn" data-reader-lang="mn" @checked($settings->tts_read_mongolian)>
            <label class="form-check-label" for="reader-mn">Монгол тайлбарыг унших</label>
        </div>
    </div>
</section>
