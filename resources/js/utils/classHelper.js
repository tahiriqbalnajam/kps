/**
 * Extract class_id and section_id from tree select value
 * @param {string|number} selectedValue - The value from tree select (e.g., "class_5" or "section_3")
 * @param {Array} classes - The classes array with sections as children
 * @returns {Object} - { classId, sectionId }
 */
export function extractClassAndSection(selectedValue, classes) {
  let classId = null;
  let sectionId = null;

  if (typeof selectedValue === 'string') {
    if (selectedValue.startsWith('class_')) {
      // Class selected
      classId = parseInt(selectedValue.replace('class_', ''));
      sectionId = null;
    } else if (selectedValue.startsWith('section_')) {
      // Section selected - need to find parent class
      sectionId = parseInt(selectedValue.replace('section_', ''));
      
      // Find the section and get its class_id
      for (const cls of classes) {
        if (cls.children) {
          const section = cls.children.find(s => s.id === sectionId);
          if (section) {
            classId = section.class_id;
            break;
          }
        }
      }
    }
  } else if (typeof selectedValue === 'number') {
    // If it's already a number, use it directly as class_id
    classId = selectedValue;
    sectionId = null;
  }

  return { classId, sectionId };
}

/**
 * Extract only class_id from tree select value
 * @param {string|number} selectedValue - The value from tree select
 * @param {Array} classes - The classes array with sections as children
 * @returns {number|null} - The class ID
 */
export function extractClassId(selectedValue, classes) {
  const { classId } = extractClassAndSection(selectedValue, classes);
  return classId;
}

/**
 * Transform classes data to tree structure for el-tree-select
 * @param {Array} classesData - Raw classes data from API
 * @returns {Array} - Transformed tree structure
 */
export function transformClassesToTree(classesData) {
  return classesData.map(cls => {
    const classItem = {
      id: cls.id,
      label: cls.name,
      value: `class_${cls.id}`,
      type: 'class',
      name: cls.name,
      students_count: cls.students_count,
      males_count: cls.males_count,
      females_count: cls.females_count,
    };
    
    // Add children if there are sections
    if (cls.sections && cls.sections.length > 0) {
      classItem.children = cls.sections.map(section => ({
        id: section.id,
        label: section.name,
        value: `section_${section.id}`,
        type: 'section',
        class_id: cls.id,
        name: section.name,
        students_count: section.students_count,
        males_count: section.males_count,
        females_count: section.females_count,
      }));
    }
    
    return classItem;
  });
}

/**
 * Get the selected class or section name
 * @param {string|number} selectedValue - The value from tree select
 * @param {Array} classes - The classes array with sections as children
 * @returns {string} - The name of selected class/section
 */
export function getSelectedClassName(selectedValue, classes) {
  if (typeof selectedValue === 'string') {
    if (selectedValue.startsWith('class_')) {
      const classId = parseInt(selectedValue.replace('class_', ''));
      const cls = classes.find(c => c.id === classId);
      return cls ? cls.label : '';
    } else if (selectedValue.startsWith('section_')) {
      const sectionId = parseInt(selectedValue.replace('section_', ''));
      for (const cls of classes) {
        if (cls.children) {
          const section = cls.children.find(s => s.id === sectionId);
          if (section) {
            return `${cls.label} - ${section.label}`;
          }
        }
      }
    }
  }
  return '';
}
