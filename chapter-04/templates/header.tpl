<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>Title</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    </head>
    <body>
        <div>
            <a href="/">Home</a>
            {if $authenticated}
                | <a href="/account">Your Account</a>
                | <a href="/account/details">Update Your Details</a>
                | <a href="/account/logout">Logout</a>
            {else}
                | <a href="/account/register">Register</a>
                | <a href="/account">Log In</a>
            {/if}

            {if $authenticated}
                <hr />
                <div>
                    Logged in as
                    {$identity->first_name|escape} {$identity->last_name|escape}
                    (<a href="/account/logout">logout</a>)
                </div>
            {/if}

            <hr />