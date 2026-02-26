<script>
const PaymentCountdown = {
  props: {
    status: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      localRemainingSeconds: null,
      totalSeconds: null,
      countdownInterval: null,
      lastParsedSeconds: null,
    };
  },
  watch: {
    status: {
      immediate: true,
      handler(newStatus) {
        if (newStatus) {
          this.updateCountdownFromStatus();
        }
      },
    },
  },
  mounted() {
    this.updateCountdownFromStatus();

    // Update countdown display every second (for smooth countdown)
    this.countdownInterval = setInterval(() => {
      if (this.localRemainingSeconds !== null && this.localRemainingSeconds > 0) {
        this.localRemainingSeconds -= 1;
      }
    }, 1000);
  },
  unmounted() {
    if (this.countdownInterval) {
      clearInterval(this.countdownInterval);
    }
  },
  computed: {
    remainingTime() {
      if (this.localRemainingSeconds === null || this.localRemainingSeconds < 0) return null;

      const minutes = Math.floor(this.localRemainingSeconds / 60);
      const seconds = this.localRemainingSeconds % 60;
      return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    },
    countdownPercentage() {
      if (this.localRemainingSeconds === null || this.totalSeconds === null
        || this.totalSeconds === 0) return 100;

      // Calculate percentage: remaining time / total time * 100
      const percentage = (this.localRemainingSeconds / this.totalSeconds)
        * 100;
      return Math.max(0, Math.min(100, percentage));
    },
    countdownState() {
      if (this.countdownPercentage > 60) return 'start';
      if (this.countdownPercentage > 30) return 'mid';
      return 'final';
    },
    strokeWidth() {
      return 2;
    },
    perimeter() {
      const width = 65;
      const height = 28;
      return 2 * (width + height - 2 * this.strokeWidth);
    },
    dashOffset() {
      const progress = this.countdownPercentage / 100;
      return this.perimeter * (1 - progress);
    },
    pathData() {
      const width = 65;
      const height = 28;
      const { strokeWidth } = this;
      const halfStroke = strokeWidth / 2;
      const cx = width / 2;
      const top = halfStroke;
      const right = width - halfStroke;
      const bottom = height - halfStroke;
      const left = halfStroke;
      const r = 5;

      return [
        `M ${cx} ${top}`,
        `H ${left + r}`,
        `Q ${left} ${top} ${left} ${top + r}`,
        `V ${bottom - r}`,
        `Q ${left} ${bottom} ${left + r} ${bottom}`,
        `H ${right - r}`,
        `Q ${right} ${bottom} ${right} ${bottom - r}`,
        `V ${top + r}`,
        `Q ${right} ${top} ${right - r} ${top}`,
        `H ${cx}`,
      ].join(' ');
    },
  },
  methods: {
    updateCountdownFromStatus() {
      if (!this.status) return;

      // Parse time from status like "Waiting for payment [14:25]"
      const timeMatch = this.status.match(/\[([^\]]+)\]/);
      if (!timeMatch) return;

      const time = timeMatch[1];
      const timeDigits = time.split(':');
      const timeInSeconds = parseInt(timeDigits[0], 10) * 60 + parseInt(timeDigits[1], 10);

      // Only update if the parsed value changed (status updated from server)
      if (this.lastParsedSeconds !== timeInSeconds) {
        this.lastParsedSeconds = timeInSeconds;

        // Set total time on first initialization only (use the initial remaining time as total)
        if (this.totalSeconds === null) {
          this.totalSeconds = timeInSeconds;
        }

        // Update local countdown
        this.localRemainingSeconds = timeInSeconds;
      }
    },
  },
};

export default PaymentCountdown;
</script>

<template>
  <div
    v-if="remainingTime"
    :class="['countdown-box', countdownState]"
    style="width: 65px; height: 28px;"
  >
    <svg>
      <path class="border-static" :d="pathData" :stroke-width="strokeWidth / 2" />
      <path
        class="border-animated"
        :d="pathData"
        :stroke-width="strokeWidth"
        :stroke-dashoffset="dashOffset"
        :stroke-dasharray="perimeter"
      />
    </svg>
    <div class="countdown-content">
      {{ remainingTime }}
    </div>
  </div>
</template>
