<?php

namespace App\Api\V1\Controllers\Developer;

use DB;
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

class PermissionController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-permission', ['only' => ['store']]);
        $this->middleware('ability:developer,view-permission', ['only' => ['index']]);
        $this->middleware('ability:developer,update-permission', ['only' => ['update']]);
        $this->middleware('ability:developer,assign-permission', ['only' => ['assignPermission']]);
        $this->middleware('ability:developer,detach-permission', ['only' => ['detachPermission']]);
        $this->middleware('ability:developer,assign-permission|detach-permission', ['only' => ['assignDetachPermission']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|unique:permissions,name|max:200',
            'display_name' => 'required|max:200',
            'description'  => 'nullable|max:200',
        ]);

        $permission = new Permission($request->all());

        if($permission->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $permission,
                'status' => Lang::get('messages.permission_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.permission_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $permissions = Permission::get();

        if($permissions->count()){
            return ($request->groupBy == true) ? $permissions->groupBy('description') : $permissions;
        }
        else throw new NotFoundHttpException(Lang::get('messages.permission_not_found')); 
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:permissions,id|max:200',
            'name'         => 'required|max:200|unique:permissions,name,'.$request->id,
            'display_name' => 'required|max:200',
            'description'  => 'nullable|max:200',
        ]);

        $permission = Permission::find($request->id);
        $permission->fill($request->all());

        if($permission->save()){
            return response()->json([
                'code'   => 201,
                'data'   => $permission,
                'status' => Lang::get('messages.permission_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.permission_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id|max:200',
        ]);

        $permission = Permission::find($request->id);

        if($permission->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.permission_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.permission_delete_fail')], 200);
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id|max:200',
            'permission_id' => 'required|array',
            'permission_id.*' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);

        foreach( $request->permission_id as $key => $permissionId ) {
            $permission = Permission::find($permissionId);
            if(! $role->hasPermission($permission->name)) $role->attachPermission($permission->name);
        }

        return response()->json([
            'code' => 201,
            'status' => Lang::get('messages.permission_assign_success'),
        ], 201);
    }

    public function detachPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id|max:200',
            'permission_id' => 'required|array',
            'permission_id.*' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        
        foreach( $request->permission_id as $key => $permissionId ) {
            $permission = Permission::find($permissionId);
            if($role->hasPermission($permission->name)) $role->detachPermission($permission->name);
        }

        return response()->json([
            'code' => 201,
            'status' => Lang::get('messages.permission_detach_success'),
        ], 201);
    }

    public function assignDetachPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id|max:200',
            'permission_id' => 'required|array',
            'permission_id.*' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);

        foreach(Auth::guard()->user()->roles as $key => $currentUserRole ) {
           if($currentUserRole->hasPermission('detach-permission')){
               foreach($role->permissions as $key => $permission ) {
                    if(!in_array($permission->id, $request->permission_id)) $role->detachPermission($permission->name);
                } 
            }

            if($currentUserRole->hasPermission('assign-permission')){
               foreach($request->permission_id as $key => $permissionId ) {
                    $permission = Permission::find($permissionId);
                    if(! $role->hasPermission($permission->name)) $role->attachPermission($permission->name);
                } 
            }
        }

        $roles = Role::whereId($request->role_id)->get()->map(function($role){
            $permissions = Permission::get()->map(function($permission) use(&$role){
                $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                return $permission;
            });

            $role->permissions = $permissions->groupBy('description')->values()->toArray();
            return $role;
        });

        return response()->json([
            'code' => 201,
            'role_data' => $roles[0],
            'status' => Lang::get('messages.permission_assign_success'),
        ], 201);
    }
}
