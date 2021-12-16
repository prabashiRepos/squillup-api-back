<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\User;
use App\Models\ExamBoard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Api\V1\Controllers\Common\FilesController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamBoardController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-examboard', ['only' => ['store']]);
        $this->middleware('ability:developer,view-examboard', ['only' => ['index']]);
        $this->middleware('ability:developer,update-examboard', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-examboard', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:exam_boards,name|max:200',
            'logo' => 'sometimes|required|validBase64Image',
            'description' => 'nullable|max:200',
        ]);

        $examBoard = new ExamBoard($request->all());

        if(value($request->logo)) {
            $path = 'public/images/exam_boards/';
            $examBoard->logo = FilesController::saveBase64Images($request->logo, $path);
        }        

        if($examBoard->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $examBoard,
                'status' => Lang::get('messages.examboard_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.examboard_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $examBoards = ExamBoard::get();
        return response()->json($examBoards, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:exam_boards,id',
            'name' => 'required|max:200|unique:exam_boards,name,'.$request->id,
            'logo' => 'sometimes|required|validBase64Image',
            'description' => 'nullable|max:200',
        ]);

        $examBoard = ExamBoard::find($request->id); 
        $examBoard->fill($request->all());

        if(value($request->logo)) {
            $path = 'public/images/exam_boards/';
            $examBoard->logo = FilesController::saveBase64Images($request->logo, $path);
        }

        if($examBoard->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $examBoard,
                'status' => Lang::get('messages.examboard_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.examboard_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $examBoard = ExamBoard::find($request->id); 
        if(!$examBoard) throw new NotFoundHttpException(Lang::get('messages.examboard_not_found')); 

        if($examBoard->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.examboard_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.examboard_delete_fail')], 200);
    }
}
