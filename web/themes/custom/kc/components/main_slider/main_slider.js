(function(Drupal, once, drupalSettings) {

  Drupal.behaviors.mainSlider = {
    attach(context) {

      const mainSliders = once('mainSlider', document.getElementsByClassName('kc-main-slider'));
      const titles = drupalSettings.titles;

      mainSliders.forEach((mainSlider) => {
        let options = {
          type: 'loop',
          pagination: true,
          pagination: 'splide__pagination kc-main-slider__pagination',

          arrows: false,
          classes: {
            pagination: 'splide__pagination kc-main-slider__pagination',
          }
        };

        const splide = new Splide( mainSlider, options);

        // splide.on( 'pagination:mounted', function ( data ) {
        //   data.items.forEach( function ( item, key ) {
        //     item.button.textContent = String( titles[key] );
        //   });
        // });

        splide.mount();
      });

    },
  };

})(Drupal, once, drupalSettings);
