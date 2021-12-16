<?php

namespace App\Api\V1\Controllers\Common;

use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VimeoController extends Controller
{
    public static function createVideo(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'name' => 'required|string|max:500',
            'description' => 'required|string|max:500',
            'size' => 'required|int',
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.vimeo.com/me/videos',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "upload": {
                "approach": "tus",
                "size": '.$request->size.'
            },
            "name": "'.$request->name.'",
            "description": "'.$request->description.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Accept: application/vnd.vimeo.*+json;version=3.4',
            'Authorization: bearer '.env('VIMEO_TOKEN').'',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($http_status == 200){
            $lesson = lesson::find($request->lesson_id); 
            $lesson->video_url = $response;
            $lesson->save();
        }

        return $response;
    }

    public static function createVimeoVideo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:500',
            'description' => 'required|string|max:500',
            'size' => 'required|int',
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.vimeo.com/me/videos',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "upload": {
                "approach": "tus",
                "size": '.$request->size.'
            },
            "name": "'.$request->name.'",
            "description": "'.$request->description.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Accept: application/vnd.vimeo.*+json;version=3.4',
            'Authorization: bearer '.env('VIMEO_TOKEN').'',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $response;
    }
}
