<script>

import SvgCopy from '../images/SvgCopy.vue';

const Copy = {
  components: { SvgCopy },
  props: {
    value: String,
    onCopy: Function,
  },
  data() {
    return {
      isClipboardAvailable: window.isSecureContext && navigator.clipboard,
      isCopied: false,
    };
  },
  methods: {
    copyToClipboard() {
      navigator.clipboard.writeText(this.value);
      this.isCopied = true;

      if (typeof this.onCopy === 'function') {
        this.onCopy();
      }

      setTimeout(() => { this.isCopied = false; }, 1000);
    },
  },
};

export default Copy;

</script>

<template>
  <div
    class="forumpay-pgw-payment-amount-field-copy_button"
    style="width:24px; height:24px; cursor: pointer;"
    role="button"
    tabIndex="0"
    @keyup="copyToClipboard"
    @click="copyToClipboard"
  >
    <SvgCopy v-if="isClipboardAvailable && !isCopied" />
    <small
      v-if="isCopied"
      class="forumpay-pgw-copy-copy"
      @click.stop
    >Copied!</small>
  </div>
</template>
