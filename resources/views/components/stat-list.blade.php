@props(['stats'])

{{-- $stats: [нэр, утга, icon] мөрүүдийн жагсаалт. dl > div дотор зөвхөн dt, dd байна. --}}
<dl class="grid-stats">
    @foreach ($stats as [$label, $value, $icon])
        <div class="stat">
            <dt>
                <span class="stat-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
                {{ $label }}
            </dt>
            <dd>{{ $value }}</dd>
        </div>
    @endforeach
</dl>
