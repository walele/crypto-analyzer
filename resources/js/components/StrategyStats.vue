<template>
  <div class="row">
    <div class="col-sm large-col">
      <p class="subtle-text" v-if="item.date">Stats since {{ item.date }}</p>
      <p class="" v-for="(value, name) in item.stats">
          <b>{{ name }}</b> {{ value }}
      </p>
    </div>

    <div class="col-sm small-text large-col stripped-row">
      <bet-row-simple
        v-for="item in item.bets"
        v-bind:key="item.id"
        v-bind:item="item"
      ></bet-row-simple>
    </div>

  </div>

</template>
<script>

  export default {
    components() {
    },
    computed: {
    },
    data() {
      return {
        item: []
      }
    },
    methods: {
    },
    props: ['item'],
    mounted() {
    }
  }
</script>

<style>
</style>
