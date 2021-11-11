Vue.component('backend-component-documentmarkdowneditor-formwidgetconnector', {
    props: {
        textarea: null,
        lang: {},
        useMediaManager: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        var toolbarExtensionPoint = [];

        return {
            toolbarExtensionPoint: toolbarExtensionPoint,
            fullScreen: false,
            value: ''
        };
    },
    computed: {
        toolbarElements: function computeToolbarElements() {
            return [this.toolbarExtensionPoint, {
                type: 'button',
                icon: this.fullScreen ? 'octo-icon-fullscreen-collapse' : 'octo-icon-fullscreen',
                command: 'document:toggleFullscreen',
                pressed: this.fullScreen,
                fixedRight: true,
                tooltip: this.lang.langFullscreen
            }];
        }
    },
    mounted: function onMounted() {
        var _this = this;

        this.value = this.textarea.value;

        Vue.nextTick(function () {
            _this.$refs.markdownEditor.clearHistory();
        });
    },
    methods: {
        onFocus: function onFocus() {
            this.$emit('focus');
        },

        onBlur: function onBlur() {
            this.$emit('blur');
        },

        onToolbarCommand: function onToolbarCommand(cmd) {
            var _this2 = this;

            if (cmd == 'document:toggleFullscreen') {
                this.fullScreen = !this.fullScreen;

                Vue.nextTick(function () {
                    _this2.$refs.markdownEditor.refresh();
                });
            }
        }
    },
    beforeDestroy: function beforeDestroy() {
        this.$refs.toolbar.$destroy();
        this.$refs.document.$destroy();
        this.textarea = null;
    },
    watch: {
        value: function watchValue(newValue) {
            if (newValue != this.textarea.value) {
                this.textarea.value = newValue;
                this.$emit('change');
            }
        }
    },
    template: '#backend_vuecomponents_documentmarkdowneditor_formwidgetconnector'
});
