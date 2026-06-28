import template from './mentat-listing-index.html.twig';

Shopware.Component.register('mentat-listing-index', {
    template,
    inject: ['repositoryFactory'],
    data() {
        return {
            productName: '',
            price: null,
            stock: 0,
            categoryId: null,
            categories: [],
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
                this.categories = result.map(c => ({ label: c.name, value: c.id }));
            } catch (error) {
                window.alert('Could not load categories: ' + (error.message || 'unknown error'));
            }
        },
    },
});
