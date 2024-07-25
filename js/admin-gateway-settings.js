jQuery(document).ready(function($) {
  const gatewayId = gatewaySettings.gatewayId;
  const apiUrl = gatewaySettings.apiUrl;
  const nonce = gatewaySettings.nonce;

  function initializeFieldHandlers(field) {
    $(`#woocommerce_${gatewayId}_${field}`).change(() => {
      togglePaymentOptions(field);
    });

    $(`#woocommerce_${gatewayId}_${field}_modify_order_total`).change(() => {
      toggleModifyOrderDescription(field);
    });
  }

  // Toggle visibility of payment options
  function togglePaymentOptions(field) {
    const isChecked = $(`#woocommerce_${gatewayId}_${field}`).is(':checked');

    $(`#woocommerce_${gatewayId}_${field}_threshold`).closest('tr').toggle(isChecked);
    $(`#woocommerce_${gatewayId}_${field}_threshold`).prop('required',isChecked);
    $(`#woocommerce_${gatewayId}_${field}_modify_order_total`).closest('tr').toggle(isChecked);

    // Ensure dependent fields are updated based on current state
    toggleModifyOrderDescription(field);
  }

  // Toggle visibility of additional description based on the modify order total checkbox
  function toggleModifyOrderDescription(field) {
    const isModifyOrderChecked = $(`#woocommerce_${gatewayId}_${field}_modify_order_total`).is(':checked');
    const isModifyOrderVisible = $(`#woocommerce_${gatewayId}_${field}_modify_order_total`).is(':visible');
    $(`#woocommerce_${gatewayId}_${field}_modify_order_total_description`).closest('tr').toggle(isModifyOrderVisible && isModifyOrderChecked);
    $(`#woocommerce_${gatewayId}_${field}_modify_order_total_description`).prop('required', isModifyOrderVisible && isModifyOrderChecked);
  }

  // List of fields to apply dynamic behavior
  const fields = [
    'accept_underpayment',
    'accept_overpayment',
    'accept_latepayment',
  ];

  fields.forEach(field => {
    togglePaymentOptions(field);
    initializeFieldHandlers(field);
  });

  $('#woocommerce_forumpay_api_test').on('click', function(e) {
    e.preventDefault();

    var $button = $(this);
    var originalText = $button.text();
    $button.prop('disabled', true);
    $button.text('Testing ...');

    // You can perform AJAX calls or other logic here
    $.ajax({
      url: apiUrl + '?act=ping', // This is a global variable in WP admin that points to admin-ajax.php
      type: 'POST',
      dataType: 'json',
      headers: {
          'X-WP-Nonce': nonce,
      },
      data: JSON.stringify({
        apiEnv: $('#woocommerce_forumpay_api_url').val(),
        apiKey: $('#woocommerce_forumpay_api_user').val(),
        apiSecret: $('#woocommerce_forumpay_api_key').val(),
        apiUrlOverride: $('#woocommerce_forumpay_api_url_override').val(),
        forumpay_nonce: nonce,
      }),
      success: function(response) {
        $button.prop('disabled', false);
        $button.text(originalText);
        alert('Server responded: ' + response?.message);
      },
      error: function(error) {
        $button.prop('disabled', false);
        $button.text(originalText);
        const now = new Date();

        // Extract the UTC components
        const year = now.getUTCFullYear();
        const month = String(now.getUTCMonth() + 1).padStart(2, '0'); // Months are zero-indexed, so add 1
        const day = String(now.getUTCDate()).padStart(2, '0');
        const hours = String(now.getUTCHours()).padStart(2, '0');
        const minutes = String(now.getUTCMinutes()).padStart(2, '0');
        const seconds = String(now.getUTCSeconds()).padStart(2, '0');

        // Format the date and time in UTC
        const currentDateTimeUTC = `${year}-${month}-${day} ${hours}:${minutes}:${seconds} UTC`;

        var message = '';

        if (error?.responseJSON?.code > 0) {
          message += error.responseJSON.code + ' - ';
        }

        message += error?.responseJSON?.message ?? "Unknown error occurred. Please contact support."

        message += "\n\n" + "Date: " + currentDateTimeUTC;
        if (error?.responseJSON?.cfray_id) {
          message += "\n" + "Ray Id: " + error?.responseJSON?.cfray_id;
        }

        alert(message);
      }
    });
  });
});
