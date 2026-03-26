/**
 * Event Listener Patch
 * This script patches the addEventListener method to make touch and wheel events passive by default.
 * It should be loaded before any other scripts in the <head> section.
 */

// Only run in browser environment
if (typeof window !== 'undefined') {
    // Store the original addEventListener
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    
    // Define events that should be passive by default
    const passiveEvents = {
        'touchstart': true,
        'touchmove': true,
        'touchend': true,
        'touchcancel': true,
        'wheel': true,
        'mousewheel': true,
        'scroll': true // Also make scroll events passive
    };
    
    // Override addEventListener
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        // Convert options to object if it's a boolean (for capture)
        let useCapture = false;
        let opts = options;
        
        if (typeof options === 'boolean') {
            useCapture = options;
            opts = { capture: useCapture };
        } else if (typeof options === 'object') {
            opts = { ...options };
        } else {
            opts = {};
        }
        
        // Make the event passive if it's in our list and not explicitly set to false
        if (passiveEvents[type] !== undefined && opts.passive === undefined) {
            opts.passive = true;
        }
        
        // Call the original addEventListener with our options
        return originalAddEventListener.call(this, type, listener, opts);
    };
    
    // Patch for addEventListener on the window object specifically
    const originalWindowAddEventListener = window.addEventListener;
    window.addEventListener = function(type, listener, options) {
        let opts = options;
        
        if (passiveEvents[type] !== undefined) {
            if (typeof opts === 'boolean') {
                opts = { capture: opts, passive: true };
            } else if (opts && typeof opts === 'object') {
                opts = { ...opts, passive: opts.passive !== false };
            } else {
                opts = { passive: true };
            }
        }
        
        return originalWindowAddEventListener.call(window, type, listener, opts);
    };
    
    console.log('Event listener patch applied');
}
