<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use App\Events\NotifyEvent;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Mail\SendMailAdmin;
use App\Mail\SendReply;
use App\Mail\SendThankYou;
use App\Models\InquiryReply;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inquiry = Inquiry::get();

        return response()->json($inquiry, 201);
    }

    public function viewReply()
    {
        $inquiryReply = InquiryReply::with('inquiry')->get();

        return response()->json($inquiryReply, 201);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendMail(Request $request)
    {
        $email = $request->email;

        Mail::to('thushalangd@gmail.com')->send(new SendMail($email));

        return 'Email sent Successfully';
    }


    public function sendReply(Request $request)
    {
        $request->validate([
            'inquiry_id' => 'required|exists:inquiries,id',
            'message' => 'required',
        ]);

        $inquiry = Inquiry::find($request->inquiry_id);
        $email = $inquiry->email;
        $messages = $request->message;
        $subjects = $request->subject;

        $inquiryReply = new InquiryReply();
        $inquiryReply->inquiry_id = $request->inquiry_id;
        $inquiryReply->reply = $request->message;

        if($inquiryReply->save()){

        }

        Mail::to($email)->send(new SendReply($email,$messages,$subjects));

        return 'Reply sent Successfully';

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
            'email' => 'required',
            'message' => 'required',
        ]);

        $inquiry = new Inquiry($request->all());

        if ($request->file('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '.' . $request->file('attachment')->extension();
            $filePath = public_path() . '/files/uploads/mail/attachments/';
            $file->move($filePath, $filename);
            $inquiry->attachment = $filePath . $filename;
        }

        if($inquiry->save()){

            $inquiry = inquiry::with(['user'])->find(value($request->user_id));

            $email = $request->email;
            $name = $request->name;
            $messages = $request->message;

            // Mail::to($email)->send(new SendThankYou($email,$name));
            // Mail::to('thushalangd@gmail.com')->send(new SendMailAdmin($email,$name,$messages));

            event(new  NotifyEvent('New inquiry recevied from '.$inquiry->user->first_name ." ". $inquiry->user->last_name. " ( ".($inquiry->user->roles->first()->name ? : 'public')." ) "));

            return response()->json([
                'code'   => 201,
                'data'   => $inquiry,
                'status' => Lang::get('messages.inquiry_create_success'),
            ], 201);

        }

        else return response()->json(['code' => 200, 'status' => Lang::get('messages.inquiry_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */
    public function show(Inquiry $inquiry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */
    public function edit(Inquiry $inquiry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inquiry $inquiry)
    {
        //
    }
}
