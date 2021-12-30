<?php

namespace App\Api\V1\Controllers\ContentWriter;

use DB;
use Auth;
use App\Models\User;
use App\Models\Lesson;
use App\Models\SelfTest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Api\V1\Controllers\ContentWriter\QuestionController;
use App\Events\NotifyEvent;
use App\Models\Question;

class SelfTestController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-selftest', ['only' => ['store']]);
        $this->middleware('ability:developer,view-selftest', ['only' => ['index']]);
        $this->middleware('ability:developer,update-selftest', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-selftest', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id'       => 'required|exists:lessons,id',
            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'questions'       => 'nullable|array',
            'questions.*'     => 'required|exists:questions,id',
            'time_required'   => 'nullable|in:true',
            'duration_hours'  => 'required',
            'duration_minutes'=> 'required',
            'level'           => 'nullable|in:foundation,higher',
            'status'          => 'required|in:publish,draft',
        ]);

        $selfTest = new SelfTest($request->all());
        $selfTest->user_id = Auth::guard()->user()->id;

        if ($selfTest->save()) {
            $selfTest = SelfTest::with('user')
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
                ->find($selfTest->id);

            event(new  NotifyEvent('New self test has been created by '.$selfTest->user->first_name ." ". $selfTest->user->last_name));

            return response()->json([
                'code'   => 201,
                'data'   => $selfTest,
                'status' => Lang::get('messages.self_test_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.self_test_create_fail')], 200);
    }

    public function index(Request $request)
    {
        if (value($request->chapter_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';

            $chapter_id = $request->chapter_id;
            $selfTests = SelfTest::with('user')
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
                // ->whereHas('lesson', function ($query) use ($chapter_id) {
                //     $query->where('chapter_id', '=', $chapter_id);
                // })
                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->whereRaw($whereChapterId)
                ->get();
        }
        elseif (value($request->subject_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';

            $subject_id = $request->subject_id;
            $selfTests = SelfTest::with('user')
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
                // ->whereHas('lesson.chapter.subject', function ($query) use ($subject_id) {
                //     $query->where('subject_id', '=', $subject_id);
                // })
                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->whereRaw($whereChapterId)
                ->get();
        }
        elseif (value($request->grade_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';

            $grade_id = $request->grade_id;
            $selfTests = SelfTest::with('user')
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
                // ->where('grade_id', '=', $grade_id)
                // ->whereHas('grade', function ($query) use ($grade_id) {
                //     $query->where('id', '=', $grade_id);
                // })
                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->whereRaw($whereChapterId)
                ->get();
        } else {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';

            $selfTests = SelfTest::with('user')
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
                ->whereRaw($whereId)
                ->get();
        }

        return response()->json($selfTests, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'              => 'required|exists:self_tests,id',
            'lesson_id'       => 'required|exists:lessons,id',
            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'questions'       => 'nullable|array',
            'questions.*'     => 'required|exists:questions,id',
            'time_required'   => 'nullable|in:true',
            'duration_hours'  => 'required',
            'duration_minutes'=> 'required',
            'level'           => 'nullable|in:foundation,higher',
            'status'          => 'required|in:publish,draft',
        ]);

        $selfTest = SelfTest::find($request->id);
        $selfTest->fill($request->all());

        if ($selfTest->save()) {
            $selfTest = SelfTest::with('user')
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
                ->find($selfTest->id);

            return response()->json([
                'code'   => 201,
                'data'   => $selfTest,
                'status' => Lang::get('messages.self_test_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.self_test_update_fail')], 200);
    }

    public function selfDemoQuestion(Request $request)
    {
        $whereTestId = 'test_referrer_id <> ""';
        $whereTestType = 'test_type <> ""';

        $whereTestId = 'test_referrer_id = "' . $request->test_referrer_id . '"';
        $whereTestType = 'test_type = "self_test"';


        $question = Question::whereRaw($whereTestId)
            ->whereRaw($whereTestType)
            ->whereNull('parent_question_id')
            ->get();

        return response()->json($question, 201);
    }

    public function addExplanationVideo(Request $request) {
        $question = Question::where('id',$request->question_id)
        ->where('test_type','self_test')
        ->first();
        $question->video_explanation = $question->video_explanation;
        $question->save();
    }

    public function delete(Request $request)
    {
        $selfTest = SelfTest::find($request->id);
        if (!$selfTest) throw new NotFoundHttpException(Lang::get('messages.self_test_not_found'));

        if ($selfTest->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.self_test_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.self_test_delete_fail')], 200);
    }
}
