<script>

import SvgLoader from '../images/SvgLoader.vue';

const Loader = {
  components: { SvgLoader },
  props: {
    loading: Boolean,
    small: Boolean,
  },
  computed: {
    loaderClassName() {
      return this.small
        ? 'forumpay-pgw-loader-spin forumpay-pgw-loader-spin--small'
        : 'forumpay-pgw-loader-spin';
    },
  },
};

export default Loader;

</script>

<template>
  <div class="forumpay-pgw-loader">
    <div :class="loaderClassName">
      <SvgLoader v-if="loading" />
    </div>
  </div>
</template>
