<script>
import Container from '../components/Container.vue';
import Loader from '../components/Loader.vue';
import PageLogo from '../components/PageLogo.vue';

const KycContainer = {
  components: {
    Container,
    Loader,
    PageLogo,
  },
  data() {
    return {
      store: this.$store,
    };
  },
  computed: {
    kycError() {
      return this.store.state.kycError;
    },
    isKycError() {
      return !!this.store.state.kycError;
    },
    kycPin() {
      return this.store.state.kycPin;
    },
    isPinEmpty() {
      return this.store.state.kycPin.length === 0;
    },
    cryptoCurrency() {
      return this.store.state.cryptoCurrency;
    },
    isLoading() {
      return !!this.store.state.loading;
    },
  },
  watch: {
    kycPin(value) {
      this.store.commit('setKycPin', value);
    },
  },
  methods: {
    onPinSubmit() {
      this.store.commit('setLoading', true);
      this.store.commit('setKycError', null);
      this.store.dispatch('startPayment', this.cryptoCurrency);
    },
    onCancel() {
      this.store.commit('setLoading', true);
      this.store.dispatch(
        'cancelPayment',
        {
          forceRedirect: true,
          restoreCart: true,
          reason: 'other',
          description: 'Auto error: Customer decided to cancel during KYC confirmation.',
        },
      );
    },
  },
};

export default KycContainer;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center">
    <Container class="forumpay-pgw-container--gap">
      <div v-if="!isLoading">
        <div>
          <div class="forumpay-pgw-guide-text-padding-bottom">
            <b>Verification code needed</b>
          </div>
          <p class="forumpay-pgw-guide-text-align-start forumpay-pgw-guide-text-thin-font-weight">
            Due to regulatory requirements we will need to verify your identity.<br />
            A verification code has been sent to your email.
            If you do not see the email in a few minutes,
            please check your "junk mail" or "spam" folder.
          </p>
          <br>
          <label for="kycPin" />
          <input
            id="kycPin"
            v-model="kycPin"
            type="password"
            placeholder="Enter verification code here"
          />
          <div
            v-if="isKycError"
            class="forumpay-pgw-guide-text-alert forumpay-pgw-guide-text-font-size-medium"
          >
            {{ kycError }}
          </div>
        </div>
        <div class="forumpay-pgw-horizontal-list">
          <button
            class="forumpay-pgw-button forumpay-pgw-button--cancel"
            type="button"
            @click="onCancel"
          >
            Cancel
          </button>
          <button
            class="forumpay-pgw-button"
            type="button"
            :disabled="isPinEmpty"
            @click="onPinSubmit"
          >
            Continue
          </button>
        </div>
      </div>
      <div v-else>
        <Loader :loading="true" :small="true" />
      </div>
    </Container>
  </div>
  <PageLogo />
</template>
