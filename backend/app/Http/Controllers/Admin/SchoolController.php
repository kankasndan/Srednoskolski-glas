<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Support\Slug;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::query()
            ->with("studentData.user", "city")
            ->when($request->filled("city"), function ($query) use ($request){
                $query->where("city_id", $request->city);
            })
            ->when($request->filled("search"), function ($query) use ($request){
                $query->where("name", $request->search);
            })     
            ->paginate(10);

        $cities = City::get();

        return view("admin.schools.index", compact("schools", "cities"));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => "required",
            'city' => "required",
        ]);

        School::create([
            "name" => $request->name,
            "city_id" => $request->city
        ]);

        $slug = Slug::make($request->name);

        Forum::create([
            "name" => $request->name,
            "type" => "school",
            "slug" => $slug
        ]);

        return back()->with(['success' => 'School and forum created successfully!']);
    }

    public function liveSearch(Request $request)
    {
        $query = $request->q;    

        $schools = School::where("name", "like", "%$query%")->get();

        return response()->json($schools);
    }

    public function destroy(School $school, Request $request)
    {
        $school->delete();

        if($request->q){
            unset($request->q);
        }

        return back()->with(["success" => "School deleted successfully"]);
    }
}
