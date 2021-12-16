<?php

namespace App\Api\V1\Controllers\ContentWriter;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;


class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $whereUserType = (value($request->user_type)) ? 'user_type = "'.$request->user_type.'"' : 'user_type <> ""';

        $faq = Faq::whereRaw($whereUserType)
        ->get();

        return response()->json($faq, 201);
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
            'user_type'            => 'required',
            'category'             => 'required',
            'question'             => 'required',
            'answer'               => 'required',
            'status'               => 'required',
        ]);

        $faq = new Faq($request->all());

        if ($faq->save()) {

                $faq = Faq::find($faq->id);

                return response()->json([
                    'code'   => 201,
                    'data'   => $faq,
                    'status' => Lang::get('messages.faq_create_success'),
                ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.faq_create_fail')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function show(Faq $faq)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function edit(Faq $faq)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'user_type'            => 'required',
            'category'             => 'required',
            'question'             => 'required',
            'answer'               => 'required',
            'status'               => 'required',
        ]);


        $faq = Faq::find($request->id);
        $faq->fill($request->all());

        if ($faq->save()) {

                $faq = Faq::find($faq->id);

                return response()->json([
                    'code'   => 201,
                    'data'   => $faq,
                    'status' => Lang::get('messages.faq_update_success'),
                ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.faq_update_fail')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $faq = Faq::find($request->id);

        if ($faq->delete()) {
            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.faq_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.faq_delete_fail')], 200);
    }
}
