<template>

  <div class="row hscroll">
      <div class="container-center">
          <div class="card ">
              <div class="card-header">
                <h6>{{ title }}</h6>
                <b-form-input
                  v-model="filter"
                  type="search"
                  placeholder="Type to Search"
                ></b-form-input>
              </div>

              <div class="card-body">

                <b-table :id="tableId" striped hover
                    :items="items"
                    :fields="fields"
                    :filter="filter"
                    :filterIncludedFields="filterOn"
                    :per-page="perPage"
                    :currentPage="currentPage">
                  <template v-slot:cell(payload)="data">
                    <div class="small-text">
                      <p v-for="indic in data.value">
                        <b>{{ indic.label }}</b> {{ indic.value }}
                      </p>
                    </div>
                  </template>
                  <template v-slot:cell(binance_payload)="data">
                    <div class="small-text">
                      <p v-for="indic in data.value">
                        <b>{{ indic.label }}</b> {{ indic.value }}
                      </p>
                    </div>
                  </template>
                  <template v-slot:cell(bet_prices)="data">
                    <div class="small-text">
                      <p v-for="(value, name) in data.value">
                        <b>{{ name }}</b> {{ value }}
                      </p>
                    </div>
                  </template>
                  <template v-slot:cell(final_prices)="data">
                    <div class="small-text">
                      <p v-for="(value, name) in data.value">
                        <b>{{ name }}</b> {{ value }}
                      </p>
                    </div>
                  </template>
                  <template v-slot:cell(times)="data">
                    <div class="small-text">
                      <p v-for="(value, name) in data.value">
                        <b>{{ name }}</b> {{ value }}
                      </p>
                    </div>
                  </template>
                  <template v-slot:cell(name_link)="data">
                    <div class="small-text">
                      <a target='_blank' :href="data.value.link">{{ data.value.name }}</a>
                    </div>
                  </template>
                  <template v-slot:cell(logs)="data">
                    <div class="small-text">
                      <div v-for="log in data.value">
                        <p>{{ log.created_at }}</p>
                        <span v-for="(value, name) in log.log">
                          <b>{{ name }}</b> {{ value }} <br/>
                        </span>
                      </div>
                    </div>
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
        items: [],
        filter: null,
        filterOn: ['name_link'],
      }
    },
    methods: {
      getData(val) {
        console.log(this.apiUrl);
        axios({
          method: 'get',
          url: this.apiUrl
        }).then(res => {
          res.data.data.forEach(item => this.items.push(item));
        });

      }
    },
    props: ['tableId', 'title', 'perPage', 'apiUrl'],
    mounted() {
      //this.currentPage = 0;
      this.getData()
    }
  }
</script>

<style>
</style>
