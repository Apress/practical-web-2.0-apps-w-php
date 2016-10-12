{include file='header.tpl' section='blogmanager' maps=true}

<form method="post"
      action="{geturl action='locationsmanage'}"
      id="location-add">

    <div>
        <input type="hidden" name="post_id" value="{$post->getId()}" />

        Add a new location:
        <input type="text" name="location" />
        <input type="submit" value="Add Location" />
    </div>
</form>

<div id="location-manager"></div>

<script type="text/javascript">
    new BlogLocationManager('location-manager', 'location-add');
</script>

{include file='footer.tpl'
         leftcolumn='blogmanager/lib/left-column.tpl'}
