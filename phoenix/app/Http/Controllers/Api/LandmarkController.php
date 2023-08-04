<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Landmark;
use App\Models\Admin;
use App\Models\City;
use App\Models\Category;
use App\Models\Landmarks_type;
use Illuminate\Support\Facades\DB;
use App\Models\Image1;


class LandmarkController extends Controller
{
    public function createLandmark(Request $request){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
        $request->validate([
            "ar_name" => "required",
            "en_name" => "required",
            "city_id" => "required",
            "days_off_id" => "required",
            "ar_description" => "required",
            "en_description" => "required",
            "open_time" => "required",
            "close_time" => "required",
            "type_id" => "required",
            "category_id" => "required",
            "ar_location" => "required",
            "en_location" => "required",
            "map_x" => "nullable",
            "map_y" => "nullable",
            "images.*"=> "required|image|mimes:png,jpg,jpeg"
                    ]);

        try {
            DB::beginTransaction();
            $landmark = new Landmark();

            $landmark->ar_name = $request->ar_name;
            $landmark->en_name = $request->en_name; 
            $landmark->days_off_id = $request->days_off_id;
            $landmark->ar_description = $request->ar_description;
            $landmark->en_description = $request->en_description;
            $landmark->open_time = $request->open_time;
            $landmark->close_time = $request->close_time;
            $landmark->type_id = $request->type_id;
            $landmark->category_id = $request->category_id;
            $landmark->ar_location = $request->ar_location;
            $landmark->en_location = $request->en_location;
            $landmark->map_x = $request->map_x;
            $landmark->map_y = $request->map_y;
            $landmark->city_id = $request->city_id;

            $landmark->save();

            $images = $request->images;

            foreach ($images as $item){
                echo "k";
                $image = new Image1();                      
                $imageName = (String)time().$item->getClientOriginalName(); 
                $item->move(public_path('groups'), $imageName);
                $image->url = $imageName;
                $image->landmark_id = $landmark->id;
                $image->save();
            }

            DB::commit();
            return response()->json([
                "status" => 1,
                "message" => "Landmark added successfully"
            ]); 
        } catch (\Throwable $th) {
            DB::rollBack();
             throw $th;
            return response()->json([
            "status" => 1,
            "message" => $th 
            ]);
        }
        
        }else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }
    }

    public function listLandmark(){
        $landmarks = Landmark::get();

        return response()->json([
            "status" => 1,
            "message" => "Listing Landmarks: ",
            "data" => $landmarks
        ],200);
    }

    public function getSingleLandmark($id){
        if(Landmark::where("id", $id)->exists()){
           
            $landmark_details = Landmark::where("id", $id)->first();
            return response()->json([
                "status" => 1,
                "message" => "Landmark found ",
                "data" => $landmark_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Landmark not found"
            ],404);
        }
    }

    public function updateLandmark(Request $request, $id){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
        if(Landmark::where("id", $id)->exists()){
           
            $landmark = Landmark::find($id);
            
            $landmark->ar_name = !empty($request->ar_name)? $request->ar_name : $landmark->ar_name;
            $landmark->en_name = !empty($request->en_name)? $request->en_name : $landmark->en_name;
            $landmark->city_id = !empty($request->city_id)? $request->city_id : $landmark->city_id;
            $landmark->days_off_id = !empty($request->days_off_id)? $request->days_off_id : $landmark->days_off_id;
            $landmark->en_description = !empty($request->en_description)? $request->en_description : $landmark->en_description;
            $landmark->ar_description = !empty($request->ar_description)? $request->ar_description : $landmark->ar_description;
            $landmark->open_time = !empty($request->open_time)? $request->open_time : $landmark->open_time;
            $landmark->close_time = !empty($request->close_time)? $request->close_time : $landmark->close_time;
            $landmark->type_id = !empty($request->type_id)? $request->type_id : $landmark->type_id;
            $landmark->category_id = !empty($request->category_id)? $request->category_id : $landmark->category_id;
            $landmark->en_location = !empty($request->en_location)? $request->en_location : $landmark->en_location;
            $landmark->ar_location = !empty($request->ar_location)? $request->ar_location : $landmark->ar_location;
            $landmark->map_x = !empty($request->map_x)? $request->map_x : $landmark->map_x;
            $landmark->map_y = !empty($request->map_y)? $request->map_y : $landmark->map_y;

            $landmark->save();

            return response()->json([
                "status" => 1,
                "message" => "Landmark updated successfully "
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Landmark not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }
    }

    public function deleteLandmark($id){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
        if(Landmark::where("id", $id)->exists()){
            DB::beginTransaction();  
            $landmark = Landmark::find($id);

            $images_ids = Image1::where('landmark_id',$id)->get('id');
            echo "start";

            if(!$landmark || !$images_ids){
                echo "null";
                DB::rollback();
                return response()->json([
                    "status" => 0,
                    "message" => "Landmark not found"
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
                $landmark->delete();
                DB::commit();
                return response()->json([
                    "status" => 1,
                    "message" => "Landmark deleted successfully "
                ],200);
            }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Landmark not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }
    }

    public function searchByName($search){
        
        if(Landmark::where("ar_name", "like", "%".$search."%")->orWhere("en_name", "like", "%".$search."%")->exists()){
           
            $landmark_details = Landmark::where("ar_name", "like", "%".$search."%")->orWhere("en_name", "like", "%".$search."%")->get();
            foreach ($landmark_details as $landmark){
                $city = City::where("id",$landmark->city_id)->first();
                $images = Image1::where("landmark_id",$landmark->city_id)->get();
                $category = Category::where("id",$landmark->category_id)->first();
                $type = Landmarks_type::where("id",$landmark->type_id)->first();
                $landmark->city_id = $city;
                $landmark->images = $images;
                $landmark->category_id = $category;
                $landmark->type_id = $type;
                }
            return response()->json([
                "status" => 1,
                "message" => "Landmark found ",
                "data" => $landmark_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Landmark not found"
            ],404);
        }
    }

    public function searchByCity($search){
       
        if( Landmark::withWhereHas('city', fn  ($query) => 
        $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
           )->exists()){
           
            $landmark_details = Landmark::withWhereHas('city', fn  ($query) => 
            $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
               )->get();
               foreach ($landmark_details as $landmark){
                // $city = City::where("id",$landmark->city_id)->first();
                $images = Image1::where("landmark_id",$landmark->city_id)->get();
                $category = Category::where("id",$landmark->category_id)->first();
                $type = Landmarks_type::where("id",$landmark->type_id)->first();
                // $landmark->city_id = $city;
                $landmark->images = $images;
                $landmark->category_id = $category;
                $landmark->type_id = $type;
                }
            return response()->json([
                "status" => 1,
                "message" => "Landmark found ",
                "data" => $landmark_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Landmark not found"
            ],404);
        }
    }

    public function listLandmark2(){
        $landmarks = Landmark::get();
    foreach ($landmarks as $landmark){
                $city = City::where("id",$landmark->city_id)->first();
                $images = Image1::where("landmark_id",$landmark->city_id)->get();
                $category = Category::where("id",$landmark->category_id)->first();
                $type = Landmarks_type::where("id",$landmark->type_id)->first();
                $landmark->city_id = $city;
                $landmark->images = $images;
                $landmark->category_id = $category;
                $landmark->type_id = $type;
                }
        return response()->json([
            "status" => 1,
            "message" => "Listing Landmarks: ",
            "data" => $landmarks
        ],200);
    }

    public function getSingleLandmark2($id){
        if(Landmark::where("id", $id)->exists()){
           
            $landmark_details = Landmark::where("id", $id)->first();
            $city = City::where("id", $landmark_details->city_id)->first();
            $category = Category::where("id", $landmark_details->category_id)->first();
            $type = Landmarks_type::where("id", $landmark_details->type_id)->first();
            $landmark_details->city_id = $city;
            $landmark_details->ctegory_id = $category ;
            $landmark_details->type_id = $type ;
            $images = Image1::where("landmark_id", $id)->get();
            $landmark_details->images = $images;
        
            return response()->json([
                "status" => 1,
                "message" => "Landmark found ",
                "data" => $landmark_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Landmark not found"
            ],404);
        }
    }
}
