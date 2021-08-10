<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use File;
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
            'photo' => 'nullable|mimes:jpg,jpeg,png|max:5048',
        ]);

        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        if ($request->has('photo')) {
            $newImageName = time() . '_' . $request->photo->getClientOriginalName();
            $request->photo->move('uploads/ProfilePics', $newImageName);
            $imgaeURL = url('/uploads/ProfilePics'.'/'.$newImageName);
            DB::table('users')->where('email', $request['email'])->update([
                'photo' => $imgaeURL,
            ]);
        }
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
        $userData = User::where('email', $request['email'])->first();
	    $success['name'] = $userData->name;
	    $success['is_Admin'] = $userData->is_Admin;
        $success['photo'] = $userData->photo;
        $success['token'] = $userData->createToken('customer')->accessToken;
        return $this->SendResponse($success, 'Registered successfully');
    }

    public function login(Request $request){
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            // if ($user->is_Admin == 1) {
            //     $token = Str::random(5);
            //     DB::table('users')->where('email', $request['email'])->update([
            //         'is_verify' => $token,
            //     ]);
            //     $email = $request['email'];
            //     Mail::send('Mails.verify', ['token' => $token], function ($message) use ($email) {
            //         $message->to($email);
            //         $message->subject('Verify your email');
            //     });
            //     return $this->SendResponse('verify your email', 200);
            // }
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
		    $user = Auth::user();
		    // if($user->is_verify != 1){
			//     return $this->SendError('Verify your email', 400);
		    // }
            $success['name'] = $user->name;
	        $success['is_Admin'] = $user->is_Admin;
            $success['photo'] = $user->photo;
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
        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }
        if ($request->has('photo')) {
            $newImageName = time() . '_' . $request->photo->getClientOriginalName();
            $oldImage = substr($review->photo, strlen($request->url()));;
            File::delete(public_path($oldImage));
            $request->photo->move('uploads/ProfilePics', $newImageName);
            $imgaeURL = url('/uploads/ProfilePics'.'/'.$newImageName);
            $user->photo = $imgaeURL;
        }
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
    public function forgot(Request $request){
        $email = $request['email'];
        $validator = Validator::make($request->all(),[
            'email' => 'required | email',
        ]);
        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }
        //To check if the email is true or not
        if(User::where('email', $email)->doesntExist()){
            return $this->SendError('Email is not exist', 404);
        }
        //Generate the forgot code
        $token = Str::random(4);
        try {
            if (DB::table('password_resets')->where('email', $request['email'])->first()) {
                DB::table('password_resets')->where('email', $request['email'])->update([
                    'token' => $token,

                ]);
                $email = $request['email'];
                Mail::send('Mails.forgot', ['token' => $token], function ($message) use ($email){
                    $message->to($email);
                    $message->subject('Reset your password');
                    $message->priority(1);
                });
                return $this->SendResponse('check your email', 200);
            }
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
    public function passwordReset(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required',
            'c_password' => 'required |same:password',
        ]);
        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }
        if (User::where('email', $request['email'])->doesntExist()) {
            $this->SendError('Invalid Email', 404);
        }
        if (!DB::table('password_resets')->where('email', $request['email'])->first()) {
            return $this->SendError('Invalid email', 404);
        }
        if (!DB::table('password_resets')->where('token', $request['token'])->first()) {
            return $this->SendError('Invalid token', 404);
        }
        $user = User::where('email', $request['email'])->first();
        $user->password = Hash::make($request['password']);
        DB::table('password_resets')->where('email', $request['email'])->where('token', $request['token'])->delete();
        $user->save();
        return $this->SendResponse('User password changed successfully', 200);
    }

    public function emailVerify(Request $request){
	    $validator = Validator::make($request->all(), [
		    'email' => 'required | email',
		    'token' => 'required',
	    ]);
        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }

	    $user = User::where('email', $request['email'])->first();
	    $token = $request['token'];
	    if($user->is_verify == $token){
		    $user->is_verify = 1;
		    $user->markEmailAsVerified();
		    $user->save();
            if ($user->is_Admin == 1) {
                $success['name'] = $user->name;
	            $success['is_Admin'] = $user->is_Admin;
                $success['photo'] = $user->photo;
                $success['token'] = $user->createToken('customer')->accessToken;
		        return $this->SendResponse($success, 200);
            }
		    return $this->SendResponse('Email verified', 200);
	    }

        if ($user->is_verify == 1) {
            return $this->SendError('Email is already Verified', 400);
        }
	    return $this->SendError('Wrong token', 404);
    }

    public function resendCode(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        $email = $request['email'];
        $token = Str::random(4);
        DB::table('password_resets')->where('email', $request['email'])->update([
            'token' => $token,

        ]);
        $email = $request['email'];
        Mail::send('Mails.forgot', ['token' => $token], function ($message) use ($email){
            $message->to($email);
            $message->subject('Reset your password');
            $message->priority(1);
        });
        return $this->SendResponse('check your email', 200);
    }
}
