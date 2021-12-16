<?php

namespace App\Api\V1\Controllers\Developer;

use Auth;
use Validator;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-role', ['only' => ['store']]);
        $this->middleware('ability:developer,view-role', ['only' => ['index']]);
        $this->middleware('ability:developer,update-role', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-role', ['only' => ['delete']]);
        $this->middleware('ability:developer,assign-role', ['only' => ['assignRole']]);
        $this->middleware('ability:developer,detach-role', ['only' => ['detachRole']]);
        $this->middleware('ability:developer,assign-role|detach-role', ['only' => ['assignDetachRole']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|unique:roles,name|max:200',
            'display_name' => 'required|max:200',
            'description'  => 'nullable|max:200',
        ]);

        $role = new Role($request->all());

        if($role->save()){
            $permissions = Permission::get()->map(function($permission) use(&$role){
                $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                return $permission;
            });

            $role->permissions = $permissions->groupBy('description')->values()->toArray();

            return response()->json([
                'code' => 201,
                'data' => $role,
                'status' => Lang::get('messages.role_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $whereName = (value($request->name)) ? 'name = "' . $request->name . '"' : 'name <> ""';

        $roles = Role::whereNotIn('name', ['developer'])
        ->whereRaw($whereName)
        ->get()->map(function($role){
            $permissions = Permission::get()->map(function($permission) use(&$role){
                $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                return $permission;
            });

            $role->permissions = $permissions->groupBy('description')->values()->toArray();
            return $role;

        });

        if($roles->count()) return $roles;
        else throw new NotFoundHttpException(Lang::get('messages.role_not_found'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:roles,id|max:200',
            'name'         => 'required|max:200|unique:roles,name,'.$request->id,
            'display_name' => 'required|max:200',
            'description'  => 'nullable|max:200',
        ]);

        $role = Role::find($request->id);
        $role->fill($request->all());

        if($role->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $role,
                'status' => Lang::get('messages.role_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id|max:200',
        ]);

        $role = Role::find($request->id);

        if($role->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.role_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_delete_fail')], 200);
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|max:200',
            'role_id' => 'required|exists:roles,id|max:200',
        ]);

        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);

        if(! $user->hasRole($role->name)){
            if($user->attachRole($role->name)){
                return response()->json([
                    'code' => 201,
                    'status' => Lang::get('messages.role_assign_success'),
                ], 201);
            }
            else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_assign_fail')], 200);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_already_assigned')], 200);
    }

    public function detachRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|max:200',
            'role_id' => 'required|exists:roles,id|max:200',
        ]);

        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);

        if($user->hasRole($role->name)){
            if($user->detachRole($role->name)){
                return response()->json([
                    'code' => 201,
                    'status' => Lang::get('messages.role_detach_success'),
                ], 201);
            }
            else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_detach_fail')], 200);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.role_already_detached')], 200);
    }

    public function assignDetachRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|max:200',
            'role_id' => 'required|array',
            'role_id.*' => 'required|exists:roles,id',
        ]);

        $user = User::find($request->user_id);

        foreach(Auth::guard()->user()->roles as $key => $currentUserRole ) {
            if($currentUserRole->hasPermission('detach-role')){
                foreach($user->roles as $key => $role ) {
                    if(!in_array($role->id, $request->role_id)) $user->detachRole($role->name);
                }
            }

            if($currentUserRole->hasPermission('assign-role')){
                foreach($request->role_id as $key => $roleId ) {
                    $role = Role::find($roleId);
                    if(! $user->hasRole($role->name)) $user->attachRole($role->name);
                }
            }
        }

        return response()->json([
            'code' => 201,
            'status' => Lang::get('messages.role_assign_success'),
        ], 201);
    }
}
