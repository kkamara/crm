/*
 * Vue Inspector table control implementation
 */
Vue.component('backend-component-inspector-control-table-cell', {
    props: {
        row: Object,
        column: Object,
        cellIndex: Number,
        inspectorPreferences: Object,
        tableConfiguration: Object,
        isLastCell: Boolean
    },
    data: function() {
        return {
        };
    },
    computed: {
    },
    methods: {
        focusControl: function focusControl() {
            this.$refs.editor.focusControl();
        }
    },
    template: '#backend_vuecomponents_inspector_control_table_cell'
});
