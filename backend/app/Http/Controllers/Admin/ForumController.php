<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Media;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Support\LikeEscape;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view forums');

        $forums = Forum::query()
            ->with('school', 'forumUser')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', LikeEscape::contains((string) $request->search));
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $type = $request->type;
                $query->where('type', $type);
            })
            ->when($request->filled('city'), function ($query) use ($request) {
                $city = $request->city;
                $query->whereHas('school', function ($q) use ($city) {
                    $q->where('city_id', $city);
                });
            })
            ->paginate(10);

        $cities = City::all();

        $schools = School::all();


        return view('admin.forums.index', compact('forums', 'cities', 'schools'));
    }

    public function store(Request $request)
    {
        $this->authorize('create forums');

        if ($request->input('slug') === '') {
            $request->merge(['slug' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:forums,slug'],
            'icon' => ['nullable', 'image', 'max:5120'],
            'banner' => ['nullable', 'image', 'max:10240'],
        ]);

        $slug = filled($validated['slug'] ?? null)
            ? $validated['slug']
            : Slug::make($validated['name']);

        // School forums are created together with their school (SchoolController),
        // so anything created here is a general forum.
        $imageUrl = $this->defaultIconUrl($slug, 'general');
        $bannerUrl = $this->defaultBannerUrl($slug, 'general');

        if ($request->file('icon') instanceof UploadedFile) {
            $imageUrl = Media::upload($request->file('icon'), 'forums/icons')->url;
        }

        if ($request->file('banner') instanceof UploadedFile) {
            $bannerUrl = Media::upload($request->file('banner'), 'forums/banners')->url;
        }

        Forum::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'slug' => $slug,
            'type' => 'general',
            'imageUrl' => $imageUrl,
            'bannerUrl' => $bannerUrl,
        ]);

        return back()->with('success', 'Форумот е креиран!');
    }

    public function liveSearch(Request $request)
    {
        $this->authorize('search forums');

        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $forums = Forum::where('name', 'like', LikeEscape::contains($query))
            ->limit(10)
            ->get(['id', 'name', 'slug', 'type', 'imageUrl']);

        return response()->json($forums);
    }

    public function show(Forum $forum)
    {
        $this->authorize('view forum details');

        $forum->load(['school.city', 'moderator']);

        $threads = $forum->threads()->with('user')->withCount('comments')->latest()->paginate(10);

        return view('admin.forums.show', compact('forum', 'threads'));
    }

    public function edit(Forum $forum, Request $request)
    {
        $this->authorize('update forums');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('forums', 'slug')->ignore($forum->id)],
            'icon' => ['nullable', 'image', 'max:5120'],
            'banner' => ['nullable', 'image', 'max:10240'],
        ]);

        $imageUrl = $forum->imageUrl;
        $bannerUrl = $forum->bannerUrl;

        if ($request->file('icon') instanceof UploadedFile) {
            $imageUrl = Media::upload($request->file('icon'), 'forums/icons')->url;
        }

        if ($request->file('banner') instanceof UploadedFile) {
            $bannerUrl = Media::upload($request->file('banner'), 'forums/banners')->url;
        }

        $forum->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'slug' => $validated['slug'],
            'imageUrl' => $imageUrl,
            'bannerUrl' => $bannerUrl,
        ]);

        return back()->with('success', 'Форумот е ажуриран!');
    }

    public function destroy(Forum $forum)
    {
        $this->authorize('delete forums');

        $forum->delete();

        return redirect()->route('forum.index')->with('success', 'Форумот е успешно избришан!');
    }

    private function defaultIconUrl(string $slug, string $type): string
    {
        if ($type === 'school') {
            return '/icons/uchilishte.svg';
        }

        return '/icons/'.$slug.'.svg';
    }

    private function defaultBannerUrl(string $slug, string $type): string
    {
        if ($type === 'school') {
            return '/banners/school.svg';
        }

        return '/banners/'.$slug.'.svg';
    }
}
