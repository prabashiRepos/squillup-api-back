<?php

namespace App\Api\V1\Controllers\ContentWriter;

use App\Models\User;
use App\Models\ParentDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Support\Facades\Lang;
use App\Notifications\SignupNotification;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class ParentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'parent');
        })
            ->with('parent_detail')
            ->with('parent_detail.plan')
            ->get();


        return response()->json($users, 201);
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
            'first_name'        => 'required|max:200',
            'last_name'         => 'nullable|max:200',
            'city'              => 'nullable',
            'email'             => 'required|unique:users,email|email:filter|max:200',
            'phone'             => 'sometimes|required|unique:users,phone|regex:/[0-9]{9}/',
            'user_name'         => 'nullable',
            'password'          => 'required|min:6',
            'plan_id'           => 'required|exists:plans,id',
            'payment_statement' => 'nullable',
        ]);

        $user = new User($request->all());

        if ($user->save()) {
            $parent = new ParentDetail($request->all());
            $parent->user_id = $user->id;
            if ($parent->save()) {
                $user->attachRole("parent");
                $token = $user->createToken('auth_token')->plainTextToken;

                $roles = array();
                $permissions = array();
                $userUniqueString = $user->id . $user->email;
                $userUniqueToken = md5($userUniqueString);

                $user->roles->map(function ($role) use (&$roles, &$permissions) {
                    $role->permissions->map(function ($permission) use (&$permissions) {
                        array_push($permissions, $permission->name);
                        return $permission;
                    });

                    array_push($roles, $role->name);
                    return $role;
                });

                $user->roles_array = $roles;
                $user->permissions_array = $permissions;

                $data = [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'password' => $request->password,
                ];

                // try{
                //     $notify = Notification::send($user, new SignupNotification($user, $data));
                // }
                // catch(\Exception $e){

                // }

                $parent = ParentDetail::with('plan')->find($parent->id);

                return response()->json([
                    'code'           => 201,
                    'user'           => $user,
                    'user_details'   => $parent,
                    'token_type'     => 'Bearer',
                    'token'          => $token,
                    'user_signature' => $userUniqueToken,
                    'token_expiration' => config('sanctum.expiration'),
                    'status' => Lang::get('messages.parent_create_success'),
                ], 201);
            }
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.parent_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParentDetail  $parentDetail
     * @return \Illuminate\Http\Response
     */
    public function show(ParentDetail $parentDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ParentDetail  $parentDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(ParentDetail $parentDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParentDetail  $parentDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParentDetail $parentDetail)
    {
        $user = User::find($request->id);
        if (!$user) throw new NotFoundHttpException(Lang::get('messages.parent_not_found'));

        $request->validate([
            'first_name'        => 'required|max:200',
            'last_name'         => 'nullable|max:200',
            'city'              => 'nullable',
            'email'             => 'required|email:filter|max:200|unique:users,email,' . $user->id,
            'phone'             => 'sometimes|required|regex:/[0-9]{9}/|unique:users,phone,' . $user->id,
            'user_name'         => 'nullable',
            'password'          => 'required|min:6',
            'plan_id'           => 'required|exists:plans,id',
            'payment_statement' => 'nullable',
        ]);

        $user->fill($request->all());

        if ($user->save()) {
            $parent = ParentDetail::where('user_id', $user->id)->first();
            $parent->fill($request->all());
            $parent->user_id = $user->id;
            if ($parent->save()) {

                $parent = ParentDetail::with('plan')->find($parent->id);;

                return response()->json([
                    'code'           => 201,
                    'user'           => $user,
                    'user_details'   => $parent,
                    'token_expiration' => config('sanctum.expiration'),
                    'status' => Lang::get('messages.update_create_success'),

                ], 201);
            }
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.parent_create_fail')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParentDetail  $parentDetail
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $user = User::find($request->id);
        $parent = ParentDetail::where('user_id', $user->id)->first();

        if (!$user) throw new NotFoundHttpException(Lang::get('messages.parent_not_found'));
        if (!$parent) throw new NotFoundHttpException(Lang::get('messages.parent_not_found'));

        if ($user->delete() && $parent->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.parent_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.parent_delete_fail')], 200);
    }
}
