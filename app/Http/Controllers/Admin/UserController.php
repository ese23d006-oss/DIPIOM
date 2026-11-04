<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $term = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '';
        $role = UserRole::tryFrom((string) (is_string($request->query('role')) ? $request->query('role') : ''));
        $status = is_string($request->query('status')) ? $request->query('status') : '';

        $users = User::query()
            ->when($term !== '', function (Builder $query) use ($term) {
                $like = '%'.addcslashes($term, '\\%_').'%';
                $query->where(fn (Builder $q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($role, fn (Builder $query) => $query->where('role', $role))
            ->when(in_array($status, ['active', 'inactive'], true), fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'term' => $term,
            'role' => $role?->value ?? '',
            'status' => $status,
            'isFiltered' => $term !== '' || $role !== null || in_array($status, ['active', 'inactive'], true),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'phone', 'password']));
        $user->role = UserRole::from($request->validated('role', UserRole::Student->value));
        $user->is_active = $request->boolean('is_active');
        $user->save();

        return redirect()->route('admin.users.index')->with('status', "{$user->name} хэрэглэгчийг нэмлээ.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->fill($request->safe()->only(['name', 'email', 'phone']));

        // Админ өөрийнхөө эрхийг хасаж, системд админгүй болохоос хамгаална.
        if (! $request->user()->is($user)) {
            $user->role = UserRole::from($request->validated('role', $user->role->value));
            $user->is_active = $request->boolean('is_active');
        }

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
            $user->remember_token = Str::random(60);

            if (config('session.driver') === 'database' && ! $request->user()->is($user)) {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', "{$user->name} хэрэглэгчийн мэдээллийг хадгаллаа.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->ensureNotSelf($request, $user);

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('status', $user->is_active
            ? "{$user->name} хэрэглэгчийг идэвхжүүллээ."
            : "{$user->name} хэрэглэгчийг идэвхгүй болголоо.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureNotSelf($request, $user);

        $name = $user->name;
        $user->delete();

        return back()->with('status', "{$name} хэрэглэгчийг устгалаа.");
    }

    /**
     * Админ өөрийгөө хааж, системд админгүй болохоос хамгаална.
     */
    private function ensureNotSelf(Request $request, User $user): void
    {
        abort_if($request->user()->is($user), 403);
    }
}
