/*
 *  Universal file uploader item implementation
 */
Vue.component('backend-component-uploader-item', {
    props: {
        errorMessage: String,
        fileName: String,
        progress: Number,
        status: String
    },
    data: function data() {
        return {};
    },
    computed: {
        cssClass: function computeCssClass() {
            return {
                'status-completed': this.status === 'completed',
                'status-uploading': this.status === 'uploading',
                'status-error': this.status === 'error'
            };
        }
    },
    methods: {},
    template: '#backend_vuecomponents_uploader_item'
});
