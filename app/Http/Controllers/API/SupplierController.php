<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;

use Validator;

class SupplierController extends BaseController
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::all();
    
        return $this->sendResponse(SupplierResource::collection($suppliers), 'Suppliers retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
   
        $supplier = Supplier::create($input);
   
        return $this->sendResponse(new SupplierResource($supplier), 'Supplier created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $supplier = Supplier::find($id);
  
        if (is_null($supplier)) {
            return $this->sendError('Supplier not found.');
        }
   
        return $this->sendResponse(new SupplierResource($supplier), 'Supplier retrieved successfully.');
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
   
        $supplier->name = $input['name'];
        $supplier->address = $input['address'];
        $supplier->phone = $input['phone'];
        $supplier->email = $input['email'];
        $supplier->save();
   
        return $this->sendResponse(new SupplierResource($supplier), 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return $this->sendResponse([], 'Supplier deleted successfully.');
    }
}
