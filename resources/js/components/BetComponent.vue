<template>
  <div class="row">
    <div class="col">
      {{ market }}
    </div>
    <div class="col">
      {{ created_at }}
    </div>
    <div class="col">
      {{ active }}
    </div>
    <div class="col">
      {{ success }}
    </div>
    <div class="col-6">
      <p v-for="(thing, index) in payload">
        {{ index }} : {{ thing }}
      </p>
    </div>
  </div>
</template>
<script>
  export default {
    computed: {
      image() {
        return `/images/${this.color}.png`;
      }
    },
    methods: {
      update(val) {
        this.$emit('update', this.id, val.target.selectedOptions[0].value);
      },
      del() {
        this.$emit('delete', this.id);
      }
    },
    props: ['id', 'market', 'created_at', 'active', 'success', 'payload'],
    filters: {
      properCase(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      }
    }
  }
</script>

<style>
</style>
