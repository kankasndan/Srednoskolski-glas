<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Support\LikeEscape;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view schools');

        $schools = School::query()
            ->with("studentData.user", "city")
            ->when($request->filled("city"), function ($query) use ($request){
                $query->where("city_id", $request->city);
            })
            ->when($request->filled("search"), function ($query) use ($request){
                $query->where("name", "like", LikeEscape::contains((string) $request->search));
            })
            ->paginate(10);

        $cities = City::get();

        return view("admin.schools.index", compact("schools", "cities"));
    }

    public function store(Request $request)
    {
        $this->authorize('create schools');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'city' => ['required', 'integer', 'exists:cities,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            $school = School::create([
                "name" => $validated['name'],
                "city_id" => $validated['city'],
            ]);

            // The forum must point back at the school, otherwise nothing can tell
            // which students it belongs to.
            Forum::create([
                "name" => $validated['name'],
                "type" => "school",
                "slug" => Slug::make($validated['name']),
                "school_id" => $school->id,
            ]);
        });

        return back()->with(['success' => 'Училиштето и форумот се успешно креирани!']);
    }

    public function liveSearch(Request $request)
    {
        $this->authorize('search schools');

        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $schools = School::query()
            ->where("name", "like", LikeEscape::contains($query))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($schools);
    }

    public function destroy(School $school, Request $request)
    {
        $this->authorize('delete schools');

        $school->delete();

        if($request->q){
            unset($request->q);
        }

        return back()->with(['success' => 'Училиштето е успешно избришано.']);
    }
}
