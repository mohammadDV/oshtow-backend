<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('site.Password reset request') }}</title>
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

        .warning-text {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
            font-size: 14px;
        }

        .url-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #495057;
            font-size: 12px;
            word-break: break-all;
            direction: ltr;
            text-align: left;
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
            <h2 class="title">{{ __('site.Password reset request') }}</h2>

            <!-- Content Section -->
            <p class="content">
                {{ __('site.Hello') }} {{ $user->first_name }},<br><br>
                {{ __('site.You are receiving this email because we received a password reset request for your account.') }}
            </p>

            <!-- CTA Button -->
            <a href="{{ $resetUrl }}" class="cta-button">
                {{ __('site.Reset Password') }}
            </a>

            <!-- Warning Section -->
            <div class="warning-text">
                {{ __('site.This password reset link will expire in 60 minutes.') }}<br>
                {{ __('site.If you did not request a password reset, no further action is required.') }}
            </div>

            <!-- URL Section -->
            <div class="url-text">
                <strong>{{ __('site.If you are having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:') }}</strong><br><br>
                {{ $resetUrl }}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ __('site.Thanks') }},<br>{{ __('site.The') }} {{ config('app.name') }} {{ __('site.Team') }}</p>
        </div>
    </div>
</body>
</html>
