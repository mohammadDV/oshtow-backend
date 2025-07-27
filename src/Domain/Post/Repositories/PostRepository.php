<?php

namespace Domain\Post\Repositories;

use Application\Api\Post\Requests\PostRequest;
use Application\Api\Post\Requests\PostUpdateRequest;
use Application\Api\Post\Resources\PostResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Post\Models\Post;
use Domain\Post\Repositories\Contracts\IPostRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Domain\User\Services\TelegramNotificationService;
use Illuminate\Support\Facades\DB;

/**
 * Class PostRepository.
 */
class PostRepository implements IPostRepository
{
    use GlobalFunc;

    /**
     * Constructor of PostController.
     */
    public function __construct(protected TelegramNotificationService $service)
    {
        //
    }

    /**
     * Get the posts.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getPosts(TableRequest $request) :LengthAwarePaginator
    {

        $search = $request->get('query');
        $posts = Post::query()
            ->where('status', 1)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $posts->through(fn ($post) => new PostResource($post));

    }

     /**
     * Get the post info.
     * @param Post $post
     * @return PostResource
     */
    public function getPostInfo(Post $post) :PostResource
    {

        $post->increment('view');

        return new PostResource($post);

    }

    /**
     * Get the post.
     * @param Post $post
     * @return array
     */
    public function show(Post $post) {

        $this->checkLevelAccess($post->user_id == Auth::user()->id);

        return Post::query()
            ->where('id', $post->id)
            ->first();
    }

    /**
     * Get all posts.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {

        $search = $request->get('query');
        return Post::query()
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
     * Store the post.
     *
     * @param  PostRequest  $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(PostRequest $request) :JsonResponse
    {

        $thumb = null;
        $slide = null;
        if (!empty($request->input('image')) && !empty($request->input('thumb'))) {
            $image = $request->input('image');
            $path = parse_url($image, PHP_URL_PATH);

            $filename = basename($path);
            $thumb = str_replace($filename, 'thumbnails/' . $filename, $image);
            $slide = str_replace($filename, 'slides/' . $filename,  $image);
        }

        $post = auth()->user()->posts()->create([
            'pre_title'   => $request->input('pre_title'),
            'title'       => $request->input('title'),
            'content'     => $request->input('content'),
            'summary'     => $request->input('summary'),
            'image'       => $request->input('image', null),
            'thumbnail'   => $request->input('thumb') == 1 ? $thumb : null,
            'slide'       => $request->input('thumb') == 1 ? $slide : null,
            'video'       => $request->input('type') == 1 ? $request->input('video', null) : null,
            'video_id'    => $request->input('type') == 1 ? $request->input('video_id') : null,
            'type'        => $request->input('type', 0),
            'status'      => $request->input('status'),
            'special'     => $request->input('special', 0),
        ]);

        // $this->service->sendPhoto(
        //     config('telegram.chat_id'),
        //     $request->input('image', null),
        //     sprintf('انتشار یک پست از %s', Auth::user()->nickname) . PHP_EOL . $request->input('title')
        // );

        return response()->json([
            'status' => 1,
            'message' => __('site.New post has been stored')
        ], 200);
    }

    /**
     * Update the post.
     *
     * @param  PostUpdateRequest  $request
     * @param  Post  $post
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(PostUpdateRequest $request, Post $post) :JsonResponse
    {

        $this->checkLevelAccess($post->user_id == Auth::user()->id);

        $thumb = null;
        $slide = null;
        if (!empty($request->input('image')) && !empty($request->input('thumb'))) {
            $image = $request->input('image');
            $path = parse_url($image, PHP_URL_PATH);

            $filename = basename($path);
            $thumb = str_replace($filename, 'thumbnails/' . $filename, $image);
            $slide = str_replace($filename, 'slides/' . $filename,  $image);
        }

        DB::beginTransaction();
        try {
            $post->update([
                'pre_title'   => $request->input('pre_title'),
                'title'       => $request->input('title'),
                'content'     => $request->input('content'),
                'summary'     => $request->input('summary'),
                'image'       => $request->input('image', null),
                'thumbnail'   => $request->input('thumb') == 1 ? $thumb : null,
                'slide'       => $request->input('thumb') == 1 ? $slide : null,
                'video'       => $request->input('type') == 1 ? $request->input('video', null) : null,
                'video_id'    => $request->input('type') == 1 ? $request->input('video_id') : null,
                'type'        => $request->input('type',0),
                'status'      => $request->input('status'),
                'special'     => $request->input('special',0),
            ]);

            // $this->service->sendPhoto(
            //     config('telegram.chat_id'),
            //     $request->input('image', null),
            //     sprintf('ویرایش یک پست از %s', Auth::user()->nickname) . PHP_EOL . $request->input('title')
            // );
            DB::commit();
        }catch (\Exception $e){
            DB::rollback();
            throw new \Exception(__('site.Error in save data'));
        }

        return response()->json([
            'status' => 1,
            'message' => __('site.The post has been updated')
        ], 200);
    }

    /**
     * Delete the post.
     *
     * @param  Post  $post
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Post $post) :JsonResponse
    {

        $this->checkLevelAccess($post->user_id == Auth::user()->id);

        $post->delete();

        return response()->json([
            'status' => 1,
            'message' => __('site.The post has been deleted')
        ], 200);
    }
}
