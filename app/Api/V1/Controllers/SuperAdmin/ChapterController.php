<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\User;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChapterController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-chapter', ['only' => ['store']]);
        $this->middleware('ability:developer,view-chapter', ['only' => ['index']]);
        $this->middleware('ability:developer,update-chapter', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-chapter', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('chapters', 'name')
                                        ->where('number' , $request->number)
                                        ->where('subject_id' , $request->subject_id)
                                        ->where('year_id' , $request->year_id)
                                        ->where('exam_board_id' , $request->exam_board_id)],

            'number' => ['required', Rule::unique('chapters', 'number')
                                        ->where('name' , $request->name)
                                        ->where('subject_id' , $request->subject_id)
                                        ->where('year_id' , $request->year_id)
                                        ->where('exam_board_id' , $request->exam_board_id), 'integer'],

            'subject_id' => 'required|exists:subjects,id',
            'year_id' => 'required|exists:years,id',
            'exam_board_id' => 'required|exists:exam_boards,id',
        ]);

        $chapter = new Chapter($request->all());

        if($chapter->save()){
            $chapter = Chapter::with(['subject', 'year', 'year.key_stage', 'exam_board'])->find($chapter->id);
            
            return response()->json([
                'code'   => 201,
                'data'   => $chapter,
                'status' => Lang::get('messages.chapter_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.chapter_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $whereYearId = (value($request->year_id)) ? 'year_id = "'.$request->year_id.'"' : 'year_id <> ""';
        $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
        $whereExamBoardId = (value($request->exam_board_id)) ? 'exam_board_id = "'.$request->exam_board_id.'"' : 'exam_board_id <> ""';
        
        $chapter = Chapter::with(['subject', 'year', 'year.key_stage', 'exam_board'])
                            ->whereRaw($whereExamBoardId)
                            ->whereRaw($whereSubjectId)
                            ->whereRaw($whereYearId)
                            ->get();
        return response()->json($chapter, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:chapters,id',
            'name' => ['required', Rule::unique('chapters', 'name')
                                        ->ignore($request->id, 'id')
                                        ->where('number' , $request->number)
                                        ->where('subject_id' , $request->subject_id)
                                        ->where('year_id' , $request->year_id)
                                        ->where('exam_board_id' , $request->exam_board_id)],

            'number' => ['required', Rule::unique('chapters', 'number')
                                        ->ignore($request->id, 'id')
                                        ->where('name' , $request->name)
                                        ->where('subject_id' , $request->subject_id)
                                        ->where('year_id' , $request->year_id)
                                        ->where('exam_board_id' , $request->exam_board_id), 'integer'],

            'subject_id' => 'required|exists:subjects,id',
            'year_id' => 'required|exists:years,id',
            'exam_board_id' => 'required|exists:exam_boards,id',
        ]);

        $chapter = Chapter::find($request->id); 
        $chapter->fill($request->all());

        if($chapter->save()){
            $chapter = Chapter::with(['subject', 'year', 'year.key_stage', 'exam_board'])->find($request->id);

            return response()->json([
                'code'   => 201,
                'data'   => $chapter,
                'status' => Lang::get('messages.chapter_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.chapter_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $chapter = Chapter::find($request->id); 
        if(!$chapter) throw new NotFoundHttpException(Lang::get('messages.chapter_not_found')); 

        if($chapter->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.chapter_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.chapter_delete_fail')], 200);
    }
}
