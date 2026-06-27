import './page/mentat-listing-index';

Shopware.Module.register('mentat-listing', {
    type: 'plugin',
    name: 'Mentat',
    title: 'Mentat',
    description: 'Create product listings from spec sheets',
    color: '#ff3d58',
    icon: 'regular-shopping-bag',

    routes: {
        index: {
            component: 'mentat-listing-index',
            path: 'index',
        },
    },

    navigation: [{
        id: 'mentat-listing',
        label: 'Mentat',
        color: '#ff3d58',
        path: 'mentat.listing.index',
        icon: 'regular-shopping-bag',
        parent: 'sw-catalogue',
        position: 100,
    }],
});