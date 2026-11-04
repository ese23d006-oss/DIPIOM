@extends('layouts.app')

@section('title', 'Хэрэглэгчид')

@section('content')
    <x-page-header title="Хэрэглэгчид" lead="Хэрэглэгчийг нэмж, засаж, идэвхжүүлж, устгана.">
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
            <i class="bi bi-person-plus" aria-hidden="true"></i> Хэрэглэгч нэмэх
        </a>
    </x-page-header>

    <form method="GET" action="{{ route('admin.users.index') }}" role="search" aria-label="Хэрэглэгч шүүх" class="filter-bar card-surface">
        <div>
            <label for="q" class="form-label">Нэр эсвэл имэйл</label>
            <input id="q" name="q" type="search" class="form-control" maxlength="100" value="{{ $term }}">
        </div>
        <div>
            <label for="role" class="form-label">Эрх</label>
            <select id="role" name="role" class="form-select">
                <option value="">Бүгд</option>
                @foreach (\App\Enums\UserRole::cases() as $option)
                    <option value="{{ $option->value }}" @selected($role === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="form-label">Төлөв</label>
            <select id="status" name="status" class="form-select">
                <option value="">Бүгд</option>
                <option value="active" @selected($status === 'active')>Идэвхтэй</option>
                <option value="inactive" @selected($status === 'inactive')>Идэвхгүй</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel" aria-hidden="true"></i> Шүүх</button>
        </div>
    </form>

    <h2 class="mt-4 mb-3" id="users-title" @if ($isFiltered && ! session('status')) tabindex="-1" data-focus-on-load @endif>
        Олдсон хэрэглэгч: {{ $users->total() }}
    </h2>

    @if ($users->isEmpty())
        <div class="empty-state">
            <i class="bi bi-search" aria-hidden="true"></i>
            <p>Шүүлтэд тохирох хэрэглэгч олдсонгүй.</p>
            <p><a href="{{ route('admin.users.index') }}">Шүүлт цэвэрлэх</a></p>
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive" tabindex="0" role="region" aria-labelledby="users-title">
                <table class="table table-hover">
                    <caption class="visually-hidden">Хэрэглэгчдийн жагсаалт</caption>
                    <thead>
                        <tr>
                            <th scope="col">Нэр</th>
                            <th scope="col">Имэйл</th>
                            <th scope="col">Эрх</th>
                            <th scope="col">Төлөв</th>
                            <th scope="col">Бүртгэгдсэн</th>
                            <th scope="col">Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <th scope="row" class="fw-semibold">{{ $user->name }}</th>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->label() }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge-status badge-status--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Идэвхтэй</span>
                                    @else
                                        <span class="badge-status badge-status--error"><i class="bi bi-slash-circle" aria-hidden="true"></i> Идэвхгүй</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if ($user->is(auth()->user()))
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">
                                            Засах <span class="visually-hidden">: {{ $user->name }}</span>
                                        </a>
                                        <span class="text-small ms-2">Таны бүртгэл</span>
                                    @else
                                        <div class="table-actions">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">
                                                Засах <span class="visually-hidden">: {{ $user->name }}</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    {{ $user->is_active ? 'Идэвхгүй болгох' : 'Идэвхжүүлэх' }}
                                                    <span class="visually-hidden">: {{ $user->name }}</span>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger-token"
                                                    data-bs-toggle="modal" data-bs-target="#delete-user"
                                                    data-action="{{ route('admin.users.destroy', $user) }}"
                                                    data-message="{{ $user->name }} ({{ $user->email }}) хэрэглэгчийг устгах уу? Түүний явц, шалгалтын үр дүн хамт устах бөгөөд буцаах боломжгүй.">
                                                Устгах <span class="visually-hidden">: {{ $user->name }}</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $users->links('pagination.accessible') }}

        <x-confirm-modal id="delete-user" title="Хэрэглэгч устгах" />
    @endif
@endsection
