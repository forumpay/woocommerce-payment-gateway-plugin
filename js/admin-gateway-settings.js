jQuery(document).ready(function($) {
  const gatewayId = gatewaySettings.gatewayId;
  const apiUrl = gatewaySettings.apiUrl;
  const nonce = gatewaySettings.nonce;

  // Initialize dialog for messages
  var $dialog = $('<div id="forumpay-dialog" title="ForumPay Notification" style="display: none;"></div>').appendTo('body');
  $dialog.dialog({
    'dialogClass': 'wp-dialog',
    'modal': true,
    'autoOpen': false,
    'draggable': false,
    'closeOnEscape': true,
    'width': 600,
    'closeText': '',
    'buttons': {}
  });

  // Initialize autoconfigure webhook button (now rendered by PHP)
  function initializeAutoconfigureButton() {
    const autoconfigureButton = $('#woocommerce_forumpay_autoconfigure_webhook');
    if (autoconfigureButton.length > 0) {
      autoconfigureButton.on('click', function(e) {
        e.preventDefault();
        autoConfigureWebhook();
      });
    }
  }

  // Initialize test webhook button (now rendered by PHP)
  function initializeTestWebhookButton() {
    const testButton = $('#woocommerce_forumpay_test_webhook');
    const webhookUrlField = $(`#woocommerce_${gatewayId}_webhook_url`);
    
    if (testButton.length > 0 && webhookUrlField.length > 0) {
      // Add click handler for test webhook button
      testButton.on('click', function(e) {
        e.preventDefault();
        testWebhook();
      });
      
      // Initially disable the test button if webhook URL is empty
      updateTestButtonState();
      
      // Update button state when webhook URL changes
      webhookUrlField.on('input change', function() {
        updateTestButtonState();
      });
    }
  }

  // Initialize test API credentials button
  function initializeTestApiCredentials() {
    const apiTestButton = $('#woocommerce_forumpay_api_test');
    
    if (apiTestButton.length > 0) {
      apiTestButton.on('click', function(e) {
        e.preventDefault();
        testApiCredentials();
      });
    }
  }

  // Update test button state based on webhook URL field value
  function updateTestButtonState() {
    const webhookUrlField = $(`#woocommerce_${gatewayId}_webhook_url`);
    const testButton = $('#woocommerce_forumpay_test_webhook');
    
    if (webhookUrlField.val().trim() === '') {
      testButton.prop('disabled', true);
    } else {
      testButton.prop('disabled', false);
    }
  }

  // Test webhook functionality (only tests, doesn't change value)
  function testWebhook() {
    const webhookUrl = $(`#woocommerce_${gatewayId}_webhook_url`).val().trim();
    
    if (!webhookUrl) {
      showDialog('Test Webhook', 
        `<div class="notice notice-error inline">
          <p><span class="dashicons dashicons-no-alt" style="color: #d63638; margin-right: 5px;"></span><strong>No Webhook URL</strong></p>
          <p>Please enter a webhook URL before testing.</p>
        </div>`);
      return;
    }

    const $button = $('#woocommerce_forumpay_test_webhook');
    const originalText = $button.text();
    $button.prop('disabled', true);
    $button.text('Testing ...');

    $.ajax({
      url: apiUrl + '?act=ping',
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
        webhookUrl: webhookUrl,
        forumpay_nonce: nonce,
      }),
      success: function(response) {
        $button.prop('disabled', false);
        $button.text(originalText);

        const {webhookSuccess, webhookPingResponse, message} = response || {};
        const {status, duration, webhookUrl: responseWebhookUrl, responseCode, responseBody} = webhookPingResponse || {};

        if (!webhookSuccess || !webhookPingResponse) {
          showDialog('Webhook Test Results', `Server responded: ${message}`);
          return;
        }

        if (webhookSuccess === 'OK') {
          showDialog('Webhook Test Results', 
            `<div class="notice notice-success inline">
              <p><span class="dashicons dashicons-yes-alt" style="color: #00a32a; margin-right: 5px;"></span><strong>Webhook Test Successful</strong></p>
              <p><strong>Server Response:</strong> ${message}</p>
              <p><strong>Tested URL:</strong> <code>${webhookUrl}</code></p>
              <p><small>The webhook endpoint is working correctly and can receive notifications from ForumPay.</small></p>
            </div>`);
          return;
        }

        showDialog('Webhook Test Results', 
          `<div class="notice notice-warning inline">
            <p><span class="dashicons dashicons-warning" style="color: #dba617; margin-right: 5px;"></span><strong>Webhook Test Failed</strong></p>
            <p><strong>Server Response:</strong> ${response?.message || 'No response received'}</p>
            <p><strong>Tested URL:</strong> <code>${webhookUrl}</code></p>
            <p><small>The webhook endpoint could not be reached or returned an error. Please check the URL and ensure it's accessible.</small></p>
          </div>`);
      },
      error: function(error) {
        $button.prop('disabled', false);
        $button.text(originalText);

        var message = error?.responseJSON?.message ?? "Unknown error occurred. Please contact support.";

        showDialog('Webhook Test Error', 
          `<div class="notice notice-error inline">
            <p><span class="dashicons dashicons-no-alt" style="color: #d63638; margin-right: 5px;"></span><strong>Webhook Test Failed</strong></p>
            <p>${message}</p>
            <p><strong>Tested URL:</strong> <code>${webhookUrl}</code></p>
          </div>`);
      }
    });
  }

  // Test API credentials functionality
  function testApiCredentials() {
    const $button = $('#woocommerce_forumpay_api_test');
    const originalText = $button.text();
    $button.prop('disabled', true);
    $button.text('Testing ...');

    $.ajax({
      url: apiUrl + '?act=ping', 
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
        webhookUrl: $('#woocommerce_forumpay_webhook_url').val(),
        forumpay_nonce: nonce,
      }),
      success: function(response) {
        $button.prop('disabled', false);
        $button.text(originalText);

        const {message} = response || {};

        const isSuccess = response && response.message === 'OK';
        const statusClass = isSuccess ? 'notice-success' : 'notice-warning';
        const iconClass = isSuccess ? 'dashicons-yes-alt' : 'dashicons-warning';
        const iconColor = isSuccess ? '#00a32a' : '#dba617';
        const title = isSuccess ? 'API Test Successful' : 'API Test Results';
        const status = isSuccess ? 'Connected to ForumPay successfully' : 'Connection issues detected';
        const serverResponse = isSuccess ? message : (message || 'Unknown response');

        const content = `<div class="notice ${statusClass} inline">
          <p><span class="dashicons ${iconClass}" style="color: ${iconColor}; margin-right: 5px;"></span><strong>${title}</strong></p>
          <p><strong>Server Response:</strong> ${serverResponse}</p>
          <p><strong>API Status:</strong> ${status}</p>
        </div>`;

        showDialog('API Test Results', content);
      },
      error: function(error) {
        $button.prop('disabled', false);
        $button.text(originalText);

        var message = error?.responseJSON?.message ?? "Unknown error occurred. Please contact support.";

        showDialog('API Test Error', 
          `<div class="notice notice-error inline">
            <p><span class="dashicons dashicons-no-alt" style="color: #d63638; margin-right: 5px;"></span><strong>API Test Failed</strong></p>
            <p>${message}</p>
          </div>`);
      }
    });
  }

  // Auto configure webhook functionality (moved from the old button)
  function autoConfigureWebhook() {
    const $button = $('#woocommerce_forumpay_autoconfigure_webhook');
    const originalText = $button.text();
    $button.prop('disabled', true);
    $button.text('Testing webhook URLs via ForumPay API...');

    // Get current domain
    var baseUrl = window.location.origin;

    // Debug with proxy
    if (window?.forumpayDebugBaseUrl) {
      baseUrl = window.forumpayDebugBaseUrl
    }

    // Define URLs to test
    var urlsToTest = [
      {
        name: 'Legacy Format',
        url: baseUrl + '/?wc-api=wc_forumpay&act=webhook'
      },
      {
        name: 'New Format',   
        url: baseUrl + '/wp-json/wc-api/wc_forumpay?act=webhook'
      }
    ];
    
    // Test each URL via ForumPay API
    var testResults = [];
    var workingUrl = null;
    var completedTests = 0;
    
    urlsToTest.forEach(function(urlInfo, index) {
      $.ajax({
        url: apiUrl + '?act=ping',
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
          webhookUrl: urlInfo.url,
          forumpay_nonce: nonce,
        }),
        success: function(response) {
          completedTests++;
          
          const {webhookSuccess, webhookPingResponse, message} = response || {};
          const {status, duration} = webhookPingResponse || {};
          
          if (webhookSuccess === 'OK' || webhookSuccess === true) {
            isWorking = true;
          } else {
            isWorking = false;
          }
          
          var testResult = {
            name: urlInfo.name,
            url: urlInfo.url,
            working: isWorking,
            webhookSuccess: webhookSuccess,
            status: status,
            duration: duration,
            message: message
          };
          
          testResults.push(testResult);
          
          if (testResult.working && !workingUrl) {
            workingUrl = urlInfo.url;
            // Auto-fill the working URL
            $('#woocommerce_forumpay_webhook_url').val(urlInfo.url);
            // Update test button state after setting the value
            updateTestButtonState();
          }
          
          // Check if all tests are complete
          if (completedTests === urlsToTest.length) {
            showFinalResults();
          }
        },
        error: function(error) {
          completedTests++;
          
          var testResult = {
            name: urlInfo.name,
            url: urlInfo.url,
            working: false,
            error: error?.responseJSON?.message || 'Request failed',
            errorDetails: error
          };
          
          testResults.push(testResult);
          
          // Check if all tests are complete
          if (completedTests === urlsToTest.length) {
            showFinalResults();
          }
        }
      });
    });
    
    // Function to show final results
    function showFinalResults() {
      $button.prop('disabled', false);
      
      if (workingUrl) {
        $button.text('Webhook Configured!');
        $button.removeClass('button-secondary').addClass('button-primary');
        
        const content = `<div class="notice notice-success inline">
          <p><span class="dashicons dashicons-yes-alt" style="color: #00a32a; margin-right: 5px;"></span><strong>Webhook Auto-Configuration Successful</strong></p>
          <p><strong>Working Endpoint Found:</strong></p>
          <p><code>${workingUrl}</code></p>
          <p><strong>Test Results:</strong></p>
          ${testResults.map(result => 
            `<p><span class="dashicons ${result.working ? 'dashicons-yes-alt' : 'dashicons-no-alt'}" style="color: ${result.working ? '#00a32a' : '#d63638'}; margin-right: 5px;"></span> 
            <strong>${result.name}:</strong> ${result.working ? 'Working' : 'Failed'}
            ${result.status ? `<br><small>Status: ${result.status}</small>` : ''}
            </p>`
          ).join('')}
          <p><small>This URL has been automatically set in the webhook field above.</small></p>
        </div>`;
        
        showDialog('Webhook Auto-Configuration', content);
        return;
      }

      $button.text('No Working Endpoints Found');
      $button.removeClass('button-secondary').addClass('button-secondary');
      
      const content = `<div class="notice notice-error inline">
        <p><span class="dashicons dashicons-no-alt" style="color: #d63638; margin-right: 5px;"></span><strong>No Working Webhook Endpoints Found</strong></p>
        <p><strong>Test Results:</strong></p>
        ${testResults.map(result => 
          `<p><span class="dashicons dashicons-no-alt" style="color: #d63638; margin-right: 5px;"></span> 
          <strong>${result.name}:</strong> Failed - ${result.error || 'No response'}</p>`
        ).join('')}
        <p><small>Please check your server configuration and ensure the webhook endpoints are accessible.</small></p>
      </div>`;
      
      showDialog('Webhook Test Failed', content);
      
      // Reset button after delay
      setTimeout(function() {
        $button.prop('disabled', false);
        $button.text(originalText);
        $button.removeClass('button-primary button-secondary').addClass('button-secondary');
      }, 3000);
    }
  }

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
    $(`#woocommerce_${gatewayId}_${field}_modify_order_total`).closest('tr').toggle(isChecked);

    validateThreshold(field, isChecked);
    // Ensure dependent fields are updated based on current state
    toggleModifyOrderDescription(field);
  }

  function validateThreshold(field, isChecked) {
    const thresholdElement = $(`#woocommerce_${gatewayId}_${field}_threshold`);

    if (thresholdElement.length === 0) {
      return;
    }

    if (field === 'accept_underpayment' && isChecked) {
      thresholdElement.attr('pattern', '^(?!0+(\\.0{1,2})?$)(\\d{1,2})(\\.\\d{1,2})?$');
      thresholdElement.attr('title', 'Please enter a valid percentage between 0 and 100 or leave blank to accept any underpayment amount.');
    }

    if (field === 'accept_overpayment' && isChecked) {
      thresholdElement.attr('pattern', '^(?:0\\.\\d{2,}|[1-9]\\d*(\\.\\d{1,2})?)$');
      thresholdElement.attr('title', 'Please enter a valid percentage or leave blank to accept any overpayment amount.');
    }

    if (!isChecked) {
      thresholdElement.removeAttr('pattern');
      thresholdElement.removeAttr('title');
    }

    thresholdElement.on('blur', function () {
        this.value = this.value.trim();
        const value = this.value;

        if (!isNaN(value) && value !== '') {
          const floatValue = Number(value);
          const decimals = (value.split('.')[1] || '').length;

          if (decimals > 2) {
            this.value = floatValue.toFixed(2);
          }
        }

        if (/^0\d+/.test(value) || parseFloat(value) === 0) {
          this.value = 0;
        }
    });
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

  // Initialize buttons
  initializeAutoconfigureButton();
  initializeTestWebhookButton();
  initializeTestApiCredentials();

  // Initial update of button states
  updateTestButtonState();

  // Show messages in dialog
  function showDialog(title, message) {
    $dialog.dialog('option', 'title', title);
    $dialog.html(message);
    $dialog.dialog('open');
  }
});
