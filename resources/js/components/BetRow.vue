<template>
  <div class="row">
    <div class="col-sm small-text large-col">
      <p class="title-small">Info</p>
      <p><b>id</b>: {{ item.id }}</p>
      <p><b>start</b>: {{ item.times.start }}</p>
      <p><b>end</b>: {{ item.times.end }}</p>
      <p><a target='_blank' :href="item.name_link.link">{{ item.name_link.name }} link</a></p>

      <br>

      <p class="title-small">Status</p>
      <p><b>ML status</b>: {{ item.ml_status }}</p>
      <p><b>active</b>: {{ item.active }}</p>
      <p><b>success</b>: {{ item.success}}</p>
    </div>

    <div class="col-sm small-text large-col">
      <p class="title-small">Payload</p>
      <p v-for="indicator in item.payload">
        <b>{{ indicator.label }}</b> {{ indicator.value }}
      </p>
    </div>

    <div class="col-sm small-text">
      <p class="title-small">Bet prices</p>
      <p v-for="(value, name) in item.bet_prices">
        <b>{{ name }}</b> {{ value }}
      </p>

      <br>

      <p class="title-small">Final prices</p>
      <p v-for="(value, name) in item.final_prices">
        <b>{{ name }}</b> {{ value }}
      </p>
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
