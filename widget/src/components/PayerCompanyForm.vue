<script>
import CountryInput from './CountryInput.vue';
import DateInput from './DateInput.vue';

const PayerCompanyForm = {
  components: {
    DateInput,
    CountryInput,
  },
  data() {
    return {
      store: this.$store,
    };
  },
  computed: {
    payer() {
      return this.store.state.payer;
    },
    payerCompany: {
      get() {
        return this.payer.payer_company;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_company: value,
        });
      },
    },
    payerDateOfIncorporation: {
      get() {
        return this.payer.payer_date_of_incorporation;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_date_of_incorporation: value,
        });
      },
    },
    payerCountryOfIncorporation: {
      get() {
        return this.payer.payer_country_of_incorporation;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_country_of_incorporation: value,
        });
      },
    },
    payerEmail: {
      get() {
        return this.payer.payer_email;
      },
    },
  },
  methods: {
    handleTextBlur(method, key, value) {
      const updateData = {
        ...this.payer,
        [key]: value.trim().replace(/\s+/g, ' '),
      };
      this.store.commit(method, updateData);
    },
  },
};

export default PayerCompanyForm;
</script>

<template>
  <div>
    <label for="payer_company-name" class="forumpay-pgw-payer-label">Company name*</label>
    <input
      id="payer_company-name"
      v-model="payerCompany"
      class="forumpay-pgw-payer-input"
      type="text"
      placeholder="Enter your company name here"
      required
      @blur="handleTextBlur('setPayer', 'payer_company', payerCompany)"
    />
    <label for="payer_individual-email" class="forumpay-pgw-payer-label">Email</label>
    <input
      id="payer_individual-email"
      v-model="payerEmail"
      class="forumpay-pgw-payer-input"
      type="email"
      placeholder="Enter your email here"
      readonly
    />
    <CountryInput
      v-model="payerCountryOfIncorporation"
      label="Country of incorporation*"
      :computed-country="payerCountryOfIncorporation"
      :is-required="true"
    />
    <DateInput
      v-model="payerDateOfIncorporation"
      label="Date of incorporation*"
      :computed-date="payerDateOfIncorporation"
      :is-required="true"
    />
  </div>
</template>
