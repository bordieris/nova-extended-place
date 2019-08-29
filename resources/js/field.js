Nova.booting((Vue, router) => {
    Vue.component('index-extended_place', require('./components/IndexField'));
    Vue.component('detail-extended_place', require('./components/DetailField'));
    Vue.component('form-extended_place', require('./components/FormField'));
})
