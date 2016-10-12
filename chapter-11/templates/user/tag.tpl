{include file='header.tpl'}

{if $posts|@count == 0}
    <p>
        No blog posts were found for this tag.
    </p>
{else}
    {foreach from=$posts item=post name=posts key=post_id}
        {include file='user/lib/blog-post-summary.tpl'
                 post=$post}

        {if $smarty.foreach.posts.last}
            {assign var=date value=$post->ts_created}
        {/if}
    {/foreach}
{/if}

{include file='footer.tpl'
         leftcolumn='user/lib/left-column.tpl'
         rightcolumn='user/lib/right-column.tpl'}