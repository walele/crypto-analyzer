<template>
  <div class="">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">BetBot</div>

                <div class="card-body">
                    <p>Current strategies</p>
                    <p>{{ strategy }}</p>
                </div>
            </div>
        </div>
    </div>
    <hr style="width: 42%">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Current bets</div>

                <div class="card-body">

                  <b-table id="bet-table" striped hover :items="bets" :fields="fields" :per-page="7">
                    <template v-slot:cell(payload)="data">
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
            strategy: '',
            perPage: 3,
            currentPage: 1,
            fields: [
            {
              key: 'id',
              sortable: true
            },
            {
              key: 'created_at',
              sortable: false
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
            }
          ],
            items: []
          }
        },
        computed: {
          rows() {
            return this.items.length
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
