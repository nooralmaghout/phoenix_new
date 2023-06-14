<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\Admin;
use App\Models\Tourist;
use App\Models\Rate;
use App\Models\Favorite;
use Illuminate\Support\Facades\DB;
use App\Models\Image1;

class PlaceController extends Controller
{
    
    public function createPlace(Request $request){
        $user_id = auth()->user()->id;
        
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
            $request->validate([
            "ar_name" => "required|unique:events",
            "en_name" => "required|unique:events",
            "city_id" => "required",
            "ar_description"=> "required",
            "en_description"=> "required",
            "open_time"=> "required",
            "close_time"=> "required",
            "category_id"=> "required",
            "ar_location"=> "required",
            "en_location"=> "required",
            "map_location"=> "required",
            "stars"=> "required",
            "phone_number"=> "required",
            "breakfast"=> "nullable",
            "lunch_dinner"=> "nullable",
            "images.*"=> "required|image|mimes:png,jpg,jpeg"
            ]);

            try {
                //code...
                DB::beginTransaction();
                $place = new Place();

            $place->city_id = $request->city_id;
            //$place->type_id = $request->type_id;
            $place->en_name = $request->en_name;
            $place->ar_name = $request->ar_name;
            $place->stars = $request->stars;
            $place->avg_rate = $request->avg_rate;
            $place->ar_description = $request->ar_description;
            $place->en_description = $request->en_description;
            $place->phone_number = $request->phone_number;
            $place->open_time = $request->open_time;
            $place->close_time = $request->close_time;
            $place->category_id = $request->category_id;
            $place->ar_location = $request->ar_location; 
            $place->en_location = $request->en_location; 
            $place->map_location = $request->map_location;
            $place->breakfast = isset($request->breakfast) ? $request->breakfast:"";
            $place->lunch_dinner = isset($request->lunch_dinner) ? $request->lunch_dinner:"";
            $images = $request->images;

            $place->save();

        
            foreach ($images as $item){
                echo "k";
                $image = new Image1();                      
                $imageName = (String)time().$item->getClientOriginalName(); 
                $item->move(public_path('groups'), $imageName);
                $image->url = $imageName;
                $image->place_id = $place->id;
                $image->save();
            }
    
            DB::commit(); 
            return response()->json([
                "status" => 1,
                "message" => "Place added successfully"
            ]);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                // throw $th;
                return response()->json([
                "status" => 1,
                "message" => $th//"Something went wrong:" 
                ]);
            }
            
            }else{
                return response()->json([
                    "status" => 1,
                    "message" => "You are not an Admin!"
                ]); 
            }
    }

    public function listPlace(){
        $places = Place::get();

        return response()->json([
            "status" => 1,
            "message" => "Listing Places: ",
            "data" => $places
        ],200);
    }
    

    public function getSinglePlace($id){
        if(Place::where("id", $id)->exists()){
           
            $place_details = Place::where("id", $id)->first();
            return response()->json([
                "status" => 1,
                "message" => "Place found ",
                "data" => $place_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Place not found"
            ],404);
        }
    }

    public function updatePlace(Request $request, $id){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
        if(Place::where("id", $id)->exists()){
           
            $place = Place::find($id);

            $place->en_name = !empty($request->en_name)? $request->en_name : $place->en_name;
            $place->ar_name = !empty($request->ar_name)? $request->ar_name : $place->ar_name;
            $place->stars = !empty($request->stars)? $request->stars : $place->stars;
            $place->avg_rate = !empty($request->avg_rate)? $request->avg_rate : $place->avg_rate;
            $place->ar_description = !empty($request->ar_description)? $request->ar_description : $place->ar_description;
            $place->en_description = !empty($request->en_description)? $request->en_description : $place->en_description;
            $place->phone_number = !empty($request->phone_number)? $request->phone_number : $place->phone_number;
            $place->open_time = !empty($request->open_time)? $request->open_time : $place->open_time;
            $place->close_time = !empty($request->close_time)? $request->close_time : $place->close_time;
            $place->category_id = !empty($request->category_id)? $request->category_id : $place->category_id;
            $place->en_location = !empty($request->en_location)? $request->en_location : $place->en_location;
            $place->ar_location = !empty($request->ar_location)? $request->ar_location : $place->ar_location;
            $place->map_location = !empty($request->map_location)? $request->map_location : $place->map_location;
            $place->city_id = !empty($request->city_id)? $request->city_id : $place->city_id;
            //$place->type_id = !empty($request->type_id)? $request->type_id : $place->type_id;

            $place->save();

            return response()->json([
                "status" => 1,
                "message" => "Place updated successfully "
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Place not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }
    }

    public function deletePlace($id){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
        if(Place::where("id", $id)->exists()){
        DB::beginTransaction();  
            $place = Place::find($id);

            $images_ids = Image1::where('place_id',$id)->get('id');
            echo "start";
            if(!$place || !$images_ids){
                echo "null";
                DB::rollback();
                return response()->json([
                    "status" => 0,
                    "message" => "Place not found"
                ],404);
            }
            else{
                echo "else";
                foreach($images_ids as $image_id){
                    echo $image_id->id;
                    $image =Image1::find($image_id->id);
                    $image->delete();
                }
                // $images->delete();
                $place->delete();
                DB::commit();
                return response()->json([
                    "status" => 1,
                    "message" => "Place deleted successfully "
                ],200);
            }
            
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Place not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }
    }

    public function searchByName(Request $request){
        $request->validate([
            "name" => "required",
           
        ]);
        if(Place::where("ar_name", "like", "%".$request->name."%")->orWhere("en_name", "like", "%".$request->name."%")->exists()){
           
            $place_details = Place::where("ar_name", "like", "%".$request->name."%")->orWhere("en_name", "like", "%".$request->name."%")->get();
            return response()->json([
                "status" => 1,
                "message" => "Place found ",
                "data" => $place_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Place not found"
            ],404);
        }
    }

    public function searchByCity(Request $request){
        $request->validate([
            "name" => "required",
           
        ]);
        if( Place::withWhereHas('city', fn  ($query) => 
        $query->where('ar_name', 'like',  "%".$request->name."%")->orWhere('en_name', 'like', "%".$request->name."%")
           )->exists()){
           
            $place_details = Place::withWhereHas('city', fn  ($query) => 
            $query->where('ar_name', 'like',  "%".$request->name."%")->orWhere('en_name', 'like', "%".$request->name."%")
               )->get();
            return response()->json([
                "status" => 1,
                "message" => "Place found ",
                "data" => $place_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Place not found"
            ],404);
        }
    }

    public function mostVisited(){
        $user_id = auth()->user()->id;
        $rates = Rate::where('rate', '>', 2)->get();
        // echo $rates;
        if($rates->isEmpty()){
            // echo "empty";
            $city_id = Tourist::where('id',$user_id)->first();
            $place_details = Place::where('city_id',$city_id->city_id)->get();
            return response()->json([
                "status" => 1,
                "message" => "no rates ",
                "data" => $place_details
            ],200);
        }
        else{
            // echo "full";
            $result = 
            DB::table('places')->select('places.*',
             DB::raw('COUNT(rates.place_id) AS places_count'))
            ->join('rates', 'places.id', '=', 'rates.place_id')->where('rates.rate', '>', 2)
            // ->select('places.*')
            ->groupBy('places.id',
            'places.city_id',
            'places.ar_name',
            'places.en_name',
            'places.open_time',
            'places.close_time',
            'places.category_id',
            'places.ar_description',
            'places.en_description',
            'places.ar_location',
            'places.en_location',
            'places.map_location',
            'places.stars',
            'places.avg_rate',
            'places.phone_number',
            'places.breakfast',
            'places.lunch_dinner',
            'places.created_at',
            'places.updated_at',
            )->orderBy('places_count', 'desc')
            ->get();

            return response()->json([
                "status" => 1,
                "message" => "from rates ",
                "data" => $result
            ],200);
            
        }
    }

    public function recommendedForYou(){
        $user_id = auth()->user()->id;
        $favs = Favorite::where('tourist_id',$user_id)->get();
        if($favs->isEmpty()){
            $tourist = Tourist::where('id',$user_id)->first();
            echo $tourist->city_id;
            $place_details = Place::where(['city_id'=>$tourist->city_id],
                    )->get();
            return response()->json([
                "status" => 1,
                "message" => "no favs ",
                "data" => $place_details
            ],200);
        }
        else{
            $cities = DB::table('places')->select('places.city_id',
            DB::raw('COUNT(places.city_id) AS cities_count','COUNT(places.stars) AS stars_count'))
           ->join('favorites', 'places.id', '=', 'favorites.place_id')->where('favorites.tourist_id', '=', $user_id)
           ->groupBy(
           'places.city_id',
                 )->orderBy('cities_count', 'desc')
           ->get();
           $stars = DB::table('places')->select('places.stars',
            DB::raw('COUNT(places.stars) AS stars_count'))
           ->join('favorites', 'places.id', '=', 'favorites.place_id')->where('favorites.tourist_id', '=', $user_id)
           ->groupBy(
           'places.stars',
                 )->orderBy('stars_count', 'desc')
           ->get();
           $categories = DB::table('places')->select('places.category_id',
            DB::raw('COUNT(places.category_id) AS categories_count'))
           ->join('favorites', 'places.id', '=', 'favorites.place_id')->where('favorites.tourist_id', '=', $user_id)
           ->groupBy(
           'places.category_id',
                 )->orderBy('categories_count', 'desc')
           ->get();
            $result_cities =[];
            $result_stars =[];
            $result_categories =[];
            foreach($cities as $city){
                echo $city->city_id;
                array_push($result_cities,$city->city_id);

            }
            foreach($stars as $star){
                echo $star->stars;
                array_push($result_stars,$star->stars);
            }
            foreach($categories as $category){
                echo $category->category_id;
                array_push($result_categories,$category->category_id);
            }

            $place_details = Place::whereIn('stars', $result_stars)
            ->whereIn('city_id',$result_cities )
            ->whereIn('category_id',$result_categories )->get();
                // array_push($result,$place_details);
                
            // }
            return response()->json([
                "status" => 1,
                "message" => "from rates ",
                "data" => $place_details
            ],200);
            
        }
    }
}
