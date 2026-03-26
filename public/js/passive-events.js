/**
 * Passive Event Listeners
 * This script makes all touch and wheel events passive by default
 * to improve scrolling performance and remove browser warnings.
 */
(function() {
    // Check if running in a browser environment
    if (typeof window === 'undefined') {
        return;
    }

    // List of event types that should be passive by default
    const passiveEvents = [
        'touchstart', 'touchmove', 'touchend', 'touchcancel',
        'wheel', 'mousewheel',
        'pointerdown', 'pointermove', 'pointerup',
        'mousedown', 'mousemove', 'mouseup', 'mouseenter', 'mouseleave', 'mouseover', 'mouseout'
    ];

    // Store the original addEventListener
    const originalAddEventListener = EventTarget.prototype.addEventListener;

    // Override addEventListener
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        // Only modify the options for events that should be passive
        if (passiveEvents.includes(type)) {
            // If options is a boolean (for capture), convert to object
            if (typeof options === 'boolean') {
                options = {
                    capture: options,
                    passive: true
                };
            } 
            // If options is an object, ensure passive is true
            else if (typeof options === 'object') {
                options = {
                    ...options,
                    passive: options.passive !== false // Only set to false if explicitly set to false
                };
            } 
            // No options provided, use passive by default
            else {
                options = { passive: true };
            }
        }

        // Call the original addEventListener with potentially modified options
        return originalAddEventListener.call(this, type, listener, options);
    };

    console.log('Passive event listeners enabled');
})();
