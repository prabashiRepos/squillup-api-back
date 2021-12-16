<?php

namespace App\Api\V1\Controllers\ContentWriter;

use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class RevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
        $whereYearId = (value($request->grade_id)) ? 'grade_id = "'.$request->grade_id.'"' : 'grade_id <> ""';
        $whereSubjectId = (value($request->subject_id)) ? 'subject_id = "'.$request->subject_id.'"' : 'subject_id <> ""';
        $whereChapterId = (value($request->chapter_id)) ? 'chapter_id = "'.$request->chapter_id.'"' : 'chapter_id <> ""';
        $whereLessonId = (value($request->lesson_id)) ? 'lesson_id = "'.$request->lesson_id.'"' : 'lesson_id <> ""';
        $whereExamId = (value($request->exam_board_id)) ? 'exam_board_id = "'.$request->exam_board_id.'"' : 'exam_board_id <> ""';

        $revision = Revision::with('lesson')
            ->with('chapter')
            ->with('subject')
            ->with('grade')
            ->with('exam_board')
            ->whereRaw($whereId)
            ->whereRaw($whereYearId)
            ->whereRaw($whereSubjectId)
            ->whereRaw($whereChapterId)
            ->whereRaw($whereExamId)
            ->whereRaw($whereLessonId)
            ->get();

            return response()->json($revision, 201);

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
            'lesson_id'       => 'required|exists:lessons,id',
            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'resource'        => 'required',
            'status'          => 'required|in:publish,draft',
        ]);

        $revision = new Revision($request->all());

        if ($request->file('presentation')) {
            $file = $request->file('presentation');
            $filename = time() . '.' . $request->file('presentation')->extension();
            $filePath = public_path() . '/files/uploads/revision/presentation/';
            $file->move($filePath, $filename);
            $revision->presentation = $filePath . $filename;
        }
        if ($request->file('short_note')) {
            $file = $request->file('short_note');
            $filename = time() . '.' . $request->file('short_note')->extension();
            $filePath = public_path() . '/files/uploads/revision/shortnote/';
            $file->move($filePath, $filename);
            $revision->short_note = $filePath . $filename;
        }

        if ($revision->save()) {
            $revision = Revision::with('lesson')
            ->with('chapter')
            ->with('subject')
            ->with('grade')
            ->with('exam_board')
            ->find($revision->id);

            return response()->json([
                'code'   => 201,
                'data'   => $revision,
                'status' => Lang::get('messages.revision_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.revision_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function show(Revision $revision)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function edit(Revision $revision)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Revision $revision)
    {
        $request->validate([
            'id'              => 'required|exists:revisions,id',
            'lesson_id'       => 'required|exists:lessons,id',
            'grade_id'        => 'required|exists:years,id',
            'chapter_id'      => 'required|exists:chapters,id',
            'subject_id'      => 'required|exists:subjects,id',
            'exam_board_id'   => 'required|exists:exam_boards,id',
            'resource'        => 'required',
            'status'          => 'required|in:publish,draft',
        ]);


        $revision = Revision::find($request->id);

        $revision->fill($request->all());

        if ($request->file('presentation')) {
            $file = $request->file('presentation');
            $filename = time() . '.' . $request->file('presentation')->extension();
            $filePath = public_path() . '/files/uploads/revision/presentation/';
            $file->move($filePath, $filename);
            $revision->presentation = $filePath . $filename;
        }
        if ($request->file('short_note')) {
            $file = $request->file('short_note');
            $filename = time() . '.' . $request->file('short_note')->extension();
            $filePath = public_path() . '/files/uploads/revision/shortnote/';
            $file->move($filePath, $filename);
            $revision->short_note = $filePath . $filename;
        }

        if ($revision->save()) {
            $revision = Revision::with('lesson')
            ->with('chapter')
            ->with('subject')
            ->with('grade')
            ->with('exam_board')
            ->find($revision->id);

            return response()->json([
                'code'   => 201,
                'data'   => $revision,
                'status' => Lang::get('messages.revision_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.revision_create_fail')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $revision = Revision::find($request->id);
        if (!$revision) throw new NotFoundHttpException(Lang::get('messages.revision_not_found'));

        if ($revision->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.revision_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.revision_delete_fail')], 200);
    }
}
