<?php

namespace App\Http\Controllers\Api;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Rate;
use App\Models\Image1;
use App\Models\Place;
use App\Models\Event;
use App\Models\Tourist;
use App\Models\Landmark;
use App\Models\Favorite;
use DateTime;
use Phpml\Math\Distance\Euclidean;
// use MathPHP\LinearALgebra\Vector;



class PlanController extends Controller
{
    public function suggestPlan(Request $request){
        $request->validate([
            "city_id" => "required",
            "type_id" => "required",
            "category_id1" => "nullable",
            "category_id2" => "nullable",
            "category_id3" => "nullable",
            "start_time" => "required",
            "end_time" => "required",
            "start_date" => "required",
            "stars" => "required",

        ]);
        $finalPlan = Array();
        $date1=Carbon::parse($request->start_date);
        // $date2=Carbon::parse($request->end_date);
        $date3=Carbon::today();
        // echo $date3;
        // $interval = $date1->diff($date2);
        // $days = $interval->format('%a');
        // echo $days;

        if($date1->lt($date3)){
                return response()->json([
                    "status" => 1,
                    "message" => "Invalid date value!"
                ]); 
            }
        
            
        if($request->start_time>=$request->end_time){
            return response()->json([
            "status" => 1,
            "message" => "Invalid time value!"
        ]); }

        if($request->category_id1!=Null){
            
            $r['city_id'] = $request->city_id;
            $r['type_id'] = $request->type_id;
            $r['category_id'] = $request->category_id;
            $r['stars'] = $request->stars;
            $finalPlan['meal 1'] = $this->suggestPlace($r);
        }
        if($request->category_id2!=Null){
            
            $r['city_id'] = $request->city_id;
            $r['type_id'] = $request->type_id;
            $r['category_id'] = $request->category_id;
            $r['stars'] = $request->stars;
            $finalPlan['meal 2'] = $this->suggestPlace($r);
        }
        if($request->category_id3!=Null){
            // $r=Array();
            $r['city_id'] = $request->city_id;
            $r['type_id'] = $request->type_id;
            $r['category_id'] = $request->category_id;
            $r['stars'] = $request->stars;
            $finalPlan['meal 3'] = $this->suggestPlace($r);
        }
        $r['city_id'] = $request->city_id;
        $r['type_id'] = $request->type_id;
        $r['category_id'] = $request->category_id;
        $r['stars'] = $request->stars;
        $r['start_date'] = $request->start_date;
        $r['start_time'] = $request->start_time;
        $r['end_time'] = $request->end_time;
        
        $finalPlan['landmarks'] = $this->suggestLandmark($r);
        $finalPlan['events'] = $this->suggestEvent($r);
        $finalPlan['hotels'] = $this->suggestHotel($r);

        return response()->json([
            "status" => 1,
            "message" => "event suggesstions:2!",
            "data" =>  $finalPlan
        ]); 

    }
    public function createPlan(Request $request){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        $request->validate([
            "name" => "required",
            "city_id" => "required",
            "breakfast_id" => "nullable",
            "lunch_id" => "nullable",
            "dinner_id" => "nullable",
            "landmark_id" => "nullable",
            "event_id" => "nullable",
            "type_id" => "required",
            "start_time" => "required",
            "end_time" => "required",
            "start_date" => "required",
            "hotel_id" => "nullable",
            "stars" => "required",

        ]);
        // $tourist_id = auth()->user()->id;
        $plan = new Plan();

        $plan->tourist_id = $user_id;
        $plan->name = $request->name;
        $plan->city_id = $request->city_id;
        $plan->breakfast_id = $request->breakfast_id;
        $plan->lunch_id = $request->lunch_id;
        $plan->dinner_id = $request->dinner_id;
        $plan->landmark_id = $request->landmark_id;
        $plan->event_id = $request->event_id;
        $plan->type_id = $request->type_id;
        $plan->start_time = $request->start_time;
        $plan->end_time = $request->end_time;
        $plan->start_date = $request->start_date;
        $plan->hotel_id = $request->hotel_id;
        $plan->stars = $request->stars;

        $plan->save();

        return response()->json([
            "status" => 1,
            "message" => "Plan added successfully"
        ]);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function suggestPlace($r){
    $user_id = auth()->user()->id;
    $plan = Array($r['stars'],0,0,0,0,0,0,0,0,0,0);
    
    if($r['category_id'] == 1){
        $plan[0]=1;
    }
    if($r['category_id'] == 2){
        $plan[1]=1;
    }
    if($r['category_id'] == 3){
        $plan[2]=1;
        $plan[0]=0.5;
    }
    if($r['category_id'] == 4){
        $plan[3]=1;
        $plan[1]=0.5;
    }
    if($r['type_id'] == 2){
        $plan[8]=1;
    }
    if($r['type_id'] == 1){
        $plan[9]=1;
    }
    $euclidean = new Euclidean();
    $place_list =Array();
    $result =[];
    $places = Place::with('city','category')->whereIn('category_id',[1,2,3,4,9,10])->where('city_id', $r['city_id'])->get();
    foreach($places as $p){
        $rate= Rate::where('tourist_id',$user_id)->where('place_id',$p->id)->first();

        if($rate==Null ||  $rate->rate > 2 ){
            $restaurant = Array(
                $p->stars,0,0,0,0,0,0,0,0,0,0);
            if($p->category_id == 1){
                $restaurant[1]=1;
            }
            if($p->category_id == 2){
                $restaurant[2]=1;
            }
            if($p->category_id == 3){
                $restaurant[3]=1;
                // $restaurant[1]=0.5;
            }
            if($p->category_id == 4){
                $restaurant[4]=1;
                // $restaurant[2]=0.5;
            }
            // if($p->category_id == 5){
            //     $restaurant[5]=1;
            // }
            // if($p->category_id == 6){
            //     $restaurant[6]=1;
            // }
            // if($p->category_id == 7){
            //     $restaurant[7]=1;
            // }
            // if($p->category_id == 8){
            //     $restaurant[8]=1;
            // }
            if($p->category_id == 9){
                $restaurant[9]=1;
            }
            if($p->category_id == 10){
                $restaurant[10]=1;
            }
            
            $place_list[$p->id] =  $restaurant;
            $a= array('sim'=> $euclidean->distance($plan, $restaurant),'place'=>$p);
            array_push($result,$a);
     
        }
        
   
    }
    
    $key_values = array_column($result, 'sim'); 
    array_multisort($key_values, SORT_ASC, $result);
    $array=array_column($result,'place');
    foreach($array as $key => $value){
        if($value->city_id !=$r['city_id']){
            unset($array[$key]);
        }
        $images = Image1::where("place_id",$value->id)->get();
        $value->images = $images;
    }
    
    return array_values($array);

    }
    
    
    public function suggestLandmark($request){
        
        $date1=Carbon::parse($request['start_date']);
        $date3=Carbon::today();
        
        $date11 = Carbon::createFromFormat('Y-m-d',$request['start_date']);
        $date = strtolower($date11->format('l'));
        // echo $date;
        $suggestion = Landmark::with('days_off','city','category','type')->where('city_id',$request['city_id'])->get();
        
        $suggestion =  $suggestion->where('open_time','<=',$request['start_time'])->where('close_time','<=',$request['end_time']);
        foreach ($suggestion as $key => $value) {
            if ($value->days_off->en_name == $date) {
                $suggestion->forget($key);
            }
            $images = Image1::where("landmark_id",$value->id)->get();
            $value->images = $images;
        }
        
        return $suggestion;
        
    
    }


    public function suggestEvent($request){

         $date1=Carbon::parse($request['start_date']);
        
        $date3=Carbon::today();
        // echo $request['start_time'];
        // echo $request['end_time'];
    
        $date = Carbon::parse($date1);
        
        $suggestion = Event::with('city')
        ->where('city_id',$request['city_id'])
        ->where('end_date','>=',$date)
        ->where('start_date','<=',$date)
        ->where(function ($query)use($request){
            $query->where('open_time','>=',$request['start_time'])
            ->where('close_time','<=',$request['end_time']);
          })
        ->orWhere(function ($query)use($request){
            $query->where('open_time','>=',$request['start_time'])
            ->where('open_time','<=',$request['end_time'])
            ->where('close_time','>=',$request['end_time']);
          })
        ->orWhere(function ($query)use($request){
            $query->where('close_time','<=',$request['end_time'])
            ->where('open_time','<=',$request['start_time'])
            ->where('close_time','>=',$request['start_time']);
            })
        ->orWhere(function ($query)use($request){
            $query->where('open_time','<=',$request['start_time'])
            ->where('close_time','>=',$request['end_time']);
        })
        ->get();
        foreach ($suggestion as $event){
            $images = Image1::where("event_id",$event->id)->get();
            $event->images = $images;
            }
        
    return $suggestion;

        
    }

    public function getMeals(Request $request){
        $request->validate([
            "city_id" => "required",
            "start_time" => "required",
            "end_time" => "required",
            "start_date" => "required",
            
        ]);
        $date1=Carbon::parse($request->start_date);
        
        $date3=Carbon::today();
       

        if($date1->lt($date3)){
                return response()->json([
                    "status" => 1,
                    "message" => "Invalid date value!"
                ]); 
            }
        
            
            if($request->start_time>=$request->end_time){
            return response()->json([
            "status" => 1,
            "message" => "Invalid time value!"
        ]); 
    
    }
    
    
        $time1=Carbon::parse($request->start_time);
        $time2=Carbon::parse($request->end_time);
       
        $diff =$time1->diffInHours($time2);
        echo $diff ;
        if($diff>=10){
            $meals=3;
        }elseif($diff>=6){
            $meals=2;
        }else $meals=1;
    
    
    return response()->json([
        "status" => 1,
        "message" => "meals:!",
        "data" => $meals
    ]); 
        
    }

    public function suggestHotel($request){
        $user_id = auth()->user()->id;


        $date1=Carbon::parse($request['start_date']);
        
        $date3=Carbon::today();
    
        $hotels = Place::with('city','category')
        ->where('category_id',5)
        ->where('city_id',$request['city_id'])
        ->where('stars',$request['stars'])->get();
        $list = [];
        foreach($hotels as $value){
            $rate= Rate::where('tourist_id',$user_id)->where('place_id',$value->id)->first();
            if($rate==Null ||  $rate->rate > 2 ){
                $images = Image1::where("place_id",$value->id)->get();
                $value->images = $images;
                array_push($list,$value);
            }
        }

    return $list;
        
    }


    public function listPlan(){
        $tourist_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $tourist_id,
        ] )->exists()){

        $plans = Plan::with('landmark','breakfast','lunch','dinner','hotel','city','event','type')->where("tourist_id", $tourist_id)->get();
        foreach ($plans as $plan){
            // foreach($suggestion as $s){
                if($plan->landmark != NULL){
                    $images = Image1::where("landmark_id",$plan->landmark_id)->get();
                    $plan->landmark->images = $images;
                }
                if($plan->breakfast != NULL){
                    $images = Image1::where("place_id",$plan->breakfast_id)->get();
                    $plan->breakfast->images = $images;
                }

                if($plan->lunch != NULL){
                    $images = Image1::where("place_id",$plan->lunch_id)->get();
                    $plan->lunch->images = $images;
                }
                if($plan->dinner != NULL){
                    $images = Image1::where("place_id",$plan->dinner_id)->get();
                    $plan->dinner->images = $images;
                }
                if($plan->hotel != NULL){
                    $images = Image1::where("place_id",$plan->hotel_id)->get();
                    $plan->hotel->images = $images;
                }
                if($plan->event != NULL){
                    $images = Image1::where("event_id",$plan->event_id)->get();
                    $plan->event->images = $images;
                }
            // }
           
            }

        return response()->json([
            "status" => 1,
            "message" => "Listing Plans: ",
            "data" => $plans
        ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }



    ////////////////


    
    public function getSinglePlan($id){
        $tourist_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $tourist_id,
        ] )->exists()){

        if(Plan::where([
            "id"=> $id,
            "tourist_id" => $tourist_id
            ])->exists()){
           
            $plan = Plan::with('landmark','breakfast','lunch','dinner','hotel','city','event','type')->
            where([
                "id"=> $id,
                "tourist_id" => $tourist_id
                ]
            )->first();
            if($plan->landmark != NULL){
                $images = Image1::where("landmark_id",$plan->landmark_id)->get();
                $plan->landmark->images = $images;
            }
            if($plan->breakfast != NULL){
                $images = Image1::where("place_id",$plan->breakfast_id)->get();
                $plan->breakfast->images = $images;
            }

            if($plan->lunch != NULL){
                $images = Image1::where("place_id",$plan->lunch_id)->get();
                $plan->lunch->images = $images;
            }
            if($plan->dinner != NULL){
                $images = Image1::where("place_id",$plan->dinner_id)->get();
                $plan->dinner->images = $images;
            }
            if($plan->hotel != NULL){
                $images = Image1::where("place_id",$plan->hotel_id)->get();
                $plan->hotel->images = $images;
            }
            if($plan->event != NULL){
                $images = Image1::where("event_id",$plan->event_id)->get();
                $plan->event->images = $images;
            }
            return response()->json([
                "status" => 1,
                "message" => "Plan found ",
                "data" => $plan
            ],200);
            }else{
                return response()->json([
                    "status" => 0,
                    "message" => "Plan not found"
                ],404);
            }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function updatePlan(Request $request, $id){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        if(Plan::where([
            "id"=> $id,
            "tourist_id" => $user_id
            ])->exists()){
           
            $plan = Plan::find($id);

            $plan->name = !empty($request->name)? $request->name : $plan->name;
            $plan->city_id = !empty($request->city_id)? $request->city_id : $plan->city_id;
            $plan->breakfast_id = !empty($request->breakfast_id)? $request->breakfast_id : $plan->breakfast_id;
            $plan->lunch_id = !empty($request->lunch_id)? $request->lunch_id : $plan->lunch_id;
            $plan->dinner_id = !empty($request->dinner_id)? $request->dinner_id : $plan->dinner_id;
            $plan->event_id = !empty($request->event_id)? $request->event_id : $plan->event_id;
            $plan->landmark_id = !empty($request->landmark_id)? $request->landmark_id : $plan->landmark_id;
            $plan->type_id = !empty($request->type_id)? $request->type_id : $plan->type_id;
            $plan->start_time = !empty($request->start_time)? $request->start_time : $plan->start_time;
            $plan->end_time = !empty($request->end_time)? $request->end_time : $plan->end_time;
            $plan->start_date = !empty($request->start_date)? $request->start_date : $plan->start_date;
            $plan->hotel_id = !empty($request->hotel_id)? $request->hotel_id : $plan->hotel_id;
            $plan->stars = !empty($request->stars)? $request->stars : $plan->stars;

            $plan->save();

            return response()->json([
                "status" => 1,
                "message" => "Plan updated successfully "
            ],200);
            }else{
                return response()->json([
                    "status" => 0,
                    "message" => "Plan not found"
                ],404);
            }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function deletePlan($id){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        if(Plan::where([
            "id"=> $id,
            "tourist_id" => $user_id
        ])->exists()){
           
            $plan = Plan::find($id);

            $plan->delete();

            return response()->json([
                "status" => 1,
                "message" => "Plan deleted successfully "
            ],200);
            }else{
                return response()->json([
                    "status" => 0,
                    "message" => "Plan not found"
                ],404);
            }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function searchByName($search){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
       
        if(Plan::where("name", "like", "%".$search."%"
        )->where("tourist_id",$user_id)
        ->exists()){
           
            $plans = Plan::where("name", "like", "%".$search."%")
            ->where("tourist_id",$user_id)
            ->get();
            foreach ($plans as $plan){
                // foreach($suggestion as $s){
                    if($plan->landmark != NULL){
                        $images = Image1::where("landmark_id",$plan->landmark_id)->get();
                        $plan->landmark->images = $images;
                    }
                    if($plan->breakfast != NULL){
                        $images = Image1::where("place_id",$plan->breakfast_id)->get();
                        $plan->breakfast->images = $images;
                    }
    
                    if($plan->lunch != NULL){
                        $images = Image1::where("place_id",$plan->lunch_id)->get();
                        $plan->lunch->images = $images;
                    }
                    if($plan->dinner != NULL){
                        $images = Image1::where("place_id",$plan->dinner_id)->get();
                        $plan->dinner->images = $images;
                    }
                    if($plan->hotel != NULL){
                        $images = Image1::where("place_id",$plan->hotel_id)->get();
                        $plan->hotel->images = $images;
                    }
                    if($plan->event != NULL){
                        $images = Image1::where("event_id",$plan->event_id)->get();
                        $plan->event->images = $images;
                    }
                // }
               
                }
            return response()->json([
                "status" => 1,
                "message" => "Plan found ",
                "data" => $plans
            ],200);
            }else{
                return response()->json([
                    "status" => 0,
                    "message" => "Plan not found"
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
        if( Plan::where('tourist_id', '=',  $user_id)->withWhereHas('city', fn  ($query) => 
        $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
           )->exists()){
           
            $plans = Plan::where('tourist_id', '=',  $user_id)->withWhereHas('city', fn  ($query) => 
            $query->where('ar_name', 'like',  "%".$search."%")->orWhere('en_name', 'like', "%".$search."%")
               )->get();
               foreach ($plans as $plan){
                // foreach($suggestion as $s){
                    if($plan->landmark != NULL){
                        $images = Image1::where("landmark_id",$plan->landmark_id)->get();
                        $plan->landmark->images = $images;
                    }
                    if($plan->breakfast != NULL){
                        $images = Image1::where("place_id",$plan->breakfast_id)->get();
                        $plan->breakfast->images = $images;
                    }
    
                    if($plan->lunch != NULL){
                        $images = Image1::where("place_id",$plan->lunch_id)->get();
                        $plan->lunch->images = $images;
                    }
                    if($plan->dinner != NULL){
                        $images = Image1::where("place_id",$plan->dinner_id)->get();
                        $plan->dinner->images = $images;
                    }
                    if($plan->hotel != NULL){
                        $images = Image1::where("place_id",$plan->hotel_id)->get();
                        $plan->hotel->images = $images;
                    }
                    if($plan->event != NULL){
                        $images = Image1::where("event_id",$plan->event_id)->get();
                        $plan->event->images = $images;
                    }
                // }
               
                }
            return response()->json([
                "status" => 1,
                "message" => "Plan found ",
                "data" => $plans
            ],200);
            }else{
                return response()->json([
                    "status" => 0,
                    "message" => "Plan not found"
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
