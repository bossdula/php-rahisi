namespace Core {
    export class DataView {
        constructor() {}

// ------------
// FUNCTION 1
// ------------
        public static displayTHead(headers: string[], hidden: string[], actions: boolean = false): string[] {
            const eliminate = ['id', 'row_id', 'txt_row_value'];

            let headerHiddenDiff = headers.filter(header => !hidden.includes(header));
            headerHiddenDiff = headerHiddenDiff.filter(header => !eliminate.includes(header));

            const trimmedHeaders: string[] = [];

            for (const header of headerHiddenDiff) {
                trimmedHeaders.push(this.trimColumnNames(header));
            }

            if (actions) {
                trimmedHeaders.push('Actions');
            }

            return trimmedHeaders;
        }


// ---------------
// FUNCTION 2
// ---------------
        public static trimColumnNames(th: string, body: boolean = false): string {
            const prefixes = ['txt', 'int', 'dbl', 'tar', 'dat', 'tim'];
            const suffixes = ['id'];

            const remove = ['txt_', 'int_', 'dbl_', 'tar_', 'dat_', 'tim_'];
            const capitalize = ['otp', 'id'];

            let trimmed = remove.reduce((result, prefix) => result.replace(prefix, ''), th);

            if (!body) {
                trimmed = trimmed.replace(/(_id|mx_|opt_)/g, '').replace(/[_-]/g, ' ');
                return capitalize.includes(trimmed) ? trimmed.toUpperCase() : this.capitalizeWords(trimmed);
            } else {
                if (trimmed.includes('opt_mx_')) {
                    return trimmed.toLowerCase();
                } else {
                    trimmed = trimmed.replace(/[_-]/g, ' ');
                    return capitalize.includes(trimmed) ? trimmed.toUpperCase() : this.capitalizeWords(trimmed);
                }
            }
        }


// ----------------
// FUNCTION 3
// ----------------
        private static capitalizeWords(str: string): string {
            return str.replace(/\b\w/g, char => char.toUpperCase());
        }


// -----------------
// FUNCTION 4
// ----------------
        public static displayTBody(
            object: any[],
            headers: string[],
            hidden: string[],
            actions: any[] = [],
            labels: any[] = [],
            formatters: any[] = []
            ): any[]  {
            const cleanedUpTitles = this.trimHiddenData(headers, hidden);
            const tmpData: any[] = [];

            for (let i = 0; i < object.length; i++) {
                const row = object[i];
                for (const field in row) {
                    const value = row[field];
                    const key = this.trimColumnNames(field, true);
                    let fk: any[] = [];

                    if (field.includes('opt_mx_') && labels[key] && labels[key][value]) {
                        fk = this.getFKValues(key, value, labels[key]);
                    }

                    if (cleanedUpTitles.includes(key)) {
                        tmpData[i] = tmpData[i] || {};
                        tmpData[i][key] = fk.length ? fk : value;
                    }
                }
            }

            return tmpData;
        }


// -----------------
// FUNCTION 5
// ----------------
        private static trimHiddenData(header: string[], hidden: string[]): string[] {
            const eliminate = ['id', 'row_id', 'txt_row_value', 'txt_added_by'];
            let headerHiddenDiff = header.filter(h => !hidden.includes(h));
            return headerHiddenDiff.filter(h => !eliminate.includes(h));
        }


// -----------------
// FUNCTION 6
// ----------------
        private static getFKValues(field: string, value: any, label: any): any {
            const trimmedTh = field.replace(/(_id|mx_|opt_)/g, '');
            return {
                type: 'fk',
                og_name: field,
                display_name: trimmedTh,
                og_value: value,
                display_value: label[value]?.value,
                color: label[value]?.color ?? ''
            };
        }


// -----------------
// FUNCTION 7
// ----------------
        private static generateActionButtons(actions: any[], row: any, rowId: string): any[] {
            const tmpActions: any[] = [];

            for (const action of actions) {
                const tmpAction = {
                    action: action.action.toLowerCase(),
                    name: action.name,
                    icon: action.icon,
                    color: action.color,
                    url: action.url,
                    parameter: 'none',
                    disabled: 0
                };

                if (action.disabled) {
                    const checkDisable = this.evaluateDisabledActions(row, action.disabled);
                    tmpAction.parameter = checkDisable.key;
                    tmpAction.disabled = checkDisable.value;
                }

                tmpActions.push(tmpAction);
            }

            return tmpActions;
        }


// -----------------
// FUNCTION 8
// ----------------
        private static evaluateDisabledActions(row: any, disabledValues: any): any {
            let evaluation = true;  // Start with true for AND condition, false for OR condition
            let fk: string | null = null;
        
            // Handle OR condition
            if (disabledValues.OR) {
                evaluation = false; // OR condition starts as false
                for (const key in disabledValues.OR) {
                    const value = disabledValues.OR[key];
                    const fieldValue = row[key];
                    const valuesIsArray = Array.isArray(value);
                    if (valuesIsArray) {
                        for (const item of value) {
                            if (fieldValue === item) {
                                evaluation = true; // Set to true if any condition is met
                                break;
                            }
                        }
                    } else {
                        if (fieldValue === value) {
                            evaluation = true; // Set to true if the condition is met
                            break;
                        }
                    }
                }
                delete disabledValues.OR;
            }
        
            // Handle AND condition
            if (disabledValues.AND) {
                for (const key in disabledValues.AND) {
                    const value = disabledValues.AND[key];
                    const fieldValue = row[key];
                    const valuesIsArray = Array.isArray(value);
                    let conditionMet = false;
        
                    if (valuesIsArray) {
                        for (const item of value) {
                            if (fieldValue === item) {
                                conditionMet = true;
                                break; 
                            }
                        }
                    } else {
                        conditionMet = fieldValue === value;
                    }
        
                    if (!conditionMet) {
                        evaluation = false; // Set to false if any AND condition fails
                        break;
                    }
                }
                delete disabledValues.AND;
            }
        
            // Check remaining conditions other than OR and AND
            if (Object.keys(disabledValues).length > 0) {
                for (const key in disabledValues) {
                    const value = disabledValues[key];
                    const fieldValue = row[key];
                    const valuesIsArray = Array.isArray(value);
                    let conditionMet = false;
        
                    if (valuesIsArray) {
                        for (const item of value) {
                            if (fieldValue === item) {
                                conditionMet = true;
                                break;
                            }
                        }
                    } else {
                        conditionMet = fieldValue === value;
                    }
        
                    if (!conditionMet) {
                        evaluation = false; // Set to false if condition fails
                        break;
                    }
                }
            }
        
            return { key: fk, value: evaluation };
        }
        


// -----------------
// FUNCTION 9
// -----------------
        private static getActionName(action: string): string {
            let actionName = action;
            if (action.startsWith('post_')) {
                return this.capitalizeWords(action.substring(5).replace('_', ' '));
            } else {
                return this.capitalizeWords(action.replace('_', ' '));
            }
        }
    }
}
