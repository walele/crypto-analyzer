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
                    <p v-for="stat in stats">
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

    <div class="row justify-content-lg-center hscroll">
        <div class="">
            <div class="card ">
                <div class="card-header">Current Trades</div>

                <b-table id="trade-table" striped hover
                    :items="trades">
                  <template v-slot:cell(payload)="data">
                    <span class="small-text" v-html="data.value"></span>
                  </template>
                  <template v-slot:cell(final_prices)="data">
                    <span v-html="data.value"></span>
                  </template>
                </b-table>


                <div class="card-body">
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn"  v-on:click="makeTrades">make trades</button>

    <hr style="width: 42%">

    <div class="row justify-content-lg-center hscroll">
        <div class="">
            <div class="card ">
                <div class="card-header">Current bets</div>

                <div class="card-body">

                  <b-table id="bet-table" striped hover
                      :items="bets"
                      :fields="fields"
                      :per-page="perPage"
                      :currentPage="currentPage">
                    <template v-slot:cell(payload)="data">
                      <span class="small-text" v-html="data.value"></span>
                    </template>
                    <template v-slot:cell(final_prices)="data">
                      <span v-html="data.value"></span>
                    </template>
                  </b-table>

                  <b-pagination
                    v-model="currentPage"
                    :total-rows="rows"
                    :per-page="perPage"
                    aria-controls="bet-table"
                  ></b-pagination>

                </div>
            </div>
        </div>
    </div>


  </div>
</template>

<script>

  function Bet({ id, market, created_at, active, success, payload, final_prices}) {
     this.id = id;
     this.market = market;
     this.created_at = created_at;
     this.active = active;
     this.success = success;
     this.payload = payload;
     this.final_prices = final_prices;

   }

   function Trade({ id, market, created_at, active, success, buy_price, final_prices}) {
      this.id = id;
      this.market = market;
      this.created_at = created_at;
      this.active = active;
      this.success = success;
      this.buy_price = buy_price;
      this.final_prices = final_prices;
    }

   import BetComponent from './BetComponent.vue';

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
          return {
            bets : [],
            trades : [],
            strategy: '',
            stats: [],
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
              url: '/api/bets'
            }).then(res => {
              res.data.data.forEach(bet => this.bets.push(new Bet(bet)));
            });

            axios({
              method: 'get',
              url: '/api/trades'
            }).then(res => {
              res.data.forEach(trade => this.trades.push(new Trade(trade)));
            });

            axios({
              method: 'get',
              url: '/api/bot'
            }).then(res => {
              this.strategy = res.data.strategy
            });

            axios({
              method: 'get',
              url: '/api/bot/stats'
            }).then(res => {
              this.stats = res.data.stats
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
