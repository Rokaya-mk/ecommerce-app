<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Owner_Communication_Info;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class OwnerCommunicationInfoController extends BaseController
{

    public function index()
    {
        try {
            $OCI = Owner_Communication_Info::all();
            if ($OCI->count() == 0) {
                return $this->SendError('There is no data');
            }
            return $this->SendResponse($OCI, 'Data retrived successfully');
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'column_name' => 'required|unique:owner__communication__infos',
                'value' => 'required',
            ]);

            if ($validate->fails()) {
                return $this->SendError('Validate error', $validate->error());
            }
            $user = Auth::user();
            if ($user->is_Admin == 1) {
                $OCI = Owner_Communication_Info::create($request->all());
                return $this->SendResponse($OCI, 'Data added successfully');
            }else{
                return $this->SendError('You do not have rights to add data');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $OCI = Owner_Communication_Info::find($id);
            if (is_null($OCI)) {
                return $this->SendError('Data not found');
            }
            return $this->SendResponse($OCI, 'Data retrived successfully');
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'column_name' => 'required',
                'value' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->SendError('validate error', $validator->errors());
            }
            $user = Auth::user();
            if ($user->is_Admin == 1) {
                $OCI = Owner_Communication_Info::find($id);
                if (is_null($OCI)) {
                    return $this->SendError('Data not found');
                }
                $OCI->column_name = $request->column_name;
                $OCI->value = $request->value;
                $OCI->save();
                return $this->SendResponse($OCI, 'Data updated successfully');
            }else{
                return $this->SendError('You do not have rights to update data');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $user = Auth::user();
            if ($user->is_Admin == 1) {
                $OCI = Owner_Communication_Info::find($id);
                if (is_null($OCI)) {
                    return $this->SendError('Data not found');
                }
                $OCI->delete();
                return $this->SendResponse($OCI, 'Data deleted successfully');
            }else{
                return $this->SendError('You do not have right to delete data');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Something went wrong', $th->getMessage());
        }
    }
}
