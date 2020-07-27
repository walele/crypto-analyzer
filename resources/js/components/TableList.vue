<template>

  <div class="row justify-content-lg-center hscroll">
      <div class="">
          <div class="card ">
              <div class="card-header">{{ title }}</div>

              <div class="card-body">

                <b-table :id="tableId" striped hover
                    :items="items"
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
                  :aria-controls="tableId"
                ></b-pagination>

              </div>
          </div>
      </div>
  </div>

</template>
<script>

 function Trade({ id, market, created_at, active, success, buy_price, final_prices}) {
    this.id = id;
    this.market = market;
    this.created_at = created_at;
    this.active = active;
    this.success = success;
    this.buy_price = buy_price;
    this.final_prices = final_prices;
  }

  export default {
    components() {
      BTable,
      BPagination
    },
    computed: {
      rows() {
        return this.items.length
      },
      fields() {
        return []
      }
    },
    data() {
      return {
        currentPage: 1,
        items: []
      }
    },
    methods: {
      getData(val) {
        axios({
          method: 'get',
          url: '/api/trades'
        }).then(res => {
          console.log(res.data);
          //res.data.forEach(item => this.items.push(item));
          console.log(this.items)
          res.data.forEach(trade => this.items.push(new Trade(trade)));

        });

      }
    },
    props: ['tableId', 'title', 'perPage'],
    mounted() {
      //this.currentPage = 0;
      this.getData()
    }
  }
</script>

<style>
</style>
