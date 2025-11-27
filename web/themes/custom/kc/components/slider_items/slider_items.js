(function(Drupal, once, drupalSettings) {

  Drupal.behaviors.sliderItems = {
    attach(context) {

      const itemsSliders = once('sliderItems', document.getElementsByClassName('kc-slider-items'));

      itemsSliders.forEach((itemsSlider) => {
        let options = {
          gap: '2rem',
          perMove: 1,
          classes: {
            arrows: 'kc-slider-items__arrows',
            arrow: 'kc-slider-items__arrow',
            prev: 'kc-slider-items__arrow--prev',
        		next: 'kc-slider-items__arrow--next',
          },
          pagination: false,
          arrowPath: 'M0.87952 0.880461C0.316366 1.44439 2.38934e-06 2.20914 2.35449e-06 3.00654C2.31963e-06 3.80393 0.316365 4.56868 0.87952 5.13261L15.7491 20.0182L0.879519 34.9037C0.332324 35.4709 0.0295424 36.2305 0.0363866 37.019C0.0432309 37.8074 0.359153 38.5617 0.91611 39.1192C1.47307 39.6768 2.2265 39.993 3.01413 39.9999C3.80175 40.0067 4.56056 39.7036 5.12711 39.1558L22.1205 22.1442C22.6836 21.5803 23 20.8156 23 20.0182C23 19.2208 22.6836 18.456 22.1205 17.8921L5.12711 0.880461C4.56379 0.316702 3.79986 -8.39265e-07 3.00332 -8.74083e-07C2.20677 -9.08901e-07 1.44284 0.316701 0.87952 0.880461Z',
        };

        const splide = new Splide( itemsSlider, options);

        splide.mount();
      });

    },
  };

})(Drupal, once, drupalSettings);
