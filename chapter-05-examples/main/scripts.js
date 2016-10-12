var settings = {
    containerId        : 'container',
    statusId           : 'status',
    processUrl         : 'processor.php',
    statusSuccessColor : '#99ff99',
    statusErrorColor   : '#ff9999'
};

function init()
{
    $(settings.statusId).defaultContent = $(settings.statusId).innerHTML;
    loadItems();
}

Event.observe(window, 'load', init);

function setStatus(msg)
{
    var isError = typeof arguments[1] == 'boolean' && arguments[1];
    var status = $(settings.statusId);

    var options = {
        startcolor : isError ?
                        settings.statusErrorColor :
                        settings.statusSuccessColor,
        afterFinish : function() {
            this.update(this.defaultContent);
        }.bind(status)
    };

    status.update(msg);
    new Effect.Highlight(status, options);
}

function loadItems()
{
    var options = {
        method     : 'post',
        parameters : 'action=load',
        onSuccess  : loadItemsSuccess,
        onFailure  : loadItemsFailure
    };

    setStatus('Loading items');
    new Ajax.Request(settings.processUrl, options);
}

function loadItemsFailure(transport)
{
    setStatus('Error loading items', true);
}

function loadItemsSuccess(transport)
{
    // Find all <item></item> tags in the return XML, then cast it into
    // a Prototype Array
    var xml = transport.responseXML;
    var items = $A(xml.documentElement.getElementsByTagName('item'));

    // If no items were found there's nothing to do
    if (items.size() == 0) {
        setStatus('No items found', true);
        return;
    }

    // Create an array to hold items in. These will become the <li></li> tags.
    // By storing them in an array, we can pass this array to Builder when
    // creating the surrounding <ul></ul>. This will automatically take care
    // of adding the items to the list
    var listItems = $A();

    // Use Builder to create an <li> element for each item in the list, then
    // add it to the listItems array
    items.each(function(s) {
        var elt = Builder.node('li',
                               { id : 'item_' + s.getAttribute('id') },
                               s.getAttribute('title'));
        listItems.push(elt);
    });

    // Finally, create the surrounding <ul> element, giving it the className
    // property (for styling purposes), and the 'items' values as an Id (for
    // form processing - Scriptaculous uses this as the form item name).
    // The final parameter is the <li> element we just created
    var list = Builder.node('ul',
                            { className : 'sortable', id : 'items' },
                            listItems);

    // Get the item container and clear its content
    var container = $(settings.containerId);
    container.update();

    // Add the <ul> to the empty container
    container.appendChild(list);

    // Finally, make the list into a Sortable list. All we need to pass here
    // is the callback function to use after an item has been dropped in a
    // new position.
    Sortable.create(list, { onUpdate : saveItemOrder.bind(list) });
}

function saveItemOrder()
{
    var options = {
        method     : 'post',
        parameters : 'action=save&' + Sortable.serialize(this),
        onSuccess  : saveItemOrderSuccess,
        onFailure  : saveItemOrderFailure
    };

    new Ajax.Request(settings.processUrl, options);
}

function saveItemOrderFailure(transport)
{
    setStatus('Error saving order', true);
}

function saveItemOrderSuccess(transport)
{
    if (transport.responseText != '1')
        return saveItemOrderFailure(transport);

    setStatus('Order saved');
}