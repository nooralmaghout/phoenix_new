<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rate;
use App\Models\Tourist;
use App\Models\Place;
use Illuminate\Support\Facades\DB;


class RateController extends Controller
{
    public function createRate(Request $request){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        $request->validate([
            "place_id" => "required",
            "rate" => "required",
            "review" => "required"
        ]);

        $rate = new Rate();

        $rate->tourist_id = $user_id;
        $rate->place_id = $request->place_id;
        $rate->rate = $request->rate;
        $rate->review = $request->review;

        $rate->save();

        return response()->json([
            "status" => 1,
            "message" => "Rate added successfully"
        ]);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function listRate($place_id){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        if(Rate::where("place_id", $place_id)->exists()){
           
        $rates = Rate::where("place_id", $place_id)->get();

        foreach($rates as $rate){
            $tourist = Tourist::where("id",$rate->tourist_id)->first();
            $rate->tourist_id = $tourist->name;
        }
        return response()->json([
            "status" => 1,
            "message" => "Listing Rates: ",
            "data" => $rates
        ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "no available rates!"
            ],404); 
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function getSingleRate($id){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        if(Rate::where("id", $id)->exists()){
           
            $rate_details = Rate::where("id", $id)->first();
            $place = Place::where("id", $rate_details->place_id)->first();
            $tourist = Tourist::where("id", $rate_details->touist_id)->first();
            $rate_details->place_id = $place;
            $rate_details->tourist_id = $tourist;
            return response()->json([
                "status" => 1,
                "message" => "Rate found ",
                "data" => $rate_details
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Rate not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function updateRate(Request $request, $id){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        if(Rate::where(["id"=> $id,"tourist_id"=>$user_id])->exists()){
           
            $rate = Rate::find($id);

            $rate->rate = !empty($request->rate)? $request->rate : $rate->rate;
            $rate->review = !empty($request->review)? $request->review : $rate->review;

            $rate->save();

            if (!empty($request->rate)){
                echo "not";
                $avg = DB::table('places')->select('places.id',
                DB::raw('AVG(rates.rate) AS rate_avg'))
                ->join('rates', 'places.id', '=', 'rates.place_id')
                ->groupBy('places.id',
                )->orderBy('rate_avg', 'desc')
                ->get();
                //    echo $avg;
                $average = $avg->where('id',$rate->place_id)->first();
                //    echo $average->rate_avg;
                DB::table('places')
                        ->where('id', $rate->place_id)
                        ->update(['avg_rate' => $average->rate_avg]);

            }

            return response()->json([
                "status" => 1,
                "message" => "Rate updated successfully "
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Rate not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    public function deleteRate($id){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        if(Rate::where(["id"=> $id,"tourist_id"=>$user_id])->exists()){
           
            $rate = Rate::find($id);

            $rate->delete();

            return response()->json([
                "status" => 1,
                "message" => "Rate deleted successfully "
            ],200);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Rate not found"
            ],404);
        }
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    
    }


    public function rate(Request $request){
        $user_id = auth()->user()->id;
    
        if(Tourist::where([
            "id" => $user_id,
        ] )->exists()){
        $request->validate([
            "place_id" => "required",
            "rate" => "required",
            "review" => "required"
        ]);
        
      
        $rate = new Rate();

        $rate->tourist_id = $user_id;
        $rate->place_id = $request->place_id;
        $rate->rate = $request->rate;
        $rate->review = $request->review;

        $rate->save();
        $avg = DB::table('places')->select('places.id',
        DB::raw('AVG(rates.rate) AS rate_avg'))
       ->join('rates', 'places.id', '=', 'rates.place_id')
       ->groupBy('places.id',
       )->orderBy('rate_avg', 'desc')
       ->get();
    //    echo $avg;
       $average = $avg->where('id',$request->place_id)->first();
    //    echo $average->rate_avg;
       DB::table('places')
            ->where('id', $request->place_id)
            ->update(['avg_rate' => $average->rate_avg]);
        return response()->json([
            "status" => 1,
            "message" => "Rate added successfully"
        ]);
        }else{
            return response()->json([
                "status" => 0,
                "message" => "You are not registered Tourist!"
            ],404);
        }
    }

    
}
