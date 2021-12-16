<?php

namespace App\Api\V1\Controllers\ContentWriter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Support\Facades\Lang;
use App\Api\V1\Controllers\Common\FilesController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubmissionController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum', []);
        // $this->middleware('ability:developer,create-submission', ['only' => ['store']]);
        // $this->middleware('ability:developer,view-submission', ['only' => ['index']]);
        // $this->middleware('ability:developer,update-submission', ['only' => ['update']]);
        // $this->middleware('ability:developer,delete-submission', ['only' => ['delete']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
        $whereWorkSheetId = (value($request->work_sheet_id)) ? 'work_sheet_id = "'.$request->work_sheet_id.'"' : 'work_sheet_id <> ""';

        $submission = Submission::with('user')
        ->with('lesson', function ($query) {
            $query->select('id', 'name', 'number', 'chapter_id');
        })
        ->with('chapter', function ($query) {
            $query->select('id', 'name', 'number', 'subject_id', 'year_id', 'exam_board_id');
        })
        ->with('subject', function ($query) {
            $query->select('id', 'name');
        })
        ->with('grade', function ($query) {
            $query->select('id', 'year','name','description');
        })
        ->with('exam_board', function ($query) {
            $query->select('id', 'name');
        })
        ->with('work_sheet', function ($query) {
            $query->select('id', 'name','questions');
        })
        ->whereRaw($whereId)
        ->whereRaw($whereWorkSheetId)
        ->get()
        ->map(function ($workSheet) {
            $questionsData = [];
            if (isset($workSheet->questions)) {
                if (value($workSheet->questions) && is_array($workSheet->questions)) {
                    $questionsData = Question::whereIn('id', array_values($workSheet->questions))->get();
                }
            }

            $workSheet->questions_data = $questionsData;
            return $workSheet;
        });

        return response()->json($submission, 201);
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
        $request->validate([
            'student_id'         => 'required',
            'grade_id'           => 'required|exists:years,id',
            'chapter_id'         => 'required|exists:chapters,id',
            'subject_id'         => 'required|exists:subjects,id',
            'exam_board_id'      => 'required|exists:exam_boards,id',
            'lesson_id'          => 'required|exists:lessons,id',
            'answer_type'        => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
            'student_answers'    => 'nullable|array',
            'work_sheet_id'      => 'required|exists:work_sheets,id',
            'status'             => 'required|in:publish,draft',
        ]);

        $submission = new Submission($request->all());
        if(value($request->student_answers)){
            foreach ($request->student_answers as $studentAnswers) {
                array_push($studentAnswers, ['type' => 'mcq', 'answer' => $studentAnswers['answer'], 'question_id' => $studentAnswers['question_id']]);
            }
        }

        if(value($request->attachment)){
            if($request->attachment_type == "image"){
                $path = 'public/images/submission/';
                $imageName = FilesController::saveBase64Images($request->attachment, $path);
                $submission->attachment = $imageName;

            }
        }

        if ($submission->save()) {

            $submission = Submission::with('user')
                ->with('lesson', function ($query) {
                    $query->select('id', 'name', 'number', 'chapter_id');
                })
                ->with('chapter', function ($query) {
                    $query->select('id', 'name', 'number', 'subject_id', 'year_id', 'exam_board_id');
                })
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year','name','description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->find($submission->id);

            return response()->json([
                'code'   => 201,
                'data'   => $submission,
                'status' => Lang::get('messages.submission_create_success'),
        ], 201);
    } else return response()->json(['code' => 200, 'status' => Lang::get('messages.submission_create_fail')], 200);
}

    public function assignUser(Request $request)
    {
        $request->validate([
            'teacher_id'       => 'required:users,id',
            'subject_id'       => 'required|exists:subjects,id',
        ]);

        $submission = Submission::find($request->id);
        $submission->teacher_id = $request->teacher_id;
        $submission->subject_id = $request->subject_id;
        $submission->save();

        return response()->json([
            'code'   => 201,
            'data'   => $submission,
            'status' => Lang::get('messages.work_sheet_update_success'),
        ], 201);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $WorkSheet = Submission::find($request->id);
        if (!$WorkSheet) throw new NotFoundHttpException(Lang::get('messages.work_sheet_not_found'));

        if ($WorkSheet->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.work_sheet_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.work_sheet_delete_fail')], 200);
    }
}
