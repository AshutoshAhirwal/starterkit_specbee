(function($, Drupal) {
  Drupal.behaviors.stormAccordion = {
    attach: function(context) {
      $('.block--storm-accordion', context).find('.field--storm-field-accordion-items .field__items').accordion({
        collapsible: true,
        heightStyle: "content"
      });
    }
  };
}(jQuery, Drupal));
