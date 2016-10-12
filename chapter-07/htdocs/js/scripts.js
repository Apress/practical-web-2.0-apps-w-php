var settings = {
    messages : 'messages'
};

function init(e)
{
    // check if the messages element exists and is visible,
    // and if so, apply the highlight effect to it
    var messages = $(settings.messages);

    if (messages && messages.visible()) {
        new Effect.Highlight(messages);
    }
}

Event.observe(window, 'load', init);
