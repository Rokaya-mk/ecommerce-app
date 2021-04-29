<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

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
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $input['password'],
            'shippingAddress' => $request->shippingAddress,
            // 'photo' => ,
        ]);
        $success['name'] = $user->name;
        $success['token'] = $user->createToken('customer')->accessToken;
        return $this->SendResponse($success, 'Customer registered successfully');
    }

    public function login(Request $request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['name'] = $user->name;
            $success['token'] = $user->createToken('customer')->accessToken;
            return $this->SendResponse($success, 'Customer logged in successfully');
        }else{
            return $this->SendError('Unauthorised', ['error', 'Unauthorised']);
        }
    }

    //Owner auth functions
    public function OwnerRegister(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required | email',
            'password' => 'required',
            'c_password' => 'required | same:password'
        ]);

        if($validator->fails()){
            return $this->SendError('Validate Error', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $success['name'] = $user->name;
        //the brackets is to give the owner all permission
        $success['token'] = $user->createToken('owner',['*'])->accessToken;
        return $this->SendResponse($success, 'Owner registered successfully');
    }

    public function OwnerLogin(Request $request){
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['name'] = $user->name;
            $success['token'] = $user->createToken('owner', ['*'])->accessToken;
            return $this->SendResponse($success, 'Owner logged in successfully');
        }else{
            return $this->SendError('Unauthorised', ['error', 'Unauthorised']);
        }
    }
    //To change the user informations
    public function updateUserInformation(Request $request, $id){
        $user = User::find($id);
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'shippingAddress' => 'required',
        ]);
        if ($id != Auth::id()) {
            return $this->SendError('You do not have permission', 500);
        }
        $user->name = $request->name;
        $user->shippingAddress = $request->shippingAddress;
        $user->save();
        return $this->SendResponse('Information Updated Successfully', Auth::id());
    }
}
