{get_tag_summary user_id=$user->getId() assign=summary}

{if $summary|@count > 0}
    <div class="box">
        <h3>{$user->username|escape}'s Tags</h3>
        <ul>
            {foreach from=$summary item=tag}
                <li>
                    <a href="{geturl route='tagspace'
                                     username=$user->username
                                     tag=$tag.tag}">
                        {$tag.tag|escape}
                    </a>
                    ({$tag.count} post{if $tag.count != 1}s{/if})
                </li>
            {/foreach}
        </ul>
    </div>
{/if}