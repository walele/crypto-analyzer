<template>

  <div class="row justify-content-md-center hscroll">
      <div class="">
          <div class="card ">
              <div class="card-header">{{ title }}</div>

              <div class="card-body">

                <b-table :id="tableId" striped hover
                    :items="items"
                    :fields="fields"
                    :per-page="perPage"
                    :currentPage="currentPage">

                  <template v-slot:cell(show_details)="row">
                    <b-button size="sm" @click="row.toggleDetails" class="button mr-2">
                    {{ row.detailsShowing ? 'Hide' : 'Show'}} Details
                    </b-button>

                  </template>

                  <template v-slot:row-details="row">
                    <div class="stripped-row" >
                      <div class="row" v-for="item in row.item.bets">

                        <div class="col-sm small-text large-col">
                          <p class="title-small">Info</p>
                          <p><b>id</b>: {{ item.id }}</p>
                          <p><b>start</b>: {{ item.times.start }}</p>
                          <p><b>end</b>: {{ item.times.end }}</p>
                          <p><a target='_blank' :href="item.name_link.link">{{ item.name_link.name }} link</a></p>

                          <br>

                          <p class="title-small">Status</p>
                          <p><b>ML status</b>: {{ item.ml_status }}</p>
                          <p><b>active</b>: {{ item.active }}</p>
                          <p><b>success</b>: {{ item.success}}</p>
                        </div>

                        <div class="col-sm small-text large-col">
                          <p class="title-small">Payload</p>
                          <p v-for="indicator in item.payload">
                            <b>{{ indicator.label }}</b> {{ indicator.value }}
                          </p>
                        </div>

                        <div class="col-sm small-text">
                          <p class="title-small">Bet prices</p>
                          <p v-for="(value, name) in item.bet_prices">
                            <b>{{ name }}</b> {{ value }}
                          </p>

                          <br>

                          <p class="title-small">Final prices</p>
                          <p v-for="(value, name) in item.final_prices">
                            <b>{{ name }}</b> {{ value }}
                          </p>
                        </div>
                      </div>
                    </div>


                 </template>

                  <template v-slot:cell(payload)="data">
                    <div class="small-text">
                      <p v-for="indic in data.value">
                        <b>{{ indic.label }}</b> {{ indic.value }}
                      </p>
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
        return ['name', 'show_details']
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
