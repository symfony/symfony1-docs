Appendice A - codice JavaScript per sfWidgetFormGMapAddress
===========================================================

Il seguente codice è il JavaScript necessario per far funzionare
il widget `sfWidgetFormGMapAddress`:

    [js]
    function sfGmapWidgetWidget(options){
      // attributi globali di this
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

      // recupera l'elemento del dom
      this.lng      = jQuery("#" + this.options.longitude);
      this.lat      = jQuery("#" + this.options.latitude);
      this.address  = jQuery("#" + this.options.address);
      this.lookup   = jQuery("#" + this.options.lookup);

      // crea l'oggetto google geocoder
      this.geocoder = new GClientGeocoder();

      // crea la mappa
      this.map = new GMap2(jQuery("#" + this.options.map).get(0));
      this.map.setCenter(new GLatLng(this.lat.val(), this.lng.val()), 13);
      this.map.setUIToDefault();

      // riferimenti incrociati tra gli oggetti
      this.map.sfGmapWidgetWidget = this;
      this.geocoder.sfGmapWidgetWidget = this;
      this.lookup.get(0).sfGmapWidgetWidget = this;

      // aggiunge la località predefinita
      var point = new GLatLng(this.lat.val(), this.lng.val());
      var marker = new GMarker(point);
      this.map.setCenter(point, 15);
      this.map.addOverlay(marker);

      // collega l'azione di movimento sulla mappa
      GEvent.addListener(this.map, "move", function() {
         var center = this.getCenter();
         this.sfGmapWidgetWidget.lng.val(center.lng());
         this.sfGmapWidgetWidget.lat.val(center.lat());
      });

      // collega l'azione click sulla mappa
      GEvent.addListener(this.map, "click", function(overlay, latlng) {
        if (latlng != null) {
          sfGmapWidgetWidget.activeWidget = this.sfGmapWidgetWidget;

          this.sfGmapWidgetWidget.geocoder.getLocations(
            latlng,
            sfGmapWidgetWidget.reverseLookupCallback
          );
        }
      });

      // collega l'azione click al campo di ricerca
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
      // prende il widget e pulisce la variabile di stato
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
      // prende il widget e pulisce la variabile di stato
      var widget = sfGmapWidgetWidget.activeWidget;
      sfGmapWidgetWidget.activeWidget = null;

      widget.map.clearOverlays();

      if (!response || response.Status.code != 200) {
        alert('no address found');
        return;
      }

      // prende l'informazione sulla località e inizializza le variabili
      var place = response.Placemark[0];
      var point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
      var marker = new GMarker(point);

      // aggiunge il segnalino al centro della mappa
      widget.map.setCenter(point, 15);
      widget.map.addOverlay(marker);

      // aggiorna i valori
      widget.address.val(place.address);
      widget.lat.val(place.Point.coordinates[1]);
      widget.lng.val(place.Point.coordinates[0]);
    }
