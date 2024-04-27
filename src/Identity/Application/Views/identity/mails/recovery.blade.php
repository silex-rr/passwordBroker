<!DOCTYPE html>
<html lang="en">
<head>
    <title>Password Recovery for {{ $APP_NAME }}</title>
</head>
<body>
    <h2>Hello, {{ $user->name->getValue() }}</h2>

    <p>
        We received a request for a password reset for your account.
        If you did not make this request, you can ignore this email and continue using your current password.
    </p>

    <p>
        To reset your password, click on the following link or copy and paste it into your browser:
    </p>

    <a href="{{ $activation_link_url }}" target="_blank" >{{ $activation_link_url }}</a>

    <p>
        This link will expire at {{ $expired_at }}, so be sure to use it right away.
    </p>

    <p>
        Thank you for using our service!
    </p>


    <p>Kind regards,</p>

    <p>Secure Team</p>
</body>
</html>
