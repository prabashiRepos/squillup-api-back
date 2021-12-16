<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\User;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LessonController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-lesson', ['only' => ['store']]);
        $this->middleware('ability:developer,view-lesson', ['only' => ['index']]);
        $this->middleware('ability:developer,update-lesson', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-lesson', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('lessons', 'name')
                ->where('number', $request->number)
                ->where('chapter_id', $request->chapter_id)],

            'number' => ['required', Rule::unique('lessons', 'number')
                ->where('name', $request->name)
                ->where('chapter_id', $request->chapter_id)],

            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'video_url'       => 'sometimes|required|url',
            'overview'        => 'sometimes|required|max:5000',
            'data'            => 'sometimes|required|array',
            'data.*'          => 'required|max:1000',
        ]);

        $lesson = new Lesson($request->all());

        if ($request->file('presentation')) {
            $file = $request->file('presentation');
            $filename = time() . '.' . $request->file('presentation')->extension();
            $filePath = public_path() . '/files/uploads/lesson/presentation/';
            $file->move($filePath, $filename);
            $lesson->presentation = $filePath . $filename;
        }
        if ($request->file('short_notes')) {
            $file = $request->file('short_notes');
            $filename = time() . '.' . $request->file('short_notes')->extension();
            $filePath = public_path() . '/files/uploads/lesson/shortnotes/';
            $file->move($filePath, $filename);
            $lesson->short_notes = $filePath . $filename;
        }

        if ($lesson->save()) {
            $lesson = lesson::with('chapter')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->find($lesson->id);

            return response()->json([
                'code'   => 201,
                'data'   => $lesson,
                'status' => Lang::get('messages.lesson_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.lesson_create_fail')], 200);
    }

    public function index(Request $request)
    {
        if($request->description_short_note){
            $lesson = Lesson::where('id',$request->id)
            ->select('description','short_notes')
            ->get();
        }elseif($request->presentation){
            $lesson = Lesson::where('id',$request->id)
            ->select('presentation')
            ->get();

        }elseif($request->video_url){
            $lesson = Lesson::where('id',$request->id)
            ->select('video_url')
            ->get();

        }
        elseif($request->glossary){
            $lesson = Lesson::where('id',$request->id)
            ->select('glossary')
            ->get();

        }else {
        $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
        $whereYearId = (value($request->grade_id)) ? 'grade_id = "' . $request->grade_id . '"' : 'grade_id <> ""';
        $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "' . $request->subject_id . '"' : 'subject_id <> ""';
        $whereExamBoardId = (value($request->exam_board_id)) ? 'exam_board_id = "' . $request->exam_board_id . '"' : 'exam_board_id <> ""';
        $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "' . $request->chapter_id . '"' : 'chapter_id <> ""';

        $lesson = lesson::with('chapter')
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
            ->whereRaw($whereChapterId)
            ->whereRaw($whereSubjectId)
            ->whereRaw($whereExamBoardId)
            ->whereRaw($whereYearId)
            ->get();
        }

        return response()->json($lesson, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:lessons,id',
            'name' => ['required', Rule::unique('lessons', 'name')
                ->ignore($request->id, 'id')
                ->where('number', $request->number)
                ->where('chapter_id', $request->chapter_id)],

            'number' => ['required', Rule::unique('lessons', 'number')
                ->ignore($request->id, 'id')
                ->where('name', $request->name)
                ->where('chapter_id', $request->chapter_id)],

            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'video_url'       => 'sometimes|required|url',
            'overview'        => 'sometimes|required|max:5000',
            'data'            => 'sometimes|required|array',
            'data.*'          => 'required|max:1000',
        ]);

        $lesson = lesson::find($request->id);
        $lesson->fill($request->all());

        if ($request->file('presentation')) {
            $file = $request->file('presentation');
            $filename = time() . '.' . $request->file('presentation')->extension();
            $filePath = public_path() . '/files/uploads/lesson/presentation/';
            $file->move($filePath, $filename);
            $lesson->presentation = $filePath . $filename;
        }
        if ($request->file('short_notes')) {
            $file = $request->file('short_notes');
            $filename = time() . '.' . $request->file('short_notes')->extension();
            $filePath = public_path() . '/files/uploads/lesson/shortnotes/';
            $file->move($filePath, $filename);
            $lesson->short_notes = $filePath . $filename;
        }

        if ($lesson->save()) {
            $lesson = lesson::with('chapter')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year', 'name', 'description');
                })
                ->with('exam_board', function ($query) {
                    $query->select('id', 'name');
                })
                ->find($lesson->id);

            return response()->json([
                'code'   => 201,
                'data'   => $lesson,
                'status' => Lang::get('messages.lesson_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.lesson_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $lesson = lesson::find($request->id);
        if (!$lesson) throw new NotFoundHttpException(Lang::get('messages.lesson_not_found'));

        if ($lesson->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.lesson_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.lesson_delete_fail')], 200);
    }
}
