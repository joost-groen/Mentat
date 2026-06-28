import template from './mentat-category-editor.html.twig';
import '../mentat-template-section';
import {
    SECTION_TYPES,
    createSection,
} from '../../helper/category-template.helper';

Shopware.Component.register('mentat-category-editor', {
    template,
    emits: ['save', 'cancel'],

    props: {
        draft: {
            type: Object,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        description: {
            type: String,
            required: true,
        },
        saveLabel: {
            type: String,
            required: true,
        },
        showCancel: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            selectedSectionType: 'description',
        };
    },

    computed: {
        sectionTypes() {
            return SECTION_TYPES;
        },
    },

    methods: {
        addSection() {
            this.draft.template.sections.push(createSection(this.selectedSectionType));
        },

        onSave() {
            this.$emit('save');
        },

        onCancel() {
            this.$emit('cancel');
        },
    },
});
