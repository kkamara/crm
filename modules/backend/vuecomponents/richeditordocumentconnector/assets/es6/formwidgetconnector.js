Vue.component('backend-component-richeditor-document-connector-formwidgetconnector', {
    props: {
        textarea: null,
        lang: {},
        useMediaManager: {
            type: Boolean,
            default: false
        },
        options: Object
    },
    data: function() {
        const toolbarExtensionPoint = [];

        return {
            toolbarExtensionPoint,
            fullScreen: false,
            value: ''
        };
    },
    computed: {
        toolbarElements: function computeToolbarElements() {
            return [
                this.toolbarExtensionPoint,
                {
                    type: 'button',
                    icon: this.fullScreen ? 'octo-icon-fullscreen-collapse' : 'octo-icon-fullscreen',
                    command: 'document:toggleFullscreen',
                    pressed: this.fullScreen,
                    fixedRight: true,
                    tooltip: this.lang.langFullscreen
                }
            ];
        },

        toolbarButtons: function computeToolbarButtons() {
            if (typeof this.options.toolbarButtons !== 'string') {
                return [];
            }

            return this.options.toolbarButtons.split(',').map((button) => {
                return button.trim();
            });
        },

        fullPage: function computeFullPage() {
            return !!this.options.fullpage;
        },

        readOnly: function computeReadOnly() {
            return this.options.readOnly;
        }
    },
    mounted: function onMounted() {
        this.value = this.textarea.value;
    },
    methods: {
        getEditor: function getEditor() {
            return this.$refs.richeditor.getEditor();
        },

        setContent: function setContent(str) {
            this.value = str;
        },

        onToolbarCommand: function onToolbarCommand(cmd) {
            if (cmd == 'document:toggleFullscreen') {
                this.fullScreen = !this.fullScreen;
            }
        },

        onFocus: function onFocus() {
            this.$emit('focus');
        },

        onBlur: function onBlur() {
            this.$emit('blur');
        }
    },
    beforeDestroy: function beforeDestroy() {
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
    template: '#backend_vuecomponents_richeditordocumentconnector_formwidgetconnector'
});
