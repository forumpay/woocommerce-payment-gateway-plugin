<script>
import {
  logo,
  hand,
  help,
  defaultIcon,
} from '../icons/icons';

const CurrencyIcon = {
  props: {
    currency: {
      type: Object,
      default() {
        return { currency: '', icon_url: '' };
      },
    },
    icon: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      icons: {
        logo,
        hand,
        help,
        defaultIcon,
      },
    };
  },
  computed: {
    getIcon() {
      if (!this.currency && !this.icon) {
        return false;
      }

      if (this.currency && !this.icon) {
        return this.currency.icon_url ?? this.icons.default;
      }

      const key = this.icon ? this.icon : this.currency.currency.toLowerCase();

      return Object.prototype.hasOwnProperty.call(this.icons, key)
        ? this.icons[key]
        : this.icons.default;
    },
  },
};

export default CurrencyIcon;
</script>

<template>
  <img
    v-if="getIcon"
    :src="getIcon"
    :alt="icon"
  >
</template>
