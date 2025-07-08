/**
 * Scrolls the page so that the bottom of the specified element aligns with the bottom of the viewport.
 * Performs scrolling only if the bottom of the element is not already in view.
 * 
 * @param {Element} element - The DOM element to scroll into view.
 */
function scrollToBottomOfElement(element) {
    const elementRect = element.getBoundingClientRect();
    const elementBottom = elementRect.bottom;
    const viewportHeight = window.innerHeight;

    // Check if the bottom of the element is below the viewport's bottom edge
    if (elementBottom > viewportHeight) {
        const offset = elementBottom - viewportHeight;
        window.scrollBy({ top: offset, behavior: 'smooth' });
    }
}

function scrollToMiddle(element) {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}

// Function to check if a variable is callable
function isCallable(variable) {
    return typeof variable === 'function';
}
/**
 * Executes a given code block after a random delay, with an option to execute immediately.
 * The function sets a form's `ariaBusy` state to true during the delay, and resets it to false after execution.
 * 
 * @param {Function} code - The code block to be executed.
 * @param {boolean} [now=false] - If true, executes the code block immediately without delay.
 */
function charger(code, load, now = false) {
    if (now) {
        return code()
    }

    const delay = 0.5 + Math.random() * 1.3;
    if (load) {
        load.ariaBusy = 'true';
    }
    setTimeout(() => {
        code();
        if (load) {
            load.ariaBusy = 'false';
        }
    }, delay * 1000);
}

/**
 * Formats a number as a currency value in Euros.
 * @param {string | number} number - The number to be formatted.
 * @return {string} The formatted currency string in Euros.
 */
function formatToEuro(number, decimal = true) {
    const formatter = new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    });
    let ret = formatter.format(number);
    if (!decimal) {
        ret = ret.replaceAll(',00', '');
    }
    return ret;
}