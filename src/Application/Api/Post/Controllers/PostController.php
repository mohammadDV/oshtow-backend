<?php

namespace Application\Api\Post\Controllers;

use Application\Api\Post\Requests\PostRequest;
use Application\Api\Post\Requests\PostUpdateRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Post\Models\Post;
use Domain\Post\Repositories\Contracts\IPostRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class PostController extends Controller
{
/**
     * Constructor of PostController.
     */
    public function __construct(protected IPostRepository $repository)
    {
        //
    }

    /**
     * Get all of active posts.
     */
    public function getPosts(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getPosts($request), Response::HTTP_OK);
    }

    /**
     * Get the post info.
     */
    public function getPostInfo(Post $post): JsonResponse
    {
        return response()->json($this->repository->getPostInfo($post), Response::HTTP_OK);
    }

    /**
     * Get all of post except newspaper.
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get the post.
     */
    public function show(Post $post): JsonResponse
    {
        return response()->json($this->repository->show($post), Response::HTTP_OK);
    }

    /**
     * Store the post.
     */
    public function store(PostRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Update the post.
     * @param PostUpdateRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(PostUpdateRequest $request, Post $post): JsonResponse
    {
        return $this->repository->update($request, $post);
    }

    /**
     * Delete the post.
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Post $post): JsonResponse
    {
        return $this->repository->destroy($post);
    }
}