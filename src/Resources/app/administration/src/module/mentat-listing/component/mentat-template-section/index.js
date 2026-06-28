import template from './mentat-template-section.html.twig';
import { getSectionTypeLabel } from '../../helper/category-template.helper';

Shopware.Component.register('mentat-template-section', {
    template,

    props: {
        draft: {
            type: Object,
            required: true,
        },
        section: {
            type: Object,
            required: true,
        },
        sectionIndex: {
            type: Number,
            required: true,
        },
    },

    methods: {
        getSectionTypeLabel,

        moveSection(direction) {
            const sections = this.draft.template.sections;
            const targetIndex = this.sectionIndex + direction;

            if (targetIndex < 0 || targetIndex >= sections.length) {
                return;
            }

            const [section] = sections.splice(this.sectionIndex, 1);
            sections.splice(targetIndex, 0, section);
        },

        removeSection() {
            this.draft.template.sections.splice(this.sectionIndex, 1);
        },

        addTableRow() {
            this.section.rows.push({
                key: '',
                label: '',
            });
        },

        removeTableRow(rowIndex) {
            this.section.rows.splice(rowIndex, 1);
        },
    },
});
