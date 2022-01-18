<?php

namespace App\Api\V1\Controllers\Parent\Student;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\StudentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class StudentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $parent_id = $request->parent_id;
        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })
            ->whereHas('student_detail', function ($q) use ($parent_id) {
                $q->where('parent_id', $parent_id);
            })
            ->with('student_detail')
            ->with('student_detail.parent')
            ->with('student_detail.grade')
            ->with('student_detail.exam_board')
            ->with('student_detail.key_stage')
            ->with('student_detail.key_stage.year')
            ->get();

        return response()->json($students, 201);
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
            'full_name'             => 'required|max:200',
            'user_name'             => 'nullable|max:200',
            // 'email' => 'required|unique:users,email|email:filter|max:200',
            'exam_board_id'         => 'required|exists:exam_boards,id',
            'grade_id'              => 'required|exists:years,id',
            'key_stage_id'          => 'required|exists:key_stages,id',
            'school_name'           => 'required|max:200',
            'dob'                   => 'required|date_format:d/m/Y|after_or_equal:2005-01-01',
            'password'              => 'required|min:6|max:20',
        ]);


        $parent = User::find($request->parent_id);
        $parent = $parent->first_name;

        $user = new User($request->all());

        $user->email = $parent . "_" . $request->first_name . "@gmail.com";

        if ($user->save()) {
            $student = new StudentDetail($request->all());
            $student->user_id = $user->id;
            if ($student->save()) {
                $user->attachRole('student');
                $saveGroupUser = DB::table('group_users')->insert(['parent_user_id' => $request->parent_id, 'children_user_id' => $user->id]);

                // $user->roles->map(function($role){
                //     $permissions = Permission::get()->map(function($permission) use(&$role){
                //         $permission->hasPermission = $role->hasPermission($permission->name) ? true : false;
                //         return $permission;
                //     });

                //     $role->permissions = $permissions->groupBy('description')->values()->toArray();
                //     return $role;
                // });

                // $data = [
                //     'first_name' => $user->first_name,
                //     'last_name' => $user->last_name,
                //     'email' => $user->email,
                //     'password' => $request->password,
                // ];

                // try{
                //     $notify = Notification::send($user, new InformNewUser($user, $data));
                // }
                // catch(\Exception $e){

                // }

                $student = StudentDetail::with('parent')
                    ->with('grade')
                    ->with('exam_board')
                    ->with('key_stage')
                    ->with('key_stage.year')
                    ->find($student->id);

                return response()->json([
                    'code'   => 201,
                    'data'   => $user,
                    'student_details'   => $student,
                    'status' => Lang::get('messages.student_create_success'),
                ], 201);
            }
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.student_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentDetail  $studentDetail
     * @return \Illuminate\Http\Response
     */
    public function show(StudentDetail $studentDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentDetail  $studentDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentDetail $studentDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentDetail  $studentDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentDetail $studentDetail)
    {
        $user = User::find($request->id);
        if (!$user) throw new NotFoundHttpException(Lang::get('messages.stident_not_found'));

        $request->validate([
            'full_name'             => 'required|max:200',
            'user_name'             => 'nullable|max:200',
            'exam_board_id'         => 'required|exists:exam_boards,id',
            'grade_id'              => 'required|exists:years,id',
            'key_stage_id'          => 'required|exists:key_stages,id',
            'school_name'           => 'required|max:200',
            'dob'                   => 'required|date_format:d/m/Y|after_or_equal:2005-01-01',
            'password'              => 'required|min:6|max:20',
        ]);

        $user->first_name = $request->first_name;
        $user->last_name = $request->password;
        $user->password = $request->password;

        $parent = User::find($request->parent_id);
        $parent = $parent->first_name;

        if ($user->save()) {
            $student = StudentDetail::where('user_id', $user->id)->first();
            $student->fill($request->all());
            $student->user_id = $user->id;
            if ($student->save()) {
                $student = StudentDetail::with('parent')
                    ->with('grade')
                    ->with('exam_board')
                    ->with('key_stage')
                    ->with('key_stage.year')
                    ->find($student->id);

                return response()->json([
                    'code'   => 201,
                    'data'   => $user,
                    'student_details'   => $student,
                    'status' => Lang::get('messages.student_update_success'),
                ], 201);
            }
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.student_update_fail')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentDetail  $studentDetail
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $user = User::find($request->id);
        $student = StudentDetail::where('user_id', $user->id)->first();

        if (!$user) throw new NotFoundHttpException(Lang::get('messages.parent_not_found'));
        if (!$student) throw new NotFoundHttpException(Lang::get('messages.parent_not_found'));

        if ($user->delete() && $student->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.student_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.student_delete_fail')], 200);
    }
}
