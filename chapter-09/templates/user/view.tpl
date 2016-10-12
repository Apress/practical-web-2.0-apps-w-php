{include file='header.tpl'}

<div class="post-date">
    {$post->ts_created|date_format:'%b %e, %Y %l:%M %p'}
</div>

<div class="post-content">
    {$post->profile->content}
</div>

{include file='footer.tpl'
         leftcolumn='user/lib/left-column.tpl'}
