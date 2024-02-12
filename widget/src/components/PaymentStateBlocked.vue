<script setup>

import Container from './Container.vue';

defineProps({
  confirmed: Boolean,
});

</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center">
    <Container
      :alert="confirmed ? 'success' : 'danger'"
      :message-class="'forumpay-pgw-container-state-blocked'"
    >
      <p>
        Your payment is currently being reviewed by our compliance team.
        These reviews usually take no more than two hours during regular operating hours.
      </p>
    </Container>
  </div>
</template>
