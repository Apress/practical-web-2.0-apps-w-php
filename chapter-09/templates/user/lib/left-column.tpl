{get_monthly_blog_summary user_id=$user->getId() assign=summary liveOnly=true}

{if $summary|@count > 0}
    <div id="preview-months" class="box">
        <h3>{$user->username|escape}'s Blog Archive</h3>
        <ul>
            {foreach from=$summary key=month item=numPosts}
                <li>
                    <a href="{geturl username=$user->username
                                     route='archive'
                                     year=$month|date_format:'%Y'
                                     month=$month|date_format:'%m'}">
                        {$month|date_format:'%B %Y'}
                    </a>
                    ({$numPosts} post{if $numPosts != 1}s{/if})
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
