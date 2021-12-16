<?php

namespace App\Api\V1\Controllers\Parent;

use DB;
use Auth;
use App\Models\User;
use App\Models\UsersPlan;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Notifications\InformNewUser;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudentController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer|parent,create-user', ['only' => ['store']]);
        $this->middleware('ability:developer|parent,view-user', ['only' => ['index']]);
        $this->middleware('ability:developer|parent,update-user', ['only' => ['update']]);
        $this->middleware('ability:developer|parent,delete-user', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $parent = Auth::guard()->user(); 

        $myPlan = UsersPlan::with('plan')
                                ->where('user_id', $parent->id)
                                ->whereIn('status', ['active', 'succeeded'])
                                ->first();

        if($myPlan){
            $plan = $myPlan->plan;
            $saveGroupUser = DB::table('group_users')->where('parent_user_id', $parent->id)->pluck('children_user_id')->toArray();

            if($plan->max_students > count($saveGroupUser)){
                $request->validate([
                    'first_name' => 'required|max:200',
                    'last_name' => 'nullable|max:200',
                    'email' => 'required|unique:users,email|email:filter|max:200',
                    'phone' => 'sometimes|required|unique:users,phone|regex:/[0-9]{9}/',
                    'country_code' => 'sometimes|required|integer',
                    'password' => 'required|min:6|max:20',
                ]);

                $user = new User($request->all());

                if($user->save()){
                    $user->attachRole('student');
                    $saveGroupUser = DB::table('group_users')->insert(['parent_user_id' => $parent->id, 'children_user_id' => $user->id]);
             
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
             else return response()->json(['status' => false, 'message' => 'max_students_reached'], 200);
        }
        else return response()->json(['status' => false, 'message' => 'no_active_plans'], 200);
    }

    public function index(Request $request)
    {
        $parent = Auth::guard()->user(); 
        $perPage = (value($request->per_page)) ? $request->per_page : 10;
        $saveGroupUser = DB::table('group_users')->where('parent_user_id', $parent->id)->pluck('children_user_id')->toArray();

        $users = User::where(function ($query) use($request){
                            if(value($request->full_name)){
                                $query->where(DB::raw('CONCAT_WS(" ", first_name, last_name)'), 'like', '%'.$request->input('full_name').'%');
                            }
                            else $query->whereRaw('id <> ""');
                        })
                        ->whereIn('id', $saveGroupUser)
                        ->paginate($perPage);

        return response()->json($users, 201);
    }

    public function update(Request $request)
    {
        $parent = Auth::guard()->user(); 

        $request->validate([
            'id' => 'required|exists:users,id|exists:group_users,children_user_id,parent_user_id,'.$parent->id,
            'first_name' => 'required|max:200',
            'last_name' => 'nullable|max:200',
            'email' => 'required|email:filter|max:200|unique:users,email,'.$parent->id,
            'phone' => 'sometimes|required|regex:/[0-9]{9}/|unique:users,phone,'.$parent->id,
            'country_code' => 'sometimes|required|integer',
            'password' => 'sometimes|required|min:6|max:20',
        ]);

        $user = User::find($request->id);
        $user->fill($request->all());

        if($user->save()){
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
        $parent = Auth::guard()->user(); 

        $request->validate([
            'id' => 'required|exists:users,id|exists:group_users,children_user_id,parent_user_id,'.$parent->id,
        ]);

        $user = User::find($request->id); 

        if($user->delete()){
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.user_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.user_delete_fail')], 200);
    }
}
