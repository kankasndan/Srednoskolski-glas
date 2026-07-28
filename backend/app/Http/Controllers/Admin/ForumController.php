<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Forum;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Request $request)
    {

        $forums = Forum::query()
            ->with("school", "forumUser")
            ->when($request->filled("search"), function ($query) use ($request){
                $search = $request->search;
                $query->where("name", "like", "%{$search}%");
            })
            ->when($request->filled("type"), function ($query) use ($request){
                $type = $request->type;
                $query->where("type", $type);
            })
            ->when($request->filled("city"), function ($query) use ($request){
                $city = $request->city;
                $query->whereHas("school", function ($q) use ($city){
                    $q->where("city_id", $city);
                });
            })
            ->paginate(10);

        $cities = City::all();

        return view("admin.forums.index", compact("forums", "cities"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
            "description" => "required",
            "slug" => "required",
            "type" => "required"
        ]);

        Forum::create([
            "name" => $request->name,
            "description" => $request->description,
            "slug" => $request->slug,
            "type" => $request->type,
            "imageUrl" => $request->imageUrl,
            "bannerUrl" => $request->bannerUrl
        ]);

        return back()->with("success", "Forum created!");
    }

    public function liveSearch(Request $request)
    {
        $query = $request->q;

        $forums = Forum::where("slug", "like", "%{$query}%")
                ->limit(10)
                ->get();

        return response()->json($forums);
    }

    public function show(Forum $forum)
    {
        $forum->load(['school.city', 'moderator']);

        $threads = $forum->threads()->with(["user", "comments"])->latest()->paginate(10);

        return view("admin.forums.show", compact('forum', 'threads'));
    }

    public function edit(Forum $forum, Request $request)
    {
        $request->validate([
            "name" => "required",
            "description" => "required",
            "slug" => "required",
            "type" => "required"
        ]);

        $forum->update([
            "name" => $request->name,
            "description" => $request->description,
            "slug" => $request->slug,
            "type" => $request->type,
            "imageUrl" => $request->imageUrl,
            "bannerUrl" => $request->bannerUrl
        ]);

        return back()->with("success", "Forum updated!");
    }

    public function destroy(Forum $forum)
    {
        $forum->delete();

        return redirect()->route("forum.index")->with(["success", "Forum successfully deleted!"]);
    }
}
