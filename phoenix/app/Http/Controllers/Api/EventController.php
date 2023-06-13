<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Admin;
use App\Models\city;
use Illuminate\Support\Facades\DB;
use App\Models\Image1;


class EventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
      
        $imageName = time().'.'.$request->image->extension();  
       
        $request->image->move(public_path('images'), $imageName);
        // public/images/file.png
    
        /* 
            Write Code Here for
            Store $imageName name in DATABASE from HERE 
        */
      
        return back()
            ->with('success','You have successfully upload image.')
            ->with('image',$imageName); 
    }
    
    public function createEvent(Request $request){
        $user_id = auth()->user()->id;
        
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
            $request->validate([
                "ar_name" => "required|unique:events",
                "en_name" => "required|unique:events",
                "city_id" => "required",
                "start_date" => "required",
                "end_date" => "required",
                "ar_description" => "required",
                "en_description" => "required",
                "open_time" => "required",
                "close_time" => "required",
                "ar_location"=> "required",
                "en_location"=> "required",
                "map_location"=> "required",
                "images.*"=> "required|image|mimes:png,jpg,jpeg"
                ]);
        try {
        DB::beginTransaction();
        $event = new Event();

        $event->ar_name = $request->ar_name;
        $event->en_name = $request->en_name;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->ar_description = $request->ar_description;
        $event->en_description = $request->en_description;
        $event->open_time = $request->open_time;
        $event->close_time = $request->close_time;
        $event->ar_location = $request->ar_location;
        $event->en_location = $request->en_location;
        $event->map_location = $request->map_location;
        $event->city_id = $request->city_id;
        $event->save();
        $images = $request->images;
        //if ($request->hasFile('images')) {
        foreach ($images as $item){
            echo "k";
            $image = new Image1();                      
            $imageName = (String)time().$item->getClientOriginalName(); 
            $item->move(public_path('groups'), $imageName);
            $image->url = $imageName;
            $image->event_id = $event->id;
            $image->save();
        }

        DB::commit(); 
        return response()->json([
            "status" => 1,
            "message" => "Event added successfully"
        ]);      
    } catch (\Throwable $th) {
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

    public function listEvent(){
        $events = Event::get();

        return response()->json([
            "status" => 1,
            "message" => "Listing Events: ",
            "data" => $events
        ],200);
    }

    public function getSingleEvent($id){
        if(Event::where("id", $id)->exists()){
           
            $event_details = Event::where("id", $id)->first();
            return response()->json([
                "status" => 1,
                "message" => "Event found ",
                "data" => $event_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Event not found"
            ],404);
        }
    }

    public function updateEvent(Request $request, $id){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
        if(Event::where("id", $id)->exists()){
           
            $event = Event::find($id);

            $event->ar_name = !empty($request->ar_name)? $request->ar_name : $event->ar_name;
            $event->en_name = !empty($request->en_name)? $request->en_name : $event->en_name;
            $event->city_id = !empty($request->city_id)? $request->city_id : $event->city_id;
            $event->start_date = !empty($request->start_date)? $request->start_date : $event->start_date;
            $event->end_date = !empty($request->end_date)? $request->end_date : $event->end_date;
            $event->ar_description = !empty($request->ar_description)? $request->ar_description : $event->ar_description;
            $event->en_description = !empty($request->en_description)? $request->en_description : $event->en_description;
            $event->open_time = !empty($request->open_time)? $request->open_time : $event->open_time;
            $event->close_time = !empty($request->close_time)? $request->close_time : $event->close_time;
            $event->ar_location = !empty($request->ar_location)? $request->ar_location : $event->ar_location;
            $event->en_location = !empty($request->en_location)? $request->en_location : $event->en_location;
            $event->map_location = !empty($request->map_location)? $request->map_location : $event->map_location;

            $event->save();

            return response()->json([
                "status" => 1,
                "message" => "Event updated successfully "
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Event not found"
            ],404);
        }
        }
        else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }
    }

    public function deleteEvent($id){
        $user_id = auth()->user()->id;
    
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){

        if(Event::where("id", $id)->exists()){
            DB::beginTransaction();  

            $event = event::find($id);
            $images_ids = Image1::where('event_id',$id)->get('id');
            echo "start";

            if(!$event || !$images_ids){
                echo "null";
                DB::rollback();
                return response()->json([
                    "status" => 0,
                    "message" => "Event not found"
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
                $event->delete();
                DB::commit();
                return response()->json([
                    "status" => 1,
                    "message" => "Event deleted successfully "
                ],200);
            }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Event not found"
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
        if(Event::where("ar_name", "like", "%".$request->name."%")->orWhere("en_name", "like", "%".$request->name."%")->exists()){
           
            $event_details = Event::where("ar_name", "like", "%".$request->name."%")->orWhere("en_name", "like", "%".$request->name."%")->get();
            return response()->json([
                "status" => 1,
                "message" => "Event found ",
                "data" => $event_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Event not found"
            ],404);
        }

    
    }

    public function searchByCity(Request $request){

        $request->validate([
            "name" => "required",
           
        ]);

        if( Event::withWhereHas('city', fn  ($query) => 
        $query->where('ar_name', 'like',  "%".$request->name."%")->orWhere('en_name', 'like', "%".$request->name."%")
           )->exists()){
           
            $event_details = Event::withWhereHas('city', fn  ($query) => 
            $query->where('ar_name', 'like',  "%".$request->name."%")->orWhere('en_name', 'like', "%".$request->name."%")
               )->get();
            return response()->json([
                "status" => 1,
                "message" => "Event found ",
                "data" => $event_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Event not found"
            ],404);
        }
    }

}
