<?php

namespace Domain\Project\Repositories;

use Application\Api\Project\Requests\ProjectRequest;
use Application\Api\Project\Resources\ProjectResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Project\Models\Project;
use Domain\Project\Repositories\Contracts\IProjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Application\Api\Project\Requests\SearchProjectRequest;

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
        $status = $request->get('status');
        $projects = Project::query()
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
            ->when(!empty($status), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));

        return $projects->through(fn ($project) => new ProjectResource($project));
    }

    /**
     * Get the active projects.
     * @return Collection
     */
    public function activeProjects() :Collection
    {
        $projects = Project::query()
            ->with([
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
                'categories'
            ])
            ->where('active', 1)
            ->where('status', Project::PENDING)
            ->get();

        return $projects->map(fn ($project) => new ProjectResource($project));
    }

    /**
     * Get the project.
     * @param Project $project
     * @return ProjectResource
     */
    public function show(Project $project) :ProjectResource
    {
        $project = Project::query()
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

        return new ProjectResource($project);
    }

    /**
     * Store the project.
     * @param ProjectRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ProjectRequest $request) :JsonResponse
    {

        $this->expireSubscriprions();

        if (!$this->checkSubscriprion()) {
            return response()->json([
                'status' => 0,
                'message' => __('site.No active subscription found'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $project = Project::create([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'path_type' => $request->input('path_type'),
            'amount' => $request->input('amount'),
            'description' => $request->input('description'),
            'address' => $request->input('address'),
            'weight' => $request->input('weight'),
            'active' => 1,
            'status' => Project::PENDING,
            'vip' => $request->input('vip'),
            'priority' => $request->input('priority'),
            'send_date' => $request->input('send_date'),
            'receive_date' => $request->input('receive_date'),
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
                'message' => __('site.The operation has been successfully'),
                'data' => new ProjectResource($project)
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
        $this->checkLevelAccess(Auth::user()->level == 3);
        // $this->checkLevelAccess(Auth::user()->id == $project->user_id);

        $updated = $project->update([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'path_type' => $request->input('path_type'),
            'amount' => $request->input('amount'),
            'description' => $request->input('description'),
            'address' => $request->input('address'),
            'weight' => $request->input('weight'),
            'active' => $request->input('active'),
            'status' => $request->input('status'),
            'vip' => $request->input('vip'),
            'priority' => $request->input('priority'),
            'send_date' => $request->input('send_date'),
            'receive_date' => $request->input('receive_date'),
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
                'message' => __('site.The operation has been successfully'),
                'data' => new ProjectResource($project)
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

    /**
     * Get featured projects by type with configurable limits.
     * @return array{sender: Collection, passenger: Collection}
     */
    public function getFeaturedProjects(): array
    {
        $today = now()->startOfDay();

        $senderProjects = Project::query()
            ->with([
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
                'categories'
            ])
            ->where('type', Project::SENDER)
            ->where('active', 1)
            ->where('status', Project::PENDING)
            ->where('send_date', '>=', $today)
            ->orderBy('priority', 'desc')
            ->limit(config('project.senderLimit'))
            ->get()
            ->map(fn ($project) => new ProjectResource($project));

        $passengerProjects = Project::query()
            ->with([
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
                'categories'
            ])
            ->where('type', Project::PASSENGER)
            ->where('active', 1)
            ->where('status', Project::PENDING)
            ->where('send_date', '>=', $today)
            ->orderBy('priority', 'desc')
            ->limit(config('project.passengerLimit'))
            ->get()
            ->map(fn ($project) => new ProjectResource($project));

        return [
            'sender' => $senderProjects,
            'passenger' => $passengerProjects
        ];
    }

    /**
     * Search projects with filters and pagination.
     * @param SearchProjectRequest $request
     * @return LengthAwarePaginator
     */
    public function search(SearchProjectRequest $request): LengthAwarePaginator
    {
        $today = now()->startOfDay();

        // Generate a unique cache key based on all search parameters
        $cacheKey = 'project_search_' . md5(json_encode([
            'type' => $request->input('type'),
            'o_city_id' => $request->input('o_city_id'),
            'd_city_id' => $request->input('d_city_id'),
            'o_province_id' => $request->input('o_province_id'),
            'd_province_id' => $request->input('d_province_id'),
            'o_country_id' => $request->input('o_country_id'),
            'd_country_id' => $request->input('d_country_id'),
            'send_date' => $request->input('send_date'),
            'receive_date' => $request->input('receive_date'),
            'path_type' => $request->input('path_type'),
            'categories' => $request->input('categories'),
            'min_weight' => $request->input('min_weight'),
            'max_weight' => $request->input('max_weight'),
            'page' => $request->input('page', 1),
        ]));

        // Try to get results from cache first
        // return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($request, $today) {
            $query = Project::query()
                ->with([
                    'oCountry',
                    'oProvince',
                    'oCity',
                    'dCountry',
                    'dProvince',
                    'dCity',
                    'categories'
                ])
                ->where('active', 1)
                ->where('status', Project::PENDING)
                ->where('send_date', '>=', $today)
                ->where('type', $request->input('type'))
                ->orderBy('priority', 'desc');

            // Apply filters
            if ($request->has('o_city_id')) {
                $query->where('o_city_id', $request->input('o_city_id'));
            }

            if ($request->has('d_city_id')) {
                $query->where('d_city_id', $request->input('d_city_id'));
            }

            if ($request->has('o_province_id')) {
                $query->where('o_province_id', $request->input('o_province_id'));
            }

            if ($request->has('d_province_id')) {
                $query->where('d_province_id', $request->input('d_province_id'));
            }

            if ($request->has('o_country_id')) {
                $query->where('o_country_id', $request->input('o_country_id'));
            }

            if ($request->has('d_country_id')) {
                $query->where('d_country_id', $request->input('d_country_id'));
            }

            if ($request->has('send_date')) {
                $query->where('send_date', '=', $request->input('send_date'));
            }

            if ($request->has('receive_date')) {
                $query->where('receive_date', '>=', $request->input('receive_date'));
            }

            if ($request->has('path_type')) {
                $query->where('path_type', $request->input('path_type'));
            }

            // Apply weight range filter
            if ($request->has('min_weight')) {
                $query->where('weight', '>=', $request->input('min_weight'));
            }

            if ($request->has('max_weight')) {
                $query->where('weight', '<=', $request->input('max_weight'));
            }

            if ($request->has('categories')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->whereIn('project_categories.id', $request->input('categories'));
                });
            }

            $projects = $query->paginate(9);

            return $projects->through(fn ($project) => new ProjectResource($project));
        // });
    }
}