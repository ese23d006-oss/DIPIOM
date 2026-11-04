@if ($lesson->video)
    <div class="notice notice--info mb-3" data-resume-note hidden>
        <i class="bi bi-clock-history" aria-hidden="true"></i>
        <div class="notice-body d-flex flex-wrap align-items-center gap-2">
            <span>Та өмнө <span data-resume-time></span> хүртэл үзсэн тул тэндээс үргэлжлүүлнэ.</span>
            <button type="button" class="btn btn-sm btn-outline-primary" data-control="restart">Эхнээс нь эхлэх</button>
        </div>
    </div>

    <div class="notice notice--error mb-3" data-player-error role="alert" hidden>
        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
        <div class="notice-body">Видеог ачаалж чадсангүй. Доорх бичвэрийг уншиж эсвэл дараа дахин оролдоно уу.</div>
    </div>

    {{-- Автоматаар тоглуулахгүй. Хадмалын data-* нь хэрэглэгчийн тохиргооноос. --}}
    <section class="player" aria-label="Видео тоглуулагч" data-player
             data-resume="{{ $currentProgress?->last_position_seconds ?? 0 }}"
             data-captions="{{ $settings->subtitles_enabled && $lesson->subtitle ? '1' : '0' }}"
             data-describe="{{ $settings->audio_description_enabled && $lesson->audioDescription ? '1' : '0' }}"
             @if ($isStudent) data-save-url="{{ route('lessons.progress', $lesson) }}" @endif>
        <video preload="metadata" playsinline aria-label="{{ $lesson->title }}">
            <source src="{{ route('media.show', $lesson->video) }}" type="{{ $lesson->video->mime_type }}">
            @if ($lesson->subtitle)
                <track kind="subtitles" src="{{ route('media.show', $lesson->subtitle) }}" srclang="{{ $lesson->subtitle->language }}" label="Монгол хадмал">
            @endif
            Таны хөтөч видео тоглуулахыг дэмжихгүй байна. Доорх хичээлийн бичвэрийг уншина уу.
        </video>

        @if ($lesson->audioDescription)
            <audio preload="metadata" src="{{ route('media.show', $lesson->audioDescription) }}"></audio>
        @endif

        <div class="player-controls" role="group" aria-label="Видео удирдлага">
            <div class="player-seek">
                <label for="player-seek">Видеоны байрлал</label>
                <input id="player-seek" type="range" class="form-range" min="0" max="0" step="1" value="0" disabled data-control="seek">
                <span class="player-time" data-control="time">0:00 / 0:00</span>
            </div>

            <div class="player-row player-row--transport">
                <button type="button" class="btn player-btn" data-control="back">
                    <i class="bi bi-skip-backward-fill" aria-hidden="true"></i> 5 секунд буцаах
                </button>
                <button type="button" class="btn player-btn player-btn--main" data-control="play">
                    <i class="bi bi-play-fill" aria-hidden="true"></i> <span>Тоглуулах</span>
                </button>
                <button type="button" class="btn player-btn" data-control="stop">
                    <i class="bi bi-stop-fill" aria-hidden="true"></i> Зогсоох
                </button>
                <button type="button" class="btn player-btn" data-control="forward">
                    5 секунд урагшлуулах <i class="bi bi-skip-forward-fill" aria-hidden="true"></i>
                </button>
            </div>

            <div class="player-row">
                <label class="player-field">
                    Дууны түвшин
                    <input type="range" class="form-range" min="0" max="100" step="5" value="100" data-control="volume">
                </label>

                <label class="player-field">
                    Тоглуулах хурд
                    <select class="form-select" data-control="rate">
                        @foreach (['0.5', '0.75', '1', '1.25', '1.5', '2'] as $speed)
                            <option value="{{ $speed }}" @selected($speed === '1')>{{ $speed }} дахин</option>
                        @endforeach
                    </select>
                </label>

                @if ($lesson->subtitle)
                    <button type="button" class="btn player-btn" data-control="captions" aria-pressed="false">
                        <i class="bi bi-badge-cc" aria-hidden="true"></i> Хадмал
                    </button>
                @endif

                @if ($lesson->audioDescription)
                    <button type="button" class="btn player-btn" data-control="describe" aria-pressed="false">
                        <i class="bi bi-volume-mute" aria-hidden="true"></i> Аудио тайлбар
                    </button>
                @endif

                <button type="button" class="btn player-btn" data-control="fullscreen">
                    <i class="bi bi-fullscreen" aria-hidden="true"></i> <span>Бүтэн дэлгэц</span>
                </button>
            </div>

            @if (! $lesson->subtitle || ! $lesson->audioDescription)
                <div class="player-notes">
                    @unless ($lesson->audioDescription)
                        <p>Энэ хичээлд аудио тайлбар оруулаагүй байна.</p>
                    @endunless
                    @unless ($lesson->subtitle)
                        <p>Энэ хичээлд хадмал оруулаагүй байна.</p>
                    @endunless
                </div>
            @endif
        </div>

        {{-- Бүтэн дэлгэцэд зөвхөн энэ элементийн дотор зарлагддаг тул гадагш гаргахгүй. --}}
        <div class="visually-hidden" aria-live="polite" data-player-live></div>
    </section>
@else
    <div class="player player-empty">
        <i class="bi bi-camera-video-off" aria-hidden="true"></i>
        <p class="mb-0">Энэ хичээлд видео оруулаагүй байна. Доорх хичээлийн бичвэрийг уншина уу.</p>
    </div>
@endif
