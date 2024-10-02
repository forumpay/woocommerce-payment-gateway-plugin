jQuery(document).ready(function($) {
    const apiUrl = orderEditSettings.apiUrl;
    const nonce = orderEditSettings.nonce;

    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id') ?? urlParams.get('post');

    const paymentIdElement = $('.order_payment_id');
    const paymentId = paymentIdElement.text();

    $('#forumpay_api_sync_payment').on('click', function(e) {
        e.preventDefault();

        var $button = $(this);
        var originalText = $button.text();
        $button.prop('disabled', true);
        $button.text('Syncing ...');

        $.ajax({
            url: apiUrl + '?act=syncPayment',
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-WP-Nonce': nonce,
            },
            data: JSON.stringify({
                orderId: orderId,
                payment_id: paymentId,
                forumpay_nonce: nonce,
            }),
            success: function(response) {
                $button.prop('disabled', false);
                $button.text(originalText);
                alert('Order status updated to: ' + response?.status);
                window.location.reload();
            },
            error: function(error) {
                $button.prop('disabled', false);
                $button.text(originalText);

                var message = "Unknown error occurred. Please contact support.";
                alert(message);
            }
        });
    });
});
