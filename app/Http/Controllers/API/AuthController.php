<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Mail;
use Illuminate\Mail\Mailable;
class AuthController extends BaseController
{
    //Cutomers auth functions
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required | email',
            'password' => 'required',
            'c_password' => 'required | same:password',
            'shippingAddress' => 'required',
        ]);

        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
	//send the verify code
	$token = Str::random(5);
	try{
		DB::table('users')->where('email', $request['email'])->update([
			'is_verify' => $token,
		]);
		$email = $request['email'];
		Mail::send('Mails.verify', ['token' => $token], function ($message) use ($email) {
			$message->to($email);
			$message->subject('Verify your email');
		});
	}catch(\Exception $exception){
		return $this->SendError($exception->getMessage(), 400);
	}
	$success['name'] = $user->name;
	$success['is_Admin'] = $user->is_Admin;
        $success['token'] = $user->createToken('customer')->accessToken;
        return $this->SendResponse($success, 'Registered successfully');
    }

    public function login(Request $request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['name'] = $user->name;
	    $success['is_Admin'] = $user->is_Admin;
            $success['token'] = $user->createToken('customer')->accessToken;
            return $this->SendResponse($success, 'Customer logged in successfully');
        }else{
            return $this->SendError('Unauthorised', ['error', 'Unauthorised']);
        }
    }
   //To change the user informations
    public function updateUserInformation(Request $request){
	$user = Auth::user();
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'shippingAddress' => 'required',
        ]);
	
	$user->name = $request->name;
        $user->shippingAddress = $request->shippingAddress;
        $user->save();
        return $this->SendResponse('Information Updated Successfully', 200);
    }

    //logout
    public function logout(Request $request){
        if(auth()->check()){
            Auth::user()->token()->revoke();
            return $this->SendResponse('User logout successfully', 200);
        }else{
            return $this->SendError('User doesn\'t logged in', 404);
        }
    }

    //forgot password
    public function Forgot(Request $request){
        $email = $request['email'];
        $validate = Validator::make($request->all(),[
            'email' => 'required | email',
        ]);
        //To check if the email is true or not
        if(User::where('email', $email)->doesntExist()){
            return $this->SendError('Email is not exist', 404);
        }
        //Generate the forgot code
        $token = Str::random(4);
        try {
            //To add the token to th db to check it later
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token,
            ]);
            //Send the code to the email
            Mail::send('Mails.forgot', ['token' => $token], function ($message) use ($email){
                $message->to($email);
                $message->subject('Reset your password');
                $message->priority(1);
            });
            return $this->SendResponse('check your email', 200);
        } catch (\Exception $exception) {
            return $this->SendError($exception->getMessage(), 400);
        }
    }
    public function PasswordReset(Request $request){
        $validate = Validator::make($request->all(),[
            'token' => 'required',
            'password' => 'required',
            'c_password' => 'required |same:password',
        ]);

        $token = $request['token'];
        if (!$passwordResets = DB::table('password_resets')->where('token', $token)->first()) {
            return $this->SendError('Invalid token', 404);
        }
        if (!$user = User::where('email', $passwordResets->email)->first()) {
            return $this->SendError('User does not exist', 404);
        }
        $user->password = Hash::make($request['password']);
        $user->save();
<<<<<<< HEAD
        return $this->SendResponse('User password changed successfully', 200);
    }
    public function Emailverify(Request $request){
	    $user = Auth::user();
	    $validate = Validator::make($request->all(), [
		    'token' => 'required',
	    ]);
	    $token = $request['token'];
	    if($user->is_verify == $token){
		    $user->is_verify = 1;
		    $user->save();
		    return $this->SendResponse('Email verified', 200);
	    }
	    return $this->SendError('Token wrong', 404);
=======
        return $this->SendResponse('Information Updated Successfully', 200);
>>>>>>> 9c827f8850a5d220f5fce4e3a0419d474b0febcb
    }
}
