<?php

namespace App\Api\V1\Controllers\Auth;

use DB;
use Auth;
use Hash;
use Config;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Api\V1\Controllers\Common\Files\FilesController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{
    use Helpers;
    
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
    }

    public function viewProfile($id = null)
    {
        $user =  Auth::guard()->user(); 

        $roles = array();
        $permissions = array();

        $user->roles->map(function($role) use(&$roles, &$permissions){
            $role->permissions->map(function($permission) use(&$permissions){
                array_push($permissions, $permission->name);
                return $permission;
            });

            array_push($roles, $role->name);
            return $role;
        });

        $user->roles_array = $roles;
        $user->permissions_array = $permissions;
        return $user;
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard()->user(); 
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:100',
            'last_name'  => 'required|max:100',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'phone'      => 'nullable|regex:/[0-9]{9}/|unique:users,phone,'.$user->id,

            'old_password'         => 'sometimes|required|string',
            'new_password'         => 'required_with:old_password|max:20|min:6',
            'confirm_new_password' => 'required_with:old_password|max:20|min:6|different:old_password|same:new_password',
        ]);
        if($validator->fails()) return response()->json($validator->errors(), 422);

        $requestArray = $request->except(['password', 'profile_image']);
        $requestArray = array_filter($requestArray);
        $user->fill($requestArray);

        if(value($request->old_password)){
            if(Hash::check($request->old_password, $user->password)){
                $user->password = $request->new_password;
            }
        }
        
        if($user->save()){
            $roles = array();
            $permissions = array();

            $user->roles->map(function($role) use(&$roles, &$permissions){
                $role->permissions->map(function($permission) use(&$permissions){
                    array_push($permissions, $permission->name);
                    return $permission;
                });

                array_push($roles, $role->name);
                return $role;
            });

            $user->roles_array = $roles;
            $user->permissions_array = $permissions;

            return response()->json([
                'data' => $user,
                // 'delete_image' => $deleteRes,
                'status' => Lang::get('messages.user_update_success'),
            ], 201);
        } 
        else return response()->json(['status' => Lang::get('messages.user_update_fail'),], 200);
    }

    public function uploadProfileImage(Request $request, $id = null)
    {
        $user = (is_null($id)) ? Auth::guard()->user() : User::find($id); 
        if(!$user) throw new NotFoundHttpException(Lang::get('messages.request_user_not_found'));         
                        
        $maxSize = Config::get('filepaths.max_file_sizes.user_profile_image', '2048');
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|mimes:jpeg,jpg,png|max:'.$maxSize,
        ]);
        if($validator->fails()) return response()->json($validator->errors(), 422);

        $path = Config::get('filespath.files_directory.profile_image');
        $imageName = FilesController::saveImages($request->file('profile_image'),  $path);
        $imagePath = Config::get('filespath.files_directory.profile_image_path').$imageName;

        $deleteRes = FilesController::deleteImages(DB::table('users')->find($user->id)->profile_image);
        $user->profile_image = $imagePath;
        
        if($user->save()){
            return response()->json([
                'data' => $user,
                // 'delete_image' => $deleteRes,
                'status' => Lang::get('messages.image_updated'),
            ], 201);
        }
        else return response()->json(['status' => Lang::get('messages.error_in_image_updating'),], 200);
    }

    public function changePassword(Request $request, $id=null)
    {
        $user = (is_null($id)) ? Auth::guard()->user() : User::find($id); 
        if(!$user) throw new NotFoundHttpException(Lang::get('messages.request_user_not_found'));     

        $validator = Validator::make($request->all(), [
            'old_password'         => 'required',
            'new_password'         => 'required|max:20|min:6|different:old_password|same:confirm_new_password',
            'confirm_new_password' => 'required|max:20|min:6',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        if(Hash::check($request->old_password, Auth::user()->password)){
            $user->password = $request->new_password;
            if($user->save()) return response()->json(['status' => Lang::get('messages.password_updated'),], 201);
            else return response()->json(['status' => Lang::get('messages.error_in_password_updating'),], 200);
        }
        else return $this->response->error(Lang::get('messages.old_password_is_wrong'), 403);
    }
}
