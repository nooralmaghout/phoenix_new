<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image1;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class ImageController extends Controller
{
    //
  //   public function store(Request $request)
  //   {
  //       //
  //       $request->validate([
  //           'image.*' => 'mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg',
  //       ]);
  //     if($file = $request->hasFile('image')) {
             
  //           $file = $request->file('image') ;
  //           $fileName = $file->getClientOriginalName() ;
  //           $destinationPath = public_path().'/images' ;
  //           $file->move($destinationPath,$fileName);
  //           return redirect('/uploadfile');
  //   }
  // }

      public function listPlaceImages($id){
          $images = Image1::where(['place_id' => $id] )->get();

          return response()->json([
              "status" => 1,
              "message" => "Listing Images: ",
              "data" => $images
          ],200);
      }
      public function listEventImages($id){
        $images = Image1::where(['event_id' => $id] )->get();

        return response()->json([
            "status" => 1,
            "message" => "Listing Images: ",
            "data" => $images
        ],200);
    }
    public function listLandmarkImages($id){
      $images = Image1::where(['landmark_id' => $id] )->get();

      return response()->json([
          "status" => 1,
          "message" => "Listing Images: ",
          "data" => $images
      ],200);
  }
      public function getSingleiImage($id){
          if(Image1::where("id", $id)->exists()){
             
              $image_details = Image1::where("id", $id)->first();
              return response()->json([
                  "status" => 1,
                  "message" => "Image found ",
                  "data" => $image_details
              ],200);
          }else{
              return response()->json([
                  "status" => 0,
                  "message" => "Image not found"
              ],404);
          }
      }
  
      public function deleteImage($id){
          if(Image1::where("id", $id)->exists()){
             
            $user_id = auth()->user()->id;
    
            if(Admin::where([
                "id" => $user_id,
            ] )->exists()){
    
            if(Image1::where("id", $id)->exists()){
               
              $image = Image1::find($id);
              //$image_name=Image1::find($id)->get('url');
                //code...
              $image->delete();
              $image_path = "groups/".$image_name;  // Value is not URL but directory file path
              //if(Image1::exists($image_path)) {
                //Image1::delete($image_path);
                echo "image found";
                //Storage::disk('groups')->delete($image_path);//1684141805Poster 28x40 in.jpeg
                return response()->json([
                  "status" => 1,
                  "message" => "Image deleted successfully "
              ],200);
           
          }else{
              return response()->json([
                  "status" => 0,
                  "message" => "Image not found"
              ],404);
          }
        }
        else{
          return response()->json([
            "status" => 0,
            "message" => "admin not found"
        ],404);
        }
      
      }

    }

      public function deleteImage1($id){
        if(Image1::where("id", $id)->exists()){
          $images=Image1::find($id)->get();
foreach($images as $image){
  $image_path = public_path("groups")."\\".$image->url;
  echo  $image_path;
  if (file_exists($image_path)) {
    echo " true";
    //Image1::delete($image_path);
    unlink(public_path("groups")."\\",$image_path);
          return response()->json([
            "status" => 1,
            "message" => "Image found"
        ],404);
}
          
}         

         
        }else{
            return response()->json([
                "status" => 0,
                "message" => "Image not found"
            ],404);
        }
    }
  }
  

