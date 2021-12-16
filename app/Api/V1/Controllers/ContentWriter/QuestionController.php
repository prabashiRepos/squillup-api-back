<?php

namespace App\Api\V1\Controllers\ContentWriter;

use DB;
use Auth;
use Validator;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\SelfTest;
use App\Models\PastPaper;
use App\Models\WorkSheet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Api\V1\Controllers\Common\FilesController;
use App\Models\Challenge;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-question', ['only' => ['store']]);
        $this->middleware('ability:developer,view-question', ['only' => ['index']]);
        $this->middleware('ability:developer,update-question', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-question', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($request->test_type == "self_test") $testExistsIn = "self_tests";
        elseif ($request->test_type == "work_sheet") $testExistsIn = "work_sheets";
        elseif ($request->test_type == "past_paper") $testExistsIn = "past_papers";
        elseif ($request->test_type == "challenge") $testExistsIn = "challenges";
        else $testExistsIn = "self_tests";

        if (!value($request->question_content_type)) $request->merge(["question_content_type" => "string"]);

        $question = new Question($request->all());
        $request->validate([
            'test_type' => 'required|in:self_test,work_sheet,past_paper,challenge',
            'test_referrer_id' => 'sometimes:required|exists:' . $testExistsIn . ',id',

            'answer_type' => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
        ]);
        if ($request->answer_type == "mcq") {
            $request->validate([
                'test_type' => 'required|in:self_test,work_sheet,past_paper,challenge',
                'test_referrer_id' => 'sometimes:required|exists:' . $testExistsIn . ',id',

                'answer_type' => 'required|in:mcq',

                // 'chapter_id' => 'sometimes|required|exists:chapters,id',
                'lesson_id' => 'sometimes|required|exists:lessons,id',
                'question_type' => 'required|in:text,image,table',
                'question_content_type' => 'required|in:string,image',
                'question_number' => 'sometimes|required',
                'question' => 'required|string',

                'parent_question_id' => 'sometimes|required|exists:questions,id',

                'answers' => 'nullable|array',
                'answers.*.type' => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
                'answers.*.answer' => 'required',
                'answers.*.id' => 'required',

                'correct_answers' => 'nullable|array',
                'correct_answers.*.type' => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
                'correct_answers.*.answer' => 'required',
                'correct_answers.*.id' => 'required',

                'mark' => 'nullable|integer',

                'solution' => 'sometimes:required',
                'knowledge' => 'sometimes:required',
                'video_explanation' => 'sometimes:required',
                'status' => 'required|in:publish,draft',
            ]);

            if ($request->question_content_type == "image") {
                $validator = Validator::make($request->all(), ['question' => 'validBase64Image']);
            } else $validator = Validator::make($request->all(), ['question' => 'max:10000']);
            if ($validator->fails()) return response()->json($validator->errors(), 422);

            $imageError = false;
            $imageValidator = Validator::make([], []);
            if (value($request->answers)) {
                foreach ($request->answers as $key => $answer) {
                    if ($answer['type'] == 'image') {
                        $validator = Validator::make($answer, [
                            'answer' => 'validBase64Image'
                        ]);
                        if ($validator->fails()) {
                            $imageError = true;
                            $keyName = "answers." . $key . ".answer";
                            $imageValidator->errors()->add($keyName, 'Invalid image file.');
                        }
                    }
                }
                if ($imageError) return response()->json($imageValidator->errors(), 422);
            }

            $answers = [];
            $correctAnsers = [];

            if ($request->question_content_type == "image") {
                $path = 'public/images/question/';
                $imageName = FilesController::saveBase64Images($request->question, $path);
                $question->question = $imageName;
            }


            if (value($request->answers)) {
                foreach ($request->answers as $answer) {
                    if ($answer['type'] == 'image') {
                        $path = 'public/images/question/';
                        $imageName = FilesController::saveBase64Images($answer['answer'], $path);
                        array_push($answers, [
                            'type' => 'image',
                            'id' => $answer['id'],
                            'answer' => $imageName,
                        ]);
                    } else
                        array_push($answers, ['type' => $answer['type'], 'answer' => $answer['answer'], 'id' => $answer['id']]);
                }
            }

            if (value($request->correct_answers)) {
                foreach ($request->correct_answers as $correctAnswer) {
                    if ($correctAnswer['type'] == 'image') {
                        $path = 'public/images/question/';
                        $imageKey = $correctAnswer['id'];
                        $imageName = $answers[$imageKey - 1]['answer'];
                        $imageName = FilesController::saveBase64Images($correctAnswer['answer'], $path);
                        array_push($correctAnsers, [
                            'type' => 'image',
                            'answer' => $imageName,
                            'id' => $correctAnswer['id']
                        ]);
                    } else array_push($correctAnsers, ['type' => 'string', 'answer' => $correctAnswer['answer'], 'id' => $correctAnswer['id']]);
                }
            }

            // array_push($correctAnsers, ['type' => 'string', 'answer' => $correctAnswer['answer'], 'id' => $correctAnswer['id']]);

            $question->user_id = Auth::guard()->user()->id;
            $question->correct_answers = $correctAnsers;
            $question->answers = $answers;


            $question->filling_in_the_blank = "";
            $question->filling_in_the_blank = "";
            $question->one_word_answer = "";
            $question->one_word_correct_answer = "";
        } else if ($request->answer_type == "one_word") {
            $request->validate([
                'test_type' => 'required|in:self_test,work_sheet,past_paper,challenge',
                'test_referrer_id' => 'sometimes:required|exists:' . $testExistsIn . ',id',

                'answer_type' => 'required|in:one_word',

                // 'chapter_id' => 'sometimes|required|exists:chapters,id',
                'lesson_id' => 'sometimes|required|exists:lessons,id',
                'question_type' => 'required|in:text,image,table',
                'question_content_type' => 'required|in:string,image',
                'question_number' => 'sometimes|required',
                'question' => 'required|string',

                'one_word_answer' => 'required',
                'one_word_correct_answer' => 'required',
                'parent_question_id' => 'sometimes|required|exists:questions,id',

                'mark' => 'nullable|integer',

                'solution' => 'sometimes:required',
                'video_explanation' => 'sometimes:required',
                'status' => 'required|in:publish,draft',
            ]);

            // $question = new Question($request->all());
            $question->one_word_answer = $request->one_word_answer;
            $question->one_word_correct_answer = $request->one_word_correct_answer;
            $question->filling_in_the_blank = "";
            $question->filling_in_the_blank = "";
            $question->correct_answers = [];
            $question->answers = [];
        } elseif ($request->answer_type == "filling_in_the_blank") {
            $request->validate([
                'filling_in_the_blank' => 'required',
            ]);

            $question->filling_in_the_blank = $request->filling_in_the_blank;

            $question->one_word_answer = "";
            $question->one_word_correct_answer = "";
            $question->correct_answers = [];
            $question->answers = [];
        } elseif ($request->answer_type == "matching_pair") {
            $request->validate([
                'matching_pair' => 'required',
            ]);

            $question->filling_in_the_blank = $request->filling_in_the_blank;

            $question->one_word_answer = "";
            $question->one_word_correct_answer = "";
            $question->correct_answers = [];
            $question->answers = [];
        }

        if ($question->save()) {
            if (!value($request->parent_question_id)) {
                if ($request->test_type == "self_test") $model = new SelfTest();
                elseif ($request->test_type == "work_sheet") $model = new WorkSheet();
                elseif ($request->test_type == "past_paper") $model = new PastPaper();
                elseif ($request->test_type == "challenge") $model = new Challenge();
                else $model = new SelfTest();

                $testData = $model->find($request->test_referrer_id);
                $questionsArray = $testData->questions;

                if (is_null($questionsArray)) $questionsArray = [$question->id];
                else array_push($questionsArray, $question->id);

                $testData->questions = $questionsArray;
                $testData->save();
            }
            return response()->json([
                'code'   => 201,
                'data'   => $question,
                'status' => Lang::get('messages.question_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.question_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $whereId = (value($request->id)) ? 'id = "' . $request->id . '"' : 'id <> ""';
        $whereTestType = (value($request->test_type)) ? 'test_type = "' . $request->test_type . '"' : 'test_type <> ""';
        $whereTestId = (value($request->test_id)) ? 'test_referrer_id = "' . $request->test_id . '"' : 'test_referrer_id <> ""';
        $wherePaperType = (value($request->past_paper_type)) ? 'past_paper_type = "' . $request->past_paper_type . '"' : 'id <> ""';
        $wherePaperYear = (value($request->past_paper_year)) ? 'past_paper_year = "' . $request->past_paper_year . '"' : 'id <> ""';
        $wherePaperNumber = (value($request->past_paper_number)) ? 'past_paper_number = "' . $request->past_paper_number . '"' : 'id <> ""';

        $question = Question::whereRaw($whereId)
            ->whereRaw($whereTestId)
            ->whereRaw($whereTestType)
            ->whereRaw($wherePaperType)
            ->whereRaw($wherePaperYear)
            ->whereRaw($wherePaperNumber)
            ->whereNull('parent_question_id')
            ->get();

        return response()->json($question, 201);
    }

    public function update(Request $request)
    {

        if ($request->test_type == "self_test") $testExistsIn = "self_tests";
        elseif ($request->test_type == "work_sheet") $testExistsIn = "work_sheets";
        elseif ($request->test_type == "past_paper") $testExistsIn = "past_papers";
        elseif ($request->test_type == "challenge") $testExistsIn = "challenges";
        else $testExistsIn = "self_tests";



        if (!value($request->question_content_type)) $request->merge(["question_content_type" => "string"]);
        $question = Question::find($request->id);
        $request->validate([
            'test_type' => 'required|in:self_test,work_sheet,past_paper,challenge',
            'test_referrer_id' => 'sometimes:required|exists:' . $testExistsIn . ',id',

            'answer_type' => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
        ]);

        if ($request->answer_type == 'mcq') {

            $request->validate([
                'id' => 'required|exists:questions,id',
                'test_type' => 'required|in:self_test,work_sheet,past_paper,challenge',
                'test_referrer_id' => 'sometimes:required|exists:' . $testExistsIn . ',id',

                'question_type' => 'required|in:text,image,table',
                'question_content_type' => 'required|in:string,image,url',
                'question_number' => 'sometimes|required',
                'question' => 'required|string',

                'parent_question_id' => 'sometimes|required|exists:questions,id',

                'answers' => 'nullable|array',
                'answers.*.type' => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
                'answers.*.answer' => 'required',
                'answers.*.id' => 'required',

                'correct_answers' => 'nullable|array',
                'correct_answers.*.type' => 'required|in:one_word,mcq,filling_in_the_blank,matching_pair',
                'correct_answers.*.answer' => 'required',
                'correct_answers.*.id' => 'required',

                'mark' => 'nullable|integer',

                'solution' => 'sometimes:required',
                'video_explanation' => 'sometimes:required',
                'status' => 'required|in:publish,draft',
            ]);

            if ($request->question_content_type == "image") {
                $validator = Validator::make($request->all(), ['question' => 'validBase64Image']);
            } else $validator = Validator::make($request->all(), ['question' => 'max:10000']);
            if ($validator->fails()) return response()->json($validator->errors(), 422);

            $imageError = false;
            $imageValidator = Validator::make([], []);
            if (value($request->answers)) {
                foreach ($request->answers as $key => $answer) {
                    if ($answer['type'] == 'image') {
                        $validator = Validator::make($answer, [
                            'answer' => 'validBase64Image'
                        ]);
                        if ($validator->fails()) {
                            $imageError = true;
                            $keyName = "answers." . $key . ".answer";
                            $imageValidator->errors()->add($keyName, 'Invalid image file.');
                        }
                    }
                }
                if ($imageError) return response()->json($imageValidator->errors(), 422);
            }

            $answers = [];
            $correctAnsers = [];

            if ($request->question_content_type == "image") {
                $path = 'public/images/question/';
                $imageName = FilesController::saveBase64Images($request->question, $path);
                $question->question_content_type = 'url';
                $question->question = $imageName;
            } elseif ($request->question_content_type == 'url') {
                $imageName = str_replace(asset('storage/images/question/'), "", $request->question);
                $imageName = str_replace("/", "", $imageName);
                $question->question = $imageName;
            }

            if (value($request->answers)) {
                foreach ($request->answers as $answer) {
                    if ($answer['type'] == 'image') {
                        $path = 'public/images/question/';
                        $imageName = FilesController::saveBase64Images($answer['answer'], $path);
                        array_push($answers, [
                            'type' => 'image',
                            'id' => $answer['id'],
                            'answer' => $imageName,
                        ]);
                    } elseif ($answer['type'] == 'url') {
                        $imageName = str_replace(asset('storage/images/question/'), "", $answer['answer']);
                        $imageName = str_replace("/", "", $imageName);
                        array_push($answers, [
                            'type' => 'image',
                            'id' => $answer['id'],
                            'answer' => $imageName,
                        ]);
                    } else
                        array_push($answers, ['type' => $answer['type'], 'answer' => $answer['answer'], 'id' => $answer['id']]);
                }
            }

            if (value($request->correct_answers)) {
                foreach ($request->correct_answers as $correctAnswer) {
                    if ($correctAnswer['type'] == 'image') {
                        $path = 'public/images/question/';
                        $imageKey = $correctAnswer['id'];
                        $imageName = $answers[$imageKey - 1]['answer'];
                        // $imageName = FilesController::saveBase64Images($correctAnswer['answer'], $path);
                        array_push($correctAnsers, [
                            'type' => 'image',
                            'answer' => $imageName,
                            'id' => $correctAnswer['id']
                        ]);
                    } elseif ($correctAnswer['type'] == 'url') {
                        $imageName = str_replace(asset('storage/images/question/'), "", $correctAnswer['answer']);
                        $imageName = str_replace("/", "", $imageName);
                        array_push($correctAnsers, [
                            'type' => 'image',
                            'answer' => $imageName,
                            'id' => $correctAnswer['id']
                        ]);
                    } else array_push($correctAnsers, ['type' => 'string', 'answer' => $correctAnswer['answer'], 'id' => $correctAnswer['id']]);
                }
            }

            $question->user_id = Auth::guard()->user()->id;
            $question->correct_answers = $correctAnsers;
            $question->answers = $answers;

            $question->one_word_answer = "";
            $question->one_word_correct_answer = "";
        } else if ($request->answer_type == 'one_word') {

            $request->validate([
                'id' => 'required|exists:questions,id',

                'test_type' => 'required|in:self_test,work_sheet,past_paper,challenge',
                'test_referrer_id' => 'sometimes:required|exists:' . $testExistsIn . ',id',

                'answer_type' => 'required|in:one_word',

                // 'chapter_id' => 'sometimes|required|exists:chapters,id',
                'lesson_id' => 'sometimes|required|exists:lessons,id',
                'question_type' => 'required|in:text,image,table',
                'question_content_type' => 'required|in:string,image',
                'question_number' => 'sometimes|required',
                'question' => 'required|string',

                'one_word_answer' => 'required',
                'one_word_correct_answer' => 'required',
                'parent_question_id' => 'sometimes|required|exists:questions,id',

                'mark' => 'nullable|integer',

                'solution' => 'sometimes:required',
                'video_explanation' => 'sometimes:required',
                'status' => 'required|in:publish,draft',
            ]);

            // $question = new Question($request->all());
            $question->one_word_answer = $request->one_word_answer;
            $question->one_word_correct_answer = $request->one_word_correct_answer;


            $question->correct_answers = [];
            $question->answers = [];
        } elseif ($request->answer_type == "filling_in_the_blank") {
            $request->validate([
                'filling_in_the_blank' => 'required',
            ]);

            $question->filling_in_the_blank = $request->filling_in_the_blank;

            $question->one_word_answer = "";
            $question->one_word_correct_answer = "";
            $question->correct_answers = [];
            $question->answers = [];
        } elseif ($request->answer_type == "matching_pair") {
            $request->validate([
                'matching_pair' => 'required',
            ]);

            $question->filling_in_the_blank = $request->filling_in_the_blank;

            $question->one_word_answer = "";
            $question->one_word_correct_answer = "";
            $question->correct_answers = [];
            $question->answers = [];
        }
        $question->fill($request->all());

        if ($question->save()) {
            return response()->json([
                'code'   => 201,
                'data'   => $question,
                'status' => Lang::get('messages.question_update_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.question_update_fail')], 200);
    }

    public function addMarkingScheme(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        $question = Question::find($request->question_id);
        $question->marking_scheme = $request->marking_scheme;
        if ($question->save()) {

            return response()->json([
                'code'   => 201,
                'data'   => $question,
                'status' => Lang::get('messages.marking_scheme_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.marking_scheme_create_fail')], 200);
    }

    public function addKnowledge(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        $question = Question::find($request->question_id);
        $question->knowledge = $request->knowledge;
        if ($question->save()) {

            return response()->json([
                'code'   => 201,
                'data'   => $question,
                'status' => Lang::get('messages.knowledge_create_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.knowledge_create_fail')], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:questions,id',
        ]);

        $question = Question::find($request->id);
        $needUpdateQuestionCount = is_null($question->parent_question_id) ? true : false;
        $oldQuestionData = $question;

        if ($question->delete()) {
            if ($needUpdateQuestionCount) {
                if ($oldQuestionData->test_type == "self_test") $model = new SelfTest();
                elseif ($oldQuestionData->test_type == "work_sheet") $model = new WorkSheet();
                elseif ($oldQuestionData->test_type == "past_paper") $model = new PastPaper();
                elseif ($oldQuestionData->test_type == "challenge") $model = new Challenge();
                else $model = new SelfTest();

                $testData = $model->find($oldQuestionData->test_referrer_id);
                $questionsArray = $testData->questions;

                if (($key = array_search($request->id, $questionsArray)) !== false) {
                    unset($questionsArray[$key]);
                }

                $testData->questions = array_values($questionsArray);
                $testData->save();
            }

            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.question_delete_success'),
            ], 201);
        } else return response()->json(['code' => 200, 'status' => Lang::get('messages.question_delete_fail')], 200);
    }
}
