<script>

const ringColorByCode = {
  info: 'var(--wlx-green-1)',
  warning: 'var(--wlx-orange-1)',
  alert: 'var(--wlx-red-1)',
};
const CountdownRing = {
  props: {
    radius: String,
    stroke: String,
    countdown: Number,
  },
  data() {
    const circumference = 2 * Math.PI * this.radius;
    return {
      circumference,
    };
  },
  computed: {
    strokeColor() {
      if (this.countdown > 60) {
        return ringColorByCode.info;
      } if (this.countdown > 30) {
        return ringColorByCode.warning;
      }
      return ringColorByCode.alert;
    },
    strokeDashoffset() {
      return (
        this.circumference - (this.countdown / 100) * this.circumference
      );
    },
  },
};

export default CountdownRing;

</script>

<template>
  <svg
    :height="radius * 2"
    :width="radius * 2"
  >
    <circle
      class="circle-background"
      :stroke-width="stroke"
      :r="radius"
      :cx="radius"
      :cy="radius"
    />
    <circle
      :stroke="strokeColor"
      :stroke-dasharray="circumference + ' ' + circumference"
      :style="{ strokeDashoffset }"
      :stroke-width="stroke"
      :r="radius"
      :cx="radius"
      :cy="radius"
    />
  </svg>
</template>
