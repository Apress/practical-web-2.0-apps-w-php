{include file='header.tpl'}

{if $search.performed}
    {if $search.total == 0}
        <p>
            No results were found for this search.
        </p>
    {else}
        <p>
            Displaying results {$search.start}-{$search.finish} of {$search.total}
        </p>

        {foreach from=$search.results item=post}
            {assign var='user_id' value=$post->user_id}
            {include file='user/lib/blog-post-summary.tpl'
                     post=$post
                     user=$users.$user_id
                     linkToBlog=true}
        {/foreach}

        <div class="pager">
            {section loop=$search.pages name=page}
                {assign var=p value=$smarty.section.page.index_next}
                {if $p == $search.page}
                    <strong>{$p}</strong>
                {else}
                    <a href="{geturl controller='search'}?q={$q|escape}&amp;p={$p}"
                        >{$p}</a>
                {/if}
            {/section}
        </div>
    {/if}
{else}
    <p>
        Please use the search form in the left column to find content.
    </p>
{/if}

{include file='footer.tpl'}