<template>
  <div class="">
    <div class="row justify-content-center">
        <div class="col-sm">
            <div class="card">
                <div class="card-header">BetBot</div>

                <div class="card-body">
                    <p>Current strategies: {{ strategy.description }}</p>
                    <ul class="small-list">
                      Conditions :
                      <li v-for="cond in strategy.conditions">
                         - {{ cond }}
                      </li>
                    </ul>

                </div>
            </div>
        </div>

        <div class="col-sm">
            <div class="card">
                <div class="card-header">Bets Stats</div>

                <div class="card-body">
                    <p v-for="stat in statsBets">
                      <b>{{ stat.label }}</b> {{ stat.text }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-sm">
            <div class="card">
                <div class="card-header">Trades Stats</div>

                <div class="card-body">
                    <p v-for="stat in statsTrades">
                      <b>{{ stat.label }}</b> {{ stat.text }}
                    </p>
                </div>

            </div>
        </div>

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
      title="Bets"
      apiUrl="/api/bets"
      perPage="16">
    </table-list>

    <hr style="width: 42%">

  </div>
</template>

<script>

    import BetComponent from './BetComponent.vue';
    import TableList from './TableList.vue';

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
          return {
            bets : [],
            trades : [],
            strategy: '',
            statsBets: [],
            statsTrades: [],
            wallet:{
              btc: '',
              all: '',
            },
            perPage: 7,
            currentPage: 1,
            fields: [
            {
              key: 'id',
              sortable: true
            },
            {
              key: 'market',
              sortable: true
            },
            {
              key: 'created_at',
              sortable: true
            },
            {
              key: 'active',
              sortable: true,
            },
            {
              key: 'success',
              sortable: true,
            },
            ,
            {
              key: 'payload',
              sortable: true,
            },
            {
              key: 'buy_price',
              sortable: true,
            },
            {
              key: 'final_prices',
              sortable: true,
            }
          ],
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
          BPagination
        },
        methods: {
          async read() {

            axios({
              method: 'get',
              url: '/api/bot'
            }).then(res => {
              this.strategy = res.data.strategy
            });

            axios({
              method: 'get',
              url: '/api/bot/stats/bets'
            }).then(res => {
              this.statsBets = res.data.stats
            });

            axios({
              method: 'get',
              url: '/api/bot/stats/trades'
            }).then(res => {
              this.statsTrades = res.data.stats
            });


            axios({
              method: 'get',
              url: '/api/bot/wallet'
            }).then(res => {
              this.wallet = res.data.wallet
            });
          },
          makeTrades: function (event) {
            console.log('lets go')
          }
        },
        created() {
          this.read();
        }
    }
</script>
