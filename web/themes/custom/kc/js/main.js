/**
 * @file
 * Custom behaviors for example layout.
 */

(function ($, Drupal, once, drupalSettings) {

  'use strict';

  Drupal.behaviors.kcMain = {
    attach (context, settings) {

      const href_links = once('kcMain', document.getElementsByClassName('kc-link--target'));

      // Handler for click on href links.
      href_links.forEach(function(href_link, index) {
        href_link.addEventListener('click', (event) => {
          let target = href_link.getAttribute('data-target');
          console.log(target)

          let target_element = document.getElementById(target);
          target_element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });

      // Add sticky to header on scroll.
      window.addEventListener('scroll', function() {
        let scroll_position = window.scrollY || document.documentElement.scrollTop;
        let trigger_point = 100;

        let header = document.getElementById('header');

        if (scroll_position > trigger_point) {
          header.classList.add('sticky');
        } else {
          header.classList.remove('sticky');
        }
      });

    }
  };

}(jQuery, Drupal, once, drupalSettings));
