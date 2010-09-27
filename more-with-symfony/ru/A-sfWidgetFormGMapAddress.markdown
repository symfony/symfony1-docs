Приложение A - JavaScript-код для sfWidgetFormGMapAddress
========================================================

Следующий JavaScript-код необходим для работы виджета `sfWidgetFormGMapAddress`:

    [js]
    function sfGmapWidgetWidget(options){
      // глобальные атрибуты
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

      // получение dom-элементов
      this.lng      = jQuery("#" + this.options.longitude);
      this.lat      = jQuery("#" + this.options.latitude);
      this.address  = jQuery("#" + this.options.address);
      this.lookup   = jQuery("#" + this.options.lookup);

      // создание объекта google geocoder
      this.geocoder = new GClientGeocoder();

      // создание карты
      this.map = new GMap2(jQuery("#" + this.options.map).get(0));
      this.map.setCenter(new GLatLng(this.lat.val(), this.lng.val()), 13);
      this.map.setUIToDefault();

      // связывание объектов между собой
      this.map.sfGmapWidgetWidget = this;
      this.geocoder.sfGmapWidgetWidget = this;
      this.lookup.get(0).sfGmapWidgetWidget = this;

      // добавление позиции по умолчанию
      var point = new GLatLng(this.lat.val(), this.lng.val());
      var marker = new GMarker(point);
      this.map.setCenter(point, 15);
      this.map.addOverlay(marker);

      // связывание действия move карты
      GEvent.addListener(this.map, "move", function() {
         var center = this.getCenter();
         this.sfGmapWidgetWidget.lng.val(center.lng());
         this.sfGmapWidgetWidget.lat.val(center.lat());
      });

      // связывание действия click по карте
      GEvent.addListener(this.map, "click", function(overlay, latlng) {
        if (latlng != null) {
          sfGmapWidgetWidget.activeWidget = this.sfGmapWidgetWidget;

          this.sfGmapWidgetWidget.geocoder.getLocations(
            latlng,
            sfGmapWidgetWidget.reverseLookupCallback
          );
        }
      });

      // связывание действия click по полю lookup
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
      // получение виджета и очистка переменной состояния
      var widget = sfGmapWidgetWidget.activeWidget;
      sfGmapWidgetWidget.activeWidget = null;

      if (!point) {
        alert("адрес не найден");
        return;
      }

      widget.map.clearOverlays();
      widget.map.setCenter(point, 15);
      var marker = new GMarker(point);
      widget.map.addOverlay(marker);
    }

    sfGmapWidgetWidget.reverseLookupCallback = function(response)
    {
      // получение виджета и очиста переменной состояния
      var widget = sfGmapWidgetWidget.activeWidget;
      sfGmapWidgetWidget.activeWidget = null;

      widget.map.clearOverlays();

      if (!response || response.Status.code != 200) {
        alert('адрес не найден');
        return;
      }

      // получение информации о расположении и инициализация переменных
      var place = response.Placemark[0];
      var point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
      var marker = new GMarker(point);

      // добавление маркера и центровка карты
      widget.map.setCenter(point, 15);
      widget.map.addOverlay(marker);

      // обновление значений
      widget.address.val(place.address);
      widget.lat.val(place.Point.coordinates[1]);
      widget.lng.val(place.Point.coordinates[0]);
    }
