<template>
  <div class="row bet-card">
    <div class="col-sm large-col">
      <p>
        [{{ item.id }} ]<a target='_blank' :href="item.name_link.link">{{ item.name_link.name }}</a>
        <span v-if="item.success == 1">Success 🔥</span>
        <span v-else>Fail 😢</span>
      </p>
      <p class="subtle-text">{{ item.times.short }}</p>
      <p><b>min</b> {{ item.final_prices.min_perc }}%</p>
      <p><b>max</b> {{ item.final_prices.max_perc }}%</p>
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
