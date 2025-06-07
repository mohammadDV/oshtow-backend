<?php

namespace Application\Api\User\Controllers;

use Application\Api\User\Requests\LoginRequest;
use Application\Api\User\Requests\RegisterRequest;
use Core\Http\Controllers\Controller;
use Domain\User\Models\Role;
use Domain\User\Models\User;
use Domain\User\Services\TelegramNotificationService;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * @param TelegramNotificationService $service
     */
    public function __construct(protected TelegramNotificationService $service)
    {

    }

    /**
     * Log in the user.
     */
    public function login(LoginRequest $request): Response
    {

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => __('site.These credentials do not match our records.'),
                'status' => 0
            ], 401);
        }

        $token = $user->createToken('finybotokenapp')->plainTextToken;

        return response([
            'token' => $token,
            'mesasge' => 'success',
            'status' => 1
        ], 200);
    }

    /**
     * Log in the user.
     */
    public function verify(Request $request): Response
    {

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->token);

        if ($payload) {

            $user = User::where('email', $payload['email'])->first();

            if (!empty($user->id)) {
                $token = $user->createToken('myapptokens')->plainTextToken;
            } else {

                $nickname = str_replace(' ', '-', $payload['name']);

                $nickname = $this->nicknameCheck($nickname);

                $user = User::create([
                    'first_name' => !empty($payload['given_name']) ? $payload['given_name'] : $payload['name'],
                    'last_name' => !empty($payload['family_name']) ? $payload['family_name'] : '',
                    'nickname' => $nickname,
                    'role_id' => 4,
                    'status' => 1,
                    'email' => $payload['email'],
                    'google_id' => $payload['sub'],
                    'password' => bcrypt($nickname . '!@#' . rand(1111, 9999)),
                    'profile_photo_path' => !empty($payload['picture']) ? $payload['picture'] : config('image.default-profile-image'),
                    'bg_photo_path' => config('image.default-background-image'),
                ]);

                $user->assignRole(['user']);

                $token = $user->createToken('myapptokens')->plainTextToken;

            }


            return response([
                'token' => $token,
                'status' => 1
            ], Response::HTTP_ACCEPTED);


        } else {
            return response([
                'token' => '',
                'status' => 0
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Check the nickname is unique or not
     * @param string $nickname
     * @return string $nickname
     */
    public function nicknameCheck(string $nickname): string
    {
        $user = User::query()
            ->where('nickname', $nickname)
            ->first();

        return !empty($user->id) ? $this->nicknameCheck($nickname . rand(111111, 999999)) : $nickname;
    }

    /**
     * Register the user.
     */
    public function register(RegisterRequest $request): Response
    {

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'nickname' => $request->nickname,
            'role_id' => 4,
            'status' => 1,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'profile_photo_path'    => config('image.default-profile-image'),
            'bg_photo_path'         => config('image.default-background-image'),
        ]);

        $user->assignRole(['user']);

        $token = $user->createToken('myapptokens')->plainTextToken;

        // $this->service->sendNotification(
        //     config('telegram.chat_id'),
        //     'ثبت نام کاربر جدید' . PHP_EOL .
        //     'first_name ' . $request->first_name . PHP_EOL .
        //     'last_name ' . $request->last_name. PHP_EOL .
        //     'nickname ' . $request->nickname . PHP_EOL .
        //     'email ' . $request->email . PHP_EOL .
        //     'mobile ' . $request->mobile . PHP_EOL
        // );

        return response([
            'token' => $token,
            'status' => 1
        ], Response::HTTP_CREATED);
    }

    /**
     * Log out the user.
     */
    public function logout(): Response
    {

        auth()->user()->tokens()->delete();
        return response([
            'mesasge' => 'success',
            'status' => 1
        ], 201);
    }
}
