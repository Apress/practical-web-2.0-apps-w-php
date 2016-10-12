Event.observe(window, 'load', function() {

    var publishButton   = $('status-publish');
    var unpublishButton = $('status-unpublish');
    var deleteButton    = $('status-delete');

    if (publishButton) {
        publishButton.observe('click', function(e) {
            if (!confirm('Click OK to publish this post'))
                Event.stop(e);
        });
    }

    if (unpublishButton) {
        unpublishButton.observe('click', function(e) {
            if (!confirm('Click OK to unpublish this post'))
                Event.stop(e);
        });
    }

    if (deleteButton) {
        deleteButton.observe('click', function(e) {
            if (!confirm('Click OK to permanently delete this post'))
                Event.stop(e);
        });
    }

    var im = new BlogImageManager('post_images');
});
