{include file='header.tpl' lightbox=true}

<div id="post-tags">
    <strong>Tags:</strong>
    {foreach from=$post->getTags() item=tag name=tags}
        <a href="{geturl route='tagspace' username=$user->username tag=$tag}"
            rel="tag">{$tag}</a>{if !$smarty.foreach.tags.last},{/if}
    {foreachelse}
        (none)
    {/foreach}
</div>

<div class="post-date">
    {$post->ts_created|date_format:'%b %e, %Y %l:%M %p'}
</div>

{foreach from=$post->images item=image}
    <div class="post-image">
        <a href="{imagefilename id=$image->getId() w=600}" rel="lightbox[blog]">
            <img src="{imagefilename id=$image->getId() w=150}" />
        </a>
    </div>
{/foreach}

<div class="post-content">
    {$post->profile->content}
</div>

{include file='footer.tpl'
         leftcolumn='user/lib/left-column.tpl'
         rightcolumn='user/lib/right-column.tpl'}