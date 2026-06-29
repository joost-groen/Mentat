// Module imports for the Mentat plugin
import './acl';
import './page/mentat-listing-index';
import './page/mentat-category-list';

// Register new admin module with unique id 'mentat-listing'
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
        category: {
            component: 'mentat-category-list',
            path: 'category',
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
    },
    {
        id: 'mentat-category',
        label: 'Create Categories',
        color: '#ff3d58',
        path: 'mentat.listing.category',
        icon: 'regular-list',
        parent: 'mentat-listing',
        position: 10,
    }],
});