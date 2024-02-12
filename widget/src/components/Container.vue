<script>

import SvgIconExclamation from '../images/SvgIconExclamation.vue';
import SvgIconDanger from '../images/SvgIconDanger.vue';
import SvgIconWarning from '../images/SvgIconWarning.vue';

const containerClassByAlert = {
  success: 'forumpay-pgw-container-success',
  warning: 'forumpay-pgw-container-warning',
  danger: 'forumpay-pgw-container-danger',
};

const containerAlerts = Object.keys(containerClassByAlert);
const Container = {
  components: { SvgIconWarning, SvgIconDanger, SvgIconExclamation },
  props: {
    alert: {
      validator(value) {
        return containerAlerts.includes(value);
      },
      default: null,
    },
    message: { type: String },
    messageClass: { type: String },
  },
  computed: {
    containerClass() {
      return containerClassByAlert[this.alert];
    },
  },
};

export default Container;

</script>

<template>
  <div
    v-if="alert"
    class="forumpay-pgw-container"
    :class="containerClass"
  >
    <div class="forumpay-pgw-container-alert">
      <div
        v-if="alert === 'success'"
        class="forumpay-pgw-container-alert-icon"
      >
        <SvgIconExclamation />
      </div>
      <div
        v-if="alert === 'danger'"
        class="forumpay-pgw-container-alert-icon"
      >
        <SvgIconDanger />
      </div>
      <div
        v-if="alert === 'warning'"
        class="forumpay-pgw-container-alert-icon"
      >
        <SvgIconWarning />
      </div>

      <span
        v-if="message"
        :class="messageClass"
      >{{ message }}</span>
      <slot />
    </div>
  </div>
  <div
    v-else
    class="forumpay-pgw-container"
  >
    <slot />
  </div>
</template>
