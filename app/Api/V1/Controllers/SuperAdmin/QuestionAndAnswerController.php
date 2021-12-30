<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\QA;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use App\Api\V1\Controllers\Common\FilesController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionAndAnswerController extends Controller
{
    /**
     * Create a new ModuleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-qa', ['only' => ['store']]);
        $this->middleware('ability:developer,view-qa', ['only' => ['index']]);
        $this->middleware('ability:developer,update-qa', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-qa', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::guard()->user();
        if($user->hasRole("student")) $request->merge(["user_id" =>  $user->id]);

        $request->validate([
            'user_id' => 'required|exists:users,id|max:200',
            'lesson_id' => 'required|exists:lessons,id|max:200',
            'parent_q_a_id' => 'sometimes|required|exists:q_a_s,id',
            'contant' => 'required|max:10000',
            'attachment' => 'sometimes|required|array',
            'attachment.*' => 'required|validBase64Image',
        ]);

        $qa =  new QA($request->all()) ;
        $lesson = lesson::find($request->lesson_id);
        $qa->chapter_id = $lesson->chapter_id;

        $attachmentNames = [];
        if(value($request->attachment)) {
            foreach ($request->attachment as $attachment) {
                $path = 'public/images/q_a/';
                $imageName = FilesController::saveBase64Images($attachment, $path);
                array_push($attachmentNames, $imageName);
            }
        }

        $qa->attachment = $attachmentNames;

        if($qa->save()){
            $qa = QA::with('user')
                            ->with('lesson', function($query){
                                $query->select('id', 'name','number', 'chapter_id');
                            })
                            ->with('chapter')
                            ->find($qa->id);

            return response()->json([
                'code' => 201,
                'data' => $qa,
                'status' => Lang::get('messages.qa_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.qa_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $user = Auth::guard()->user();
        $whereUserId = ($user->hasRole("superamdin")) ? 'user_id <> ""' : 'user_id = "'.$user->id.'"';

        $whereOrder = ($request->order == 'latest') ? 'desc' : 'asc';
        $perPage = (value($request->per_page)) ? $request->per_page : 10;
        $whereYearId = (value($request->year_id)) ? 'year_id = "'.$request->year_id.'"' : 'year_id <> ""';
        $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
        $whereExamBoardId = (value($request->exam_board_id)) ? 'exam_board_id = "'.$request->exam_board_id.'"' : 'exam_board_id <> ""';
        $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';


        $qas = QA::with('user')
                            ->with('lesson', function($query){
                                $query->select('id', 'name','number', 'chapter_id');
                            })
                            ->with('chapter')
                            ->whereHas('chapter', function($q)  use($whereYearId, $whereSubjectId, $whereExamBoardId){
                                $q->whereRaw($whereExamBoardId)->whereRaw($whereSubjectId)->whereRaw($whereYearId);
                            })
                            ->whereNull('parent_q_a_id')
                            ->whereRaw($whereUserId)
                            ->orderBy('id', $whereOrder)
                            ->paginate($perPage);

        return response()->json($qas, 201);
    }

    public function update(Request $request)
    {
        $user = Auth::guard()->user();
        if($user->hasRole("student")){
            $validateId = 'required|exists:q_a_s,id,user_id,'.$user->id;
            $request->merge(["user_id" =>  $user->id]);
        }
        else $validateId = 'required|exists:q_a_s,id';

        $request->validate([
            'id' => $validateId,
            'user_id' => 'required|exists:users,id|max:200',
            'lesson_id' => 'required|exists:lessons,id|max:200',
            'parent_q_a_id' => 'sometimes|required|exists:q_a_s,id',
            'contant' => 'required|max:10000',
            'attachment' => 'sometimes|required|array',
            'attachment.*' => 'required|validBase64Image',
        ]);

        $qa = QA::find($request->id);
        $qa->fill($request->all());
        $lesson = lesson::find($request->lesson_id);
        $qa->chapter_id = $lesson->chapter_id;

        $attachmentNames = [];
        if(value($request->attachment)) {
            foreach ($request->attachment as $attachment) {
                $path = 'public/images/q_a/';
                $imageName = FilesController::saveBase64Images($attachment, $path);
                array_push($attachmentNames, $imageName);
            }
        }

        $qa->attachment = $attachmentNames;

        if($qa->save()){
            $qa = QA::with('user')
                            ->with('lesson', function($query){
                                $query->select('id', 'name','number', 'chapter_id');
                            })
                            ->with('chapter')
                            ->find($qa->id);

            return response()->json([
                'code' => 201,
                'data' => $qa,
                'status' => Lang::get('messages.qa_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.qa_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $user = Auth::guard()->user();
        if($user->hasRole("student")){
            $validateId = 'required|exists:q_a_s,id,user_id,'.$user->id;
            $request->merge(["user_id" =>  $user->id]);
        }
        else $validateId = 'required|exists:q_a_s,id';

        $request->validate([ 'id' => $validateId]);

        $qa = QA::find($request->id);

        if($qa->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.qa_delete_success'),
            ], 201);
        }
        else throw new NotFoundHttpException(Lang::get('messages.qa_not_found'));
    }
}
