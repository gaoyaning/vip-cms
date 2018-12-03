<?php
namespace  App\Http\Controllers\Vip;

use Illuminate\Http\Request;
use App\Models\CustomerService;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function getQuestions(Request $req) {
        $params = $req->params;
        try {
            $values = CustomerService::getQuestions();
        } catch (\Exception $e) {
            \Log::error('------ getQuestions ------', [$e->getCode(), $e->getMessage()]);
            ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        return ResponseService::returnArr($values); 
    } 

    public function getQuestion(Request $req) {
        $params = $req->params;
        try {
            $values = CustomerService::getQuestionDetail($params);
        } catch (\Exception $e) {
            \Log::error('------ getQuestion ------', [$e->getCode(), $e->getMessage()]);
            ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        return ResponseService::returnArr($values); 
    } 
}
