<script>

import CurrencyIcon from './CurrencyIcon.vue';
import SvgLock from '../images/SvgLock.vue';

const PageLogo = {
  components: {
    CurrencyIcon,
    SvgLock,
  },
};

export default PageLogo;
</script>

<template>
  <div class="forumpay-pgw-logo">
    <div>
      <div class="forumpay-pgw-logo-powered-by">
        <span class="forumpay-pgw-logo-powered-by-copy">Powered by</span>
      </div>
      <CurrencyIcon
        class="forumpay-pgw-logo-img"
        icon="logo"
        alt="ForumPay Logo"
      />
    </div>
  </div>
</template>
