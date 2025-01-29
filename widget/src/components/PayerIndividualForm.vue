<script>
import CountryInput from './CountryInput.vue';
import DateInput from './DateInput.vue';

const PayerIndividualForm = {
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
    payerFirstName: {
      get() {
        return this.payer.payer_first_name;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_first_name: value,
        });
      },
    },
    payerLastName: {
      get() {
        return this.payer.payer_last_name;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_last_name: value,
        });
      },
    },
    payerEmail: {
      get() {
        return this.payer.payer_email;
      },
    },
    payerCountry: {
      get() {
        return this.payer.payer_country_of_residence;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_country_of_residence: value,
        });
      },
    },
    payerCountryOfBirth: {
      get() {
        return this.payer.payer_country_of_birth;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_country_of_birth: value,
        });
      },
    },
    payerDateOfBirth: {
      get() {
        return this.payer.payer_date_of_birth;
      },
      set(value) {
        this.store.commit('setPayer', {
          ...this.payer,
          payer_date_of_birth: value,
        });
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

export default PayerIndividualForm;
</script>

<template>
  <div>
    <label for="payer_individual-first_name" class="forumpay-pgw-payer-label">First name*</label>
    <input
      id="payer_individual-first_name"
      v-model="payerFirstName"
      class="forumpay-pgw-payer-input"
      type="text"
      placeholder="Enter your first name here"
      required
      @blur="handleTextBlur('setPayer', 'payer_first_name', payerFirstName)"
    />
    <label for="payer_individual-last_name" class="forumpay-pgw-payer-label">Last name*</label>
    <input
      id="payer_individual-last_name"
      v-model="payerLastName"
      class="forumpay-pgw-payer-input"
      type="text"
      placeholder="Enter your last name here"
      required
      @blur="handleTextBlur('setPayer', 'payer_last_name', payerLastName)"
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
      v-model="payerCountry"
      label="Country of residence"
      :computed-country="payerCountry"
    />
    <DateInput
      v-model="payerDateOfBirth"
      label="Date of birth*"
      :computed-date="payerDateOfBirth"
      :is-required="true"
    />
    <CountryInput
      v-model="payerCountryOfBirth"
      label="Country of birth*"
      :computed-country="payerCountryOfBirth"
      :is-required="true"
    />
  </div>
</template>
