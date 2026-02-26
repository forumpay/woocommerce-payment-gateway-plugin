<script>

import SvgCopy from '../images/SvgCopy.vue';
import SvgCheck from '../images/SvgCheck.vue';

const Copy = {
  components: { SvgCopy, SvgCheck },
  props: {
    value: String,
    onCopy: Function,
  },
  data() {
    return {
      isClipboardAvailable: window.isSecureContext && navigator.clipboard,
      isCopied: false,
      tooltipText: 'Copy',
    };
  },
  methods: {
    copyToClipboard() {
      navigator.clipboard.writeText(this.value);
      this.isCopied = true;
      this.tooltipText = 'Copied!';

      if (typeof this.onCopy === 'function') {
        this.onCopy();
      }

      setTimeout(() => {
        this.isCopied = false;
        this.tooltipText = 'Copy';
      }, 1000);
    },
  },
};

export default Copy;

</script>

<template>
  <div
    class="copy-wrapper"
    role="button"
    tabIndex="0"
    @keyup="copyToClipboard"
    @click="copyToClipboard"
  >
    <div class="svg-container">
      <SvgCheck v-if="isCopied" :width="16" :height="16" style="color: var(--pgw-color-black); outline: none;" />
      <SvgCopy v-else-if="isClipboardAvailable" :width="16" :height="16" style="outline: none;" />
    </div>
    <div class="copy-tooltip">
      {{ tooltipText }}
    </div>
  </div>
</template>
