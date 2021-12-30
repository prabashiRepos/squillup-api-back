<?php

namespace App\Api\V1\Controllers\ContentWriter;

use DB;
use Auth;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\WorkSheet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Api\V1\Controllers\ContentWriter\QuestionController;
use App\Events\NotifyEvent;

class WorkSheetController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-worksheet', ['only' => ['store']]);
        $this->middleware('ability:developer,view-worksheet', ['only' => ['index']]);
        $this->middleware('ability:developer,update-worksheet', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-worksheet', ['only' => ['delete']]);
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
            'duration_hours'        => 'required',
            'duration_minutes'        => 'required',
            'level'           => 'nullable|in:foundation,higher',
            'status'          => 'required|in:publish,draft',
        ]);

        $WorkSheet = new WorkSheet($request->all());
        $WorkSheet->user_id = Auth::guard()->user()->id;
        $WorkSheet->questions = (value($request->questions)) ? array_values($request->questions) : null;

        if ($WorkSheet->save()) {
            $WorkSheet = WorkSheet::with('user')
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
                ->find($WorkSheet->id);

            $questionsData = [];
            if (isset($workSheet->questions)) {
                if (value($workSheet->questions) && is_array($workSheet->questions)) {
                    $questionsData = Question::whereIn('id', array_values($workSheet->questions))->get();
                }
            }

            event(new  NotifyEvent('New worksheet has been created by '.$WorkSheet->user->first_name ." ". $WorkSheet->user->last_name));

            $WorkSheet->questions_data = $questionsData;

            return response()->json([
                'code'   => 201,
                'data'   => $WorkSheet,
                'status' => Lang::get('messages.work_sheet_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.work_sheet_create_fail')], 200);
    }

    public function index(Request $request)
    {
        if (value($request->chapter_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';


            $chapter_id = $request->chapter_id;
            $WorkSheets = WorkSheet::with('user')
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
        }
        elseif (value($request->subject_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';


            $subject_id = $request->subject_id;
            $WorkSheets = WorkSheet::with('user')
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
        }
        elseif (value($request->grade_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';

            $grade_id = $request->grade_id;
            $WorkSheets = WorkSheet::with('user')
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
                // ->whereHas('grade', function ($query) use ($grade_id) {
                //     $query->where('year', '=', $grade_id);
                // })
                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->whereRaw($whereChapterId)
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
        }
        else {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';

            // $whereYearId = (value($request->year_id)) ? 'year_id = "'.$request->year_id.'"' : 'year_id <> ""';
            // $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
            // $whereExamBoardId = (value($request->exam_board_id)) ? 'exam_board_id = "'.$request->exam_board_id.'"' : 'exam_board_id <> ""';
            // $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';

            $WorkSheets = WorkSheet::with('user')
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
        }
        return response()->json($WorkSheets, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:work_sheets,id',
            'lesson_id'       => 'required|exists:lessons,id',
            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'questions'       => 'nullable|array',
            'questions.*'     => 'required|exists:questions,id',
            'time_required'   => 'nullable|in:true',
            'duration_hours'        => 'required',
            'duration_minutes'        => 'required',
            'level'           => 'nullable|in:foundation,higher',
            'status'          => 'required|in:publish,draft',
        ]);

        $WorkSheet = WorkSheet::find($request->id);
        $WorkSheet->fill($request->all());
        $WorkSheet->questions = (value($request->questions)) ? array_values($request->questions) : null;

        if ($WorkSheet->save()) {
            $WorkSheet = WorkSheet::with('user')
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
                ->find($WorkSheet->id);

            $questionsData = [];
            if (isset($workSheet->questions)) {
                if (value($workSheet->questions) && is_array($workSheet->questions)) {
                    $questionsData = Question::whereIn('id', array_values($workSheet->questions))->get();
                }
            }

            $WorkSheet->questions_data = $questionsData;

            return response()->json([
                'code'   => 201,
                'data'   => $WorkSheet,
                'status' => Lang::get('messages.work_sheet_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.work_sheet_update_fail')], 200);
    }

    public function workDemoQuestion(Request $request)
    {
        $whereTestId = 'test_referrer_id <> ""';
        $whereTestType = 'test_type <> ""';

        $whereTestId = 'test_referrer_id = "' . $request->test_referrer_id . '"';
        $whereTestType = 'test_type = "work_sheet"';

        $question = Question::whereRaw($whereTestId)
            ->whereRaw($whereTestType)
            ->whereNull('parent_question_id')
            ->get();

        return response()->json($question, 201);
    }

    public function delete(Request $request)
    {
        $WorkSheet = WorkSheet::find($request->id);
        if (!$WorkSheet) throw new NotFoundHttpException(Lang::get('messages.work_sheet_not_found'));

        if ($WorkSheet->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.work_sheet_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.work_sheet_delete_fail')], 200);
    }
}
