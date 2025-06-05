<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\Ahadeeth;
use App\Models\AhadeethClass;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class RatingController extends Controller
{

    public function index(Request $request)
    {
        // Search query
        $searchQuery = $request->input('search');

        // Fetch ratings with relationships (User and Class) and optional search
        $data = Rating::with(['user', 'class'])
            ->when($searchQuery, function ($query) use ($searchQuery) {
                return $query->whereHas('user', function ($subQuery) use ($searchQuery) {
                    $subQuery->where('name', 'LIKE', "%$searchQuery%");
                })
                ->orWhereHas('class', function ($subQuery) use ($searchQuery) {
                    $subQuery->where('name', 'LIKE', "%$searchQuery%");
                })
                ->orWhere('day', 'LIKE', "%$searchQuery%")
                ->orWhere('date_of_rating', 'LIKE', "%$searchQuery%");
            })
            ->orderBy('created_at', 'desc') // Order by latest created
            ->paginate(10); // Paginate with 10 records per page
        // Return to the index view
        return view('admin.ratings.index', compact('data', 'searchQuery'));
    }

    public function create()
    {
        $users = User::all();
        $classes = Clas::all();

        return view('admin.ratings.create', compact('users', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day' => 'required|string',
            'date_of_rating' => 'required|date',
            'rating_of_share' => 'required|numeric',
            'rating_of_homework' => 'required|numeric',
            'rating_of_save' => 'required|numeric',
            'rating_of_recitation' => 'required|numeric',
            'rating_of_quiz' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'clas_id' => 'required|exists:clas,id',
        ]);

        Rating::create($validated);

        return redirect()->route('ratings.index')->with('success', __('messages.Record created successfully.'));
    }

    public function edit($id)
    {
        $rating = Rating::findOrFail($id);
        $users = User::all();
        $classes = Clas::all();

        return view('admin.ratings.edit', compact('rating', 'users', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'day' => 'required|string',
            'date_of_rating' => 'required|date',
            'rating_of_share' => 'required|numeric',
            'rating_of_homework' => 'required|numeric',
            'rating_of_save' => 'required|numeric',
            'rating_of_recitation' => 'required|numeric',
            'rating_of_quiz' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'clas_id' => 'required|exists:clas,id',
        ]);

        $rating = Rating::findOrFail($id);
        $rating->update($validated);

        return redirect()->route('ratings.index')->with('success', __('messages.Record updated successfully.'));
    }
}
