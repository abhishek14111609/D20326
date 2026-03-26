const TRANSITION_EVENTS = ['transitionend', 'webkitTransitionEnd', 'oTransitionEnd']

class Menu {
  // ... [Previous constructor and methods remain the same until _bindEvents]

  static _bindAnimationEndEvent(el, handler) {
    const cb = e => {
      if (e.target !== el) return
      Menu._unbindAnimationEndEvent(el)
      handler(e)
    }

    let duration = window.getComputedStyle(el).transitionDuration
    duration = parseFloat(duration) * (duration.indexOf('ms') !== -1 ? 1 : 1000)

    el._menuAnimationEndEventCb = cb
    
    // Update event listeners to use passive where appropriate
    TRANSITION_EVENTS.forEach(ev => {
      if (ev === 'touchstart' || ev === 'wheel') {
        el.addEventListener(ev, el._menuAnimationEndEventCb, { passive: true, capture: false });
      } else {
        el.addEventListener(ev, el._menuAnimationEndEventCb, false);
      }
    });

    el._menuAnimationEndEventTimeout = setTimeout(() => {
      cb({ target: el })
    }, duration + 50)
  }

  // ... [Rest of the Menu class remains the same]
}

// Update window.Menu reference
if (window.Menu) {
  window.Menu = Menu;
}

export { Menu };
