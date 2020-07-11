import BetBot from './components/BetBot.vue';

const betbot = document.getElementById( "betbot" );

if(betbot){
  init(betbot);
}

//  Inti vue js app
function init(el){

  const app = new Vue({
    el: el,

    components: {
      BetBot
    },
    render: h => h(BetBot)
  });
}
