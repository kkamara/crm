/*
 * Vue Inspector table text control implementation
 */
Vue.component('backend-component-inspector-control-table-text', {
    extends: $.oc.vueComponentHelpers.inspector.table.controlBase,
    props: {
    },
    data: function() {
        return {
        };
    },
    computed: {
    },
    methods: {
        focusControl: function focusControl() {
            this.$refs.input.focus();
        }
    },
    template: '#backend_vuecomponents_inspector_control_table_text'
});
