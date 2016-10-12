{include file='header.tpl'}

{if $posts|@count == 0}
    <p>
        No blog posts were found for this month.
    </p>
{else}
    {foreach from=$posts item=post name=posts}
        {include file='user/lib/blog-post-summary.tpl' post=$post}
    {/foreach}
{/if}

{include file='footer.tpl'
         leftcolumn='user/lib/left-column.tpl'
         rightcolumn='user/lib/right-column.tpl'}