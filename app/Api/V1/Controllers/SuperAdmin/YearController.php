<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\Year;
use App\Models\User;
use App\Models\KeyStage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class YearController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-year', ['only' => ['store']]);
        $this->middleware('ability:developer,view-year', ['only' => ['index']]);
        $this->middleware('ability:developer,update-year', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-year', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|unique:years,year|integer',
            'name' => 'required|unique:years,name|max:200',
            'description' => 'nullable|max:200',
            'key_stage_id' => 'required|array',
            'key_stage_id.*' => 'required|exists:key_stages,id',
        ]);

        $year = new Year($request->all());

        if($year->save()){
            if(value($request->key_stage_id)){
                $deleteRes = DB::table('key_stages_years')->where('year_id', $year->id)->delete();
                foreach ($request->key_stage_id as $key => $keyStageId) {
                    DB::table('key_stages_years')->insert(['year_id' => $year->id, 'key_stage_id' => $keyStageId]);
                }
            }

            $year = Year::with('key_stage')->find($year->id);

            return response()->json([
                'code'   => 201,
                'data'   => $year,
                'status' => Lang::get('messages.year_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.year_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $years = Year::with('key_stage')->get();
        return response()->json($years, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:years,id',
            'year' => 'required|integer|unique:years,year,'.$request->id,
            'name' => 'required|max:200|unique:years,name,'.$request->id,
            'description' => 'nullable|max:200',
            'key_stage_id' => 'required|array',
            'key_stage_id.*' => 'required|exists:key_stages,id',
        ]);

        $year = Year::find($request->id); 
        $year->fill($request->all());

        if($year->save()){
            if(value($request->key_stage_id)){
                $deleteRes = DB::table('key_stages_years')->where('year_id', $year->id)->delete();
                foreach ($request->key_stage_id as $key => $keyStageId) {
                    DB::table('key_stages_years')->insert(['year_id' => $year->id, 'key_stage_id' => $keyStageId]);
                }
            }

            $year = Year::with('key_stage')->find($request->id);

            return response()->json([
                'code'   => 201,
                'data'   => $year,
                'status' => Lang::get('messages.year_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.year_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $year = Year::find($request->id); 
        if(!$year) throw new NotFoundHttpException(Lang::get('messages.year_not_found')); 

        if($year->delete()){
            $deleteRes = DB::table('key_stages_years')->where('year_id', $request->id)->delete();

            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.year_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.year_delete_fail')], 200);
    }
}
