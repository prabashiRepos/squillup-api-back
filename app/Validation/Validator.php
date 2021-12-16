<?php

namespace App\Validation;

use DB;
use Config;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{
    public function validateValidBase64Image($attribute, $value, $parameters, $validator) 
    {
        $base64data = $value;
        $allowedMime = ['png', 'jpg', 'jpeg'];

        if (strpos($base64data, ';base64') !== false) {
            list(, $base64data) = explode(';', $base64data);
            list(, $base64data) = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            return false;
        }

        // decoding and then reeconding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            return false;
        }

        $binaryData = base64_decode($base64data);

        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFile, $binaryData);

        // guard Against Invalid MimeType
        $allowedMime = Arr::flatten($allowedMime);
        // $allowedMime = array_flatten($allowedMime);

        // no allowedMimeTypes, then any type would be ok
        if (empty($allowedMime)) {
            return true;
        }

        // Check the MimeTypes
        $validation = \Illuminate\Support\Facades\Validator::make(
            ['file' => new \Illuminate\Http\File($tmpFile)],
            ['file' => 'mimes:' . implode(',', $allowedMime)]
        );

        return !$validation->fails();
    }

    public function validateValidBsssase64Image($attribute, $value, $parameters, $validator) {
        $base64data = $value;
        $allowedMime = ['png', 'jpg', 'jpeg'];

        if (strpos($base64data, ';base64') !== false) {
            list(, $base64data) = explode(';', $base64data);
            list(, $base64data) = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            return false;
        }

        // decoding and then reeconding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            return false;
        }

        $binaryData = base64_decode($base64data);

        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFile, $binaryData);

        // guard Against Invalid MimeType
        $allowedMime = array_flatten($allowedMime);

        // no allowedMimeTypes, then any type would be ok
        if (empty($allowedMime)) {
            return true;
        }

        // Check the MimeTypes
        $validation = \Illuminate\Support\Facades\Validator::make(
            ['file' => new \Illuminate\Http\File($tmpFile)],
            ['file' => 'mimes:' . implode(',', $allowedMime)]
        );

        return !$validation->fails();
    }
}