<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\MyEvent;
use App\Events\NotifyEvent;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['prefix' => 'auth', 'middleware' => ['cors', 'throttle:60,10']], function($api) {
        $api->post('signup', 'App\Api\V1\Controllers\Auth\SignUpController@register');
        $api->post('login', 'App\Api\V1\Controllers\Auth\LoginController@login')->name('login');

        $api->post('recovery', 'App\Api\V1\Controllers\Auth\ForgotPasswordController@sendResetEmail');
        $api->post('checkResetPasswordToken', 'App\Api\V1\Controllers\Auth\ResetPasswordController@checkResetPasswordToken');
        $api->post('reset', 'App\Api\V1\Controllers\Auth\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\Api\V1\Controllers\Auth\LogoutController@logout');
        $api->post('refresh', 'App\Api\V1\Controllers\Auth\RefreshController@refresh');

        $api->post('test', 'App\Api\V1\Controllers\TestController@test');

        $api->post('viewPlans', 'App\Api\V1\Controllers\Common\PublicController@viewPlans');
        // $api->get('stripe_success', 'App\Api\V1\Controllers\Parent\PlanController@stripeSuccess');
        // $api->get('stripe_fail', 'App\Api\V1\Controllers\Parent\PlanController@stripeFail');
    });

    $api->group(['middleware' => ['auth:sanctum', 'cors']], function($api) {
        $api->post('viewProfile', 'App\Api\V1\Controllers\Auth\UserController@viewProfile');
        $api->post('updateProfile', 'App\Api\V1\Controllers\Auth\UserController@updateProfile');
        $api->post('uploadProfileImage', 'App\Api\V1\Controllers\Auth\UserController@uploadProfileImage');
        $api->post('changePassword', 'App\Api\V1\Controllers\Auth\UserController@changePassword');

        $api->group(['middleware' => ['role:developer|superadmin']], function($api) {

        });

        $api->group(['middleware' => []], function($api) {
            $api->post('createRole', 'App\Api\V1\Controllers\Developer\RoleController@store');
            $api->post('viewRole', 'App\Api\V1\Controllers\Developer\RoleController@index');
            $api->post('updateRole', 'App\Api\V1\Controllers\Developer\RoleController@update');
            $api->post('deleteRole', 'App\Api\V1\Controllers\Developer\RoleController@delete');
            $api->post('assignRole', 'App\Api\V1\Controllers\Developer\RoleController@assignRole');
            $api->post('detachRole', 'App\Api\V1\Controllers\Developer\RoleController@detachRole');
            $api->post('assignDetachRole', 'App\Api\V1\Controllers\Developer\RoleController@assignDetachRole');

            $api->post('createPermission', 'App\Api\V1\Controllers\Developer\PermissionController@store');
            $api->post('viewPermission', 'App\Api\V1\Controllers\Developer\PermissionController@index');
            $api->post('updatePermission', 'App\Api\V1\Controllers\Developer\PermissionController@update');
            $api->post('deletePermission', 'App\Api\V1\Controllers\Developer\PermissionController@delete');
            $api->post('assignPermission', 'App\Api\V1\Controllers\Developer\PermissionController@assignPermission');
            $api->post('detechPermission', 'App\Api\V1\Controllers\Developer\PermissionController@detachPermission');
            $api->post('assignDetachPermission', 'App\Api\V1\Controllers\Developer\PermissionController@assignDetachPermission');

            $api->post('createPlan', 'App\Api\V1\Controllers\SuperAdmin\PlanController@store');
            $api->post('viewPlan', 'App\Api\V1\Controllers\SuperAdmin\PlanController@index');
            $api->post('updatePlan', 'App\Api\V1\Controllers\SuperAdmin\PlanController@update');
            $api->post('deletePlan', 'App\Api\V1\Controllers\SuperAdmin\PlanController@delete');

            $api->post('createUser', 'App\Api\V1\Controllers\SuperAdmin\UserController@store');
            $api->post('viewUser', 'App\Api\V1\Controllers\SuperAdmin\UserController@index');
            $api->post('updateUser', 'App\Api\V1\Controllers\SuperAdmin\UserController@update');
            $api->post('deleteUser', 'App\Api\V1\Controllers\SuperAdmin\UserController@delete');

            $api->post('createExamBoard', 'App\Api\V1\Controllers\SuperAdmin\ExamBoardController@store');
            $api->post('viewExamBoard', 'App\Api\V1\Controllers\SuperAdmin\ExamBoardController@index');
            $api->post('updateExamBoard', 'App\Api\V1\Controllers\SuperAdmin\ExamBoardController@update');
            $api->post('deleteExamBoard', 'App\Api\V1\Controllers\SuperAdmin\ExamBoardController@delete');

            $api->post('createKeyStage', 'App\Api\V1\Controllers\SuperAdmin\KeyStageController@store');
            $api->post('viewKeyStage', 'App\Api\V1\Controllers\SuperAdmin\KeyStageController@index');
            $api->post('updateKeyStage', 'App\Api\V1\Controllers\SuperAdmin\KeyStageController@update');
            $api->post('deleteKeyStage', 'App\Api\V1\Controllers\SuperAdmin\KeyStageController@delete');

            $api->post('createYear', 'App\Api\V1\Controllers\SuperAdmin\YearController@store');
            $api->post('viewYear', 'App\Api\V1\Controllers\SuperAdmin\YearController@index');
            $api->post('updateYear', 'App\Api\V1\Controllers\SuperAdmin\YearController@update');
            $api->post('deleteYear', 'App\Api\V1\Controllers\SuperAdmin\YearController@delete');

            $api->post('createSubject', 'App\Api\V1\Controllers\SuperAdmin\SubjectController@store');
            $api->post('viewSubject', 'App\Api\V1\Controllers\SuperAdmin\SubjectController@index');
            $api->post('updateSubject', 'App\Api\V1\Controllers\SuperAdmin\SubjectController@update');
            $api->post('deleteSubject', 'App\Api\V1\Controllers\SuperAdmin\SubjectController@delete');

            $api->post('createChapter', 'App\Api\V1\Controllers\SuperAdmin\ChapterController@store');
            $api->post('viewChapter', 'App\Api\V1\Controllers\SuperAdmin\ChapterController@index');
            $api->post('updateChapter', 'App\Api\V1\Controllers\SuperAdmin\ChapterController@update');
            $api->post('deleteChapter', 'App\Api\V1\Controllers\SuperAdmin\ChapterController@delete');

            $api->post('createLesson', 'App\Api\V1\Controllers\SuperAdmin\LessonController@store');
            $api->post('viewLesson', 'App\Api\V1\Controllers\SuperAdmin\LessonController@index');
            $api->post('updateLesson', 'App\Api\V1\Controllers\SuperAdmin\LessonController@update');
            $api->post('deleteLesson', 'App\Api\V1\Controllers\SuperAdmin\LessonController@delete');

            $api->post('createVideo', 'App\Api\V1\Controllers\Common\VimeoController@createVideo');

            $api->post('createQa', 'App\Api\V1\Controllers\SuperAdmin\QuestionAndAnswerController@store');
            $api->post('viewQa', 'App\Api\V1\Controllers\SuperAdmin\QuestionAndAnswerController@index');
            $api->post('updateQa', 'App\Api\V1\Controllers\SuperAdmin\QuestionAndAnswerController@update');
            $api->post('deleteQa', 'App\Api\V1\Controllers\SuperAdmin\QuestionAndAnswerController@delete');

            $api->post('createSelfTest', 'App\Api\V1\Controllers\ContentWriter\SelfTestController@store');
            $api->post('viewSelfTest', 'App\Api\V1\Controllers\ContentWriter\SelfTestController@index');
            $api->post('updateSelfTest', 'App\Api\V1\Controllers\ContentWriter\SelfTestController@update');
            $api->post('deleteSelfTest', 'App\Api\V1\Controllers\ContentWriter\SelfTestController@delete');
            $api->post('selfDemoQuestion', 'App\Api\V1\Controllers\ContentWriter\SelfTestController@selfDemoQuestion');
            $api->post('addExplanationVideo', 'App\Api\V1\Controllers\ContentWriter\SelfTestController@addExplanationVideo');

            $api->post('createWorkSheet', 'App\Api\V1\Controllers\ContentWriter\WorkSheetController@store');
            $api->post('viewWorkSheet', 'App\Api\V1\Controllers\ContentWriter\WorkSheetController@index');
            $api->post('updateWorkSheet', 'App\Api\V1\Controllers\ContentWriter\WorkSheetController@update');
            $api->post('deleteWorkSheet', 'App\Api\V1\Controllers\ContentWriter\WorkSheetController@delete');
            $api->post('workDemoQuestion', 'App\Api\V1\Controllers\ContentWriter\WorkSheetController@workDemoQuestion');

            $api->post('createQuestion', 'App\Api\V1\Controllers\ContentWriter\QuestionController@store');
            $api->post('viewQuestion', 'App\Api\V1\Controllers\ContentWriter\QuestionController@index');
            $api->post('updateQuestion', 'App\Api\V1\Controllers\ContentWriter\QuestionController@update');
            $api->post('deleteQuestion', 'App\Api\V1\Controllers\ContentWriter\QuestionController@delete');
            $api->post('addMarkingScheme', 'App\Api\V1\Controllers\ContentWriter\QuestionController@addMarkingScheme');
            $api->post('addKnowledge', 'App\Api\V1\Controllers\ContentWriter\QuestionController@addKnowledge');

            $api->post('createPastPaper', 'App\Api\V1\Controllers\ContentWriter\PastPaperController@store');
            $api->post('viewPastPaper', 'App\Api\V1\Controllers\ContentWriter\PastPaperController@index');
            $api->post('updatePastPaper', 'App\Api\V1\Controllers\ContentWriter\PastPaperController@update');
            $api->post('deletePastPaper', 'App\Api\V1\Controllers\ContentWriter\PastPaperController@delete');
            $api->post('uploadPastFile', 'App\Api\V1\Controllers\ContentWriter\PastPaperController@uploadPastFile');

            $api->post('createVimeoVideo', 'App\Api\V1\Controllers\Common\VimeoController@createVimeoVideo');

            $api->post('buyPlan', 'App\Api\V1\Controllers\Parent\PlanController@buyPlan');
            $api->post('viewMyPlans', 'App\Api\V1\Controllers\Parent\PlanController@viewMyPlans');
            $api->post('viewMyInvoices', 'App\Api\V1\Controllers\Parent\PlanController@viewMyInvoices');
            $api->post('changePlan', 'App\Api\V1\Controllers\Parent\PlanController@changePlan');
            $api->post('cancelPlan', 'App\Api\V1\Controllers\Parent\PlanController@cancelPlan');

            $api->post('contact', 'App\Api\V1\Controllers\SuperAdmin\HelpSupportController@contact');

            $api->post('createStudent', 'App\Api\V1\Controllers\Parent\StudentController@store');
            $api->post('viewStudent', 'App\Api\V1\Controllers\Parent\StudentController@index');
            $api->post('updateStudent', 'App\Api\V1\Controllers\Parent\StudentController@update');
            $api->post('deleteStudent', 'App\Api\V1\Controllers\Parent\StudentController@delete');

            //student_by_parent
            $api->post('createStudentByParent', 'App\Api\V1\Controllers\ContentWriter\StudentDetailController@store');
            $api->post('viewStudentByParent', 'App\Api\V1\Controllers\ContentWriter\StudentDetailController@index');
            $api->post('updateStudentByParent', 'App\Api\V1\Controllers\ContentWriter\StudentDetailController@update');
            $api->post('deleteStudentByParent', 'App\Api\V1\Controllers\ContentWriter\StudentDetailController@delete');

            //parent
            $api->post('createParent', 'App\Api\V1\Controllers\ContentWriter\ParentDetailController@store');
            $api->post('viewParent', 'App\Api\V1\Controllers\ContentWriter\ParentDetailController@index');
            $api->post('updateParent', 'App\Api\V1\Controllers\ContentWriter\ParentDetailController@update');
            $api->post('deleteParent', 'App\Api\V1\Controllers\ContentWriter\ParentDetailController@delete');

            //submissions
            $api->post('createSubmission', 'App\Api\V1\Controllers\ContentWriter\SubmissionController@store');
            $api->post('viewSubmission', 'App\Api\V1\Controllers\ContentWriter\SubmissionController@index');
            $api->post('deleteSubmission', 'App\Api\V1\Controllers\ContentWriter\SubmissionController@delete');
            $api->post('updateUserSubmission', 'App\Api\V1\Controllers\ContentWriter\SubmissionController@assignUser');

            //challenges
            $api->post('createChallenges', 'App\Api\V1\Controllers\ContentWriter\ChallengeController@store');
            $api->post('viewChallenges', 'App\Api\V1\Controllers\ContentWriter\ChallengeController@index');
            $api->post('updateChallenges', 'App\Api\V1\Controllers\ContentWriter\ChallengeController@update');
            $api->post('deleteChallenges', 'App\Api\V1\Controllers\ContentWriter\ChallengeController@delete');

            //revision
            $api->post('createRevision', 'App\Api\V1\Controllers\ContentWriter\RevisionController@store');
            $api->post('viewRevision', 'App\Api\V1\Controllers\ContentWriter\RevisionController@index');
            $api->post('updateRevision', 'App\Api\V1\Controllers\ContentWriter\RevisionController@update');
            $api->post('deleteRevision', 'App\Api\V1\Controllers\ContentWriter\RevisionController@delete');

            //faq
            $api->post('createFaq', 'App\Api\V1\Controllers\ContentWriter\FaqController@store');
            $api->post('viewFaq', 'App\Api\V1\Controllers\ContentWriter\FaqController@index');
            $api->post('updateFaq', 'App\Api\V1\Controllers\ContentWriter\FaqController@update');
            $api->post('deleteFaq', 'App\Api\V1\Controllers\ContentWriter\FaqController@delete');

            //qna
            $api->post('createQnA', 'App\Api\V1\Controllers\SuperAdmin\QnAController@store');
            $api->post('viewQnA', 'App\Api\V1\Controllers\SuperAdmin\QnAController@index');
            $api->post('updateQnA', 'App\Api\V1\Controllers\SuperAdmin\QnAController@update');
            $api->post('deleteQnA', 'App\Api\V1\Controllers\SuperAdmin\QnAController@delete');
            $api->post('createReply', 'App\Api\V1\Controllers\SuperAdmin\QnAController@createReply');
            $api->post('viewQnAReply', 'App\Api\V1\Controllers\SuperAdmin\QnAController@viewQnAReply');
            $api->post('assignQnAUser', 'App\Api\V1\Controllers\SuperAdmin\QnAController@assignQnAUser');

            //user_limit
            $api->post('createUserLimit', 'App\Api\V1\Controllers\SuperAdmin\UserAssignLimitController@store');
            $api->post('viewUserLimit', 'App\Api\V1\Controllers\SuperAdmin\UserAssignLimitController@index');
            $api->post('updateUserLimit', 'App\Api\V1\Controllers\SuperAdmin\UserAssignLimitController@update');

            //category
            $api->post('createCategory', 'App\Api\V1\Controllers\SuperAdmin\CategoryController@store');
            $api->post('viewCategory', 'App\Api\V1\Controllers\SuperAdmin\CategoryController@index');

            //category
            $api->post('createInquiry', 'App\Api\V1\Controllers\SuperAdmin\InquiryController@store');
            $api->post('viewInquiry', 'App\Api\V1\Controllers\SuperAdmin\InquiryController@index');
            $api->post('sendMail', 'App\Api\V1\Controllers\SuperAdmin\InquiryController@sendMail');
            $api->post('sendReply', 'App\Api\V1\Controllers\SuperAdmin\InquiryController@sendReply');
            $api->post('viewReply', 'App\Api\V1\Controllers\SuperAdmin\InquiryController@viewReply');

            //activity_log
            $api->post('viewActivityLog', 'App\Api\V1\Controllers\SuperAdmin\ActivityController@index');

            //backup
            $api->post('createBackUp', 'App\Api\V1\Controllers\SuperAdmin\BackupController@store');
            $api->post('viewBackUp', 'App\Api\V1\Controllers\SuperAdmin\BackupController@index');
            $api->post('downloadBackUp/{file_name}', 'App\Api\V1\Controllers\SuperAdmin\BackupController@download');


        });

        $api->post('notify', function () {
            event(new  NotifyEvent('nofification'));
        });


        // $api->get('protected', function() {
        //     return response()->json([
        //         'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
        //     ]);
        // });

        // $api->get('refresh', [
        //     'middleware' => 'auth:sanctum',
        //     function() {
        //         return response()->json([
        //             'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
        //         ]);
        //     }
        // ]);
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });

    
    $api->group(['prefix' => 'parentauth', 'middleware' => ['cors', 'throttle:60,10']], function($api) {

        $api->post('signup', 'App\Api\V1\Controllers\ParentAuth\SignUpController@register');
        //$api->post('viewPlans', 'App\Api\V1\Controllers\PlansController@viewPlans');
        $api->post('requestOtp', 'App\Api\V1\Controllers\ParentAuth\SignUpController@requestOtp');
        $api->post('verifyOtp', 'App\Api\V1\Controllers\ParentAuth\SignUpController@verifyOtp');
        $api->post('login', 'App\Api\V1\Controllers\ParentAuth\LoginController@parentLogin')->name('parentLogin');
        $api->post('logout', 'App\Api\V1\Controllers\ParentAuth\LogoutController@logout');

        $api->post('recovery', 'App\Api\V1\Controllers\ParentAuth\ForgotPasswordController@sendResetEmail');
        $api->post('checkResetPasswordToken', 'App\Api\V1\Controllers\ParentAuth\ResetPasswordController@checkResetPasswordToken');
        $api->post('reset', 'App\Api\V1\Controllers\ParentAuth\ResetPasswordController@resetPassword');

    });
});
