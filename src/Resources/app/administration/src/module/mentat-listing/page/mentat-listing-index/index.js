import template from './mentat-listing-index.html.twig';
import './mentat-listing-index.scss';
import { getSectionTypeLabel, normalizeTemplate } from '../../helper/category-template.helper';

Shopware.Component.register('mentat-listing-index', {
    template,
    inject: ['repositoryFactory', 'loginService'],

    data() {
        return {
            form: {
                name: '',
                productNumber: '',
                price: null,
                stock: 0,
            },
            categoryId: null,
            pdfFile: null,
            categories: [],
            isLoadingCategories: false,
            isSubmitting: false,
            errors: [],
            result: null,
            httpClient: null,
        };
    },

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('mentat_category');
        },

        categoryOptions() {
            return this.categories.map(category => ({
                label: category.name,
                value: category.id,
            }));
        },

        selectedCategory() {
            return this.categories.find(category => category.id === this.categoryId) || null;
        },

        templateSections() {
            if (!this.selectedCategory) {
                return [];
            }

            return normalizeTemplate(this.selectedCategory.template).sections;
        },

        templateFieldCount() {
            return this.templateSections.reduce((count, section) => {
                if (section.type === 'table') {
                    return count + section.rows.length;
                }

                if (section.type === 'legal') {
                    return count;
                }

                return count + 1;
            }, 0);
        },

        extractedEntries() {
            if (!this.result || !this.result.extractedValues) {
                return [];
            }

            return Object.entries(this.result.extractedValues).map(([key, value]) => ({
                key,
                value: value || 'Not found',
                isEmpty: value === '',
            }));
        },

        hasWarnings() {
            return this.missingFields.length > 0 || this.emptyFields.length > 0;
        },

        missingFields() {
            return this.result && this.result.missingFields ? this.result.missingFields : [];
        },

        emptyFields() {
            return this.result && this.result.emptyFields ? this.result.emptyFields : [];
        },

        canSubmit() {
            return !this.isSubmitting
                && this.form.name.trim() !== ''
                && this.form.productNumber.trim() !== ''
                && this.form.price !== null
                && this.form.price !== ''
                && Number(this.form.price) >= 0
                && this.form.stock !== null
                && this.form.stock !== ''
                && Number(this.form.stock) >= 0
                && this.categoryId !== null
                && this.pdfFile !== null;
        },
    },

    created() {
        this.httpClient = Shopware.Application.getContainer('init').httpClient;
        this.loadCategories();
    },

    methods: {
        async loadCategories() {
            this.isLoadingCategories = true;
            this.errors = [];

            try {
                const criteria = new Shopware.Data.Criteria();
                const result = await this.categoryRepository.search(criteria, Shopware.Context.api);
                this.categories = result.map(category => {
                    category.template = normalizeTemplate(category.template);

                    return category;
                });
            } catch (error) {
                this.errors = [this.getErrorMessage(error, 'Could not load categories.')];
            } finally {
                this.isLoadingCategories = false;
            }
        },

        onFileChange(event) {
            const [file] = event.target.files;
            this.result = null;

            if (!file) {
                this.pdfFile = null;
                return;
            }

            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                this.pdfFile = null;
                this.errors = ['Please choose a PDF file.'];
                event.target.value = '';
                return;
            }

            this.errors = [];
            this.pdfFile = file;
        },

        clearPdfFile() {
            this.pdfFile = null;
            this.result = null;

            if (this.$refs.pdfInput) {
                this.$refs.pdfInput.value = '';
            }
        },

        async createDraftListing() {
            this.errors = this.validateForm();

            if (this.errors.length > 0) {
                return;
            }

            this.isSubmitting = true;
            this.result = null;

            try {
                const payload = new FormData();
                payload.append('name', this.form.name.trim());
                payload.append('productNumber', this.form.productNumber.trim());
                payload.append('price', Number(this.form.price).toString());
                payload.append('stock', parseInt(this.form.stock, 10).toString());
                payload.append('categoryId', this.categoryId);
                payload.append('pdf', this.pdfFile);

                const response = await this.httpClient.post('/_action/mentat/listing/draft', payload, {
                    headers: this.getAuthHeaders(),
                });

                this.result = response.data;
            } catch (error) {
                this.errors = this.getApiErrors(error);
            } finally {
                this.isSubmitting = false;
            }
        },

        openProduct() {
            if (!this.result || !this.result.productId) {
                return;
            }

            this.$router.push({
                name: 'sw.product.detail',
                params: {
                    id: this.result.productId,
                },
            });
        },

        validateForm() {
            const errors = [];

            if (this.form.name.trim() === '') {
                errors.push('Product name is required.');
            }

            if (this.form.productNumber.trim() === '') {
                errors.push('Product number is required.');
            }

            if (this.categoryId === null) {
                errors.push('Choose a Mentat category.');
            }

            if (this.form.price === null || this.form.price === '' || Number(this.form.price) < 0) {
                errors.push('Price must be zero or a positive number.');
            }

            if (this.form.stock === null || this.form.stock === '' || Number(this.form.stock) < 0) {
                errors.push('Stock must be zero or a positive whole number.');
            }

            if (!this.pdfFile) {
                errors.push('Upload a spec-sheet PDF.');
            }

            return errors;
        },

        getAuthHeaders() {
            return {
                Authorization: `Bearer ${this.loginService.getToken()}`,
            };
        },

        getApiErrors(error) {
            const apiErrors = error && error.response && error.response.data
                ? error.response.data.errors
                : null;

            if (Array.isArray(apiErrors) && apiErrors.length > 0) {
                return apiErrors.map(apiError => {
                    if (typeof apiError === 'string') {
                        return apiError;
                    }

                    return apiError.detail || apiError.title || 'Something went wrong while creating the draft.';
                });
            }

            return [this.getErrorMessage(error, 'Something went wrong while creating the draft.')];
        },

        getErrorMessage(error, fallback) {
            return error && error.message ? error.message : fallback;
        },

        getSectionLabel(section) {
            if (section.type === 'table') {
                return `${getSectionTypeLabel(section.type)}: ${section.heading || 'Untitled table'}`;
            }

            if (section.type === 'legal') {
                return getSectionTypeLabel(section.type);
            }

            return `${getSectionTypeLabel(section.type)}: ${section.key || 'Unnamed field'}`;
        },

        formatFileSize(size) {
            if (size < 1024 * 1024) {
                return `${Math.max(1, Math.round(size / 1024))} KB`;
            }

            return `${(size / (1024 * 1024)).toFixed(1)} MB`;
        },
    },
});
