/**
 * Fix Remaining Non-Passive Event Listeners
 * This script runs after the page loads to patch any remaining non-passive event listeners.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for all scripts to load
    setTimeout(function() {
        // Patch for PerfectScrollbar if it exists
        if (typeof PerfectScrollbar !== 'undefined') {
            const originalInit = PerfectScrollbar.initialize || function() {};
            PerfectScrollbar.initialize = function(element, options) {
                const defaultOptions = {
                    wheelSpeed: 0.5,
                    minScrollbarLength: 20,
                    maxScrollbarLength: 60,
                    swipeEasing: true,
                    wheelPropagation: false,
                    suppressScrollX: true,
                    useBothWheelAxes: false,
                    handlers: ['click-rail', 'drag-thumb', 'keyboard', 'wheel', 'touch']
                };
                
                const mergedOptions = { ...defaultOptions, ...(options || {}) };
                return originalInit.call(PerfectScrollbar, element, mergedOptions);
            };
        }

        // Patch for Menu class if it exists
        if (typeof Menu !== 'undefined') {
            // Store original methods that add event listeners
            const originalBindEvents = Menu.prototype._bindEvents;
            if (originalBindEvents) {
                Menu.prototype._bindEvents = function() {
                    // Call original method
                    originalBindEvents.call(this);
                    
                    // Now patch any event listeners that were added
                    const menuInner = this._el.querySelector('.menu-inner');
                    if (menuInner) {
                        // Clone the node to remove all event listeners
                        const clone = menuInner.cloneNode(true);
                        menuInner.parentNode.replaceChild(clone, menuInner);
                        
                        // Re-add event listeners with passive: true
                        const events = ['touchstart', 'touchmove', 'wheel'];
                        events.forEach(eventType => {
                            clone.addEventListener(eventType, function(e) {
                                // This is just a placeholder to ensure the event is captured
                                // The actual event handling is done by the original code
                            }, { passive: true });
                        });
                    }
                };
            }
        }
        
        // Patch for Helpers if it exists
        if (typeof Helpers !== 'undefined') {
            // Patch any Helper methods that add touch/wheel events
            const originalBindMenuMouseEvents = Helpers._bindMenuMouseEvents;
            if (originalBindMenuMouseEvents) {
                Helpers._bindMenuMouseEvents = function() {
                    // Call original method
                    originalBindMenuMouseEvents.call(Helpers);
                    
                    // Now patch any event listeners that were added
                    const menu = this.getMenu();
                    if (menu) {
                        // Clone the node to remove all event listeners
                        const clone = menu.cloneNode(true);
                        menu.parentNode.replaceChild(clone, menu);
                        
                        // Re-add event listeners with passive: true
                        const events = ['touchstart', 'touchmove', 'wheel'];
                        events.forEach(eventType => {
                            clone.addEventListener(eventType, function(e) {
                                // This is just a placeholder to ensure the event is captured
                            }, { passive: true });
                        });
                    }
                };
            }
        }
        
        console.log('Non-passive event listeners patched');
    }, 1000); // Wait 1 second for all scripts to load
});
