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
/**
 * Animate shuffle of the direct children of a container.
 * Children will "shuffle" visually for 2s (fast reorder every 0.5s) before settling into a final random order.
 *
 * @param {Element|string} container  DOM element or a CSS selector string.
 * @returns {Promise<Element[]>}      Resolves with the children in their final order.
 */
 function animatedShuffleChildren(container) {
  const root = typeof container === 'string' ? document.querySelector(container) : container;
  if (!root) return Promise.resolve([]);

  const children = Array.from(root.children);
  const n = children.length;
  if (n < 2) return Promise.resolve(children);

  // Set CSS for smooth transitions
  root.style.display = 'grid';
  root.style.gridTemplateColumns = `repeat(${n}, 1fr)`;
  children.forEach(c => {
    c.style.transition = 'transform 0.2s';
  });

  function shuffleOnce() {
    const arr = [...children];
    for (let i = arr.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    arr.forEach(el => root.appendChild(el));
  }

  return new Promise(resolve => {
    let count = 0;
    const interval = setInterval(() => {
      shuffleOnce();
      count++;
    }, 100); // 2 times per second

    setTimeout(() => {
      clearInterval(interval);
      shuffleOnce();
      resolve(Array.from(root.children));
    }, 1000); // 2s total
  });
}
