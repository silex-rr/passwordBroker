<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Invitation for {{ $APP_NAME }}</title>
</head>
<body>
<h2>Hello, {{ $user->name->getValue() }}</h2>

    <p>
        We are pleased to inform you that an account has been created for you in our system.
        If you did not request for this, please contact our support team.
    </p>

    <p>
        To activate your account and set your password, click on the following link or copy and paste it into your browser:
    </p>

    <a href="{{ $activation_link_url }}" target="_blank" >{{ $activation_link_url }}</a>

    <p>
        This link will expire at {{ $expired_at }}, so be sure to use it right away.
    </p>

    <p>
        Thank you for joining our service!
    </p>


    <p>Kind regards,</p>

    <p>Onboarding Team</p>

</body>
</html>
