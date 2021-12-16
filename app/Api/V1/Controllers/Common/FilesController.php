<?php

namespace App\Api\V1\Controllers\Common;

use Auth;
use File;
use Config;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;


class FilesController extends Controller
{
    public static function saveImages($image, $path)
    {
        $file = $image->getClientOriginalName();
        $filename = pathinfo($file, PATHINFO_FILENAME);
    	$extension = $image->getClientOriginalExtension();
        
        $imageName = 'img_'.$filename.'_'.rand(11111, 99999).''.time().''.rand(11111, 99999).'.'.$extension;
        $image->move(storage_path().$path, $imageName);
        return $imageName;
    }

    public static function saveBase64Images($image, $path)
    {
        $extension = 'png';
        $imageName = 'img'.rand(11111, 99999).''.time().''.rand(11111, 99999).'.'.$extension;
        list($type, $image) = explode(';', $image);
        list(, $image)      = explode(',', $image);

        return (Storage::disk('local')->put($path.$imageName, base64_decode($image), 'public')) ?  $imageName : null;
    }

    public static function saveImagesFromUrl($image, $path)
    {   
        $imageName = null;

        if (strpos($image, asset('')) !== false) {
            $imageName = str_replace(asset(''), "/", $image);
            $imageName = basename($imageName);
        }
        else{
            $headers = get_headers($image);
            if(! strpos($headers[0],'200')===false){
                $file = file_get_contents($image);
                $extension = pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION);
                $imageName = 'img'.rand(11111, 99999).''.time().''.rand(11111, 99999).'.'.$extension;

                File::makeDirectory(storage_path().$path, $mode = 0777, true, true);
                file_put_contents(storage_path().$path.$imageName, $file);
            }
        }

        return $imageName;
    }

    public static function deleteImagesFromUrl($image)
    {   
        if (strpos($image, asset('')) !== false) {
            $imageName = str_replace(asset(''), "/", $image);

            if(Storage::exists(str_replace("/storage", "", $imageName))){
                $deleteRes = Storage::delete(str_replace("/storage", "", $imageName));
                return 'deleted';
            } 
            else return "not_exists";
        }
        else return "not_exists_in_this_server";
    }

    public static function deleteImages($path)
    {
        if(Storage::exists(str_replace("/storage", "", $path))){
            $deleteRes = Storage::delete(str_replace("/storage", "", $path));
            return $deleteRes;
        } 
        else return "not_exists";
    }

    public static function saveGalleryImages($images, $clubId)
    {
        $folderName = sprintf('%05d', $clubId);
        $path = '/images/clubs/' . $folderName;
        File::makeDirectory($path, $mode = 0777, true, true);

        $imageNames = array(); 
        foreach( $images as $key => $image ) {
            if(value($image)){
                $extension = $image->getClientOriginalExtension();
                $imageName = 'img'.rand(11111, 99999).''.time().''.rand(11111, 99999).'.'.$extension;
                if($image->move(public_path().$path, $imageName)){
                    array_push($imageNames, $imageName);
                }
            }
        }
        return $imageNames;
    }

    public static function saveGalleryVideo($videos, $clubId)
    {
        $folderName = sprintf('%05d', $clubId);
        $path = '/images/clubs/' . $folderName;
        File::makeDirectory($path, $mode = 0777, true, true);

        $videoNames = array(); 
        foreach( $videos as $key => $video ) {
            $extension = $video->getClientOriginalExtension();
            $videoName = 'vid'.rand(11111, 99999).''.time().''.rand(11111, 99999).'.'.$extension;
            if($video->move(public_path().$path, $videoName)){
                array_push($videoNames, $videoName);
            }
        }
        return $videoNames;
    }
}
