<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('site.Welcome to') }} {{ config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Tahoma', 'Arial', sans-serif;
            background-color: #f5f5f5;
            direction: rtl;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .email-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            margin: 20px 0;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: bold;
            color: rgba(230, 78, 181, 1);
            margin: 0;
            position: relative;
            display: inline-block;
        }

        .logo-dots {
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
        }

        .dot {
            width: 6px;
            height: 6px;
            background-color: rgba(230, 78, 181, 1);
            border-radius: 50%;
            margin: 2px 0;
            display: block;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #000000;
            margin: 20px 0;
            line-height: 1.4;
        }

        .content {
            font-size: 16px;
            color: #333333;
            line-height: 1.6;
            margin: 20px 0 30px 0;
            text-align: center;
        }

        .cta-button {
            display: inline-block;
            background-color: rgba(255, 126, 213, 1);
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            transition: background-color 0.3s ease;
        }

        .cta-button:hover {
            background-color: #b91c1c;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666666;
            font-size: 14px;
        }

        .welcome-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
            font-size: 14px;
            text-align: center;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                padding: 10px;
            }

            .email-card {
                padding: 20px;
            }

            .title {
                font-size: 20px;
            }

            .content {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-card">
            <!-- Logo Section -->
            <div class="logo">
                <h1 class="logo-text">
                    <span class="logo-dots">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </span>
                    Oshtow
                </h1>
            </div>

            <!-- Title Section -->
            <h2 class="title">{{ __('site.Welcome to') }} {{ config('app.name') }}, {{ $user->first_name }}!</h2>

            <!-- Content Section -->
            <p class="content">
                {{ __('site.Thank you for registering with us') }}. {{ __('site.We are excited to have you on board') }}.
            </p>

            <!-- Welcome Message -->
            <div class="welcome-message">
                🎉 {{ __('site.Welcome message') }} {{ config('app.name') }} داریم.
            </div>

            <!-- CTA Button -->
            <a href="{{ config('app.url') }}" class="cta-button">
                {{ __('site.Go to Dashboard') }}
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ __('site.If you have any questions, feel free to reply to this email') }}.</p>
            <p>{{ __('site.Thanks') }},<br>{{ __('site.The') }} {{ config('app.name') }} {{ __('site.Team') }}</p>
        </div>
    </div>
</body>
</html>
