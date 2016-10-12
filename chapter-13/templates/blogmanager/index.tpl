{if $isXmlHttpRequest}
    {include file='blogmanager/lib/month-preview.tpl'
             month=$month
             posts=$recentPosts}
{else}
    {include file='header.tpl' section='blogmanager'}

    {if $totalPosts == 1}
        <p>
            There is currently 1 post in your blog.
        </p>
    {else}
        <p>
            There are currently {$totalPosts} posts in your blog.
        </p>
    {/if}

    <form method="get" action="{geturl action='edit'}">
        <div class="submit">
            <input type="submit" value="Create new blog post" />
        </div>
    </form>

    <div id="month-preview">
        {include file='blogmanager/lib/month-preview.tpl'
                 month=$month
                 posts=$recentPosts}
    </div>

    {include file='footer.tpl'
             leftcolumn='blogmanager/lib/left-column.tpl'}
{/if}