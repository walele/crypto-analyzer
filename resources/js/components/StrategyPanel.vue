<template>
  <div class="row">
    <div class="col-sm large-col">


      <div class="card">
          <div class="card-header">Strategy {{ item.name }}</div>
          <div class="card-body">
            <p class="title-4">Description</p>
            <p class="">{{ item.description }} </p>
            <p>&nbsp;</p>

            <div class="row">
              <div class="col-sm">
                <p class="title-4">Conditions</p>
                <ul>
                  <li v-for="value in item.conditions">{{ value }}</li>
                </ul>
              </div>

              <div class="col-sm">
                <p class="title-4">Features</p>
                <ul>
                  <li v-for="value in item.features">{{ value }}</li>
                </ul>
              </div>
            </div>

            <hr style="width: 42%">

            <div class="row">
              <div class="col-sm">
                <p class="title-4">Total stas</p>
                <ul>
                  <li v-for="value in item.conditions">{{ value }}</li>
                </ul>
              </div>

              <div class="col-sm">
                <p class="title-4">Daily stats</p>

                <div class="card">
                    <div class="card-header">Daily Stats</div>

                    <div class="card-body">

                      <strategy-stats
                        v-for="item in daily_stats"
                        v-bind:key="item.name"
                        v-bind:item="item"
                      ></strategy-stats>

                    </div>
                </div>

              </div>
            </div>

          </div>

      </div>


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
        item: [],
        daily_stats: [],
      }
    },
    methods: {
      async read() {

        const key = this.item.key;
        axios({
          method: 'get',
          url: '/api/stats/strategy/' + key
        }).then(res => {
          this.strategies = res.data
          console.log(res.data)
        });

      }
    },
    props: ['item'],
    mounted() {
      this.read()
    }
  }
</script>

<style>
</style>
