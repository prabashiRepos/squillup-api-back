<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\User;
use App\Models\KeyStage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KeyStageController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-keystage', ['only' => ['store']]);
        $this->middleware('ability:developer,view-keystage', ['only' => ['index']]);
        $this->middleware('ability:developer,update-keystage', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-keystage', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:key_stages,name|max:200',
            'display_name' => 'nullable|max:200',
            'description' => 'nullable|max:200',
        ]);

        $keyStage = new KeyStage($request->all());

        if($keyStage->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $keyStage,
                'status' => Lang::get('messages.keystage_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.keystage_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $keyStages = KeyStage::get();
        return response()->json($keyStages, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:key_stages,id',
            'name' => 'required|max:200|unique:key_stages,name,'.$request->id,
            'display_name' => 'nullable|max:200',
            'description' => 'nullable|max:200',
        ]);

        $keyStage = KeyStage::find($request->id); 
        $keyStage->fill($request->all());

        if($keyStage->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $keyStage,
                'status' => Lang::get('messages.keystage_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.keystage_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $keyStage = KeyStage::find($request->id); 
        if(!$keyStage) throw new NotFoundHttpException(Lang::get('messages.keystage_not_found')); 

        if($keyStage->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.keystage_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.keystage_delete_fail')], 200);
    }
}
