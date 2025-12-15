<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\DTO\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companyRepository
    ) {
    }

    /**
     * Получение компаний и их адач
     * 
     * Каждая компания имеет:
     * - id, name, city
     * - tasks: объект с инфой по задачам
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $companies = $this->companyRepository
            ->getUserCompaniesWithTaskCounts($request->user())
            ->map(fn ($company) => Company::fromModel($company)->toArray());

        return response()->json([
            'companies' => $companies,
        ]);
    }
}

