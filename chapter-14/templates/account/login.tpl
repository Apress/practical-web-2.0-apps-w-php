{include file='header.tpl' section='login'}

<form method="post" action="{geturl action='login'}">
    <fieldset>
        <input type="hidden" name="redirect" value="{$redirect|escape}" />

        <legend>Log In to Your Account</legend>

        <div class="row" id="form_username_container">
            <label for="form_username">Username:</label>
            <input type="text" id="form_username"
                   name="username" value="{$username|escape}" />
            {include file='lib/error.tpl' error=$errors.username}
        </div>

        <div class="row" id="form_password_container">
            <label for="form_password">Password:</label>
            <input type="password" id="form_password"
                   name="password" value="" />
            {include file='lib/error.tpl' error=$errors.password}
        </div>

        <div class="submit">
            <input type="submit" value="Login" />
        </div>

        <div>
            <a href="{geturl action='fetchpassword'}">Forgotten your password?</a>
        </div>
    </fieldset>
</form>

{include file='footer.tpl'}