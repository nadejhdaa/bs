(function(Drupal) {

  Drupal.behaviors.galleryPoint = {
    attach(context) {

      const pointGalleries = once('galleryPoint', document.getElementsByClassName('kc-point-gallery'));

      pointGalleries.forEach((pointGallery) => {

        let splide = new Splide( pointGallery, {pagination: false, arrows: false}).mount();
        let thumbnails = pointGallery.getElementsByClassName( 'kc-point-gallery__thumbnail' );

        for ( var i = 0; i < thumbnails.length; i++ ) {
          initThumbnail( thumbnails[ i ], i );
        }

        function initThumbnail( thumbnail, index ) {
          thumbnail.addEventListener( 'click', function () {
            splide.go( index );
          } );
        }
      });

    },
  };

})(Drupal);
