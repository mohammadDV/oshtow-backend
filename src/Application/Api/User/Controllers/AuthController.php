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
use Domain\Wallet\Models\Wallet;
// use Application\Api\User\Notifications\ThankYouForRegistering;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Application\Api\User\Mail\ThankYouForRegistering;
use Application\Api\User\Resources\UserResource;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{

    /**
     * @param TelegramNotificationService $service
     */
    // public function __construct(protected TelegramNotificationService $service)
    // {

    // }

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
            'is_admin' => $user->level == 3,
            'token' => $token,
            'verify_email' => !empty($user->email_verified_at),
            'verify_access' => !empty($user->verified_at),
            'user' => new UserResource($user),
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
                    'customer_number' => User::generateCustumerNumber(),
                    'role_id' => 2,
                    'status' => 0,
                    'email' => $payload['email'],
                    'google_id' => $payload['sub'],
                    'password' => bcrypt($nickname . '!@#' . rand(1111, 9999)),
                    'profile_photo_path' => !empty($payload['picture']) ? $payload['picture'] : config('image.default-profile-image'),
                    'bg_photo_path' => config('image.default-background-image'),
                ]);

                $user->assignRole(['user']);

                $token = $user->createToken('myapptokens')->plainTextToken;

                // create wallet for the user
                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency' => Wallet::IRR,
                    'status' => 1
                ]);

            }


            return response([
                'token' => $token,
                'status' => 1,
                'data' => $user,
                'is_admin' => $user->level == 3,
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


        // Add the lite and normal roles
        // $admin = Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        // $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'nickname' => $request->nickname,
            'customer_number' => User::generateCustumerNumber(),
            'role_id' => 2,
            'status' => 1,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'profile_photo_path'    => config('image.default-profile-image'),
            'bg_photo_path'         => config('image.default-background-image'),
        ]);

        $user->assignRole(['user']);

        // Send thank you notification
        // $user->notify(new \Application\Api\User\Notifications\ThankYouForRegistering());

        $token = $user->createToken('myapptokens')->plainTextToken;

         // create wallet for the user
         Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => Wallet::IRR,
            'status' => 1
        ]);

        event(new Registered($user));

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
            'is_admin' => $user->level == 3,
            'user' => new UserResource($user),
            'token' => $token,
            'status' => 1
        ], Response::HTTP_CREATED);
    }

    /**
     * Log out the user.
     */
    public function logout(): Response
    {

        Auth::user()->tokens()->delete();
        return response([
            'mesasge' => 'success',
            'status' => 1
        ], 201);
    }

    public function mail() {

        $user = Auth::user();

        $user->notify(new \Application\Api\User\Notifications\ThankYouForRegistering());

        // Mail::to($user->email)->send(new ThankYouForRegistering($user));
    }

    /**
     * Log in the user.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        return response()->json(['message' => 'Email verified successfully'], 200);
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status' => 1,
                'message' => 'Already verified'
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'status' => 1,
            'message' => 'Verification link sent!'
        ]);
    }
}