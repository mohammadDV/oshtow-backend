@component('mail::message')
    # {{ __('site.Password reset request') }}

    {{ __('site.Hello') }} {{ $user->first_name }},

    {{ __('site.You are receiving this email because we received a password reset request for your account.') }}

    @component('mail::button', ['url' => $resetUrl])
        {{ __('site.Reset Password') }}
    @endcomponent

    {{ __('site.This password reset link will expire in 60 minutes.') }}

    {{ __('site.If you did not request a password reset, no further action is required.') }}

    {{ __('site.If you are having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:') }}

    {{ $resetUrl }}

    {{ __('site.Thanks') }},<br>
    {{ __('site.The') }} {{ config('app.name') }} {{ __('site.Team') }}
@endcomponent
