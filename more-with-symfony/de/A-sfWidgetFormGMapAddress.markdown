Anhang A - JavaScript Quellcode für die sfWidgetFormGMapAddress
========================================================

Der folgende Quellcode ist das notwendige JavaScript, damit das needed to make the
`sfWidgetFormGMapAddress` Widget funktioniert:

    [js]
    function sfGmapWidgetWidget(options){
      // this global attributes
      this.lng      = null;
      this.lat      = null;
      this.address  = null;
      this.map      = null;
      this.geocoder = null;
      this.options  = options;

      this.init();
    }

    sfGmapWidgetWidget.prototype = new Object();

    sfGmapWidgetWidget.prototype.init = function() {

      if(!GBrowserIsCompatible())
      {
        return;
      }

      // hole das DOM Element
      this.lng      = jQuery("#" + this.options.longitude);
      this.lat      = jQuery("#" + this.options.latitude);
      this.address  = jQuery("#" + this.options.address);
      this.lookup   = jQuery("#" + this.options.lookup);

      // erstelle das Google Geocoder Objekt
      this.geocoder = new GClientGeocoder();

      // erstelle die Karte
      this.map = new GMap2(jQuery("#" + this.options.map).get(0));
      this.map.setCenter(new GLatLng(this.lat.val(), this.lng.val()), 13);
      this.map.setUIToDefault();

      // Querverknüfungen für das Objekt
      this.map.sfGmapWidgetWidget = this;
      this.geocoder.sfGmapWidgetWidget = this;
      this.lookup.get(0).sfGmapWidgetWidget = this;

      // die Standardposition
      var point = new GLatLng(this.lat.val(), this.lng.val());
      var marker = new GMarker(point);
      this.map.setCenter(point, 15);
      this.map.addOverlay(marker);

      // die Bewegen-Aktion an die Karte binden
      GEvent.addListener(this.map, "move", function() {
         var center = this.getCenter();
         this.sfGmapWidgetWidget.lng.val(center.lng());
         this.sfGmapWidgetWidget.lat.val(center.lat());
      });

      // die Klick-Aktion an die Karte binden
      GEvent.addListener(this.map, "click", function(overlay, latlng) {
        if (latlng != null) {
          sfGmapWidgetWidget.activeWidget = this.sfGmapWidgetWidget;

          this.sfGmapWidgetWidget.geocoder.getLocations(
            latlng,
            sfGmapWidgetWidget.reverseLookupCallback
          );
        }
      });

      // die Klick-Aktion an das Suchenfeld binden
      this.lookup.bind('click', function(){
        sfGmapWidgetWidget.activeWidget = this.sfGmapWidgetWidget;

        this.sfGmapWidgetWidget.geocoder.getLatLng(
          this.sfGmapWidgetWidget.address.val(),
          sfGmapWidgetWidget.lookupCallback
        );

        return false;
      })
    }

    sfGmapWidgetWidget.activeWidget = null;
    sfGmapWidgetWidget.lookupCallback = function(point)
    {
      // das Widget holen und die Statusvariable leeren
      var widget = sfGmapWidgetWidget.activeWidget;
      sfGmapWidgetWidget.activeWidget = null;

      if (!point) {
        alert("address not found");
        return;
      }

      widget.map.clearOverlays();
      widget.map.setCenter(point, 15);
      var marker = new GMarker(point);
      widget.map.addOverlay(marker);
    }

    sfGmapWidgetWidget.reverseLookupCallback = function(response)
    {
      // das Widget holen und die Statusvariable leeren
      var widget = sfGmapWidgetWidget.activeWidget;
      sfGmapWidgetWidget.activeWidget = null;

      widget.map.clearOverlays();

      if (!response || response.Status.code != 200) {
        alert('no address found');
        return;
      }

      // die Ortsinformation holen und die Variablen initialisieren
      var place = response.Placemark[0];
      var point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
      var marker = new GMarker(point);

      // Pin hinzufügen und Karte zentrieren
      widget.map.setCenter(point, 15);
      widget.map.addOverlay(marker);

      // Werte aktualisieren
      widget.address.val(place.address);
      widget.lat.val(place.Point.coordinates[1]);
      widget.lng.val(place.Point.coordinates[0]);
    }
