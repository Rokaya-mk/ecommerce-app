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
            $user = Auth::user();
            if ($user->is_Admin != 1) {
                return $this->sendError('You do not have permission to access this.');
            }
            // Get all verified users email
            $users = User::where('is_verify', 1)->get(['email']);
            $topic = $request['message'];
            $sub = $request['subject'];
            // Send the email to all users
            foreach ($users as $user) {
                Mail::send('Mails.sendEmail', ['topic' => $topic], function ($message) use ($user, $sub) {
                    $message->cc($user['email']);
                    $message->subject($sub);
                });
            }
            return $this->SendResponse('Email sent successfully', 200);
        } catch (\Throwable $th) {
            return $this->sendError('Something went wrong', $th->getMessage());
        }
    }
}
