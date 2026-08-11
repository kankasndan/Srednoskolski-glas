<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Media;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $forums = Forum::query()
            ->with('school', 'forumUser')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
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

        $imageUrl = $this->defaultIconUrl($slug, $validated['type']);
        $bannerUrl = $this->defaultBannerUrl($slug, $validated['type']);

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
            'imageUrl' => $imageUrl,
            'bannerUrl' => $bannerUrl,
        ]);

        return back()->with('success', 'Forum created!');
    }

    public function liveSearch(Request $request)
    {
        $query = $request->q;

        $forums = Forum::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($forums);
    }

    public function show(Forum $forum)
    {
        $forum->load(['school.city', 'moderator']);

        $threads = $forum->threads()->with('user')->withCount('comments')->latest()->paginate(10);

        return view('admin.forums.show', compact('forum', 'threads'));
    }

    public function edit(Forum $forum, Request $request)
    {
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

        return back()->with('success', 'Forum updated!');
    }

    public function destroy(Forum $forum)
    {
        $forum->delete();

        return redirect()->route('forum.index')->with('success', 'Forum successfully deleted!');
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
