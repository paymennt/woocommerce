let loadPaymenntFrame = function () {
  var cardFrame = document.querySelector(".card-frame");
  var errorMessage = document.querySelector(".error-message");
  var infoMessage = document.querySelector(".info-message");
  var publicKey = document.getElementById("public-key");
  if (publicKey != undefined && cardFrame.childElementCount < 1) {
    publicKey = publicKey.value;

    PaymenntJS.init({
      publicKey: publicKey,
      mode: PaymenntJS.modes.TEST,
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
          var paymentToken = document.getElementById("payment-token");
          if (paymentToken != "") {
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

jQuery(document.body).on("updated_checkout", function () {
  var cardFrame = document.querySelector(".card-frame");
  if (cardFrame.childElementCount < 1) {
    loadPaymenntFrame();
  }
});

jQuery(function ($) {
  var checkout_form = $("form.woocommerce-checkout");
  checkout_form.on("checkout_place_order", tokenRequest);
});

var tokenRequest = function () {
  var paymentToken = document.getElementById("payment-token");
  if (paymentToken.value == "") {
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

var errorCallback = function (data) {
  console.log(data);
  return false;
};

jQuery(document).on("TokenReceived", function (e, eventInfo) {
  var paymentToken = document.getElementById("payment-token");
  if (paymentToken.value != "") {
    successCallback();
  } else {
    errorCallback(paymentToken.value);
  }
});
