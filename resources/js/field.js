Nova.booting((Vue, router) => {
    Vue.component('index-extended_address', require('./components/IndexField'));
    Vue.component('detail-extended_address', require('./components/DetailField'));
    Vue.component('form-extended_address', require('./components/FormField'));
})
