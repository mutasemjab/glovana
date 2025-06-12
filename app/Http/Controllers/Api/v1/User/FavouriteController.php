<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\ProductResource;
use App\Models\Favorite;
use App\Models\Favourite;
use App\Models\ProductFavourite;
use App\Models\ProviderFavourite;
use App\Traits\Responses;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    use Responses;

    public function index()
    {
        $user = auth()->user();
        $favorite = $user->favourites;
        return $this->success_response('Available favorite', $favorite);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'product_id'=>'required|exists:products,id'
        ]);

        $favorite = ProductFavourite::where('user_id',$request->user()->id)
            ->where('product_id',$request->product_id)->first();
        if($favorite){
            if ($favorite->delete()) {
                return response(['message' => 'Changed','is_favorite'=>false], 200);
            }else{
                return response(['errors' => ['Something wrong']], 403);
            }
        }
        $favorite = new ProductFavourite();
        $favorite->user_id = $request->user()->id;
        $favorite->product_id = $request->product_id;
        if ($favorite->save()) {
            return response(['message' => 'Changed','is_favorite'=>true], 200);
        }else{
            return response(['errors' => ['Something wrong']], 403);
        }
    }


    public function indexProvider()
    {
        $user = auth()->user();
        $favorite = $user->providerFavourites;
        return $this->success_response('Available Provider Favourites', $favorite);
    }

    public function storeProvider(Request $request)
    {
        $this->validate($request,[
            'provider_type_id'=>'required|exists:provider_types,id'
        ]);

        $favorite = ProviderFavourite::where('user_id',$request->user()->id)
            ->where('provider_type_id',$request->provider_type_id)->first();
        if($favorite){
            if ($favorite->delete()) {
                return response(['message' => 'Changed','is_favorite'=>false], 200);
            }else{
                return response(['errors' => ['Something wrong']], 403);
            }
        }
        $favorite = new ProviderFavourite();
        $favorite->user_id = $request->user()->id;
        $favorite->provider_type_id = $request->provider_type_id;
        if ($favorite->save()) {
            return response(['message' => 'Changed','is_favorite'=>true], 200);
        }else{
            return response(['errors' => ['Something wrong']], 403);
        }
    }

}
