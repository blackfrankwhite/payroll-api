<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyUserRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail; 

class CompanyController extends Controller
{
    public function __construct(CompanyRepository $companyRepository, CompanyUserRepository $companyUserRepository, EmployeeRepository $employeeRepository)
    {
        $this->companyRepository = $companyRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user();
        $companyData = $request->all();

        return $this->companyRepository->createCompany($user, $companyData);
    }

    public function getCompany(Request $request)
    {
        $user = $request->user();

        return $this->companyRepository->getCompany($user);
    }

    public function addEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees',
            'phone' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user();
        $employeeData = $request->all();

        return $this->employeeRepository->addEmployee($user, $employeeData);
    }

    public function getEmployees(Request $request)
    {
        $user = $request->user();

        return $this->employeeRepository->getEmployees($user);
    }

    public function updateEmployee(Request $request, $employeeID)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user();
        $employeeData = $request->all();

        return $this->employeeRepository->updateEmployee($user, $employeeID, $employeeData);
    }

    public function deleteEmployee(Request $request, $employeeID)
    {
        $user = $request->user();

        return $this->employeeRepository->deleteEmployee($user, $employeeID);
    }

    public function getEmployeeByID(Request $request, $employeeID)
    {
        $user = $request->user();

        return $this->employeeRepository->getEmployeeByID($user, $employeeID);
    }

    public function inviteCompanyUser(Request $request)
    {
        Mail::to('gugaxachvani@gmail.com')->send(new TestEmail());

        return 'Test email sent!';
    }
}
