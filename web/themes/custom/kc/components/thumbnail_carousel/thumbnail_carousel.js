(function(Drupal) {

  Drupal.behaviors.thumbnailCarousel = {
    attach(context) {

      const mainSliders = once('thumbnailCarouselMain', document.getElementsByClassName('thumbnail-carousel__main'));

      mainSliders.forEach((mainSlider) => {
        let options = {
          type : 'fade',
          rewind: true,
          arrows: false,
          pagination: false,

        };

        const main = new Splide( mainSlider, options );

        const mainThumbnailSliders = once('thumbnailCarouselMain', document.getElementsByClassName('thumbnail-carousel__thumbnails'));

        mainThumbnailSliders.forEach((mainThumbnailSlider) => {
          let options = {
            isNavigation: true,
            perPage: 5,
            rewind: true,
            arrows: false,
            pagination: false,
            gap: '1rem'
          };

          const thumbnails = new Splide( mainThumbnailSlider, options );

          main.sync( thumbnails );
          main.mount();
          thumbnails.mount();
        });
      });


    },
  };

})(Drupal);
