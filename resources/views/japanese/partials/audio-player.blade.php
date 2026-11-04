{{-- Гараар бүрэн удирдах аудио тоглуулагч. Файлгүй бол товч идэвхгүй, шалтгааныг уншуулна. --}}
<section class="player" aria-label="Аудио тоглуулагч" @if ($src) data-audio-player @endif>
    @if ($src)
        <audio preload="metadata" src="{{ $src }}"></audio>
    @endif

    <div class="player-controls" role="group" aria-label="Аудио удирдлага">
        @if (! $src)
            <p class="player-notes mt-0" id="no-audio">Энэ дасгалд аудио файл оруулаагүй байна.</p>
        @endif

        <div class="player-row">
            <button type="button" class="btn player-btn player-btn--main" data-audio="play" @disabled(! $src) @if (! $src) aria-describedby="no-audio" @endif>
                <i class="bi bi-play-fill" aria-hidden="true"></i> <span>Тоглуулах</span>
            </button>
            <button type="button" class="btn player-btn" data-audio="stop" @disabled(! $src)>
                <i class="bi bi-stop-fill" aria-hidden="true"></i> Зогсоох
            </button>
            <button type="button" class="btn player-btn" data-audio="back" @disabled(! $src)>
                <i class="bi bi-skip-backward-fill" aria-hidden="true"></i> 5 секунд буцаах
            </button>
            <button type="button" class="btn player-btn" data-audio="replay" @disabled(! $src)>
                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Дахин тоглуулах
            </button>
            @if ($src)
                <label class="player-field">
                    Дууны түвшин
                    <input type="range" class="form-range" min="0" max="100" step="5" value="100" data-audio="volume">
                </label>
                <label class="player-field">
                    Тоглуулах хурд
                    <select class="form-select" data-audio="rate">
                        @foreach (['0.5', '0.75', '1', '1.25', '1.5'] as $speed)
                            <option value="{{ $speed }}" @selected($speed === '1')>{{ $speed }} дахин</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>
    </div>
    <div class="visually-hidden" aria-live="polite" data-audio-live></div>
</section>
