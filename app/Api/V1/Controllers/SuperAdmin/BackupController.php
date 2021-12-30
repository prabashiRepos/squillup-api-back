<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $files = $disk->files(config('backup.backup.name'));
        $backups = [];
        foreach ($files as $k => $f) {
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
              $backups[] = [
                'file_path' => $f,
                'file_name' => str_replace(config('backup.backup.name') . '/', '', $f),
                'file_size' => $disk->size($f),
                'last_modified' => $disk->lastModified($f),
              ];
           }
        }

        return response()->json($backups, 201);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if ($request->storage_type == 'local') {
                Artisan::call('backup:run --only-to-disk=backup');

                return response()->json([
                    'code'   => 201,
                    'data'   => Artisan::output(),
                    // 'status' => Lang::get('Successfully created backup!'),
                ], 201);

            } elseif ($request->storage_type == 'dropbox') {

                Artisan::call('backup:run --only-to-disk=dropbox');

                return response()->json([
                    'code'   => 201,
                    'data'   => Artisan::output(),
                    // 'status' => Lang::get('Successfully created backup!'),
                ], 201);

            } else {
                Artisan::call('backup:run');
            }
        } catch (Exception $e) {
            return response()->json(['code' => 200, 'status' => Lang::get($e->getMessage())], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function download($file_name)
    {
       $file = config('backup.backup.name') . '/' . $file_name;

       $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

       if ($disk->exists($file)) {
          $fs = Storage::disk(config('backup.backup.destination.disks')[0])->getDriver();
          $stream = $fs->readStream($file);

          return \Response::stream(function () use ($stream) {
             fpassthru($stream);
          }, 200, [
            "Content-Type" => $fs->getMimetype($file),
            "Content-Length" => $fs->getSize($file),
            "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
          ]);
       } else {
          abort(404, "The backup file doesn't exist.");
       }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($file_name)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists(config('backup.backup.name') . '/' . $file_name)) {
           $disk->delete(config('backup.backup.name') . '/' . $file_name);
           return response()->json(['type' => 'success', 'message' => 'Successfully Deleted']);
        } else {
           abort(404, "The backup file doesn't exist.");
        }
    }
}
