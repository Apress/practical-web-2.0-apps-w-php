{capture assign='url'}{geturl route='user'
                              username=$user->username
                              action='feed'}{/capture}
{include file='header.tpl'
         feedTitle="%s's Blog"|sprintf:$user->username
         feedUrl=$url}

{if $posts|@count == 0}
    <p>
        No blog posts were found for this user.
    </p>
{else}
    {foreach from=$posts item=post name=posts}
        {include file='user/lib/blog-post-summary.tpl' post=$post}
    {/foreach}
{/if}

{include file='footer.tpl'
         leftcolumn='user/lib/left-column.tpl'
         rightcolumn='user/lib/right-column.tpl'}