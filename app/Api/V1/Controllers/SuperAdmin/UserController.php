<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Notifications\InformNewUser;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-user', ['only' => ['store']]);
        $this->middleware('ability:developer,view-user', ['only' => ['index']]);
        $this->middleware('ability:developer,update-user', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-user', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|max:200',
            'last_name'     => 'nullable|max:200',
            'email'         => 'required|unique:users,email|email:filter|max:200',
            'phone'         => 'sometimes|required|unique:users,phone|regex:/[0-9]{9}/',
            'country_code'  => 'sometimes|required|integer',
            'password'      => 'required|min:6',
            // 'password' => ['required', 'string', 'min:6', 'max:20',
            //                     'regex:/[a-z]/',
            //                     'regex:/[A-Z]/',
            //                     'regex:/[0-9]/',
            //                     'regex:/[@$!%*#?&]/',
            //                 ],

            'role_id' => 'sometimes|required|array',
            'role_id.*' => 'required|exists:roles,id',
        ]);

        $user = new User($request->all());

        if($user->save()){
            if(value($request->role_id)){
                foreach($request->role_id as $key => $roleId ) {
                    $role = Role::find($roleId);
                    if(! $user->hasRole($role->name)){
                        $user->attachRole($role->name);
                    }
                }
            }

            $user->roles->map(function($role){
                $permissions = Permission::get()->map(function($permission) use(&$role){
                    $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                    return $permission;
                });

                $role->permissions = $permissions->groupBy('description')->values()->toArray();
                return $role;
            });

            $data = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'password' => $request->password,
            ];

            try{
                $notify = Notification::send($user, new InformNewUser($user, $data));
            }
            catch(\Exception $e){

            }

            return response()->json([
                'code'   => 201,
                'data'   => $user,
                'status' => Lang::get('messages.user_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $request->validate([
            'roles' => 'sometimes|required|array',
            'roles.*' => 'required|string',
        ]);

        $perPage = (value($request->per_page)) ? $request->per_page : 10;

        $users = User::where(function ($query) use($request){
                            if(value($request->full_name)){
                                $query->where(DB::raw('CONCAT_WS(" ", first_name, last_name)'), 'like', '%'.$request->input('full_name').'%');
                            }
                            else $query->whereRaw('id <> ""');
                        })
                        ->where(function ($query) use($request){
                            if(value($request->roles)){
                                $query->whereRoleIs($request->roles);
                            }
                            else $query->whereRaw('id <> ""');
                        })
                        ->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
            $roles = $user->roles->map(function($role){
                $permissions = Permission::get()->map(function($permission) use(&$role){
                    $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                    return $permission;
                });

                $role->permissions = $permissions->groupBy('description')->values()->toArray();
                return $role;
            });

            $user->roles = $roles;
            return $user;
        });

        return response()->json($users, 201);
    }

    public function update(Request $request)
    {
        $user = User::find($request->id);
        if(!$user) throw new NotFoundHttpException(Lang::get('messages.user_not_found'));

        $request->validate([
            'first_name'    => 'required|max:200',
            'last_name'     => 'nullable|max:200',
            'email'         => 'required|email:filter|max:200|unique:users,email,'.$user->id,
            'phone'         => 'sometimes|required|regex:/[0-9]{9}/|unique:users,phone,'.$user->id,
            'country_code'  => 'sometimes|required|integer',
            'password'      => 'sometimes|required|min:6',
            // 'password' => ['required', 'string', 'min:6', 'max:20',
            //                     'regex:/[a-z]/',
            //                     'regex:/[A-Z]/',
            //                     'regex:/[0-9]/',
            //                     'regex:/[@$!%*#?&]/',
            //                 ],

            'role_id' => 'sometimes|required|array',
            'role_id.*' => 'required|exists:roles,id',
        ]);

        $user->fill($request->all());

        if($user->save()){
            if(value($request->role_id)){
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
            }

            $user = User::find($request->id);
            $user->roles->map(function($role){
                $permissions = Permission::get()->map(function($permission) use(&$role){
                    $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                    return $permission;
                });

                $role->permissions = $permissions->groupBy('description')->values()->toArray();
                return $role;
            });

            return response()->json([
                'code'   => 201,
                'data'   => $user,
                'status' => Lang::get('messages.user_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $user = User::find($request->id);
        if(!$user) throw new NotFoundHttpException(Lang::get('messages.user_not_found'));

        if($user->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.user_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_delete_fail')], 200);
    }
}
