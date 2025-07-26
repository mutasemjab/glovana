<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderRating;

class RatingController extends Controller
{

    public function index()
    {
        $ratings = ProviderRating::with(['providerType.provider', 'user'])
            ->latest()
            ->paginate(20);
            
        return view('admin.ratings.index', compact('ratings'));
    }
    
    /**
     * Remove the specified rating
     */
    public function destroy($id)
    {
        $rating = ProviderRating::findOrFail($id);
        $rating->delete();
        
        return redirect()->route('admin.ratings.index')
            ->with('success', 'Rating deleted successfully');
    }
   

   
}
