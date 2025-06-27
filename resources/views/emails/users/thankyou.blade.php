@component('mail::message')
# Welcome to {{ config('app.name') }}, {{ $user->first_name }}!

Thank you for registering with us. We're excited to have you on board.

@component('mail::button', ['url' => config('app.url')])
Go to Dashboard
@endcomponent

If you have any questions, feel free to reply to this email.

Thanks,<br>
The {{ config('app.name') }} Team
@endcomponent
