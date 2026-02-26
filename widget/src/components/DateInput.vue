<script>
const DateInput = {
  props: {
    modelValue: {
      type: String,
      required: false,
    },
    label: {
      type: String,
      default: 'Date of Birth',
    },
    computedDate: {
      type: String,
      required: false,
    },
    isRequired: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  emits: ['update:modelValue'],
};

export default DateInput;
</script>

<template>
  <div>
    <label :for="`date-input-${label}`" class="forumpay-pgw-payer-label">
      {{ label }}
    </label>
    <input
      :id="`date-input-${label}`"
      type="date"
      :value="modelValue"
      :required="isRequired"
      :max="new Date().toISOString().split('T')[0]"
      class="forumpay-pgw-payer-input"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>
