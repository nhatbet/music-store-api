<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tab\TabRequest;
use App\Interfaces\TabServiceInterface;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Tab\TabIndexRequest;
use App\Http\Resources\TabResource;

class TabController extends Controller
{
    protected TabServiceInterface $tabService;

    public function __construct(TabServiceInterface $tabService)
    {
        $this->tabService = $tabService;
    }

    public function index(TabIndexRequest $request): JsonResponse
    {
        $tabs = $this->tabService->index($request);

        return ApiResponseService::paginate($tabs);
    }

    public function getNewTab(): JsonResponse
    {
        $tabs = $this->tabService->getNewTab();

        return ApiResponseService::success($tabs);
    }

    public function getRandomTab(): JsonResponse
    {
        $tabs = $this->tabService->getRandomTab();

        return ApiResponseService::success($tabs); 
    }

    public function show(Request $request, $id): JsonResponse
    {
        $tab = $this->tabService->showForUser($id, $request);
        $resource = new TabResource($tab);
        
        return ApiResponseService::success($resource);
    }

    public function getTabByIds(Request $request): JsonResponse
    {
        $tabs = $this->tabService->getTabByIds($request->get('ids'));

        return ApiResponseService::success($tabs);
    }
}
