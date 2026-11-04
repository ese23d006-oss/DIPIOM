<?php

namespace App\Http\Controllers\Admin\Japanese;

use App\Http\Controllers\Controller;
use App\Models\Japanese\Audio;
use App\Models\Japanese\Category;
use App\Models\Japanese\Lesson;
use App\Models\Japanese\Level;
use App\Models\Japanese\Quiz;
use App\Support\Japanese\AdminResources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function hub(): View
    {
        $sections = collect(AdminResources::all())->except('examples')->map(function (array $config, string $key) {
            $model = $config['model'];

            return [
                'key' => $key,
                'label' => $config['label'],
                'total' => $model::count(),
                'draft' => $config['hasStatus'] ? $model::where('status', 'draft')->count() : null,
            ];
        });

        return view('admin.japanese.hub', ['sections' => $sections]);
    }

    public function index(Request $request, string $resource): View
    {
        $config = AdminResources::get($resource);
        $model = $config['model'];
        $status = is_string($request->query('status')) ? $request->query('status') : '';
        $term = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '';
        $searchColumn = array_key_first($config['columns']);

        $items = $model::query()
            ->when($config['hasStatus'] && in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
            ->when($term !== '', fn ($query) => $query->where($searchColumn, 'like', '%'.addcslashes($term, '\\%_').'%'))
            ->orderBy($resource === 'characters' ? 'script' : 'id')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.japanese.index', compact('resource', 'config', 'items', 'status', 'term'));
    }

    public function create(Request $request, string $resource): View
    {
        return $this->form($request, $resource, new (AdminResources::get($resource)['model']));
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = AdminResources::get($resource);
        $item = new $config['model'];

        return $this->save($request, $resource, $config, $item);
    }

    public function edit(Request $request, string $resource, int $id): View
    {
        return $this->form($request, $resource, $this->find($resource, $id));
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        return $this->save($request, $resource, AdminResources::get($resource), $this->find($resource, $id));
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $item = $this->find($resource, $id);

        try {
            $item->delete();
        } catch (QueryException) {
            return back()->with('error', 'Энэ бичлэгт холбогдсон агуулга байгаа тул устгах боломжгүй.');
        }

        return back()->with('status', 'Устгалаа.');
    }

    public function toggleStatus(string $resource, int $id): RedirectResponse
    {
        $item = $this->find($resource, $id);
        abort_unless(AdminResources::get($resource)['hasStatus'], 404);

        if (! $item->isPublished() && ($blocker = $item->publishBlocker())) {
            return back()->with('error', $blocker);
        }

        $item->status = $item->isPublished() ? 'draft' : 'published';
        $item->save();

        return back()->with('status', $item->isPublished() ? 'Нийтэллээ.' : 'Ноорог болголоо.');
    }

    /**
     * Админ агуулгыг шалгасны дараа бүх ноорогийг нэг дор нийтэлнэ.
     * Аудиогүй сонсох дасгал зэрэг нийтлэх боломжгүйг алгасна.
     */
    public function publishAll(string $resource): RedirectResponse
    {
        $config = AdminResources::get($resource);
        abort_unless($config['hasStatus'], 404);

        $published = 0;
        $skipped = 0;

        foreach ($config['model']::where('status', 'draft')->cursor() as $item) {
            if ($item->publishBlocker()) {
                $skipped++;

                continue;
            }

            $item->status = 'published';
            $item->save();
            $published++;
        }

        return back()->with('status', "{$published} бичлэг нийтэллээ.".($skipped ? " {$skipped} бичлэгийг нийтлэх боломжгүй тул алгаслаа." : ''));
    }

    private function find(string $resource, int $id): Model
    {
        return AdminResources::get($resource)['model']::findOrFail($id);
    }

    private function form(Request $request, string $resource, Model $item): View
    {
        return view('admin.japanese.form', [
            'resource' => $resource,
            'config' => AdminResources::get($resource),
            'item' => $item,
            'options' => [
                'level' => Level::orderBy('position')->pluck('code', 'id')->all(),
                'category' => Category::orderBy('position')->pluck('name', 'id')->all(),
                'script' => ['hiragana' => 'Хирагана', 'katakana' => 'Катакана'],
                'lesson_type' => Lesson::TYPES,
                'quiz_type' => Quiz::TYPES,
                'audio' => ['' => 'Аудиогүй'] + Audio::orderBy('title')->pluck('title', 'id')->all(),
            ],
            'parent' => $this->parent($request, $item),
        ]);
    }

    /**
     * Жишээ өгүүлбэрийн эцэг (дүрэм эсвэл хичээл).
     *
     * @return array<string, int>
     */
    private function parent(Request $request, Model $item): array
    {
        foreach (['grammar_id', 'lesson_id'] as $key) {
            $value = $item->{$key} ?? $request->integer($key);

            if ($value) {
                return [$key => (int) $value];
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function save(Request $request, string $resource, array $config, Model $item): RedirectResponse
    {
        $rules = [];

        foreach ($config['fields'] as $field) {
            [$name, , $type] = $field;
            $required = $field[3] ?? false;
            $rule = [$required ? 'required' : 'nullable'];

            $rule = array_merge($rule, match ($type) {
                'number' => ['integer', 'min:0', 'max:65000'],
                'textarea' => ['string', 'max:20000'],
                'level' => ['integer', 'exists:japanese_levels,id'],
                'category' => ['integer', 'exists:japanese_categories,id'],
                'audio' => ['integer', 'exists:japanese_audio,id'],
                'script' => [Rule::in(['hiragana', 'katakana'])],
                'lesson_type' => [Rule::in(array_keys(Lesson::TYPES))],
                'quiz_type' => [Rule::in(array_keys(Quiz::TYPES))],
                default => ['string', 'max:255'],
            });

            $rules[$name] = $rule;
        }

        $labels = collect($config['fields'])->mapWithKeys(fn ($field) => [$field[0] => $field[1]])->all();
        $data = $request->validate($rules, [], $labels);

        foreach ($config['fields'] as [$name, , $type]) {
            if ($type === 'number' && ($data[$name] ?? null) === null) {
                $data[$name] = 1;
            }
        }

        $item->fill($data);

        if ($resource === 'examples' && ! $item->exists) {
            $item->fill($request->validate([
                'grammar_id' => ['nullable', 'integer', 'exists:japanese_grammar,id', 'required_without:lesson_id'],
                'lesson_id' => ['nullable', 'integer', 'exists:japanese_lessons,id'],
            ]));
        }

        try {
            $item->save();
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return back()->withInput()->with('error', 'Ижил бичлэг аль хэдийн байгаа тул хадгалах боломжгүй.');
        }

        if ($resource === 'examples') {
            $parent = $item->grammar_id ? ['grammar', $item->grammar_id] : ['lessons', $item->lesson_id];

            return redirect()->route('admin.japanese.edit', $parent)->with('status', 'Жишээг хадгаллаа.');
        }

        return redirect()->route('admin.japanese.index', $resource)->with('status', 'Хадгаллаа. Нийтлэхгүй бол ноорог хэвээр үлдэнэ.');
    }
}
