import BetBot from './components/BetBot.vue';
import TableList from './components/TableList.vue';
import { BTable, BPagination, BButton, BFormCheckbox, BFormInput} from 'bootstrap-vue'

const betbot = document.getElementById( "betbot" );
Vue.component('b-table', BTable);
Vue.component('b-button', BButton);
Vue.component('b-form-checkbox', BFormCheckbox);
Vue.component('b-form-input', BFormInput);
Vue.component('b-pagination', BPagination);
Vue.component('table-list', TableList);



if(betbot){
  init(betbot);
}

//  Inti vue js app
function init(el){

  const app = new Vue({
    el: el,

    components: {
      BetBot,
      TableList,
      BTable,
      BPagination
    },
    render: h => h(BetBot)
  });
}
