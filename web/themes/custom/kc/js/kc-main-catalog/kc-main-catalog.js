/**
 * @file
 * Custom behaviors for example layout.
 */

(function ($, Drupal, once, drupalSettings) {

  'use strict';

  Drupal.behaviors.kcMainCatalog = {
    attach (context, settings) {

      const menuCatalogAccordion = once('kcMainCatalog', document.getElementById('mainCatalog'));

      if (menuCatalogAccordion.length > 0) {
        document.addEventListener('click', (event) => {
          const collapseTrigger = document.querySelector('[aria-controls="mainCatalog"]');

          const collapsibleElement = menuCatalogAccordion[0].querySelector('.main-catalog__cols');

          if (
            !collapsibleElement.contains(event.target) && !collapseTrigger.contains(event.target)
          ) {
            menuCatalogAccordion[0].classList.remove('show');
          }
        });
        // Open and close accoriond items on hover.
        // let accordionItems = menuCatalogAccordion[0].querySelectorAll('.accordion-header');
        //
        // accordionItems.forEach((accordionItem, i) => {
        //   let accordionButton = accordionItem.querySelector('.accordion-button');
        //
        //   accordionItem.addEventListener('mouseover', () => {
        //     let overAriaControl = accordionButton.getAttribute('aria-controls');
        //     let overControlledAria = menuCatalogAccordion[0].querySelector('#' + overAriaControl);
        //     let isOpen = overControlledAria.classList.contains('show') ? true : false;
        //
        //     if (!isOpen) {
        //       accordionItem.querySelector('[data-bs-toggle="collapse"]').click();
        //     }
        //   });
        // });

        // Scroll to letter on click.
        // let brandsAlpabeticalList = menuCatalogAccordion[0].querySelector('.main-catalog__body__brands__alphabetical');
        // let alphabeticalLinks = brandsAlpabeticalList.querySelectorAll('.main-catalog__body__brands__alphabetical-link');
        //
        // alphabeticalLinks.forEach((link, i) => {
        //   let letter = link.getAttribute('data-litera-target');
        //
        // });

      }
    }
  };

}(jQuery, Drupal, once, drupalSettings));
