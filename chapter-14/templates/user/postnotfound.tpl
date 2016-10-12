{include file='header.tpl'}

<p>
    The selected post could not be found.
</p>

<p>
    <a href="{geturl username=$user->username route='user'}">
        Return to {$user->username|escape}'s blog
    </a>
</p>

{include file='footer.tpl'
         leftcolumn='user/lib/left-column.tpl'
         rightcolumn='user/lib/right-column.tpl'}