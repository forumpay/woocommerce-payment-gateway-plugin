<script>
import Dropdown from './Dropdown.vue';
import DateInput from './DateInput.vue';
import countries from '../utils/countries';

const PayerCompanyForm = {
  components: {
    DateInput,
    Dropdown,
  },
  data() {
    return {
      store: this.$store,
      countries,
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
    <label for="payer_company-name" class="forumpay-pgw-payer-label">Company</label>
    <input
      id="payer_company-name"
      v-model="payerCompany"
      class="forumpay-pgw-payer-input"
      type="text"
      placeholder="Company"
      required
      @blur="handleTextBlur('setPayer', 'payer_company', payerCompany)"
    />
    <Dropdown
      v-model="payerCountryOfIncorporation"
      label="Country"
      :options="countries"
      option-key="code"
      filter-property="name"
      placeholder="Select a country"
      class="forumpay-pgw-payer-dropdown"
    >
      <template #selected="{ selected }">
        <span>{{ selected ? selected.name : 'Country' }}</span>
      </template>
      <template #option="{ option, markText }">
        <span v-html="markText(option.name)" />
      </template>
    </Dropdown>
    <label for="payer_individual-email" class="forumpay-pgw-payer-label">Email Address <span>(optional)</span></label>
    <input
      id="payer_individual-email"
      v-model="payerEmail"
      class="forumpay-pgw-payer-input"
      type="email"
      placeholder="Email Address"
      readonly
    />
    <DateInput
      v-model="payerDateOfIncorporation"
      label="Date of Incorporation"
      :computed-date="payerDateOfIncorporation"
      :is-required="true"
    />
  </div>
</template>
