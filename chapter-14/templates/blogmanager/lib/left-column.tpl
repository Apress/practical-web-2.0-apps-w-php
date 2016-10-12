{get_monthly_blog_summary user_id=$identity->user_id assign=summary}

{if $summary|@count > 0}
    <div id="preview-months" class="box">
        <h3>Your Blog Archive</h3>
        <ul>
            {foreach from=$summary key=month item=numPosts}
                <li>
                    <a href="{geturl controller='blogmanager'}?month={$month}">
                        {$month|date_format:'%B %Y'}
                    </a>
                    ({$numPosts} post{if $numPosts != 1}s{/if})
                </li>
            {/foreach}
        </ul>
    </div>

    <script type="text/javascript" src="/js/BlogMonthlySummary.class.js"></script>
    <script type="text/javascript">
        new BlogMonthlySummary('month-preview', 'preview-months');
    </script>
{/if}