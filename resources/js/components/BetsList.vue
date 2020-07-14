<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Current bets</div>

                <div class="card-body">

                  <bet-component
                    v-for="bet in bets"
                    v-bind="bet"
                    :key="bet.id"
                  ></bet-component>

                </div>
            </div>
        </div>
    </div>
</template>

<script>

  function Bet({ id, market, created_at, active, success, payload}) {
     this.id = id;
     this.market = market;
     this.created_at = created_at;
     this.active = active;
     this.success = success;
     this.payload = payload;

   }

   import BetComponent from './BetComponent.vue';

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
          return {
            bets : [],
            strategy: ''
          }
        },
        components() {
          BetComponent
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
              url: '/api/betbot'
            }).then(res => {
              this.strategy = res.data.strategy
            });
          }
        },
        created() {
          this.read();
        }
    }
</script>
