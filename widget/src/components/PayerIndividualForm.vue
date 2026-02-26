<script>
import Dropdown from './Dropdown.vue';
import DateInput from './DateInput.vue';
import countries from '../utils/countries';

const PayerIndividualForm = {
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
    <label for="payer_individual-first_name" class="forumpay-pgw-payer-label">First Name</label>
    <input
      id="payer_individual-first_name"
      v-model="payerFirstName"
      class="forumpay-pgw-payer-input"
      type="text"
      placeholder="First Name"
      required
      @blur="handleTextBlur('setPayer', 'payer_first_name', payerFirstName)"
    />
    <label for="payer_individual-last_name" class="forumpay-pgw-payer-label">Last Name</label>
    <input
      id="payer_individual-last_name"
      v-model="payerLastName"
      class="forumpay-pgw-payer-input"
      type="text"
      placeholder="Last Name"
      required
      @blur="handleTextBlur('setPayer', 'payer_last_name', payerLastName)"
    />
    <Dropdown
      v-model="payerCountry"
      label="Country of Residence"
      :options="countries"
      option-key="code"
      filter-property="name"
      placeholder="Select a country"
      class="forumpay-pgw-payer-dropdown"
      mark-as-optional
    >
      <template #selected="{ selected }">
        <span>{{ selected ? selected.name : 'Country of Residence' }}</span>
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
      v-model="payerDateOfBirth"
      label="Date of Birth"
      :computed-date="payerDateOfBirth"
      :is-required="true"
    />
    <Dropdown
      v-model="payerCountryOfBirth"
      label="Country of Birth"
      :options="countries"
      option-key="code"
      filter-property="name"
      placeholder="Select a country"
      class="forumpay-pgw-payer-dropdown"
    >
      <template #selected="{ selected }">
        <span>{{ selected ? selected.name : 'Country of Birth' }}</span>
      </template>
      <template #option="{ option, markText }">
        <span v-html="markText(option.name)" />
      </template>
    </Dropdown>
  </div>
</template>
