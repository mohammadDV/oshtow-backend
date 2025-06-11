<?php

namespace Domain\Project\Repositories;

use Application\Api\Project\Requests\ProjectRequest;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Project\models\Project;
use Domain\Project\Repositories\Contracts\IProjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class ProjectRepository.
 */
class ProjectRepository implements IProjectRepository
{
    use GlobalFunc;

    /**
     * Get the projects pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Project::query()
            ->with([
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
                'categories'
            ])
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));
    }

    /**
     * Get the active projects.
     * @return Collection
     */
    public function activeProjects() :Collection
    {
        return Project::query()
            ->with([
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
                'categories'
            ])
            ->where('status', 1)
            ->get();
    }

    /**
     * Get the project.
     * @param Project $project
     * @return Project
     */
    public function show(Project $project) :Project
    {
        return Project::query()
                ->with([
                    'oCountry',
                    'oProvince',
                    'oCity',
                    'dCountry',
                    'dProvince',
                    'dCity',
                    'categories'
                ])
                ->where('id', $project->id)
                ->first();
    }

    /**
     * Store the project.
     * @param ProjectRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ProjectRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $project = Project::create([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'path_type' => $request->input('path_type'),
            'amount' => $request->input('amount'),
            'weight' => $request->input('weight'),
            'status' => $request->input('status'),
            'o_country_id' => $request->input('o_country_id'),
            'o_province_id' => $request->input('o_province_id'),
            'o_city_id' => $request->input('o_city_id'),
            'd_country_id' => $request->input('d_country_id'),
            'd_province_id' => $request->input('d_province_id'),
            'd_city_id' => $request->input('d_city_id'),
            'user_id' => Auth::user()->id,
        ]);

        if ($project) {
            // Attach categories if provided
            if ($request->has('categories')) {
                $project->categories()->attach($request->input('categories'));
            }

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Update the project.
     * @param ProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ProjectRequest $request, Project $project) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $project->user_id);

        $updated = $project->update([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'path_type' => $request->input('path_type'),
            'amount' => $request->input('amount'),
            'weight' => $request->input('weight'),
            'status' => $request->input('status'),
            'o_country_id' => $request->input('o_country_id'),
            'o_province_id' => $request->input('o_province_id'),
            'o_city_id' => $request->input('o_city_id'),
            'd_country_id' => $request->input('d_country_id'),
            'd_province_id' => $request->input('d_province_id'),
            'd_city_id' => $request->input('d_city_id'),
            'user_id' => Auth::user()->id,
        ]);

        if ($updated) {
            // Sync categories if provided
            if ($request->has('categories')) {
                $project->categories()->sync($request->input('categories'));
            }

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
     * Delete the project.
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $project->user_id);

        $deleted = $project->delete();

        if ($deleted) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }
}
