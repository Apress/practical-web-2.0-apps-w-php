{$user->profile->first_name}, Your Account Password
Dear {$user->profile->first_name},

You recently requested a password reset as you had forgotten your password.

Your new password is listed below. To activate this password, click this link:

    Activate Password: http://phpweb20/account/fetchpassword?action=confirm&id={$user->getId()}&key={$user->profile->new_password_key}
    Username: {$user->username}
    New Password: {$user->_newPassword}

If you didn't request a password reset, please ignore this message and your password
will remain unchanged.

Sincerely,

Web Site Administrator