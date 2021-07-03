<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\API\BaseController as BaseController;

class SendEmailController extends BaseController
{
    public function sendEmailForAllUsers(Request $request){
        try {
            $validate = Validator::make($request->all(), [
                'subject' => 'required',
                'message' => 'required',
            ]);
            // Check the validation
            if ($validate->fails()) {
                return $this->SendError('Validate error', $validate->errors());
            }
            // Check the user permission
            $admin = Auth::user();
            if ($admin->is_Admin != 1) {
                return $this->sendError('You do not have permission to access this.');
            }
            // Get all verified users email
            $users = User::where('accept_email', 1)->get(['email']);
            $topic = $request['message'];
            $sub = $request['subject'];
            // Send the email to all users
            foreach ($users as $user) {
                Mail::send('Mails.sendEmail', ['topic' => $topic], function ($message) use ($user, $sub) {
                    $message->cc($user['email']);
                    $message->subject($sub);
                });
            }
            return $this->SendResponse('Email sent successfully for users who accept email', 200);
        } catch (\Throwable $th) {
            return $this->sendError('Something went wrong', $th->getMessage());
        }
    }

    public function sendEmailForSpecificUsers(Request $request){
	    try {
            $validate = Validator::make($request->all(), [
                'email' => 'required',
                'subject' => 'required',
                'message' => 'required'
            ]);
            $admin = Auth::user();
            if($admin->is_Admin != 1){
                return $this->sendError('You do not have permission to send emails');
            }
            if($validate->fails()){
                return $this->sendError('Validate Error', $validate->errors());
            }
            $sub = $request['subject'];
            $topic = $request['message'];
            foreach ($request['email'] as $user) {
                $userCheck = User::where('email', $user)->get();
                if ($userCheck->contains('accept_email', 1)) {
                    Mail::send('Mails.sendEmail', ['topic' => $topic], function ($message) use ($user, $sub) {
                        $message->cc($user);
                        $message->subject($sub);
                    });
                }
            }
            return $this->SendResponse("Email sent successfully for users who accept email", 200);
        } catch (\Throwable $th) {
            return $this->sendError('Something went wrong', $th->getMessage());
        }
    }
}
