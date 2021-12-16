<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use App\Models\QnA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use App\Models\QuestionUser;
use App\Models\Reply;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class QnAController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $qna = QnA::with(['subject', 'lesson', 'chapter', 'user', 'reply'])->get();

        return response()->json($qna, 201);
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
            'student_id'    => 'required|exists:users,id',
            // 'user_id'       => 'exists:users,id',
            'chapter_id'    => 'required|exists:chapters,id',
            'lesson_id'     => 'required|exists:lessons,id',
            'subject_id'    => 'required|exists:subjects,id',
            'question'      => 'required',
        ]);

        $qna = new QnA($request->all());

        if ($qna->save()) {
            $qna = QnA::with(['subject', 'lesson', 'chapter', 'user'])->find($qna->id);

            return response()->json([
                'code'   => 201,
                'data'   => $qna,
                'status' => Lang::get('messages.qna_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.qna_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QnA  $qnA
     * @return \Illuminate\Http\Response
     */
    public function show(QnA $qnA)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\QnA  $qnA
     * @return \Illuminate\Http\Response
     */
    public function edit(QnA $qnA)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\QnA  $qnA
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QnA $qnA)
    {
        $request->validate([
            'id'            => 'required|exists:qn_a_s,id',
            'student_id'    => 'required|exists:users,id',
            'user_id'       => 'exists:users,id',
            'chapter_id'    => 'required|exists:chapters,id',
            'lesson_id'     => 'required|exists:lessons,id',
            'subject_id'    => 'required|exists:subjects,id',
            'question'      => 'required',
        ]);

        $qna = QnA::find($request->id);
        $qna->fill($request->all());


        if ($qna->save()) {
            $qna = QnA::with(['subject', 'lesson', 'chapter', 'user', 'reply'])->find($qna->id);

            return response()->json([
                'code'   => 201,
                'data'   => $qna,
                'status' => Lang::get('messages.qna_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.qna_update_fail')], 200);
    }

    public function createReply(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'question_id'   => 'required|exists:qn_a_s,id',
            'reply'         => 'required',
        ]);

        $reply = new Reply($request->all());

        if ($reply->save()) {
            $reply = Reply::find($reply->id);

            return response()->json([
                'code'   => 201,
                'data'   => $reply,
                'status' => Lang::get('messages.reply_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.reply_create_fail')], 200);
    }

    public function viewQnAReply(Request $request)
    {
        $whereQuestionId = (value($request->question_id)) ? 'question_id = "'.$request->question_id.'"' : 'question_id <> ""';

        $reply = Reply::with(['user', 'question'])
        ->whereRaw($whereQuestionId)
        ->get();

        return response()->json($reply, 201);
    }

    public function assignQnAUser(Request $request)
    {
        $user = User::with('user_limit')->find($request->user_id);

        if(!$user->user_limit) throw new NotFoundHttpException(Lang::get('messages.please_set_user_limit'));

        if ($user->user_limit->duration == "day") {

            $startDay = Carbon::now()->startOfDay();
            $endDay   = $startDay->copy()->endOfDay();
            $count = QuestionUser::where('user_id', $request->user_id)
                ->where('created_at', '>=', $startDay)
                ->where('created_at', '<=', $endDay)
                ->count();

            if ($user->user_limit->how_many_question <= $count) {
                return response()->json(['code' => 200, 'status' => Lang::get('messages.user_reach_limit_per_day')], 200);
            }else{
                $request->validate([
                    'user_id'       => 'required|exists:users,id',
                    'subject_id'    => 'required|exists:subjects,id',
                    'question_id'   => 'required|exists:qn_a_s,id|unique:question_users,user_id',
                ]);
                $assignUser = new QuestionUser($request->all());

                if ($assignUser->save()) {
                    $assignUser = QuestionUser::find($assignUser->id);

                    return response()->json([
                        'code'   => 201,
                        'data'   => $assignUser,
                        'status' => Lang::get('messages.user_assign_success'),
                    ], 201);
                } else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_assign_fail')], 200);
            }

        } elseif ($user->user_limit->duration == "week") {

            $startWeek = Carbon::now()->startOfWeek();
            $endWeek = $startWeek->copy()->endOfWeek();
            $count = QuestionUser::where('user_id', $request->user_id)
                ->where('created_at', '>=', $startWeek)
                ->where('created_at', '<=', $endWeek)
                ->count();

                if ($user->user_limit->how_many_question <= $count) {
                    return response()->json(['code' => 200, 'status' => Lang::get('messages.user_reach_limit_per_week')], 200);
                }else{
                    $request->validate([
                        'user_id'       => 'required|exists:users,id',
                        'subject_id'    => 'required|exists:subjects,id',
                        'question_id'   => 'required|exists:qn_a_s,id|unique:question_users,user_id',
                    ]);

                    $assignUser = new QuestionUser($request->all());

                    if ($assignUser->save()) {
                        $assignUser = QuestionUser::find($assignUser->id);

                        return response()->json([
                            'code'   => 201,
                            'data'   => $assignUser,
                            'status' => Lang::get('messages.user_assign_success'),
                        ], 201);
                    } else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_assign_fail')], 200);
                }

        } elseif ($user->user_limit->duration == "month") {

            $startmonth = Carbon::now()->startOfMonth();
            $endmonth = $startmonth->copy()->endOfMonth();
            $count = QuestionUser::where('user_id', $request->user_id)
                ->where('created_at', '>=', $startmonth)
                ->where('created_at', '<=', $endmonth)
                ->count();

            if ($user->user_limit->how_many_question <= $count) {
                return response()->json(['code' => 200, 'status' => Lang::get('messages.user_reach_limit_per_month')], 200);
            }else{
                $request->validate([
                    'user_id'       => 'required|exists:users,id',
                    'subject_id'    => 'required|exists:subjects,id',
                    'question_id'   => 'required|exists:qn_a_s,id|unique:question_users,user_id',
                ]);

                $assignUser = new QuestionUser($request->all());

                if ($assignUser->save()) {
                    $assignUser = QuestionUser::find($assignUser->id);

                    return response()->json([
                        'code'   => 201,
                        'data'   => $assignUser,
                        'status' => Lang::get('messages.user_assign_success'),
                    ], 201);
                } else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_assign_fail')], 200);
            }
        }else {
            return response()->json(['code' => 200, 'status' => Lang::get('messages.user_assign_fail')], 200);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QnA  $qnA
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:qn_a_s,id',
        ]);

        $qna = QnA::find($request->id);

        if ($qna->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.qna_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.qna_delete_fail')], 200);
    }
}
