export const SECTION_TYPES = [
    { value: 'title', label: 'Title' },
    { value: 'description', label: 'Description' },
    { value: 'table', label: 'Table' },
    { value: 'legal', label: 'Legal text' },
    { value: 'SEO-title', label: 'SEO title' },
    { value: 'SEO-description', label: 'SEO description' },
];

export function createEmptyTemplate() {
    return { sections: [] };
}

export function normalizeTemplate(template) {
    if (!template || !Array.isArray(template.sections)) {
        return createEmptyTemplate();
    }

    return {
        sections: template.sections.map(section => ({
            ...section,
            rows: Array.isArray(section.rows) ? section.rows : [],
        })),
    };
}

export function cloneTemplate(template) {
    return JSON.parse(JSON.stringify(normalizeTemplate(template)));
}

export function createSection(type) {
    if (type === 'table') {
        return {
            type,
            heading: '',
            rows: [],
        };
    }

    if (type === 'legal') {
        return {
            type,
            content: '',
        };
    }

    return {
        type,
        key: '',
        instruction: '',
    };
}

export function getSectionTypeLabel(sectionType) {
    const matchingType = SECTION_TYPES.find(type => type.value === sectionType);

    return matchingType ? matchingType.label : sectionType;
}
