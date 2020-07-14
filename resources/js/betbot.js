import BetBot from './components/BetBot.vue';
import { BTable, BPagination } from 'bootstrap-vue'

const betbot = document.getElementById( "betbot" );
Vue.component('b-table', BTable);
Vue.component('b-pagination', BPagination);

if(betbot){
  init(betbot);
}

//  Inti vue js app
function init(el){

  const app = new Vue({
    el: el,

    components: {
      BetBot,
      BTable,
      BPagination
    },
    render: h => h(BetBot)
  });
}
