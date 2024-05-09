<script>

import CurrencyIcon from './CurrencyIcon.vue';

const PageHeader = {
  components: {
    CurrencyIcon,
  },
};

export default PageHeader;
</script>

<template>
  <div class="wlx-company-name">
    <CurrencyIcon
      icon="logo"
      class="fp-logo"
      alt="ForumPay Logo"
    />
  </div>
</template>
