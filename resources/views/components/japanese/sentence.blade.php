@props(['ja', 'reading' => null, 'romaji' => null, 'mn' => null, 'speaker' => null])

<article class="jp-sentence">
    @if ($speaker)
        <p class="jp-speaker">Ярьж буй хүн: {{ $speaker }}</p>
    @endif
    <p class="jp-sentence-main"><x-ja>{{ $ja }}</x-ja></p>
    <dl class="jp-facts">
        @if ($reading)
            <div><dt>Хирагана уншлага</dt><dd><x-ja>{{ $reading }}</x-ja></dd></div>
        @endif
        @if ($romaji)
            <div><dt>Ромажи</dt><dd><x-romaji>{{ $romaji }}</x-romaji></dd></div>
        @endif
        @if ($mn)
            <div><dt>Монгол орчуулга</dt><dd>{{ $mn }}</dd></div>
        @endif
    </dl>
    <x-speak :text="$reading ?: $ja" :label="'Япон өгүүлбэрийг сонсох: '.$ja" />
</article>
