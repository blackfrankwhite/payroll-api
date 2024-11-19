<?php 

namespace App\Repositories;

use App\Models\Company;
use App\Models\CompanyUser;

class CompanyRepository
{
    public function createCompany($user, array $companyData)
    {
        return \DB::transaction(function () use ($user, $companyData) {
            $company = Company::create($companyData);
            CompanyUser::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => 'superadmin',
            ]);
            return $company;
        });
    }

    public function getCompany($user)
    {
        $companyUser = CompanyUser::where('user_id', $user->id)->first();
        return $companyUser ? $companyUser->company : null;
    }

    public function canRegisterCompany($user)
    {
        return !CompanyUser::where('user_id', $user->id)->exists();
    }
}
