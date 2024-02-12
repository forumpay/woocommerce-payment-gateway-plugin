<script setup>

import {
  computed,
  onMounted,
  onUnmounted,
  watch,
} from 'vue';
import { useStore } from 'vuex';

import PaymentStateProcessing from '../components/PaymentStateProcessing.vue';
import PaymentStateUnderpayment from '../components/PaymentStateUnderpayment.vue';
import PaymentStateBlocked from '../components/PaymentStateBlocked.vue';
import PaymentStateOther from '../components/PaymentStateOther.vue';
import PaymentStateWaiting from '../components/PaymentStateWaiting.vue';
import Loader from '../components/Loader.vue';
import InstructionBox from '../components/InstructionBox.vue';
import CancelBox from '../components/CancelBox.vue';

const store = useStore();

const props = defineProps({
  payment: {
    type: Object,
    default() {
      return {};
    },
  },
});

const rate = computed(() => store.state.rate);
const paymentStatus = computed(() => store.state.paymentCheck);
const paymentStatusLoading = computed(() => store.state.paymentCheckLoading);
const cryptoCurrency = computed(() => store.state.cryptoCurrency);

let checkPaymentInterval = null;

watch(() => store.state.paymentCheckError, async (newPaymentCheckError) => {
  if (newPaymentCheckError) {
    if (newPaymentCheckError.code === 4001) {
      clearInterval(checkPaymentInterval);
    }
  }
});

onMounted(() => {
  if (!store.state.paymentCheckIgnoreResult) {
    store.dispatch('checkPayment', props.payment.payment_id);
    checkPaymentInterval = setInterval(
      () => store.dispatch('checkPayment', props.payment.payment_id),
      5000,
    );
  }
});

onUnmounted(() => {
  clearInterval(checkPaymentInterval);
});

const onCancelPayment = () => {
  store.dispatch('showCancel');
};

const onCancelPaymentConfirm = (reason, description) => {
  clearInterval(checkPaymentInterval);
  store.dispatch(
    'cancelPayment',
    {
      forceRedirect: false,
      reason,
      description,
    },
  );
};

</script>

<template>
  <Loader v-if="!paymentStatus" />

  <InstructionBox
    :notices="payment.notices"
  />

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
    :invoice-amount="rate.invoice_amount"
    :invoice-currency="rate.invoice_currency"
    :rate="rate.rate"
    :amount-exchange="rate.amount_exchange"
    :network-processing-fee="rate.network_processing_fee"

    :notices="payment.notices"

    @cancel-payment="onCancelPayment"
  />

  <PaymentStateProcessing
    v-if="paymentStatus && paymentStatus.state === 'processing'"
    :status="paymentStatus.status"
    :wait-time="paymentStatus.wait_time"
    :amount="paymentStatus.amount"
    :payment="paymentStatus.payment ?? '0'"
    :amount-currency="paymentStatus.currency"
  />

  <PaymentStateUnderpayment
    v-else-if="paymentStatus && paymentStatus.state === 'underpayment'"
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
  />
</template>
