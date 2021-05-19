<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\FAQ;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class FAQController extends BaseController
{
    public function index(){
        try {
            $FAQs = FAQ::all();
            if($FAQs->count() == 0){
                return $this->SendError('There is no questions');
            }
            return $this->SendResponse($FAQs, 'FAQs recived succefully');
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function store(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'question_ar' => 'required|unique:f_a_q_s,question_ar',
                'question_en' => 'required|unique:f_a_q_s,question_en',
                'answer_ar' => 'required',
                'answer_en' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->SendError('Validate Error', $validator->errors());
            }
            $user = Auth::user();
            if ($user->is_Admin == 1) {
                $FAQ = FAQ::create($request->all());
                return $this->SendResponse($FAQ, 'Question added succefully');
            }else{
                return $this->SendError('You do not have right to add a question');
            }
        } catch (\Throwable $th) {
            return $this->SendError('something went wrong', $th->getMessage());
        }
    }

    public function update(Request $request, $id){
        try {
            $validator = Validator::make($request->all(), [
                'question_ar' => 'required|unique:f_a_q_s,question_ar',
                'question_en' => 'required|unique:f_a_q_s,question_en',
                'answer_ar' => 'required',
                'answer_en' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->SendError('Validate Error', $validator->errors());
            }
            $user = Auth::user();
            if ($user->is_Admin == 1) {
                $FAQ = FAQ::find($id);
                if (is_null($FAQ)) {
                    return $this->SendError('Question is not found');
                }
                $FAQ->question_ar = $request->question_ar;
                $FAQ->question_en = $request->question_en;
                $FAQ->answer_ar = $request->answer_ar;
                $FAQ->answer_en = $request->answer_en;
                $FAQ->save();
                return $this->SendResponse($FAQ, 'Question updated succefully');
            }else{
                return $this->SendError('You do not have rights to update the question');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function deleteQuestion($id){
        try
        {
            $user = Auth::user();
            if($user->is_Admin == 1)
            {
                $FAQ = FAQ::find($id);
                if(is_null($FAQ)){
                    return $this->SendError('Question is not found');
                }
                $FAQ->delete();
                return $this->SendResponse($FAQ, 'Question deleted successfully');
            }
            else
                return $this->SendError('You do not have rights to delete this question');
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }

    }
}
