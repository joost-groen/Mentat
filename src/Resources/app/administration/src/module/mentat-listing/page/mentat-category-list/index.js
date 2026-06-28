import template from './mentat-category-list.html.twig';

Shopware.Component.register('mentat-category-list', {
    template,
    inject: ['repositoryFactory'],

    data() {
        return { categories: [], name: '', technicalName: '' };
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
                this.categories = await this.categoryRepository.search(criteria, Shopware.Context.api); 
            } catch (error) {
                window.alert('Could not load categories: ' + (error.message || 'unknown error'));
            }
        },

        async createCategory() {
            try {
                const entity = this.categoryRepository.create(Shopware.Context.api);
                entity.name = this.name;
                entity.technicalName = this.technicalName;
                await this.categoryRepository.save(entity, Shopware.Context.api);
                this.name = '';
                this.technicalName = '';
                await this.loadCategories();
            } catch (error) {
                window.alert('Could not save: ' + (error.message || 'unknown error'));
            }
        },
    },
});