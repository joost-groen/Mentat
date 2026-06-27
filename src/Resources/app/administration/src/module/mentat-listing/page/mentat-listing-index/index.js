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
        categories() {
            return this.repositoryFactory.create('mentat_category');
        },
    },

    // Load categories when the component is created
    created() {
        this.loadCategories();
    },

    methods: {
        // Load categories from the repository
        async loadCategories() {
            const criteria = new Shopware.Data.Criteria();
            const result = await this.categoryRepository.search(criteria, Shopware.Context.api);
            this.categories = result.map(c => ({ label: c.name, value: c.id }));
        },
    },
});
