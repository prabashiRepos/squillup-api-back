<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\Year;
use App\Models\User;
use App\Models\Subject;
use App\Models\KeyStage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Api\V1\Controllers\Common\FilesController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubjectController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-subject', ['only' => ['store']]);
        $this->middleware('ability:developer,view-subject', ['only' => ['index']]);
        $this->middleware('ability:developer,update-subject', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-subject', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name',
            'logo' => 'sometimes|required|validBase64Image',
            'color' => 'sometimes|required|max:200',
        ]);

        $subject = new Subject($request->all());

        if(value($request->logo)) {
            $path = 'public/images/subjects/';
            $subject->logo = FilesController::saveBase64Images($request->logo, $path);
        }

        if($subject->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $subject,
                'status' => Lang::get('messages.subject_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.subject_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $subject = Subject::get();
        return response()->json($subject, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:subjects,id',
            'name' => 'required|unique:subjects,name,'.$request->id,
            'logo' => 'sometimes|required|validBase64Image',
            'color' => 'sometimes|required|max:200',
        ]);

        $subject = Subject::find($request->id); 
        $subject->fill($request->all());

        if(value($request->logo)) {
            $path = 'public/images/subjects/';
            $subject->logo = FilesController::saveBase64Images($request->logo, $path);
        }
        
        if($subject->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $subject,
                'status' => Lang::get('messages.subject_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.subject_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $subject = Subject::find($request->id); 
        if(!$subject) throw new NotFoundHttpException(Lang::get('messages.subject_not_found')); 

        if($subject->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.subject_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.subject_delete_fail')], 200);
    }
}
