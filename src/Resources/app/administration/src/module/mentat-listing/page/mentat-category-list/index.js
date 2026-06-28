import template from './mentat-category-list.html.twig';
import './mentat-category-list.scss';
import '../../component/mentat-category-editor';
import {
    cloneTemplate,
    createEmptyTemplate,
    normalizeTemplate,
} from '../../helper/category-template.helper';

Shopware.Component.register('mentat-category-list', {
    template,
    inject: ['repositoryFactory'],

    data() {
        return {
            categories: [],
            newCategoryDraft: {
                name: '',
                technicalName: '',
                template: { sections: [] },
            },
            editingCategoryId: null,
            editingCategoryDraft: null,
        };
    },

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('mentat_category');
        },
    },

    created() {
        this.loadCategories();
    },

    methods: {
        async loadCategories() {
            try {
                const criteria = new Shopware.Data.Criteria();
                const result = await this.categoryRepository.search(criteria, Shopware.Context.api);
                this.categories = result.map(category => {
                    category.template = this.normalizeTemplate(category.template);

                    return category;
                });
            } catch (error) {
                window.alert('Could not load categories: ' + (error.message || 'unknown error'));
            }
        },

        async createCategory() {
            try {
                const errors = this.validateCategory(this.newCategoryDraft);

                if (errors.length > 0) {
                    window.alert(errors.join('\n'));
                    return;
                }

                const entity = this.categoryRepository.create(Shopware.Context.api);
                entity.name = this.newCategoryDraft.name;
                entity.technicalName = this.newCategoryDraft.technicalName;
                entity.template = this.normalizeTemplate(this.newCategoryDraft.template);
                await this.categoryRepository.save(entity, Shopware.Context.api);
                this.resetNewCategoryDraft();
                await this.loadCategories();
            } catch (error) {
                window.alert('Could not save: ' + (error.message || 'unknown error'));
            }
        },

        startEditing(category) {
            this.editingCategoryId = category.id;
            this.editingCategoryDraft = {
                name: category.name,
                technicalName: category.technicalName,
                template: this.cloneTemplate(category.template),
            };
        },

        cancelEditing() {
            this.editingCategoryId = null;
            this.editingCategoryDraft = null;
        },

        async updateCategory(category) {
            try {
                const errors = this.validateCategory(this.editingCategoryDraft);

                if (errors.length > 0) {
                    window.alert(errors.join('\n'));
                    return;
                }

                category.name = this.editingCategoryDraft.name;
                category.technicalName = this.editingCategoryDraft.technicalName;
                category.template = this.normalizeTemplate(this.editingCategoryDraft.template);
                await this.categoryRepository.save(category, Shopware.Context.api);
                this.cancelEditing();
                await this.loadCategories();
            } catch (error) {
                window.alert('Could not update: ' + (error.message || 'unknown error'));
            }
        },

        async deleteCategory(category) {
            if (!window.confirm(`Delete category "${category.name}"?`)) {
                return;
            }

            try {
                await this.categoryRepository.delete(category.id, Shopware.Context.api);
                await this.loadCategories();
            } catch (error) {
                window.alert('Could not delete: ' + (error.message || 'unknown error'));
            }
        },

        normalizeTemplate(template) {
            return normalizeTemplate(template);
        },

        createEmptyTemplate() {
            return createEmptyTemplate();
        },

        cloneTemplate(template) {
            return cloneTemplate(template);
        },

        resetNewCategoryDraft() {
            this.newCategoryDraft = {
                name: '',
                technicalName: '',
                template: this.createEmptyTemplate(),
            };
        },

        validateCategory(category) {
            const errors = [];
            const usedKeys = new Set();

            if (!(category.name || '').trim()) {
                errors.push('Name is required.');
            }

            if (!(category.technicalName || '').trim()) {
                errors.push('Technical name is required.');
            }

            category.template.sections.forEach((section, sectionIndex) => {
                if (section.type === 'legal') {
                    if (!(section.content || '').trim()) {
                        errors.push(`Section ${sectionIndex + 1}: legal text is required.`);
                    }

                    return;
                }

                if (section.type === 'table') {
                    if (!(section.heading || '').trim()) {
                        errors.push(`Section ${sectionIndex + 1}: table heading is required.`);
                    }

                    if (section.rows.length === 0) {
                        errors.push(`Section ${sectionIndex + 1}: table needs at least one row.`);
                    }

                    section.rows.forEach((row, rowIndex) => {
                        this.validateTemplateKey(row.key, `Section ${sectionIndex + 1}, row ${rowIndex + 1}`, usedKeys, errors);

                        if (!(row.label || '').trim()) {
                            errors.push(`Section ${sectionIndex + 1}, row ${rowIndex + 1}: label is required.`);
                        }
                    });

                    return;
                }

                this.validateTemplateKey(section.key, `Section ${sectionIndex + 1}`, usedKeys, errors);

                if (!(section.instruction || '').trim()) {
                    errors.push(`Section ${sectionIndex + 1}: LLM instruction is required.`);
                }
            });

            return errors;
        },

        validateTemplateKey(key, label, usedKeys, errors) {
            const normalizedKey = (key || '').trim();

            if (!normalizedKey) {
                errors.push(`${label}: field key is required.`);
                return;
            }

            if (usedKeys.has(normalizedKey)) {
                errors.push(`${label}: field key "${normalizedKey}" is already used.`);
                return;
            }

            usedKeys.add(normalizedKey);
        },
    },
});