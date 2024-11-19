<?php

namespace App\Repositories;

use App\Models\CompanyUser;

class CompanyUserRepository
{
    /**
     * Retrieve the company user by user ID.
     *
     * @param int $userId
     * @return CompanyUser
     */
    public function getCompanyUserByUserId(int $userId): CompanyUser
    {
        return CompanyUser::where('user_id', $userId)->firstOrFail();
    }
}
