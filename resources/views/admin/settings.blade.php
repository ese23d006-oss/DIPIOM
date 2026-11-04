@extends('layouts.app')

@section('title', 'Системийн тохиргоо')

@section('content')
    <x-page-header title="Системийн тохиргоо" />

    <x-form.errors />

    <div class="grid-split">
        <section class="card-surface card-pad" aria-labelledby="settings-general">
            <h2 id="settings-general" class="h5">Ерөнхий</h2>
            <form method="POST" action="{{ route('admin.settings.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-form.check name="registration_open" label="Шинэ суралцагч бүртгүүлэхийг зөвшөөрөх" :checked="$registrationOpen"
                              hint="Унтраавал бүртгэлийг зөвхөн админ нэмнэ." />
                <x-form.field name="support_contact" label="Холбоо барих мэдээлэл" :value="$supportContact"
                              hint="Хуудасны доод хэсэгт харагдана. Жишээ нь утас эсвэл имэйл." />
                <button type="submit" class="btn btn-primary">Хадгалах</button>
            </form>
        </section>

        <section class="card-surface card-pad" aria-labelledby="settings-status">
            <h2 id="settings-status" class="h5">Орчны төлөв</h2>
            <dl class="jp-facts mb-0">
                @foreach ($checks as [$label, $value, $ok])
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd>
                            <i class="bi {{ $ok ? 'bi-check-circle-fill text-success-token' : 'bi-exclamation-triangle-fill text-warning-token' }}" aria-hidden="true"></i>
                            {{ $value }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>
@endsection
