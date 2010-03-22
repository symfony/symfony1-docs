Apêndice A - Código JavaScript para o sfWidgetFormGMapAddress
========================================================

O código a seguir é o JavaScript necessário para que o widget `sfWidgetFormGMapAddress` funcione:

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

      // Recupera o elemento DOM
      this.lng      = jQuery("#" + this.options.longitude);
      this.lat      = jQuery("#" + this.options.latitude);
      this.address  = jQuery("#" + this.options.address);
      this.lookup   = jQuery("#" + this.options.lookup);

      // criar o objeto geocoder do Google
      this.geocoder = new GClientGeocoder();

      // criar o mapa
      this.map = new GMap2(jQuery("#" + this.options.map).get(0));
      this.map.setCenter(new GLatLng(this.lat.val(), this.lng.val()), 13);
      this.map.setUIToDefault();

      // objeto de referência cruzada
      this.map.sfGmapWidgetWidget = this;
      this.geocoder.sfGmapWidgetWidget = this;
      this.lookup.get(0).sfGmapWidgetWidget = this;

      // adiciona a localização padrão
      var point = new GLatLng(this.lat.val(), this.lng.val());
      var marker = new GMarker(point);
      this.map.setCenter(point, 15);
      this.map.addOverlay(marker);

      // vincular a ação de movimento no mapa
      GEvent.addListener(this.map, "move", function() {
         var center = this.getCenter();
         this.sfGmapWidgetWidget.lng.val(center.lng());
         this.sfGmapWidgetWidget.lat.val(center.lat());
      });

      // vincular a ação clique no mapa
      GEvent.addListener(this.map, "click", function(overlay, latlng) {
        if (latlng != null) {
          sfGmapWidgetWidget.activeWidget = this.sfGmapWidgetWidget;

          this.sfGmapWidgetWidget.geocoder.getLocations(
            latlng,
            sfGmapWidgetWidget.reverseLookupCallback
          );
        }
      });

      // vincular a ação de clicar no campo de pesquisa
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
      // obter o widget e limpar a variável de estado
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
      // obter o widget e limpar a variável de estado
      var widget = sfGmapWidgetWidget.activeWidget;
      sfGmapWidgetWidget.activeWidget = null;

      widget.map.clearOverlays();

      if (!response || response.Status.code != 200) {
        alert('no address found');
        return;
      }

      //  Obter informações de localização e inicializar variáveis
      var place = response.Placemark[0];
      var point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
      var marker = new GMarker(point);

      // adicionar marcador e centralizar o mapa
      widget.map.setCenter(point, 15);
      widget.map.addOverlay(marker);

      // atualizar valores
      widget.address.val(place.address);
      widget.lat.val(place.Point.coordinates[1]);
      widget.lng.val(place.Point.coordinates[0]);
    }
