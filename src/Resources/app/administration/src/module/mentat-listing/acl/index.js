Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'catalogues',
    key: 'mentat_category',
    roles: {
        viewer: {
            privileges: ['mentat_category:read'],
            dependencies: [],
        },
        editor: {
            privileges: ['mentat_category:update'],
            dependencies: ['mentat_category.viewer'],
        },
        creator: {
            privileges: ['mentat_category:create'],
            dependencies: ['mentat_category.viewer', 'mentat_category.editor'],
        },
        deleter: {
            privileges: ['mentat_category:delete'],
            dependencies: ['mentat_category.viewer'],
        },
    },
});
