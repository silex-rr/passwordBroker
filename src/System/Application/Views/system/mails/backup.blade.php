<!DOCTYPE html>
<html lang="en">
<head>
    <title>Backup Information</title>
</head>
<body>
<h1>Dear User,</h1>

<p>This is an automatic system message to inform you that a backup has been successfully created at:
    <strong>{{ $backup->backup_created }}</strong>.</p>

<p>Here is the basic information about the created backup:</p>

<ul>
    <li>Backup Size: {{ $backup->size }}</li>
    <li>Backup Location: {{ $backup->file_name }}</li>
</ul>

<p>If you have any questions or concerns about this backup, please contact our support team.</p>

<p>Kind regards,</p>
<p>Backup Management Team</p>
</body>
</html>
