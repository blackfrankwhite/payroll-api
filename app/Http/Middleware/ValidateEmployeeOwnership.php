<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Repositories\CompanyUserRepository;
use App\Repositories\EmployeeRepository;

class ValidateEmployeeOwnership
{
    protected $companyUserRepository;
    protected $employeeRepository;

    public function __construct(CompanyUserRepository $companyUserRepository, EmployeeRepository $employeeRepository)
    {
        $this->companyUserRepository = $companyUserRepository;
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->user()->id;
        $employeeId = $request->route('employeeID');

        if (!$employeeId) return $next($request);

        // Get the company of the authenticated user
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        $companyId = $companyUser->company_id;

        // Check if the employee belongs to the user's company
        $employee = $this->employeeRepository->getEmployeeById($companyId, $employeeId);
        if (!$employee) {
            return response()->json([
                'message' => 'Forbidden',
                'errors' => ['employee_id' => 'This employee does not belong to your company.']
            ], 403);
        }

        return $next($request);
    }
}
