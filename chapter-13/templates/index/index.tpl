{include file='header.tpl' section='home'}

{if $posts|@count == 0}
    <p>
        No blog posts were found!
    </p>
{else}
    {foreach from=$posts item=post name=posts}
        {assign var='user_id' value=$post->user_id}
        {include file='user/lib/blog-post-summary.tpl'
                 post=$post
                 user=$users.$user_id
                 linkToBlog=true}
    {/foreach}
{/if}

{include file='footer.tpl'}
