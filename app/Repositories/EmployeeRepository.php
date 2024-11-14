<?php 

namespace App\Repositories;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Models\Employee;

class EmployeeRepository
{
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