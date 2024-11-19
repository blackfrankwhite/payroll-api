<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class CompanyService
{
    protected $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function registerCompany($user, array $companyData)
    {
        if (!$this->companyRepository->canRegisterCompany($user)) {
            throw new \Exception('User already has a company');
        }

        return $this->companyRepository->createCompany($user, $companyData);
    }

    public function getCompany($user)
    {
        return $this->companyRepository->getCompany($user);
    }

    public function sendInviteEmail($email)
    {
        Mail::to($email)->send(new TestEmail());
        return 'Invitation email sent!';
    }
}
