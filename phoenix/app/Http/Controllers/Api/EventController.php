<?php

namespace App\Http\Controllers\Api;
use Carbon\Carbon;
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
    
    public function createEvent2(Request $request){
        $user_id = auth()->user()->id;
        
        if(Admin::where([
            "id" => $user_id,
        ] )->exists()){
            $request->validate([
                "ar_name" => "required",
                "en_name" => "required",
                "city_id" => "required",
                "start_date" => "required",
                "end_date" => "required",
                "ar_description" => "required",
                "en_description" => "required",
                "open_time" => "required",
                "close_time" => "required",
                "ar_location"=> "required",
                "en_location"=> "required",
                "map_x"=> "nullable",
                "map_y"=> "nullable",
                "images.*"=> "required|image|mimes:png,jpg,jpeg"
                ]);
        //         $date1=Carbon::parse($request->start_date)->format('Y-m-d');
        //         $date2=Carbon::parse($request->end_date)->format('Y-m-d');
        //         $date3=now();
        //         echo $request->start_date;
        //         echo $request->end_date;
        //         echo $date1;
        //         echo $date2;
        //         echo $date3;
                
        // if($date1->gt($date2)||$date1->lt($date3)){
           
        //     return response()->json([
        //         "status" => 1,
        //         "message" => "Invalid date value!"
        //     ]); 
        // }
        // if($request->start_date==$request->end_date&&$request->open_time==$request->close_time){
        //     return response()->json([
        //         "status" => 1,
        //         "message" => "Invalid time value!"
        //     ]); 
        // }
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
        $event->map_x = $request->map_x;
        $event->map_y = $request->map_y;
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
            $event->map_x = !empty($request->map_x)? $request->map_x : $event->map_x;
            $event->map_y = !empty($request->map_y)? $request->map_y : $event->map_y;

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

    public function searchByName($search){
        
        if(Event::where("ar_name", "like", "%".$search."%")->orWhere("en_name", "like", "%".$search."%")->exists()){
           
            $event_details = Event::where("ar_name", "like", "%".$search."%")->orWhere("en_name", "like", "%".$search."%")->get();
            foreach ($event_details as $event){
                $city = City::where("id",$event->city_id)->first();
                $images = Image1::where("event_id",$event->city_id)->get();
                $event->city_id = $city;
                $event->images = $images;
        
                }
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

    public function searchByCity($search){ 
        if( Event::withWhereHas('city', fn  ($query) => 
        $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
           )->exists()){
           
            $event_details = Event::withWhereHas('city', fn  ($query) => 
            $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
               )->get();
               foreach ($event_details as $event){
                // $city = City::where("id",$event->city_id)->first();
                $images = Image1::where("event_id",$event->city_id)->get();
                // $event->city_id = $city;
                $event->images = $images;
        
                }
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

    public function createEvent(Request $request){
        $user_id = auth()->user()->id;
        
        if(Admin::where([
            "id" => $user_id,
        ] )->exists())
        {
            $request->validate
            ([
                "ar_name" => "required",
                "en_name" => "required",
                "city_id" => "required",
                "start_date" => "required",
                "end_date" => "required",
                "ar_description" => "required",
                "en_description" => "required",
                "open_time" => "required",
                "close_time" => "required",
                "ar_location"=> "required",
                "en_location"=> "required",
                "map_x"=> "nullable",
                "map_y"=> "nullable",
                "images.*"=> "nullable|image|mimes:png,jpg,jpeg"
                ]);
// echo $request->images[0];
// echo "            ";
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
            $event->map_x = $request->map_x;
            $event->map_y = $request->map_y;
            $event->city_id = $request->city_id;

            //check entry
            $date1=Carbon::parse($request->start_date);
            $date2=Carbon::parse($request->end_date);
            $date3=now();
            // echo $event->start_date;
            // echo $event->end_date;
             echo $date1;
             echo $date2;
            // echo $date3;
                
            if($date1->gt($date2)||$date1->lt($date3)){
            echo "first";
                return response()->json([
                    "status" => 1,
                    "message" => "Invalid date value!"
                ]); 
            }
            if($date1->isSameDay($date2)&&$event->open_time>=$event->close_time){
                echo "second";
                return response()->json([
                    "status" => 1,
                    "message" => "Invalid time value!"
                ]); 
            }
            echo "else";
            //
            $event->save();
            $images = $request->images;
            if($images){
                echo "images";
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
            
        }
        else{
            return response()->json([
                "status" => 1,
                "message" => "You are not an Admin!"
            ]); 
        }

    }
    public function getSingleEvent2($id){
        if(Event::where("id", $id)->exists()){
           
            $event_details = Event::where("id", $id)->first();
            $city = City::where("id", $event_details->city_id)->first();
            $event_details->city_id = $city;
            $images = Image1::where("event_id", $id)->get();
            $event_details->images = $images;
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
    
    public function listEvent2(){
        $events = Event::get();

        foreach ($events as $event){
            $city = City::where("id",$event->city_id)->first();
            $images = Image1::where("event_id",$event->city_id)->get();
            $city = City::where("id",$event->city_id)->first();
            $event->city_id = $city;
            $event->images = $images;

        }
        return response()->json([
            "status" => 1,
            "message" => "Listing Events: ",
            "data" => $events
        ],200);
    }
}
