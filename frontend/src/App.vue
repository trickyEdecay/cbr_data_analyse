<template>
    <div id="app">
        <Nav v-show="$route.name!='PlayerProfile'"></Nav>
        <div class="wrap">
            <router-view/>
        </div>
    </div>
</template>

<script>
    import Nav from "./components/Nav";

    export default {
        name: 'App',
        components: {Nav}
    }
</script>

<style>
    body {
        margin: 0;
        font-family: 'Avenir', Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .wrap {
        margin: 0 auto;
        width: 100%;
        max-width: 1200px;
        padding: 28px;
        box-sizing: border-box;
    }
</style>
