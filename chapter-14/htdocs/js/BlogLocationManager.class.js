google.load('maps', '2');

BlogLocationManager = Class.create();

BlogLocationManager.prototype = {

    url       : null,

    post_id   : null,   // ID of the blog post being managed
    container : null,   // DOM element in which map is shown
    map       : null,   // The instance of Google Maps
    geocoder  : null,   // Used to look up addresses

    markers   : $H({}), // holds all markers added to map

    markerTemplate : new Template(
        '<div>'
      + '    #{desc}<br />'
      + '    <input type="button" value="Remove Location" />'
      + '</div>'
    ),

    initialize : function(container, form)
    {
        form           = $(form);
        this.url       = form.action;
        this.post_id   = $F(form.post_id);
        this.container = $(container);

        this.geocoder = new google.maps.ClientGeocoder();

        Event.observe(window, 'load', this.loadMap.bind(this));
        form.observe('submit', this.onFormSubmit.bindAsEventListener(this));
    },

    loadMap : function()
    {
        if (!google.maps.BrowserIsCompatible())
            return;

        Event.observe(window, 'unload', this.unloadMap.bind(this));

        this.map = new google.maps.Map2(this.container);
        this.zoomAndCenterMap();

        this.map.addControl(new google.maps.MapTypeControl());
        this.map.addControl(new google.maps.ScaleControl());
        this.map.addControl(new google.maps.LargeMapControl());

        var overviewMap = new google.maps.OverviewMapControl();
        this.map.addControl(overviewMap);
        overviewMap.hide(true);

        this.map.enableDoubleClickZoom();
        this.map.enableContinuousZoom();

        var options = {
            parameters : 'action=get&post_id=' + this.post_id,
            onSuccess  : this.loadLocationsSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },

    zoomAndCenterMap : function()
    {
        var bounds = new google.maps.LatLngBounds();
        this.markers.each(function(pair) {
            bounds.extend(pair.value.getPoint());
        });

        if (bounds.isEmpty()) {
            this.map.setCenter(new google.maps.LatLng(0, 0),
                               1,
                               G_HYBRID_MAP);
        }
        else {
            var zoom = Math.max(1, this.map.getBoundsZoomLevel(bounds) - 1);
            this.map.setCenter(bounds.getCenter(), zoom);
        }
    },

    addMarkerToMap : function(id, lat, lng, desc)
    {
        this.removeMarkerFromMap(id);

        this.markers[id] = new google.maps.Marker(
            new google.maps.LatLng(lat, lng),
            { 'title' : desc, draggable : true }
        );
        this.markers[id].location_id = id;

        var that = this;
        google.maps.Event.addListener(this.markers[id], 'dragend', function() {
            that.dragComplete(this);
        });
        google.maps.Event.addListener(this.markers[id], 'dragstart', function() {
            this.closeInfoWindow();
        });

        this.map.addOverlay(this.markers[id]);

        var html = this.markerTemplate.evaluate({
            'location_id' : id,
            'lat'         : lat,
            'lng'         : lng,
            'desc'        : desc
        });

        var node = Builder.build(html);
        var button = node.getElementsBySelector('input')[0];

        button.setAttribute('location_id', id);
        button.observe('click', this.onRemoveMarker.bindAsEventListener(this));

        this.markers[id].bindInfoWindow(node);

        return this.markers[id];
    },

    removeMarkerFromMap : function(location_id)
    {
        if (!this.hasMarker(location_id))
            return;

        this.map.removeOverlay(this.markers[location_id]);
        this.markers.remove(location_id);
    },

    hasMarker : function(location_id)
    {
        var location_ids = this.markers.keys();

        return location_ids.indexOf(location_id) >= 0;
    },

    loadLocationsSuccess : function(transport)
    {
        var json = transport.responseText.evalJSON(true);

        if (json.locations == null)
            return;

        json.locations.each(function(location) {
            this.addMarkerToMap(
                location.location_id,
                location.latitude,
                location.longitude,
                location.description
            );
        }.bind(this));

        this.zoomAndCenterMap();
    },

    onFormSubmit : function(e)
    {
        Event.stop(e);

        var form = Event.element(e);
        var address = $F(form.location).strip();

        if (address.length == 0)
            return;

        this.geocoder.getLocations(address, this.createPoint.bind(this));
    },

    createPoint : function(locations)
    {
        if (locations.Status.code != G_GEO_SUCCESS) {
            // something went wrong:
            var msg = '';
            switch (locations.Status.code) {
                case G_GEO_BAD_REQUEST:
                    msg = 'Unable to parse request';
                    break;
                case G_GEO_MISSING_QUERY:
                    msg = 'Query not specified';
                    break;
                case G_GEO_UNKNOWN_ADDRESS:
                    msg = 'Unable to find address';
                    break;
                case G_GEO_UNAVAILABLE_ADDRESS:
                    msg = 'Forbidden address';
                    break;
                case G_GEO_BAD_KEY:
                    msg = 'Invalid API key';
                    break;
                case G_GEO_TOO_MANY_QUERIES:
                    msg = 'Too many geocoder queries';
                    break;
                case G_GEO_SERVER_ERROR:
                default:
                    msg = 'Unknown server error occurred';
            }
            message_write(msg);
            return;
        }

        var placemark = locations.Placemark[0];

        var options = {
            parameters : 'action=add'
                       + '&post_id=' + this.post_id
                       + '&description=' + escape(placemark.address)
                       + '&latitude=' + placemark.Point.coordinates[1]
                       + '&longitude=' + placemark.Point.coordinates[0],
            onSuccess  : this.createPointSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },

    createPointSuccess : function(transport)
    {
        var json = transport.responseText.evalJSON(true);

        if (json.location_id == 0) {
            message_write('Error adding location to blog post');
            return;
        }

        marker = this.addMarkerToMap(
            json.location_id,
            json.latitude,
            json.longitude,
            json.description
        );

        google.maps.Event.trigger(marker, 'click');

        this.zoomAndCenterMap();
    },

    dragComplete : function(marker)
    {
        var point = marker.getPoint();
        var options = {
            parameters : 'action=move'
                       + '&post_id=' + this.post_id
                       + '&location_id=' + marker.location_id
                       + '&latitude=' + point.lat()
                       + '&longitude=' + point.lng(),
            onSuccess  : this.onDragCompleteSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },

    onDragCompleteSuccess : function(transport)
    {
        var json = transport.responseText.evalJSON(true);

        if (json.location_id && this.hasMarker(json.location_id)) {
            var point = new google.maps.LatLng(json.latitude, json.longitude);

            var marker = this.addMarkerToMap(
                json.location_id,
                json.latitude,
                json.longitude,
                json.description
            );
            google.maps.Event.trigger(marker, 'click');
        }
    },

    onRemoveMarker : function(e)
    {
        var button = Event.element(e);
        var location_id = button.getAttribute('location_id');

        var options = {
            parameters : 'action=delete'
                       + '&post_id=' + this.post_id
                       + '&location_id=' + location_id,
            onSuccess  : this.onRemoveMarkerSuccess.bind(this)
        };

        new Ajax.Request(this.url, options);
    },

    onRemoveMarkerSuccess : function(transport)
    {
        var json = transport.responseText.evalJSON(true);

        if (json.location_id)
            this.removeMarkerFromMap(json.location_id);
    },

    unloadMap : function()
    {
        google.maps.Unload();
    }
};
