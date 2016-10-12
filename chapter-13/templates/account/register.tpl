{include file='header.tpl' section='register'}

<form method="post"
      action="{geturl action='register'}"
      id="registration-form">

    <fieldset>
        <legend>Create an Account</legend>
        <div class="error"{if !$fp->hasError()} style="display: none"{/if}>
            An error has occurred in the form below. Please check
            the highlighted fields and re-submit the form.
        </div>

        <div class="row" id="form_username_container">
            <label for="form_username">Username:</label>
            <input type="text" id="form_username"
                   name="username" value="{$fp->username|escape}" />
            {include file='lib/error.tpl' error=$fp->getError('username')}
        </div>

        <div class="row" id="form_email_container">
            <label for="form_email">E-mail Address:</label>
            <input type="text" id="form_email"
                   name="email" value="{$fp->email|escape}" />
            {include file='lib/error.tpl' error=$fp->getError('email')}
        </div>

        <div class="row" id="form_first_name_container">
            <label for="form_first_name">First Name:</label>
            <input type="text" id="form_first_name"
                   name="first_name" value="{$fp->first_name|escape}" />
            {include file='lib/error.tpl' error=$fp->getError('first_name')}
        </div>

        <div class="row" id="form_last_name_container">
            <label for="form_last_name">Last Name:</label>
            <input type="text" id="form_last_name"
                   name="last_name" value="{$fp->last_name|escape}" />
            {include file='lib/error.tpl' error=$fp->getError('last_name')}
        </div>

        <div class="captcha">
            <img src="/utility/captcha" alt="CAPTCHA image" />
        </div>

        <div class="row" id="form_captcha_container">
            <label for="form_captcha">Enter Above Phrase:</label>
            <input type="text" id="form_captcha"
                   name="captcha" value="{$fp->captcha|escape}" />
            {include file='lib/error.tpl' error=$fp->getError('captcha')}
        </div>

        <div class="submit">
            <input type="submit" value="Register" />
        </div>
    </fieldset>
</form>

<script type="text/javascript" src="/js/UserRegistrationForm.class.js"></script>
<script type="text/javascript">
    new UserRegistrationForm('registration-form');
</script>

{include file='footer.tpl'}