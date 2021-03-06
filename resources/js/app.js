/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('bet-component', require('./components/BetComponent.vue').default);
Vue.component('table-detail', require('./components/TableDetail.vue').default);
Vue.component('bet-row', require('./components/BetRow.vue').default);
Vue.component('bet-row-simple', require('./components/BetRowSimple.vue').default);
Vue.component('strategy-stats', require('./components/StrategyStats.vue').default);
Vue.component('strategy-panel', require('./components/StrategyPanel.vue').default);


require('./betbot.js');
//import {   BTable } from 'bootstrap-vue'
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Install BootstrapVue

//Vue.component('b-table', BTable);
//Vue.use(BootstrapVue)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//const app = new Vue({
//    el: '#app',
//});
//$('#table').bootstrapTable();

$(document).ready( function () {
  $('#markets').DataTable();
  $('.table-analyze').DataTable();
} );
