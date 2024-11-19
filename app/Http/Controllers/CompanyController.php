<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        return response()->json($this->companyService->registerCompany($user, $validator->validated()), 201);
    }

    public function getCompany(Request $request)
    {
        $user = $request->user();
        return response()->json($this->companyService->getCompany($user));
    }

    public function inviteCompanyUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $email = $validator->validated()['email'];
        return response()->json(['message' => $this->companyService->sendInviteEmail($email)]);
    }
}
