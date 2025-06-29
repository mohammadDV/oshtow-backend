<?php

namespace Domain\Project\Repositories;

use Application\Api\Project\Requests\ProjectCategoryRequest;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Project\Models\ProjectCategory;
use Domain\Project\Repositories\Contracts\IProjectCategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class ProjectCategoryRepository.
 */
class ProjectCategoryRepository implements IProjectCategoryRepository
{
    use GlobalFunc;

    /**
     * Get the projectCategories pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return ProjectCategory::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the projectCategories.
     * @return Collection
     */
    public function activeProjectCategories() :Collection
    {
        return ProjectCategory::query()
            ->where('status', 1)
            ->get();
    }

    /**
     * Get the projectCategory.
     * @param ProjectCategory $projectCategory
     * @return ProjectCategory
     */
    public function show(ProjectCategory $projectCategory) :ProjectCategory
    {
        return ProjectCategory::query()
                ->where('id', $projectCategory->id)
                ->first();
    }

    /**
     * Store the projectCategory.
     * @param ProjectCategoryRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ProjectCategoryRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $projectCategory = ProjectCategory::create([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'user_id' => Auth::user()->id,
        ]);

        if ($projectCategory) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Update the projectCategory.
     * @param ProjectCategoryRequest $request
     * @param ProjectCategory $projectCategory
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $projectCategory->user_id);

        $projectCategory = $projectCategory->update([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'user_id' => Auth::user()->id,
        ]);

        if ($projectCategory) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
    * Delete the projectCategory.
    * @param UpdatePasswordRequest $request
    * @param ProjectCategory $projectCategory
    * @return JsonResponse
    */
   public function destroy(ProjectCategory $projectCategory) :JsonResponse
   {
        $this->checkLevelAccess(Auth::user()->id == $projectCategory->user_id);

        $projectCategory->delete();

        if ($projectCategory) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}
