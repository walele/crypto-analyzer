<template>
  <div class="row">
    <div class="col-sm large-col">


      <div class="card">
          <div class="card-header">Strategy {{ item.name }}</div>
          <div class="card-body">

            <div class="row">
              <div class="col-sm">
                <div class="card">
                  <div class="card-header">Description</div>
                  <div class="card-body">
                    <p class="">{{ item.description }} </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm">
                <div class="card">
                  <div class="card-header">Conditions</div>
                  <div class="card-body">
                    <ul>
                      <li v-for="value in item.conditions">{{ value }}</li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="col-sm">

                <div class="card">
                  <div class="card-header">Features</div>
                  <div class="card-body">
                    <ul>
                      <li v-for="value in item.features">{{ value }}</li>
                    </ul>
                  </div>
                </div>

              </div>
            </div>

            <hr style="width: 42%">

            <div class="row">
              <div class="col-sm">
                <div class="card">
                    <div class="card-header">Total Stats</div>

                    <div class="card-body">
                      <strategy-stats
                        v-for="item in stats.total"
                        v-bind:key="item.name"
                        v-bind:item="item"
                      ></strategy-stats>
                    </div>
                </div>
              </div>

              <div class="col-sm">

                <div class="card">
                    <div class="card-header">Daily Stats</div>

                    <div class="card-body">

                      <strategy-stats
                        v-for="item in stats.daily"
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
        stats: {},
      }
    },
    methods: {
      async read() {

        const key = this.item.key;
        axios({
          method: 'get',
          url: '/api/stats/strategy/' + key
        }).then(res => {
          this.stats = res.data.stats
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
