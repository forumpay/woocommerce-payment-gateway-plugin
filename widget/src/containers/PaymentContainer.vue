<script>
import PaymentStateProcessing from '../components/PaymentStateProcessing.vue';
import PaymentStateUnderpayment from '../components/PaymentStateUnderpayment.vue';
import PaymentStateBlocked from '../components/PaymentStateBlocked.vue';
import PaymentStateOther from '../components/PaymentStateOther.vue';
import PaymentStateWaiting from '../components/PaymentStateWaiting.vue';
import Loader from '../components/Loader.vue';
import CancelBox from '../components/CancelBox.vue';
import cryptoPaymentStatsHandler from '../utils/cryptoPaymentStatsHandler';

const PaymentContainer = {
  components: {
    PaymentStateProcessing,
    PaymentStateUnderpayment,
    PaymentStateBlocked,
    PaymentStateOther,
    PaymentStateWaiting,
    Loader,
    CancelBox,
  },
  data() {
    return {
      store: this.$store,
      checkPaymentInterval: null,
      checkBeforeCloseInterval: null,
    };
  },
  props: {
    payment: {
      type: Object,
      default: {},
    },
  },
  computed: {
    rate() {
      return this.store.state.rate;
    },
    paymentStatus() {
      return this.store.state.paymentCheck;
    },
    paymentStatusLoading() {
      return this.store.state.paymentCheckLoading;
    },
    cryptoCurrency() {
      return this.store.state.cryptoCurrency;
    },
  },
  watch: {
    'store.state.paymentCheckError': {
      handler(newPaymentCheckError) {
        if (newPaymentCheckError) {
          if (newPaymentCheckError.code === 4001) {
            clearInterval(this.checkPaymentInterval);
          }
        }
      },
    },
  },
  mounted() {
    if (!this.store.state.paymentCheckIgnoreResult) {
      this.store.dispatch('checkPayment', this.payment.payment_id);
      this.checkPaymentInterval = setInterval(
        () => this.store.dispatch('checkPayment', this.payment.payment_id),
        5000,
      );
    }

    this.checkBeforeCloseInterval = setInterval(() => {
      cryptoPaymentStatsHandler.event('beforeClose');
    }, 3000);
  },
  unmounted() {
    clearInterval(this.checkPaymentInterval);
    clearInterval(this.checkBeforeCloseInterval);
  },
  methods: {
    onCancelPayment() {
      this.store.dispatch('showCancel');
    },
    onCancelPaymentConfirm(reason, description) {
      clearInterval(this.checkPaymentInterval);
      this.store.dispatch(
        'cancelPayment',
        {
          forceRedirect: false,
          reason,
          description,
        },
      );
    },
    onRegenerateQr(walletAppId) {
      this.store.dispatch('regeneratePaymentQr', walletAppId);
    },
  },
};

export default PaymentContainer;
</script>

<template>
  <Loader v-if="!paymentStatus" />

  <CancelBox
    @on-cancel-payment-confirm="onCancelPaymentConfirm"
  />

  <PaymentStateWaiting
    v-if="paymentStatus && paymentStatus.state === 'waiting'"
    :loading="false"
    :refreshing="paymentStatusLoading"
    :currency="cryptoCurrency"
    :status="paymentStatus.status"
    :inserted="paymentStatus.inserted"
    :state="paymentStatus.state"
    :type="paymentStatus.type"
    :wait-time="paymentStatus.wait_time"
    :amount="paymentStatus.amount"
    :amount-currency="paymentStatus.currency"
    :payment="paymentStatus.payment ?? '0'"
    :address="payment.address"
    :min-confirmations="payment.min_confirmations"
    :fast-transaction-fee="payment.fast_transaction_fee"
    :fast-transaction-fee-currency="payment.fast_transaction_fee_currency"
    :qr="payment.qr"
    :qr-img="payment.qr_img"
    :qr-alt="payment.qr_alt"
    :qr-alt-img="payment.qr_alt_img"

    :invoice-no="payment.payment_id"
    :invoice-amount="paymentStatus.invoice_amount"
    :invoice-currency="paymentStatus.invoice_currency"
    :rate="rate.rate"
    :amount-exchange="rate.amount_exchange"
    :network-processing-fee="rate.network_processing_fee"

    :item-name="payment.item_name"
    :invoice-surcharge-amount="payment.invoice_surcharge_amount"
    :invoice-amount-with-surcharge="payment.invoice_amount_with_surcharge"

    :notices="payment.notices"

    :beneficiary-vasp-details="payment.beneficiary_vasp_details ?? null"

    @cancel-payment="onCancelPayment"
    @regenerate-qr="onRegenerateQr"
  />

  <PaymentStateProcessing
    v-if="paymentStatus && paymentStatus.state === 'processing'"
    :status="paymentStatus.status"
    :wait-time="paymentStatus.wait_time"
    :amount="paymentStatus.amount"
    :original-amount="paymentStatus.original_amount"
    :payment="paymentStatus.payment ?? '0'"
    :amount-currency="paymentStatus.currency"
    :payment-id="payment.payment_id"
    :invoice-amount="paymentStatus.invoice_amount"
    :invoice-currency="paymentStatus.invoice_currency"
  />

  <PaymentStateUnderpayment
    v-else-if="paymentStatus && paymentStatus.state === 'underpayment'"
    :invoice-no="payment.payment_id"
    :invoice-amount="paymentStatus.invoice_amount"
    :invoice-currency="paymentStatus.invoice_currency"
    :currency="cryptoCurrency"
    :status="paymentStatus.status"
    :state="paymentStatus.state"
    :confirmed="paymentStatus.confirmed ?? false"
    :wait-time="paymentStatus.wait_time"
    :amount="paymentStatus.amount"
    :amount-currency="paymentStatus.currency"
    :missing-amount="paymentStatus.underpayment?.missing_amount"
    :missing-currency="paymentStatus.currency"
    :payment="paymentStatus.payment ?? '0'"
    :address="paymentStatus.underpayment?.address"
    :qr="paymentStatus.underpayment?.qr"
    :qr-img="paymentStatus.underpayment?.qr_img"
    :qr-alt="paymentStatus.underpayment?.qr_alt"
    :qr-alt-img="paymentStatus.underpayment?.qr_alt_img"
  />

  <PaymentStateBlocked
    v-else-if="paymentStatus && paymentStatus.state === 'blocked' && (!paymentStatus.confirmed ?? false)"
    :confirmed="paymentStatus.confirmed ?? false"
  />

  <PaymentStateOther
    v-else-if="paymentStatus && paymentStatus.state !== 'waiting'"
    :status="paymentStatus.status"
    :state="paymentStatus.state"
    :confirmed="paymentStatus.confirmed ?? false"
    :wait-time="paymentStatus.wait_time"
    :amount="paymentStatus.amount"
    :amount-currency="paymentStatus.currency"
    :payment="paymentStatus.payment ?? '0'"
    :payment-id="payment.payment_id"
    :invoice-amount="paymentStatus.invoice_amount"
    :invoice-currency="paymentStatus.invoice_currency"
  />
</template>
