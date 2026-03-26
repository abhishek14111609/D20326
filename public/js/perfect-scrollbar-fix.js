// PerfectScrollbar configuration with passive events
if (typeof PerfectScrollbar !== 'undefined') {
    // Override default options
    PerfectScrollbar.initialize = function(element, options) {
        const defaultOptions = {
            wheelSpeed: 0.5,
            minScrollbarLength: 20,
            maxScrollbarLength: 60,
            swipeEasing: true,
            wheelPropagation: false,
            suppressScrollX: true,
            useBothWheelAxes: false,
            // Enable passive event listeners for better performance
            handlers: ['click-rail', 'drag-thumb', 'keyboard', 'wheel', 'touch']
        };

        const mergedOptions = { ...defaultOptions, ...options };
        return new PerfectScrollbar(element, mergedOptions);
    };

    // Update existing instances if any
    document.addEventListener('DOMContentLoaded', () => {
        const psElements = document.querySelectorAll('.ps');
        psElements.forEach(el => {
            if (!el.ps) { // Only initialize if not already initialized
                new PerfectScrollbar(el, {
                    wheelSpeed: 0.5,
                    minScrollbarLength: 20,
                    maxScrollbarLength: 60,
                    swipeEasing: true,
                    wheelPropagation: false,
                    suppressScrollX: true,
                    useBothWheelAxes: false
                });
            }
        });
    });
}

// Update touch and wheel event listeners to be passive
function makePassiveEventListeners() {
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        // Check if the event is scroll-blocking and should be passive
        const passiveEvents = ['touchstart', 'touchmove', 'touchend', 'wheel', 'mousewheel'];
        
        if (passiveEvents.includes(type)) {
            // If options is a boolean, convert it to an options object
            if (typeof options === 'boolean') {
                options = {
                    capture: options,
                    passive: true
                };
            } else if (typeof options === 'object') {
                // If options is an object, ensure passive is true
                options = {
                    ...options,
                    passive: options.passive !== false // Only set to false if explicitly set to false
                };
            } else {
                // No options provided, set default passive to true
                options = { passive: true };
            }
        }
        
        return originalAddEventListener.call(this, type, listener, options);
    };
}

// Only run in browser environment
if (typeof window !== 'undefined') {
    makePassiveEventListeners();
}
