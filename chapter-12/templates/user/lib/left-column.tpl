<div class="box vcard">
    <h3>{$user->username|escape}'s Profile</h3>

    {if $user->profile->public_first_name|strlen > 0 ||
        $user->profile->public_last_name|strlen > 0}

        <div class="fn n">
            {if $user->profile->public_first_name|strlen > 0}
                <span class="given-name">
                    {$user->profile->public_first_name|escape}
                </span>
            {/if}
            {if $user->profile->public_last_name|strlen > 0}
                <span class="family-name">
                    {$user->profile->public_last_name|escape}
                </span>
            {/if}
        </div>
    {else}
        <div class="fn nickname">
            {$user->username}
        </div>
    {/if}

    {if $user->profile->public_email|strlen > 0}
        <div>
            Email:
            <a href="mailto:{$user->profile->public_email|escape}" class="email">
                {$user->profile->public_email|escape}
            </a>
        </div>
    {/if}

    {if $user->profile->public_home_phone|strlen > 0}
        <div class="tel">
            Phone
            (<span class="type">Home</span>):
            <span class="value">
                {$user->profile->public_home_phone|escape}
            </span>
        </div>
    {/if}
    {if $user->profile->public_work_phone|strlen > 0}
        <div class="tel">
            Phone
            (<span class="type">Work</span>):
            <span class="value">
            {$user->profile->public_work_phone|escape}
            </span>
        </div>
    {/if}
</div>

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
