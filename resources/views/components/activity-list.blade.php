@props(['items'])

@if ($items->isEmpty())
    <p class="mb-0">Одоогоор үйл ажиллагаа бүртгэгдээгүй байна.</p>
@else
    <ul class="activity-list">
        @foreach ($items as $item)
            <li>
                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                <div>
                    {{ $item['text'] }}
                    <time datetime="{{ $item['at']->toIso8601String() }}">{{ $item['at']->format('Y-m-d H:i') }}</time>
                </div>
            </li>
        @endforeach
    </ul>
@endif
