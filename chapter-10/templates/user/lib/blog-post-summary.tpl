{capture assign='url'}{geturl username=$user->username
                              url=$post->url
                              route='post'}{/capture}

<div class="teaser">
    <h3>
        <a href="{$url|escape}" class="entry-title" rel="bookmark">
            {$post->profile->title}
        </a>
    </h3>

    <div class="teaser-date">
        {$post->ts_created|date_format:'%b %e, %Y %l:%M %p'}
    </div>

    <div class="teaser-content summary">
        {$post->getTeaser(500)}
    </div>

    <div class="teaser-links">
        <a href="{$url|escape}">Read More...</a>
        {if $linkToBlog}
            |
            <a href="{geturl username=$user->username route='user'}">
                Published by {$user->username|escape}
            </a>
        {/if}
    </div>
</div>
