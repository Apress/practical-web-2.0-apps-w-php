{include file='header.tpl' active='register'}

<p>
    Thank you {$user->profile->first_name|escape},
    your registration is now complete.
</p>

<p>
    Your password has been e-mailed to you at {$user->profile->email|escape}.
</p>

{include file='footer.tpl'}