<?php

namespace App\Api\V1\Controllers\ContentWriter;

use DB;
use Auth;
use App\Models\User;
use App\Models\Lesson;
use App\Models\PastPaper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Api\V1\Controllers\ContentWriter\QuestionController;
use Illuminate\Support\Facades\Storage;

class PastPaperController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-pastpaper', ['only' => ['store']]);
        $this->middleware('ability:developer,view-pastpaper', ['only' => ['index']]);
        $this->middleware('ability:developer,update-pastpaper', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-pastpaper', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'year'              => 'sometimes|required|int',
            'paper_type'        => 'nullable|in:higher,foundation',
            'grade_id'          => 'required|exists:years,id',
            // 'chapter_id'        => 'required|exists:chapters,id',
            'subject_id'        => 'required|exists:subjects,id',
            'exam_board_id'     => 'required|exists:exam_boards,id',
            'questions'         => 'nullable|array',
            'questions.*'       => 'required|exists:questions,id,test_type,pastpaper',
            'time_required'     => 'nullable|in:true',
            'duration_hours'    => 'required',
            'duration_minutes'  => 'required',
            'status'            => 'required|in:publish,draft',
        ]);

        $pastPaper = new PastPaper($request->all());
        $pastPaper->user_id = Auth::guard()->user()->id;

        if ($pastPaper->save()) {
            $pastPaper = PastPaper::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->find($pastPaper->id);

            return response()->json([
                'code'   => 201,
                'data'   => $pastPaper,
                'status' => Lang::get('messages.past_paper_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.past_paper_create_fail')], 200);
    }

    public function index(Request $request)
    {
        if (value($request->chapter_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "' . $request->grade_id . '"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "' . $request->subject_id . '"' : 'subject_id <> ""';

            $pastPapers = PastPaper::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->get();
        } elseif (value($request->subject_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "' . $request->grade_id . '"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "' . $request->subject_id . '"' : 'subject_id <> ""';

            $pastPapers = PastPaper::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })

                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->get();
        } elseif (value($request->grade_id)) {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "' . $request->grade_id . '"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "' . $request->subject_id . '"' : 'subject_id <> ""';

            $pastPapers = PastPaper::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })

                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->get();
        } else {
            $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
            $whereYearId = (value($request->grade_id)) ? 'grade_id = "' . $request->grade_id . '"' : 'grade_id <> ""';
            $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "' . $request->subject_id . '"' : 'subject_id <> ""';

            $pastPapers = PastPaper::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->whereRaw($whereId)
                ->whereRaw($whereYearId)
                ->whereRaw($whereSubjectId)
                ->get();
        }
        return response()->json($pastPapers, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'                => 'required|exists:past_papers,id',
            'year'              => 'sometimes|required|int',
            'paper_type'        => 'nullable|in:higher,foundation',
            'grade_id'          => 'required|exists:years,id',
            'chapter_id'        => 'required|exists:chapters,id',
            'subject_id'        => 'required|exists:subjects,id',
            'exam_board_id'     => 'required|exists:exam_boards,id',
            'questions'         => 'nullable|array',
            'questions.*'       => 'required|exists:questions,id,test_type,pastpaper',
            'time_required'     => 'nullable|in:true',
            'duration_hours'    => 'required',
            'duration_minutes'  => 'required',
            'status'            => 'required|in:publish,draft',
        ]);

        $pastPaper = PastPaper::find($request->id);
        $pastPaper->fill($request->all());

        if ($pastPaper->save()) {
            $pastPaper = PastPaper::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->find($pastPaper->id);

            return response()->json([
                'code'   => 201,
                'data'   => $pastPaper,
                'status' => Lang::get('messages.past_paper_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.past_paper_update_fail')], 200);
    }

    public function uploadPastFile(Request $request)
    {
        if ($request->file('past_paper_file')) {
            $file = $request->file('past_paper_file');
            $filename = time() . '.' . $request->file('past_paper_file')->extension();
            $filePath = public_path() . '/files/uploads/pastpaper/paper/';
            $file->move($filePath, $filename);

            $pastPaper = PastPaper::find($request->id);
            $pastPaper->paper_file = $filePath . $filename;
            if ($pastPaper->save()) {
                return response()->json([
                    'code'   => 201,
                    'data'   => $pastPaper,
                    'status' => Lang::get('messages.past_paper__file_upload_success'),
                ], 201);
            } else return response()->json(['code' => 200, 'status' => Lang::get('messages.past_paper_file_upload_fail')], 200);
        } elseif ($request->file('paper_marking_scheme')) {
            $file = $request->file('paper_marking_scheme');
            $filename = time() . '.' . $request->file('paper_marking_scheme')->extension();
            $filePath = public_path() . '/files/uploads/pastpaper/markingscheme/';
            $file->move($filePath, $filename);

            $pastPaper = PastPaper::find($request->id);
            $pastPaper->paper_marking_scheme = $filePath . $filename;
            if ($pastPaper->save()) {
                return response()->json([
                    'code'   => 201,
                    'data'   => $pastPaper,
                    'status' => Lang::get('messages.past_paper_marking_scheme_upload_success'),
                ], 201);
            } else return response()->json(['code' => 200, 'status' => Lang::get('messages.past_paper_marking_scheme_upload_fail')], 200);
        }
    }

    public function delete(Request $request)
    {
        $pastPaper = PastPaper::find($request->id);
        if (!$pastPaper) throw new NotFoundHttpException(Lang::get('messages.past_paper_not_found'));

        if ($pastPaper->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.past_paper_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.past_paper_delete_fail')], 200);
    }
}
