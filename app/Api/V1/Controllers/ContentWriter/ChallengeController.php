<?php

namespace App\Api\V1\Controllers\ContentWriter;

use App\Events\NotifyEvent;
use Auth;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ChallengeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum', []);
        // $this->middleware('ability:developer,create-challenge', ['only' => ['store']]);
        // $this->middleware('ability:developer,view-challenge', ['only' => ['index']]);
        // $this->middleware('ability:developer,update-challenge', ['only' => ['update']]);
        // $this->middleware('ability:developer,delete-challenge', ['only' => ['delete']]);
    }
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

        $challenge = Challenge::with('user')
            ->with('subject', function ($query) {
                $query->select('id', 'name');
            })
            ->with('grade', function ($query) {
                $query->select('id', 'year','name','description');
            })
            ->whereRaw($whereId)
            ->whereRaw($whereYearId)
            ->whereRaw($whereSubjectId)
            ->get();

        return response()->json($challenge, 201);
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
            'grade_id'        => 'required|exists:years,id',
            'subject_id'      => 'required|exists:subjects,id',
            'age_limit'       => 'required',
            'challenge_board' => 'required',
            'year'            => 'nullable',
            'questions.*'     => 'required|exists:questions,id',
            'duration_hours'  => 'required',
            'duration_minutes'=> 'required',
            'level'           => 'nullable|in:intermediate,senior,junior',
            'status'          => 'required|in:publish,draft',
        ]);

        $challenge = new Challenge($request->all());
        $challenge->user_id = Auth::guard()->user()->id;

        if ($challenge->save()) {
            $challenge = Challenge::with('user')
                ->with('subject')
                ->with('grade')
                ->find($challenge->id);

        event(new  NotifyEvent('New challenge has been created by '.$challenge->user->first_name ." ". $challenge->user->last_name));

            return response()->json([
                'code'   => 201,
                'data'   => $challenge,
                'status' => Lang::get('messages.challenge_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.challenge_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function show(Challenge $challenge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function edit(Challenge $challenge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Challenge $challenge)
    {
        $request->validate([
            'id'              => 'required|exists:years,id',
            'grade_id'        => 'required|exists:years,id',
            'subject_id'      => 'required|exists:subjects,id',
            'age_limit'       => 'required',
            'challenge_board' => 'required',
            'year'            => 'nullable',
            'questions.*'     => 'required|exists:questions,id',
            'duration_hours'  => 'required',
            'duration_minutes'=> 'required',
            'level'           => 'nullable|in:intermediate,senior,junior',
            'status'          => 'required|in:publish,draft',
        ]);

        $challenge = Challenge::find($request->id);
        $challenge->fill($request->all());

        if ($challenge->save()) {
            $challenge = Challenge::with('user')
                ->with('subject', function ($query) {
                    $query->select('id', 'name');
                })
                ->with('grade', function ($query) {
                    $query->select('id', 'year','name','description');
                })
                ->find($challenge->id);

            return response()->json([
                'code'   => 201,
                'data'   => $challenge,
                'status' => Lang::get('messages.challenge_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.challenge_update_fail')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $challenge = Challenge::find($request->id);
        if (!$challenge) throw new NotFoundHttpException(Lang::get('messages.challenge_not_found'));

        if ($challenge->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.challenge_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.challenge_delete_fail')], 200);
    }
}
