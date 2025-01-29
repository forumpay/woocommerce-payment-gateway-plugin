<script>

import countries from '../utils/countries';

const CountryInput = {
  props: {
    modelValue: {
      type: String,
      required: false,
    },
    label: {
      type: String,
      default: 'Select a country:',
    },
    computedCountry: {
      type: String,
      required: false,
    },
    isRequired: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      countries,
    };
  },
  computed: {
    selectClass() {
      return this.computedCountry === '' ? 'forumpay-pgw-dropdown-select-field-default' : '';
    },
  },
  methods: {
    selectCountry(country) {
      this.$emit('update:modelValue', country);
    },
  },
};

export default CountryInput;
</script>

<template>
  <label for="countries" class="forumpay-pgw-dropdown-select-label">
    {{ label }}
    <span v-if="!isRequired">
      (optional)
    </span>
  </label>
  <div class="forumpay-pgw-dropdown-select">
    <select
      id="countries"
      class="forumpay-pgw-dropdown-select-field"
      name="countries"
      :value="modelValue"
      :class="selectClass"
      :required="isRequired"
      @change="selectCountry($event.target.value)"
    >
      <option value="" :disabled="isRequired">
        Select a country
      </option>
      <option
        v-for="country in countries"
        :key="country.code"
        :value="country.code"
      >
        {{ country.name }}
      </option>
    </select>
  </div>
</template>
