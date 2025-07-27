<?php

namespace Domain\Post\Repositories\Contracts;

use Application\Api\Post\Requests\PostRequest;
use Application\Api\Post\Requests\PostUpdateRequest;
use Application\Api\Post\Resources\PostResource;
use Core\Http\Requests\TableRequest;
use Domain\Post\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IPostRepository.
 */
interface IPostRepository
{

/**
     * Get the posts.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getPosts(TableRequest $request) :LengthAwarePaginator;

     /**
     * Get the post info.
     * @param Post $post
     * @return PostResource
     */
    public function getPostInfo(Post $post) :PostResource;

    /**
     * Get the post.
     * @param Post $post
     * @return array
     */
    public function show(Post $post);

    /**
     * Get all posts.
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Store the post.
     *
     * @param  PostRequest  $request
     * @return JsonResponse
     */
    public function store(PostRequest $request) :JsonResponse;

    /**
     * Update the post.
     *
     * @param  PostUpdateRequest  $request
     * @param  Post  $post
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(PostUpdateRequest $request, Post $post) :JsonResponse;

    /**
     * Delete the post.
     *
     * @param  Post  $post
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Post $post) :JsonResponse;
}