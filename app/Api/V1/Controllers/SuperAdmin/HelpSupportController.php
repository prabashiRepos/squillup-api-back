<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;

class HelpSupportController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('role:parent', ['only' => ['contact']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function contact(Request $request)
    {
        $user = Auth::guard()->user();

        $request->validate([
            'subject' => 'required|max:200',
            'message' => 'required|max:1000',
        ]);

        $superadmins = User::whereRoleIs(['superadmin'])->first();

        if($superadmins){
            $data = [
                'sender_name' => $user->first_name." ".$user->last_name,
                'sender_email' => $user->email,

                'first_name' => $superadmins->first_name,
                'last_name' => $superadmins->last_name,
                'subject' => $request->subject,
                'message' => $request->message,
            ];

                
            try{
                $notify = Notification::send($superadmins, new Contact($superadmins, $data));
            }
            catch(\Exception $e){
                
            } 
        }
       
        return response()->json([
            'code'   => 201,
            'status' => Lang::get('messages.message_send_success'),
        ], 201);
    }
}
