let loadPaymenntFrame = function () {
  var paymenntMethodDiv = document.querySelector(
    ".payment_method_paymennt_card"
  );
  var cardFrame = paymenntMethodDiv.querySelector(".card-frame");
  var errorMessage = paymenntMethodDiv.querySelector(".error-message");
  var infoMessage = paymenntMethodDiv.querySelector(".info-message");
  var publicKey = paymenntMethodDiv.querySelector("#public-key");
  var mode = paymenntMethodDiv.querySelector("#mode").value;

  if (publicKey != undefined && cardFrame.childElementCount < 1) {
    publicKey = publicKey.value;

    PaymenntJS.init({
      wrapper: undefined,
      publicKey: publicKey,
      mode:
        mode == 1
          ? PaymenntJS.modes.LIVE
          : mode == 2
          ? PaymenntJS.modes.STAGING
          : PaymenntJS.modes.TEST,
      onTokenized: function (data) {
        infoMessage.value = data.token;

        jQuery(document).trigger("TokenReceived");
      },
      onTokenizationFailed: function (data) {
        errorMessage.innerText = data.error;
      },

      onValidationUpdate: function (data) {
        if (!data.valid) {
          var message = null;
          for (var i = 0; i < data.validationErrors.length; ++i) {
            var fieldValidation = data.validationErrors[i];
            if (!message || fieldValidation.field === data.lastActiveField) {
              message = fieldValidation.message;
            }
          }
          if (!message) {
            message = data.error ? data.error : "Invalid card details";
          }
          errorMessage.innerText = message;
        } else {
          errorMessage.innerText = "";
        }
      },
    });

    var element = document.querySelector("form.checkout");
    if (element.addEventListener) {
      element.addEventListener(
        "submit",
        function (evt) {
          var paymenntToken =
            paymenntMethodDiv.querySelector("#paymennt-token");
          var paymenntMethodRadioField = document.querySelector(
            "#payment_method_paymennt_card"
          );

          if (
            paymenntMethodRadioField &&
            paymenntMethodRadioField.checked &&
            paymenntToken != ""
          ) {
            evt.preventDefault();
            evt.stopPropagation();
            PaymenntJS.submitPayment();
            return false;
          }
        },
        true
      );
    }
  }
};

jQuery(document.body).on(
  "updated_checkout payment_method_selected",
  function () {
    var cardFrame = document.querySelector(".card-frame");
    if (cardFrame.childElementCount < 1) {
      loadPaymenntFrame();
    }
  }
);

jQuery(document.querySelector("#payment_method_paymennt_card")).on(
  "load",
  function () {
    var cardFrame = document.querySelector(".card-frame");
    if (cardFrame.childElementCount < 1) {
      loadPaymenntFrame();
    }
  }
);

jQuery(function ($) {
  var checkout_form = $("form.woocommerce-checkout");
  checkout_form.on("checkout_place_order", tokenRequest);
});

var tokenRequest = function () {
  var paymenntToken = document.querySelector(
    ".payment_method_paymennt_card #paymennt-token"
  );
  if (paymenntToken.value == "") {
    PaymenntJS.submitPayment();
  } else return true;
};

var successCallback = function () {
  var checkout_form = jQuery(
    document.querySelector("form.woocommerce-checkout")
  );

  // deactivate the tokenRequest function event
  checkout_form.off("checkout_place_order", tokenRequest);

  // submit the form now
  checkout_form.submit();
  return true;
};

jQuery(document).on("TokenReceived", function (e, eventInfo) {
  var paymenntToken = document.querySelector(
    ".payment_method_paymennt_card #paymennt-token"
  );
  if (paymenntToken.value != "") {
    successCallback();
  } else {
    return false;
  }
});
