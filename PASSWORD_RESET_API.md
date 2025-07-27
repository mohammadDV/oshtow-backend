# Password Reset API Documentation

This document describes the password reset functionality implemented in the Laravel backend.

## Overview

The password reset system consists of three main endpoints:

1. **Forgot Password** - Send reset link to user's email
2. **Verify Reset Token** - Verify the token and get user info
3. **Reset Password** - Change password with valid token

## API Endpoints

### 1. Forgot Password

**Endpoint:** `POST /api/forgot-password`

**Description:** Sends a password reset link to the user's email address.

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Response:**
```json
{
    "message": "Password reset link sent to your email.",
    "status": 1
}
```

**Error Response:**
```json
{
    "message": "We could not find a user with that email address.",
    "status": 0
}
```

### 2. Verify Reset Token

**Endpoint:** `POST /api/verify-reset-token`

**Description:** Verifies the reset token and returns user information for the reset form.

**Request Body:**
```json
{
    "token": "reset_token_here",
    "email": "user@example.com"
}
```

**Response:**
```json
{
    "message": "Token is valid.",
    "user": {
        "email": "user@example.com",
        "name": "John Doe"
    },
    "status": 1
}
```

**Error Response:**
```json
{
    "message": "Invalid reset token.",
    "status": 0
}
```

### 3. Reset Password

**Endpoint:** `POST /api/reset-password`

**Description:** Resets the user's password using a valid token.

**Request Body:**
```json
{
    "token": "reset_token_here",
    "email": "user@example.com",
    "password": "new_password",
    "password_confirmation": "new_password"
}
```

**Response:**
```json
{
    "message": "Password has been reset successfully.",
    "status": 1
}
```

**Error Response:**
```json
{
    "message": "Invalid reset token.",
    "status": 0
}
```

## Implementation Details

### Token Generation
- Tokens are 60 characters long and randomly generated
- Tokens are hashed before storing in the database
- Tokens expire after 60 minutes

### Email Template
- Uses Laravel's markdown mail templates
- Located at: `resources/views/emails/users/password-reset.blade.php`
- Includes a clickable button and fallback URL

### Database
- Uses the existing `password_reset_tokens` table
- Table structure: `email`, `token`, `created_at`

### Security Features
- Tokens are hashed using Laravel's Hash facade
- Token expiration (60 minutes)
- Email validation
- Password confirmation requirement
- Password strength validation

## Configuration

### Environment Variables
Add the following to your `.env` file:
```
FRONTEND_URL=http://localhost:3000
```

### Mail Configuration
Ensure your mail configuration is properly set up in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Frontend Integration

1. **Forgot Password Form:**
   - User enters email
   - Call `/api/forgot-password`
   - Show success message

2. **Reset Password Form:**
   - Extract token and email from URL parameters
   - Call `/api/verify-reset-token` to validate
   - Show password reset form
   - Call `/api/reset-password` to change password

## Example Frontend Flow

```javascript
// 1. User requests password reset
const forgotPassword = async (email) => {
    const response = await fetch('/api/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    });
    return response.json();
};

// 2. Verify token when user clicks email link
const verifyToken = async (token, email) => {
    const response = await fetch('/api/verify-reset-token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, email })
    });
    return response.json();
};

// 3. Reset password
const resetPassword = async (token, email, password, password_confirmation) => {
    const response = await fetch('/api/reset-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, email, password, password_confirmation })
    });
    return response.json();
};
``` 
