{include file='header.tpl' section='blogmanager'}

<form method="post" action="{geturl action='edit'}?id={$fp->post->getId()}">

{if $fp->hasError()}
    <div class="error">
        An error has occurred in the form below. Please check
        the highlighted fields and resubmit the form.
    </div>
{/if}


<fieldset>
    <legend>Blog Post Details</legend>

    <div class="row" id="form_title_container">
        <label for="form_title">Title:</label>
        <input type="text" id="form_title"
               name="username" value="{$fp->title|escape}" />
        {include file='lib/error.tpl' error=$fp->getError('title')}
    </div>

    <div class="row" id="form_date_container">
        <label for="form_date">Date of Entry:</label>

        {html_select_date prefix='ts_created'
                          time=$fp->ts_created
                          start_year=-5
                          end_year=+5}

        {html_select_time prefix='ts_created'
                          time=$fp->ts_created
                          display_seconds=false
                          use_24_hours=false}

        {include file='lib/error.tpl' error=$fp->getError('date')}
    </div>
</fieldset>

<div class="wysiwyg">
    {wysiwyg name='content' value=$fp->content}
    {include file='lib/error.tpl' error=$fp->getError('content')}
</div>

<div class="submit">
    {if $fp->post->isLive()}
        {assign var='label' value='Save Changes'}
    {elseif $fp->post->isSaved()}
        {assign var='label' value='Save Changes and Send Live'}
    {else}
        {assign var='label' value='Create and Send Live'}
    {/if}

    <input type="submit" value="{$label|escape}" />
    {if !$fp->post->isLive()}
        <input type="submit" name="preview" value="Preview This Post" />
    {/if}
</div>

</form>

{include file='footer.tpl'
         leftcolumn='blogmanager/lib/left-column.tpl'}
