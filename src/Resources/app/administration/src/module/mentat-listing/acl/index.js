Shopware.Service('privileges').addPrivilegeMappingEntry({ // Grab admin privileges service and add a new privilege mapping entry
    category: 'permissions',
    parent: 'catalogues',
    key: 'mentat_category', // Unique identifier for the privilege
    roles: { // Define roles for the privilege
        viewer: {
            privileges: ['mentat_category:read'],
            dependencies: [],
        },
        editor: { // Editor role can update categories
            privileges: ['mentat_category:update'],
            dependencies: ['mentat_category.viewer'], // Editor role depends on viewer role
        },
        creator: { // Creator role can create categories
            privileges: ['mentat_category:create'],
            dependencies: ['mentat_category.viewer', 'mentat_category.editor'], // Creator role depends on viewer and editor roles
        },
        deleter: { // Deleter role can delete categories
            privileges: ['mentat_category:delete'],
            dependencies: ['mentat_category.viewer'], // Deleter role depends on viewer role
        },
    },
});
