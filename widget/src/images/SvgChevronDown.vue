<script>
export default {
  props: {
    width: {
      type: [String, Number],
      default: 16,
    },
    height: {
      type: [String, Number],
      default: 16,
    },
  },
};
</script>

<template>
  <svg
    :width="width"
    :height="height"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
  >
    <polyline points="6 9 12 15 18 9" />
  </svg>
</template>
