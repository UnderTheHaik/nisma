jQuery(function ($) {
  $("form.variations_form")
    .on("show_variation", function (event, variation, purchasable) {
      $(this).find(".single_add_to_cart_button").prop("disabled", !purchasable);
    })
    .on("hide_variation", function () {
      $(this).find(".single_add_to_cart_button").prop("disabled", true);
    });
});
