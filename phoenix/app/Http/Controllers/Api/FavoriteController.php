<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Tourist;
use App\Models\Place;
use App\Models\Places_category;
use App\Models\city;
use App\Models\Image1;
use Illuminate\Support\Facades\DB;


class FavoriteController extends Controller
{
    public function createFavorite($place_id){
        $tourist_id = auth()->user()->id;
        if(Tourist::where([
            "id" => $tourist_id,
        ] )->exists()){
        
        // $tourist_id = auth()->user()->id;
        $favorite = new Favorite();

         $favorite->tourist_id = $tourist_id;
         $favorite->place_id = $place_id;
        
        $favorite->save();

        return response()->json([
            "status" => 1,
            "message" => "Favorite added successfully"
        ]);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function listFavorite(){
        $tourist_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $tourist_id,
        ] )->exists()){
        //$tourist_id = auth()->user()->id;

        $favorites = Favorite::where("tourist_id", $tourist_id)->get();
        
        foreach($favorites as $favorite){
            $place = Place::where("id", $favorite->place_id)->first();
            $city = city::where("id", $place->city_id)->first();
            $category = Places_category::where("id", $place->category_id)->first();
            $images = Image1::where("place_id",$place->city_id)->get();
            $place->city_id = $city;
            $place->category_id = $category;
            $place->images = $images;
            $favorite->place_id = $place;
            
        }
        
        return response()->json([
            "status" => 1,
            "message" => "Listing Favorites: ",
            "data" => $favorites->pluck('place_id')
        ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function getSingleFavorite($id){
        $tourist_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $tourist_id,
        ] )->exists()){
        //$tourist_id = auth()->user()->id;

        if(Favorite::where([
            "id"=> $id,
            "tourist_id" => $tourist_id
            ])->exists()){
           
            $favorite_details = Favorite::where([
                "id"=> $id,
                "tourist_id" => $tourist_id
                ])->first();
            $place = Place::where("id", $favorite_details->place_id)->first();
            $city = City::where("id", $place->city_id)->first();
            $category = Places_category::where("id", $place->category_id)->first();
            $place->city_id = $city;
            $place->category_id = $category;
            // $favorite_details->place_id = $place;
            return response()->json([
                "status" => 1,
                "message" => "Favorite found ",
                "data" => $place
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Favorite not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function deleteFavorite($id){
        $tourist_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $tourist_id,
        ] )->exists()){
        //$tourist_id = auth()->user()->id;

        if(Favorite::where([
            "id"=> $id,
            "tourist_id" => $tourist_id
            ])->exists()){
           
            $favorite = Favorite::where([
                "id"=> $id,
                "tourist_id" => $tourist_id
                ])->first();

            $favorite->delete();

            return response()->json([
                "status" => 1,
                "message" => "Favorite deleted successfully "
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Favorite not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function searchByName($search){//with
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){

        if( Favorite::where('tourist_id', '=',  $user_id)->withWhereHas('place', fn  ($query) => 
        $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
           )->exists()){
           
            $favorite_details = Favorite::where('tourist_id', '=',  $user_id)->withWhereHas('place', fn  ($query) => 
            $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
               )->get();
               foreach($favorite_details as $favorite){
                $place = Place::where("id", $favorite->place_id)->first();
                $city = city::where("id", $place->city_id)->first();
                $category = Places_category::where("id", $place->category_id)->first();
                $images = Image1::where("place_id",$place->city_id)->get();
                $place->city_id = $city;
                $place->category_id = $category;
                $place->images = $images;
                $favorite->place_id = $place;
                
            }
            return response()->json([
                "status" => 1,
                "message" => "Favorite found ",
                "data" => $favorite_details->pluck('place_id')
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Favorite not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function searchByCity($search){//with
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        
        
        $favs = Favorite::where('tourist_id', '=',  $user_id)->withWhereHas('place', fn  ($query) => 
        $query->withWhereHas('city', fn  ($q) =>
         $q->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")))->get();
            // $query->where($query->place->city->ar_name, 'like',  "%".$request->name."%")->orWhere($query->place->city->en_name, 'like', "%".$request->name."%")
               
        if(Favorite::where('tourist_id', '=',  $user_id)->withWhereHas('place', fn  ($query) => 
        $query->withWhereHas('city', fn  ($q) =>
         $q->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")))->exists()){
           
            $favorite_details = Favorite::where('tourist_id', '=',  $user_id)->withWhereHas('place', fn  ($query) => 
            $query->withWhereHas('city', fn  ($q) =>
             $q->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")))->get();
             foreach($favorite_details as $favorite){
                $place = Place::where("id", $favorite->place_id)->first();
                $city = city::where("id", $place->city_id)->first();
                $category = Places_category::where("id", $place->category_id)->first();
                $images = Image1::where("place_id",$place->city_id)->get();
                $place->city_id = $city;
                $place->category_id = $category;
                $place->images = $images;
                $favorite->place_id = $place;
                
            }

             return response()->json([
                "status" => 1,
                "message" => "Favorite found ",
                "data" => $favorite_details->pluck('place_id')
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Favorite not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }
}
