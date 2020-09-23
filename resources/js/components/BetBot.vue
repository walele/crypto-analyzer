<template>
  <div class="">
    <div class="row justify-content-center">

        <div class="col-sm">
            <div class="card">
                <div class="card-header">Wallet </div>

                <div class="card-body">
                  <p>BTC {{ wallet.btc }}</p>
                  <p>All {{ wallet.all }}</p>

                </div>
            </div>
        </div>

    </div>

    <hr style="width: 42%">


    <strategy-panel
      v-for="item in strategies"
      v-bind:key="item.name"
      v-bind:item="item"
    ></strategy-panel>


    <hr style="width: 42%">
<!--
    <div class="row justify-content-center">
        <div class="col-sm">
            <div class="card">
                <div class="card-header">Daily Stats</div>

                <div class="card-body">

                  <strategy-stats
                    v-for="item in stats.daily_stats"
                    v-bind:key="item.name"
                    v-bind:item="item"
                  ></strategy-stats>

                </div>
            </div>

            <div class="card">
                <div class="card-header">Success Stats</div>
                <div class="card-body">
                  <p v-for="(value, name) in stats.win_stats.stats">
                    <b>{{ name }}</b> {{ value }}
                  </p>

                  <div >
                    <div class="float-box" v-for="(value, name) in stats.win_stats.bets">
                      <span>{{ name }}</span> {{ value }}
                    </div>
                  </div>

                </div>
            </div>
          </div>
      </div>
-->
    <hr style="width: 42%">

    <table-list
      tableId="table-order"
      title="Orders"
      apiUrl="/api/orders"
      perPage="16">
    </table-list>
    <hr style="width: 42%">

    <table-list
      tableId="customTable"
      title="Trades"
      apiUrl="/api/trades"
      perPage="16">
    </table-list>
    <hr style="width: 42%">

    <table-list
      tableId="table-bets"
      title="Active Bets"
      apiUrl="/api/bets/actives"
      perPage="16">
    </table-list>
    <hr style="width: 42%">

    <table-detail
      tableId="table-betss"
      title="Old Bets"
      apiUrl="/api/bets/grouped"
      perPage="16">
    </table-detail>
    <hr style="width: 42%">

    <table-list
      tableId="table-logs"
      title="Logs"
      apiUrl="/api/logs"
      perPage="16">
    </table-list>
    <hr style="width: 42%">

  </div>
</template>

<script>

    import BetComponent from './BetComponent.vue';
    import TableList from './TableList.vue';
  //  import TableDetail from './TableDetail.vue';

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
          return {
            bets : [],
            trades : [],
            stats : {
              daily_wins: {}
            },
            statsBetsHide: true,
            strategies: [],
            statsBets: [],
            statsTrades: [],
            wallet:{
              btc: '',
              all: '',
            },
            items: []
          }
        },
        computed: {
          rows() {
            return this.bets.length
          }
        },
        components() {
          BetComponent,
          BTable,
          BPagination,
          BButton,
          BCollapse
        },
        methods: {
          async read() {

            axios({
              method: 'get',
              url: '/api/bot'
            }).then(res => {
              this.strategies = res.data.strategies
            });
/*
            axios({
              method: 'get',
              url: '/api/bot/stats/bets'
            }).then(res => {
              this.statsBets = res.data.stats
            });

            axios({
              method: 'get',
              url: '/api/bot/stats/mlbets'
            }).then(res => {
              this.statsMlBets = res.data.stats
            });

            axios({
              method: 'get',
              url: '/api/stats/'
            }).then(res => {
              this.stats = res.data.stats
            });
*/

            axios({
              method: 'get',
              url: '/api/bot/wallet'
            }).then(res => {
              this.wallet = res.data.wallet
            });
          }
        },
        created() {
          this.read();
        }
    }
</script>
