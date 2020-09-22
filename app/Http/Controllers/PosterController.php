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
            $result['poster'] =  Poster::where('id', $request->id)->first();
            $result['images'] = Image::where('poster_id', $result['poster']->id)->get();
            return response()->json([
                'poster' => $result
            ], 200);
        }else {
            $posters = null;
            if (isset($request->city)) {
                $posters = Poster::where('city', $request->city);
            }

            $params  = [ ];
            if (isset($request->priceFrom)) {
                $params[] = ['price', '>', $request->priceFrom];
            }

            if (isset($request->priceTo)) {
                $params[] = ['price', '<', $request->priceTo];
            }

            if (!empty($params)) {
                if (!is_null($posters)) {
                    $posters = $posters->where('price', $params);
                }else {
                    $posters = Poster::where('price', $params);
                }
            }
            
            if (!is_null($posters)) {
                $posters = $posters->paginate($request->perPage);
            }else {
                $posters = Poster::paginate($request->perPage);
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
