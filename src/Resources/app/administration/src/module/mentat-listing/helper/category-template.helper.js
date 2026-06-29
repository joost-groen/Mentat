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

// Normalize template to ensure it is in the correct format
export function normalizeTemplate(template) {
    if (!template || !Array.isArray(template.sections)) {
        return createEmptyTemplate();
    }

    return {
        sections: template.sections.map(section => ({ // Map each section to a new object 
            ...section,
            rows: Array.isArray(section.rows) ? section.rows : [], // If rows is not an array, set it to an empty array and if it is, set it to the original value
        })),
    };
}

// Clone template to ensure it is in the correct format
export function cloneTemplate(template) {
    return JSON.parse(JSON.stringify(normalizeTemplate(template))); // Deep clone to ensure the original template is not modified (not just a reference)
}

// Create a new section of the given type -> works like a factory function (returns a new object with the given type and empty properties)
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

// Get the label for the given section type
export function getSectionTypeLabel(sectionType) {
    const matchingType = SECTION_TYPES.find(type => type.value === sectionType); // Find the section type in the SECTION_TYPES array

    return matchingType ? matchingType.label : sectionType; // If the section type is found, return the label, otherwise return the section type
}
