<?php 

namespace App\Repositories;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Models\Employee;

class CompanyRepository
{
    public function createCompany($user, $companyData)
    {
        if (!$this->canRegisterCompany($user)) {
            return response()->json(['error' => 'User already has a company'], 422);
        }

        \DB::beginTransaction();

        try {
            $company = Company::create($companyData);

            CompanyUser::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => 'superadmin',
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Failed to create company'], 500);
        }

        return response()->json([
            'company' => $company,
        ], 201);
    }

    public function getCompany($user)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if (!$companyUser) {
            return response()->json(['error' => 'User does not have a company'], 422);
        }

        $company = Company::find($companyUser->company_id);

        return response()->json([
            'company' => $company,
        ], 200);
    }

    private function canRegisterCompany($user)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if ($companyUser) {
            return false;
        }

        return true;
    }

    public function addEmployee($user, $employeeData)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if (!$companyUser) {
            return response()->json(['error' => 'User does not have a company'], 422);
        }

        $employeeData['company_id'] = $companyUser->company_id;

        $employee = Employee::create($employeeData);

        return response()->json([
            'employee' => $employee,
        ], 201);
    }

    public function getEmployees($user)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if (!$companyUser) {
            return response()->json(['error' => 'User does not have a company'], 422);
        }

        $employees = Employee::where('company_id', $companyUser->company_id)->get();

        return response()->json([
            'employees' => $employees,
        ], 200);
    }

    public function updateEmployee($user, $employeeID, $employeeData)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if (!$companyUser) {
            return response()->json(['error' => 'User does not have a company'], 422);
        }

        $employee = Employee::where('company_id', $companyUser->company_id)
            ->where('id', $employeeID)
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $employee->update($employeeData);

        return response()->json([
            'employee' => $employee,
        ], 200);
    }

    public function deleteEmployee($user, $employeeID)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if (!$companyUser) {
            return response()->json(['error' => 'User does not have a company'], 422);
        }

        $employee = Employee::where('company_id', $companyUser->company_id)
            ->where('id', $employeeID)
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully',
        ], 200);
    }

    public function getEmployeeByID($user, $employeeID)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();

        if (!$companyUser) {
            return response()->json(['error' => 'User does not have a company'], 422);
        }

        $employee = Employee::where('company_id', $companyUser->company_id)
            ->where('id', $employeeID)
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json([
            'employee' => $employee,
        ], 200);
    }
}