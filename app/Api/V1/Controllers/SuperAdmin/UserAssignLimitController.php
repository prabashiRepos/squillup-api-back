<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use App\Models\UserAssignLimit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserAssignLimitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userAssignLimit = UserAssignLimit::get();

        return response()->json($userAssignLimit, 201);
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
            'user_id'           => 'required|exists:users,id|unique:user_assign_limits,user_id',
            'subject_id'        => 'nullable',
            'duration'          => 'required',
            'how_many_question' => 'required',
        ]);

        $userAssignLimit = new UserAssignLimit($request->all());

        if($userAssignLimit->save()){
            $userAssignLimit = UserAssignLimit::find($userAssignLimit->id);

            return response()->json([
                'code'   => 201,
                'data'   => $userAssignLimit,
                'status' => Lang::get('messages.create_user_limit_success'),
            ], 201);

        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.create_user_limit_fail')], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserAssignLimit  $userAssignLimit
     * @return \Illuminate\Http\Response
     */
    public function show(UserAssignLimit $userAssignLimit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserAssignLimit  $userAssignLimit
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAssignLimit $userAssignLimit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserAssignLimit  $userAssignLimit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserAssignLimit $userAssignLimit)
    {
        $user = User::find($request->id);
        if(!$user) throw new NotFoundHttpException(Lang::get('messages.user_not_found'));

        $request->validate([
            'id'                => 'required|exists:user_assign_limits,id',
            'user_id'           => 'required|exists:users,id|unique:user_assign_limits,user_id,'.$user->id,
            'subject_id'        => 'nullable',
            'duration'          => 'required',
            'how_many_question' => 'required',
        ]);

        $userAssignLimit = UserAssignLimit::find($request->id);
        $userAssignLimit->fill($request->all());

        if($userAssignLimit->save()){
            $userAssignLimit = UserAssignLimit::find($userAssignLimit->id);

            return response()->json([
                'code'   => 201,
                'data'   => $userAssignLimit,
                'status' => Lang::get('messages.update_user_limit_success'),
            ], 201);

        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.update_user_limit_fail')], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserAssignLimit  $userAssignLimit
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAssignLimit $userAssignLimit)
    {
        //
    }
}
