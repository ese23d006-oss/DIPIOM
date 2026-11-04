@extends('layouts.app')

@section('title', 'Миний профайл')

@section('content')
    <x-page-header title="Миний профайл" lead="Өөрийн мэдээлэл болон нууц үгээ удирдана." />

    <x-form.errors />

    <div class="grid-split">
        <section class="card-surface card-pad" aria-labelledby="profile-info">
            <h2 id="profile-info" class="h5">Хувийн мэдээлэл</h2>
            <form method="POST" action="{{ route('profile.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-form.field name="name" label="Нэр" :value="$user->name" required autocomplete="name" />
                <x-form.field name="email" label="Имэйл хаяг" type="email" :value="$user->email" required autocomplete="email" />
                <x-form.field name="phone" label="Утасны дугаар" type="tel" :value="$user->phone" autocomplete="tel" />
                <x-form.field name="email_password" label="Одоогийн нууц үг" type="password" autocomplete="current-password"
                              hint="Зөвхөн имэйл хаягаа солих үед бөглөнө." />
                <button type="submit" class="btn btn-primary">Хадгалах</button>
            </form>
        </section>

        <section class="card-surface card-pad" aria-labelledby="profile-password">
            <h2 id="profile-password" class="h5">Нууц үг солих</h2>
            <form method="POST" action="{{ route('profile.password') }}" novalidate>
                @csrf
                @method('PUT')
                <x-form.field name="current_password" label="Одоогийн нууц үг" type="password" required autocomplete="current-password" />
                <x-form.field name="password" label="Шинэ нууц үг" type="password" required autocomplete="new-password"
                              hint="Хамгийн багадаа 8 тэмдэгт, үсэг болон тоо агуулсан байна." />
                <x-form.field name="password_confirmation" label="Шинэ нууц үг давтах" type="password" required autocomplete="new-password" />
                <button type="submit" class="btn btn-primary">Нууц үг солих</button>
            </form>
        </section>
    </div>
@endsection
