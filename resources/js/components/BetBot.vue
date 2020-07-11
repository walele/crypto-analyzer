<template>
  <div class="">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">BetBot</div>

                <div class="card-body">
                    <p>Current strategies</p>
                </div>
            </div>
        </div>
    </div>
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

  </div>
</template>

<script>

  function Bet({ id, market}) {
     this.id = id;
     this.name = market;

   }

   import BetComponent from './BetComponent.vue';

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
          return {
            bets : []
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
              //this will be executed only when all requests are complete
              console.log(res.data.data);
              res.data.data.forEach(bet => this.bets.push(new Bet(bet)));
            });
          }
        },
        created() {
          this.read();
        }
    }
</script>
