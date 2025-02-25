<?php

namespace App\Services;

use App\Repositories\SalaryRepository;
use App\Repositories\EmployeeRepository;
use Carbon\Carbon;

class SalaryService
{
    protected $salaryRepository;
    protected $employeeRepository;

    public function __construct(
        SalaryRepository $salaryRepository, 
        EmployeeRepository $employeeRepository
    ) {
        $this->salaryRepository = $salaryRepository;
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Instead of throwing an exception, return an error message if overlap exists.
     */
    private function checkSalaryOverlap(
        int $employeeId, 
        string $startDate, 
        ?string $endDate, 
        ?int $excludeSalaryId = null
    ): ?string {
        $newStart = Carbon::parse($startDate);
        $newEnd = $endDate ? Carbon::parse($endDate) : Carbon::now();
        $existingSalaries = $this->salaryRepository->getByEmployee($employeeId);

        foreach ($existingSalaries as $salary) {
            if ($excludeSalaryId && $salary->id === $excludeSalaryId) {
                continue;
            }
            $existingStart = Carbon::parse($salary->start_date);
            $existingEnd = $salary->end_date ? Carbon::parse($salary->end_date) : Carbon::now();
            if ($newStart->lte($existingEnd) && $newEnd->gte($existingStart)) {
                return "Salary dates overlap with an existing record (ID: {$salary->id}).";
            }
        }

        return null;
    }

    public function addSalary(int $userId, int $employeeID, array $data): array
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $data['employee_id'] = $employee->id;

        $error = $this->checkSalaryOverlap($employee->id, $data['start_date'], $data['end_date'] ?? null);
        if ($error) {
            return ['error' => $error];
        }
        $salary = $this->salaryRepository->create($data);
        return ['data' => $salary];
    }

    public function getSalaries(int $userId, int $employeeID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        return $this->salaryRepository->getByEmployee($employee->id);
    }

    public function getSalary(int $userId, int $employeeID, int $salaryID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        return $this->salaryRepository->getById($employee->id, $salaryID);
    }

    public function updateSalary(
        int $userId, 
        int $employeeID, 
        int $salaryID, 
        array $data
    ): array {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $existingSalary = $this->salaryRepository->getById($employee->id, $salaryID);
        $newStartDate = $data['start_date'] ?? $existingSalary->start_date;
        $newEndDate = array_key_exists('end_date', $data) ? $data['end_date'] : $existingSalary->end_date;

        $error = $this->checkSalaryOverlap($employee->id, $newStartDate, $newEndDate, $existingSalary->id);
        if ($error) {
            return ['error' => $error];
        }
        $salary = $this->salaryRepository->update($employee->id, $salaryID, $data);
        return ['data' => $salary];
    }

    public function deleteSalary(int $userId, int $employeeID, int $salaryID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $this->salaryRepository->delete($employee->id, $salaryID);
        return ['data' => 'Salary deleted successfully'];
    }
}
