<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poster;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class PosterController extends Controller
{
    public function getPosters(Request $request){
        if (isset($request->id)) {
            $result = [];
            $result['poster'] =  Poster::where('posters.id', $request->id)->join('prices', 'posters.price_id', 'prices.id')->select('posters.*', 'prices.signature')->first();
            $result['images'] = Image::where('poster_id', $result['poster']->id)->get();
            return response()->json([
                'poster' => $result
            ], 200);
        }else {
            $posters = null;
            if (isset($request->city)) {
                $posters = Poster::where('city', $request->city);
            }

            if (isset($request->priceFrom)) {
                if (!is_null($posters)) {
                    $posters = $posters->where('price', '>', $request->priceFrom);
                }else {
                    $posters = Poster::where('price', '>', $request->priceFrom);
                }
            }

            if (isset($request->priceTo)) {
                if (!is_null($posters)) {
                    $posters = $posters->where('price', '<', $request->priceTo);
                }else {
                    $posters = Poster::where('price', '<', $request->priceTo);
                }
            }
            
            if (!is_null($posters)) {
                $posters = $posters->join('prices', 'posters.price_id', 'prices.id')->select('posters.*', 'prices.signature')->paginate($request->perPage);
            }else {
                $posters = Poster::join('prices', 'posters.price_id', 'prices.id')->select('posters.*', 'prices.signature')->paginate($request->perPage);
            }

            foreach ($posters as $poster) {
                $image = Image::where('poster_id', $poster->id)->first();
                if (!is_null($image))
                    $poster->image = $image->link;
                else
                    $poster->image = "";
            }
            return response()->json([
                'poster' => $posters
            ], 200);
        }
    }

    public function getCities(Request $request) {
        $cities = DB::table('posters')->select('city')->distinct()->get();
        return response()->json([
            'cities' => $cities
        ], 200);
    }
}
