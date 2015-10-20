/*!
 * Bootstrap v3.0.3 (http://getbootstrap.com)
 * Copyright 2013 Twitter, Inc.
 * Licensed under http://www.apache.org/licenses/LICENSE-2.0
 */

if (typeof $vic === "undefined") { throw new Error("Victoire's Bootstrap edition requires $vic jQuery version") }

/* ========================================================================
 * Bootstrap: transition.js v3.0.3
 * http://getbootstrap.com/javascript/#transitions
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
  // ============================================================

  function transitionEnd() {
    var el = document.createElement('bootstrap')

    var transEndEventNames = {
      'WebkitTransition' : 'webkitTransitionEnd'
    , 'MozTransition'    : 'transitionend'
    , 'OTransition'      : 'oTransitionEnd otransitionend'
    , 'transition'       : 'transitionend'
    }

    for (var name in transEndEventNames) {
      if (el.style[name] !== undefined) {
        return { end: transEndEventNames[name] }
      }
    }
  }

  // http://blog.alexmaccaw.com/css-transitions
  $vic.fn.vicemulateTransitionEnd = function (duration) {
    var called = false, $vicel = this
    $vic(this).one($vic.support.transition.end, function () { called = true })
    var callback = function () { if (!called) $vic($vicel).trigger($vic.support.transition.end) }
    setTimeout(callback, duration)
    return this
  }

  $vic(function () {
    $vic.support.transition = transitionEnd()
  })

}($vic);

/* ========================================================================
 * Bootstrap: alert.js v3.0.3
 * http://getbootstrap.com/javascript/#alerts
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // ALERT CLASS DEFINITION
  // ======================

  var dismiss = '[data-dismiss="alert"]'
  var Alert   = function (el) {
    $vic(el).on('click', dismiss, this.close)
  }

  Alert.prototype.close = function (e) {
    var $victhis    = $vic(this)
    var selector = $victhis.attr('data-target')

    if (!selector) {
      selector = $victhis.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$vic)/, '') // strip for ie7
    }

    var $vicparent = $vic(selector)

    if (e) e.preventDefault()

    if (!$vicparent.length) {
      $vicparent = $victhis.hasClass('vic-alert') ? $victhis : $victhis.parent()
    }

    $vicparent.trigger(e = $vic.Event('close.bs.vic-alert'))

    if (e.isDefaultPrevented()) return

    $vicparent.removeClass('vic-in')

    function removeElement() {
      $vicparent.trigger('closed.bs.vic-alert').remove()
    }

    $vic.support.transition && $vicparent.hasClass('vic-fade') ?
      $vicparent
        .one($vic.support.transition.end, removeElement)
        .vicemulateTransitionEnd(150) :
      removeElement()
  }


  // ALERT PLUGIN DEFINITION
  // =======================

  var old = $vic.fn.vicalert

  $vic.fn.vicalert = function (option) {
    return this.each(function () {
      var $victhis = $vic(this)
      var data  = $victhis.data('bs.vic-alert')

      if (!data) $victhis.data('bs.vic-alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($victhis)
    })
  }

  $vic.fn.vicalert.Constructor = Alert


  // ALERT NO CONFLICT
  // =================

  $vic.fn.vicalert.noConflict = function () {
    $vic.fn.vicalert = old
    return this
  }


  // ALERT DATA-API
  // ==============

  $vic(document).on('click.bs.alert.data-api', dismiss, Alert.prototype.close)

}($vic);

/* ========================================================================
 * Bootstrap: button.js v3.0.3
 * http://getbootstrap.com/javascript/#buttons
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // BUTTON PUBLIC CLASS DEFINITION
  // ==============================

  var Button = function (element, options) {
    this.$vicelement = $vic(element)
    this.options  = $vic.extend({}, Button.DEFAULTS, options)
  }

  Button.DEFAULTS = {
    loadingText: 'loading...'
  }

  Button.prototype.setState = function (state) {
    var d    = 'disabled'
    var $vicel  = this.$vicelement
    var val  = $vicel.is('input') ? 'val' : 'html'
    var data = $vicel.data()

    state = state + 'Text'

    if (!data.resetText) $vicel.data('resetText', $vicel[val]())

    $vicel[val](data[state] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $vicel.addClass('vic-'+d).attr(d, d) :
        $vicel.removeClass('vic-'+d).removeAttr(d);
    }, 0)
  }

  Button.prototype.toggle = function () {
    var $vicparent = this.$vicelement.closest('[data-toggle="vic-buttons"]')
    var changed = true

    if ($vicparent.length) {
      var $vicinput = this.$vicelement.find('input')
      if ($vicinput.prop('type') === 'radio') {
        // see if clicking on current one
        if ($vicinput.prop('checked') && this.$vicelement.hasClass('vic-active'))
          changed = false
        else
          $vicparent.find('.vic-active').removeClass('vic-active')
      }
      if (changed) $vicinput.prop('checked', !this.$vicelement.hasClass('vic-active')).trigger('change')
    }

    if (changed) this.$vicelement.toggleClass('vic-active')
  }


  // BUTTON PLUGIN DEFINITION
  // ========================

  var old = $vic.fn.vicbutton

  $vic.fn.vicbutton = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-button')
      var options = typeof option == 'object' && option

      if (!data) $victhis.data('bs.vic-button', (data = new Button(this, options)))

      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }

  $vic.fn.vicbutton.Constructor = Button


  // BUTTON NO CONFLICT
  // ==================

  $vic.fn.vicbutton.noConflict = function () {
    $vic.fn.vicbutton = old
    return this
  }


  // BUTTON DATA-API
  // ===============

  $vic(document).on('click.bs.button.data-api', '[data-toggle^=vic-button]', function (e) {
    var $vicbtn = $vic(e.target)
    if (!$vicbtn.hasClass('vic-btn')) $vicbtn = $vicbtn.closest('.vic-btn')
    $vicbtn.button('toggle')
    e.preventDefault()
  })

}($vic);

/* ========================================================================
 * Bootstrap: carousel.js v3.0.3
 * http://getbootstrap.com/javascript/#carousel
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // CAROUSEL CLASS DEFINITION
  // =========================

  var Carousel = function (element, options) {
    this.$vicelement    = $vic(element)
    this.$vicindicators = this.$vicelement.find('.vic-carousel-indicators')
    this.options     = options
    this.paused      =
    this.sliding     =
    this.interval    =
    this.$vicactive     =
    this.$vicitems      = null

    this.options.pause == 'hover' && this.$vicelement
      .on('mouseenter', $vic.proxy(this.pause, this))
      .on('mouseleave', $vic.proxy(this.cycle, this))
  }

  Carousel.DEFAULTS = {
    interval: 5000
  , pause: 'hover'
  , wrap: true
  }

  Carousel.prototype.cycle =  function (e) {
    e || (this.paused = false)

    this.interval && clearInterval(this.interval)

    this.options.interval
      && !this.paused
      && (this.interval = setInterval($vic.proxy(this.next, this), this.options.interval))

    return this
  }

  Carousel.prototype.getActiveIndex = function () {
    this.$vicactive = this.$vicelement.find('.vic-item.vic-active')
    this.$vicitems  = this.$vicactive.parent().children()

    return this.$vicitems.index(this.$vicactive)
  }

  Carousel.prototype.to = function (pos) {
    var that        = this
    var activeIndex = this.getActiveIndex()

    if (pos > (this.$vicitems.length - 1) || pos < 0) return

    if (this.sliding)       return this.$vicelement.one('slid.bs.vic-carousel', function () { that.to(pos) })
    if (activeIndex == pos) return this.pause().cycle()

    return this.slide(pos > activeIndex ? 'next' : 'prev', $vic(this.$vicitems[pos]))
  }

  Carousel.prototype.pause = function (e) {
    e || (this.paused = true)

    if (this.$vicelement.find('.vic-next, .vic-prev').length && $vic.support.transition.end) {
      this.$vicelement.trigger($vic.support.transition.end)
      this.cycle(true)
    }

    this.interval = clearInterval(this.interval)

    return this
  }

  Carousel.prototype.next = function () {
    if (this.sliding) return
    return this.slide('next')
  }

  Carousel.prototype.prev = function () {
    if (this.sliding) return
    return this.slide('prev')
  }

  Carousel.prototype.slide = function (type, next) {
    var $vicactive   = this.$vicelement.find('.vic-item.vic-active')
    var $vicnext     = next || $vicactive[type]()
    var isCycling = this.interval
    var direction = type == 'next' ? 'left' : 'right'
    var fallback  = type == 'next' ? 'first' : 'last'
    var that      = this

    if (!$vicnext.length) {
      if (!this.options.wrap) return
      $vicnext = this.$vicelement.find('.vic-item')[fallback]()
    }

    this.sliding = true

    isCycling && this.pause()

    var e = $vic.Event('slide.bs.vic-carousel', { relatedTarget: $vicnext[0], direction: direction })

    if ($vicnext.hasClass('vic-active')) return

    if (this.$vicindicators.length) {
      this.$vicindicators.find('.vic-active').removeClass('vic-active')
      this.$vicelement.one('slid.bs.vic-carousel', function () {
        var $vicnextIndicator = $vic(that.$vicindicators.children()[that.getActiveIndex()])
        $vicnextIndicator && $vicnextIndicator.addClass('vic-active')
      })
    }

    if ($vic.support.transition && this.$vicelement.hasClass('slide')) {
      this.$vicelement.trigger(e)
      if (e.isDefaultPrevented()) return
      $vicnext.addClass('vic-'+type)
      $vicnext[0].offsetWidth // force reflow
      $vicactive.addClass('vic-'+direction)
      $vicnext.addClass('vic-'+direction)
      $vicactive
        .one($vic.support.transition.end, function () {
          $vicnext.removeClass(['vic-'+type,'vic-'+direction].join(' ')).addClass('vic-active')
          $vicactive.removeClass(['vic-active','vic-'+direction].join(' '))
          that.sliding = false
          setTimeout(function () { that.$vicelement.trigger('slid.bs.vic-carousel') }, 0)
        })
        .vicemulateTransitionEnd(600)
    } else {
      this.$vicelement.trigger(e)
      if (e.isDefaultPrevented()) return
      $vicactive.removeClass('vic-active')
      $vicnext.addClass('vic-active')
      this.sliding = false
      this.$vicelement.trigger('slid.bs.vic-carousel')
    }

    isCycling && this.cycle()

    return this
  }


  // CAROUSEL PLUGIN DEFINITION
  // ==========================

  var old = $vic.fn.viccarousel

  $vic.fn.viccarousel = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-carousel')
      var options = $vic.extend({}, Carousel.DEFAULTS, $victhis.data(), typeof option == 'object' && option)
      var action  = typeof option == 'string' ? option : options.slide

      if (!data) $victhis.data('bs.vic-carousel', (data = new Carousel(this, options)))
      if (typeof option == 'number') data.to(option)
      else if (action) data[action]()
      else if (options.interval) data.pause().cycle()
    })
  }

  $vic.fn.viccarousel.Constructor = Carousel


  // CAROUSEL NO CONFLICT
  // ====================

  $vic.fn.viccarousel.noConflict = function () {
    $vic.fn.viccarousel = old
    return this
  }


  // CAROUSEL DATA-API
  // =================

  $vic(document).on('click.bs.vic-carousel.data-api', '[data-slide], [data-slide-to]', function (e) {
    var $victhis   = $vic(this), href
    var $victarget = $vic($victhis.attr('data-target') || (href = $victhis.attr('href')) && href.replace(/.*(?=#[^\s]+$vic)/, '')) //strip for ie7
    var options = $vic.extend({}, $victarget.data(), $victhis.data())
    var slideIndex = $victhis.attr('data-slide-to')
    if (slideIndex) options.interval = false

    $victarget.carousel(options)

    if (slideIndex = $victhis.attr('data-slide-to')) {
      $victarget.data('bs.vic-carousel').to(slideIndex)
    }

    e.preventDefault()
  })

  $vic(window).on('load', function () {
    $vic('[data-ride="carousel"]').each(function () {
      var $viccarousel = $vic(this);
      $viccarousel.viccarousel($viccarousel.data());
    })
  })

}($vic);

/* ========================================================================
 * Bootstrap: collapse.js v3.0.3
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // COLLAPSE PUBLIC CLASS DEFINITION
  // ================================

  var Collapse = function (element, options) {
    this.$vicelement      = $vic(element)
    this.options       = $vic.extend({}, Collapse.DEFAULTS, options)
    this.transitioning = null

    if (this.options.parent) this.$vicparent = $vic(this.options.parent)
    if (this.options.toggle) this.toggle()
  }

  Collapse.DEFAULTS = {
    toggle: true
  }

  Collapse.prototype.dimension = function () {
    var hasWidth = this.$vicelement.hasClass('width')
    return hasWidth ? 'width' : 'height'
  }

  Collapse.prototype.show = function () {
    if (this.transitioning || this.$vicelement.hasClass('vic-in')) return

    var startEvent = $vic.Event('show.bs.vic-collapse')
    this.$vicelement.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    var actives = this.$vicparent && this.$vicparent.find('> .vic-panel > .vic-in')

    if (actives && actives.length) {
      var hasData = actives.data('bs.vic-collapse')
      if (hasData && hasData.transitioning) return
      actives.collapse('hide')
      hasData || actives.data('bs.collapse', null)
    }

    var dimension = this.dimension()

    this.$vicelement
      .removeClass('vic-collapse')
      .addClass('vic-collapsing')
      [dimension](0)

    this.transitioning = 1

    var complete = function () {
      this.$vicelement
        .removeClass('vic-collapsing')
        .addClass('vic-in')
        [dimension]('auto')
      this.transitioning = 0
      this.$vicelement.trigger('shown.bs.vic-collapse')
    }

    if (!$vic.support.transition) return complete.call(this)

    var scrollSize = $vic.camelCase(['scroll', dimension].join('-'))

    this.$vicelement
      .one($vic.support.transition.end, $vic.proxy(complete, this))
      .vicemulateTransitionEnd(350)
      [dimension](this.$vicelement[0][scrollSize])
  }

  Collapse.prototype.hide = function () {
    if (this.transitioning || !this.$vicelement.hasClass('vic-in')) return

    var startEvent = $vic.Event('hide.bs.vic-collapse')
    this.$vicelement.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    var dimension = this.dimension()

    this.$vicelement
      [dimension](this.$vicelement[dimension]())
      [0].offsetHeight

    this.$vicelement
      .addClass('vic-collapsing')
      .removeClass('vic-collapse')
      .removeClass('vic-in')

    this.transitioning = 1

    var complete = function () {
      this.transitioning = 0
      this.$vicelement
        .trigger('hidden.bs.vic-collapse')
        .removeClass('vic-collapsing')
        .addClass('vic-collapse')
    }

    if (!$vic.support.transition) return complete.call(this)

    this.$vicelement
      [dimension](0)
      .one($vic.support.transition.end, $vic.proxy(complete, this))
      .vicemulateTransitionEnd(350)
  }

  Collapse.prototype.toggle = function () {
    this[this.$vicelement.hasClass('vic-in') ? 'hide' : 'show']()
  }


  // COLLAPSE PLUGIN DEFINITION
  // ==========================

  var old = $vic.fn.viccollapse

  $vic.fn.viccollapse = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-collapse')
      var options = $vic.extend({}, Collapse.DEFAULTS, $victhis.data(), typeof option == 'object' && option)

      if (!data) $victhis.data('bs.vic-collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $vic.fn.viccollapse.Constructor = Collapse


  // COLLAPSE NO CONFLICT
  // ====================

  $vic.fn.viccollapse.noConflict = function () {
    $vic.fn.viccollapse = old
    return this
  }


  // COLLAPSE DATA-API
  // =================

  $vic(document).on('click.bs.collapse.data-api', '[data-toggle=vic-collapse]', function (e) {
    var $victhis   = $vic(this), href
    var target  = $victhis.attr('data-target')
        || e.preventDefault()
        || (href = $victhis.attr('href')) && href.replace(/.*(?=#[^\s]+$vic)/, '') //strip for ie7
    var $victarget = $vic(target)
    var data    = $victarget.data('bs.vic-collapse')
    var option  = data ? 'toggle' : $victhis.data()
    var parent  = $victhis.attr('data-parent')
    var $vicparent = parent && $vic(parent)

    if (!data || !data.transitioning) {
      if ($vicparent) $vicparent.find('[data-toggle=vic-collapse][data-parent="' + parent + '"]').not($victhis).addClass('vic-collapsed')
      $victhis[$victarget.hasClass('vic-in') ? 'addClass' : 'removeClass']('vic-collapsed')
    }

    $victarget.collapse(option)
  })

}($vic);

/* ========================================================================
 * Bootstrap: dropdown.js v3.0.3
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // DROPDOWN CLASS DEFINITION
  // =========================

  var backdrop = '.vic-dropdown-backdrop'
  var toggle   = '[data-toggle=vic-dropdown]'
  var Dropdown = function (element) {
    $vic(element).on('click.bs.vic-dropdown', this.toggle)
  }

  Dropdown.prototype.toggle = function (e) {
    var $victhis = $vic(this)

    if ($victhis.is('.vic-disabled, :disabled')) return

    var $vicparent  = getParent($victhis)
    var isActive = $vicparent.hasClass('vic-open')

    clearMenus()

    if (!isActive) {
      if ('ontouchstart' in document.documentElement && !$vicparent.closest('.vic-navbar-nav').length) {
        // if mobile we use a backdrop because click events don't delegate
        $vic('<div class="vic-dropdown-backdrop"/>').insertAfter($vic(this)).on('click', clearMenus)
      }

      $vicparent.trigger(e = $vic.Event('show.bs.vic-dropdown'))

      if (e.isDefaultPrevented()) return

      $vicparent
        .toggleClass('vic-open')
        .trigger('shown.bs.vic-dropdown')

      $victhis.focus()
    }

    return false
  }

  Dropdown.prototype.keydown = function (e) {
    if (!/(38|40|27)/.test(e.keyCode)) return

    var $victhis = $vic(this)

    e.preventDefault()
    e.stopPropagation()

    if ($victhis.is('.vic-disabled, :disabled')) return

    var $vicparent  = getParent($victhis)
    var isActive = $vicparent.hasClass('vic-open')

    if (!isActive || (isActive && e.keyCode == 27)) {
      if (e.which == 27) $vicparent.find(toggle).focus()
      return $victhis.click()
    }

    var $vicitems = $vic('[role=menu] li:not(.vic-divider):visible a', $vicparent)

    if (!$vicitems.length) return

    var index = $vicitems.index($vicitems.filter(':focus'))

    if (e.keyCode == 38 && index > 0)                 index--                        // up
    if (e.keyCode == 40 && index < $vicitems.length - 1) index++                        // down
    if (!~index)                                      index=0

    $vicitems.eq(index).focus()
  }

  function clearMenus() {
    $vic(backdrop).remove()
    $vic(toggle).each(function (e) {
      var $vicparent = getParent($vic(this))
      if (!$vicparent.hasClass('vic-open')) return
      $vicparent.trigger(e = $vic.Event('hide.bs.vic-dropdown'))
      if (e.isDefaultPrevented()) return
      $vicparent.removeClass('vic-open').trigger('hidden.bs.vic-dropdown')
    })
  }

  function getParent($victhis) {
    var selector = $victhis.attr('data-target')

    if (!selector) {
      selector = $victhis.attr('href')
      selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$vic)/, '') //strip for ie7
    }

    var $vicparent = selector && $vic(selector)

    return $vicparent && $vicparent.length ? $vicparent : $victhis.parent()
  }


  // DROPDOWN PLUGIN DEFINITION
  // ==========================

  var old = $vic.fn.vicdropdown

  $vic.fn.vicdropdown = function (option) {
    return this.each(function () {
      var $victhis = $vic(this)
      var data  = $victhis.data('bs.vic-dropdown')

      if (!data) $victhis.data('bs.vic-dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($victhis)
    })
  }

  $vic.fn.vicdropdown.Constructor = Dropdown


  // DROPDOWN NO CONFLICT
  // ====================

  $vic.fn.vicdropdown.noConflict = function () {
    $vic.fn.vicdropdown = old
    return this
  }


  // APPLY TO STANDARD DROPDOWN ELEMENTS
  // ===================================

  $vic(document)
    .on('click.bs.vic-dropdown.data-api', clearMenus)
    .on('click.bs.vic-dropdown.data-api', '.vic-dropdown form', function (e) { e.stopPropagation() })
    .on('click.bs.vic-dropdown.data-api'  , toggle, Dropdown.prototype.toggle)
    .on('keydown.bs.vic-dropdown.data-api', toggle + ', [role=menu]' , Dropdown.prototype.keydown)

}($vic);

/* ========================================================================
 * Bootstrap: modal.js v3.0.3
 * http://getbootstrap.com/javascript/#modals
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // MODAL CLASS DEFINITION
  // ======================

  var VicModal = function (element, options) {
    this.options   = options
    this.$vicelement  = $vic(element)
    this.$vicbackdrop =
    this.isShown   = null

    if (this.options.remote) this.$vicelement.load(this.options.remote)
  }

  VicModal.DEFAULTS = {
      backdrop: true
    , keyboard: true
    , show: true
  }

  VicModal.prototype.toggle = function (_relatedTarget) {
    return this[!this.isShown ? 'show' : 'hide'](_relatedTarget)
  }

  VicModal.prototype.show = function (_relatedTarget) {
    var that = this
    var e    = $vic.Event('show.bs.vic-modal', { relatedTarget: _relatedTarget })

    this.$vicelement.trigger(e)

    if (this.isShown || e.isDefaultPrevented()) return

    this.isShown = true

    this.escape()

    this.$vicelement.on('click.dismiss.modal', '[data-dismiss="vic-modal"]', $vic.proxy(this.hide, this))

    this.backdrop(function () {
      var transition = $vic.support.transition && that.$vicelement.hasClass('vic-fade')

      if (!that.$vicelement.parent().length) {
        that.$vicelement.appendTo(document.body) // don't move modals dom position
      }

      that.$vicelement.show()

      if (transition) {
        that.$vicelement[0].offsetWidth // force reflow
      }

      that.$vicelement
        .addClass('vic-in')
        .attr('aria-hidden', false)

      that.enforceFocus()

      var e = $vic.Event('shown.bs.vic-modal', { relatedTarget: _relatedTarget })

      transition ?
        that.$vicelement.find('.vic-modal-dialog') // wait for modal to slide in
          .one($vic.support.transition.end, function () {
            that.$vicelement.focus().trigger(e)
          })
           :
        that.$vicelement.focus().trigger(e)
    })
  }

  VicModal.prototype.hide = function (e) {
    if (e) e.preventDefault()

    e = $vic.Event('hide.bs.vic-modal')

    this.$vicelement.trigger(e)

    if (!this.isShown || e.isDefaultPrevented()) return

    this.isShown = false

    this.escape()

    $vic(document).off('focusin.bs.vic-modal')

    this.$vicelement
      .removeClass('vic-in')
      .attr('aria-hidden', true)
      .off('click.dismiss.modal')

    $vic.support.transition && this.$vicelement.hasClass('vic-fade') ?
      this.$vicelement
        .one($vic.support.transition.end, $vic.proxy(this.hideVicModal, this))
         :
      this.hideVicModal()
  }

  VicModal.prototype.enforceFocus = function () {
    $vic(document)
      .off('focusin.bs.vic-modal') // guard against infinite focus loop
      .on('focusin.bs.vic-modal', $vic.proxy(function (e) {
        if (this.$vicelement[0] !== e.target && !this.$vicelement.has(e.target).length) {
          this.$vicelement.focus()
        }
      }, this))
  }

  VicModal.prototype.escape = function () {
    if (this.isShown && this.options.keyboard) {
      this.$vicelement.on('keyup.dismiss.bs.vic-modal', $vic.proxy(function (e) {
        e.which == 27 && this.hide()
      }, this))
    } else if (!this.isShown) {
      this.$vicelement.off('keyup.dismiss.bs.vic-modal')
    }
  }

  VicModal.prototype.hideVicModal = function () {
    var that = this
    this.$vicelement.hide()
    this.backdrop(function () {
      that.removeBackdrop()
      that.$vicelement.trigger('hidden.bs.vic-modal')
    })
  }

  VicModal.prototype.removeBackdrop = function () {
    this.$vicbackdrop && this.$vicbackdrop.remove()
    this.$vicbackdrop = null
  }

  VicModal.prototype.backdrop = function (callback) {
    var that    = this
    var animate = this.$vicelement.hasClass('vic-fade') ? 'vic-fade' : ''

    if (this.isShown && this.options.backdrop) {
      var doAnimate = $vic.support.transition && animate

      this.$vicbackdrop = $vic('<div class="vic-modal-backdrop ' + animate + '" />')
        .appendTo(document.body)

      this.$vicelement.on('click.dismiss.modal', $vic.proxy(function (e) {
        if (e.target !== e.currentTarget) return
        this.options.backdrop == 'static'
          ? this.$vicelement[0].focus.call(this.$vicelement[0])
          : this.hide.call(this)
      }, this))

      if (doAnimate) this.$vicbackdrop[0].offsetWidth // force reflow

      this.$vicbackdrop.addClass('vic-in')

      if (!callback) return

      doAnimate ?
        this.$vicbackdrop
          .one($vic.support.transition.end, callback)
          .vicemulateTransitionEnd(150) :
        callback()

    } else if (!this.isShown && this.$vicbackdrop) {
      this.$vicbackdrop.removeClass('vic-in')

      $vic.support.transition && this.$vicelement.hasClass('vic-fade')?
        this.$vicbackdrop
          .one($vic.support.transition.end, callback)
          .vicemulateTransitionEnd(150) :
        callback()

    } else if (callback) {
      callback()
    }
  }


  // MODAL PLUGIN DEFINITION
  // =======================

  var old = $vic.fn.vicmodal

  $vic.fn.vicmodal = function (option, _relatedTarget) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-modal')
      var options = $vic.extend({}, VicModal.DEFAULTS, $victhis.data(), typeof option == 'object' && option)

      if (!data) $victhis.data('bs.vic-modal', (data = new VicModal(this, options)))
      if (typeof option == 'string') data[option](_relatedTarget)
      else if (options.show) data.show(_relatedTarget)
    })
  }

  $vic.fn.vicmodal.Constructor = VicModal


  // MODAL NO CONFLICT
  // =================

  $vic.fn.vicmodal.noConflict = function () {
    $vic.fn.vicmodal = old
    return this
  }


  // MODAL DATA-API
  // ==============

  $vic(document).on('click.bs.vic-modal.data-api', '[data-toggle="modal"]', function (e) {
    var $victhis   = $vic(this)
    var href    = $victhis.attr('href')
    var $victarget = $vic($victhis.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$vic)/, ''))) //strip for ie7
    var option  = $victarget.data('modal') ? 'toggle' : $vic.extend({ remote: !/#/.test(href) && href }, $victarget.data(), $victhis.data())

    e.preventDefault()

    $victarget
      .modal(option, this)
      .one('hide', function () {
        $victhis.is(':visible') && $victhis.focus()
      })
  })

  $vic(document)
    .on('show.bs.vic-modal',  '.modal', function () { $vic(document.body).addClass('vic-modal-open') })
    .on('hidden.bs.vic-modal', '.modal', function () { $vic(document.body).removeClass('vic-modal-open') })

}($vic);

/* ========================================================================
 * Bootstrap: tooltip.js v3.0.3
 * http://getbootstrap.com/javascript/#tooltip
 * Inspired by the original $vic.tipsy by Jason Frame
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // TOOLTIP PUBLIC CLASS DEFINITION
  // ===============================

  var Tooltip = function (element, options) {
    this.type       = null
    this.options    = null
    this.enabled    = null
    this.timeout    = null
    this.hoverState = null
    this.$vicelement   = null

    this.init('tooltip', element, options)
  }

  Tooltip.DEFAULTS = {
    animation: true
  , placement: 'top'
  , selector: false
  , template: '<div class="vic-tooltip"><div class="vic-tooltip-arrow"></div><div class="vic-tooltip-inner"></div></div>'
  , trigger: 'hover focus'
  , title: ''
  , delay: 0
  , html: false
  , container: false
  }

  Tooltip.prototype.init = function (type, element, options) {
    this.enabled  = true
    this.type     = type
    this.$vicelement = $vic(element)
    this.options  = this.getOptions(options)

    var triggers = this.options.trigger.split(' ')

    for (var i = triggers.length; i--;) {
      var trigger = triggers[i]

      if (trigger == 'click') {
        this.$vicelement.on('click.' + this.type, this.options.selector, $vic.proxy(this.toggle, this))
      } else if (trigger != 'manual') {
        var eventIn  = trigger == 'hover' ? 'mouseenter' : 'focus'
        var eventOut = trigger == 'hover' ? 'mouseleave' : 'blur'

        this.$vicelement.on(eventIn  + '.' + this.type, this.options.selector, $vic.proxy(this.enter, this))
        this.$vicelement.on(eventOut + '.' + this.type, this.options.selector, $vic.proxy(this.leave, this))
      }
    }

    this.options.selector ?
      (this._options = $vic.extend({}, this.options, { trigger: 'manual', selector: '' })) :
      this.fixTitle()
  }

  Tooltip.prototype.getDefaults = function () {
    return Tooltip.DEFAULTS
  }

  Tooltip.prototype.getOptions = function (options) {
    options = $vic.extend({}, this.getDefaults(), this.$vicelement.data(), options)

    if (options.delay && typeof options.delay == 'number') {
      options.delay = {
        show: options.delay
      , hide: options.delay
      }
    }

    return options
  }

  Tooltip.prototype.getDelegateOptions = function () {
    var options  = {}
    var defaults = this.getDefaults()

    this._options && $vic.each(this._options, function (key, value) {
      if (defaults[key] != value) options[key] = value
    })

    return options
  }

  Tooltip.prototype.enter = function (obj) {
    var self = obj instanceof this.constructor ?
      obj : $vic(obj.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type)

    clearTimeout(self.timeout)

    self.hoverState = 'vic-in'

    if (!self.options.delay || !self.options.delay.show) return self.show()

    self.timeout = setTimeout(function () {
      if (self.hoverState == 'vic-in') self.show()
    }, self.options.delay.show)
  }

  Tooltip.prototype.leave = function (obj) {
    var self = obj instanceof this.constructor ?
      obj : $vic(obj.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type)

    clearTimeout(self.timeout)

    self.hoverState = 'out'

    if (!self.options.delay || !self.options.delay.hide) return self.hide()

    self.timeout = setTimeout(function () {
      if (self.hoverState == 'out') self.hide()
    }, self.options.delay.hide)
  }

  Tooltip.prototype.show = function () {
    var e = $vic.Event('show.bs.'+ this.type)

    if (this.hasContent() && this.enabled) {
      this.$vicelement.trigger(e)

      if (e.isDefaultPrevented()) return

      var $victip = this.tip()

      this.setContent()

      if (this.options.animation) $victip.addClass('vic-fade')

      var placement = typeof this.options.placement == 'function' ?
        this.options.placement.call(this, $victip[0], this.$vicelement[0]) :
        this.options.placement

      var autoToken = /\s?auto?\s?/i
      var autoPlace = autoToken.test(placement)
      if (autoPlace) placement = placement.replace(autoToken, '') || 'top'

      $victip
        .detach()
        .css({ top: 0, left: 0, display: 'block' })
        .addClass('vic-'+placement)

      this.options.container ? $victip.appendTo(this.options.container) : $victip.insertAfter(this.$vicelement)

      var pos          = this.getPosition()
      var actualWidth  = $victip[0].offsetWidth
      var actualHeight = $victip[0].offsetHeight

      if (autoPlace) {
        var $vicparent = this.$vicelement.parent()

        var orgPlacement = placement
        var docScroll    = document.documentElement.scrollTop || document.body.scrollTop
        var parentWidth  = this.options.container == 'body' ? window.innerWidth  : $vicparent.outerWidth()
        var parentHeight = this.options.container == 'body' ? window.innerHeight : $vicparent.outerHeight()
        var parentLeft   = this.options.container == 'body' ? 0 : $vicparent.offset().left

        placement = placement == 'bottom' && pos.top   + pos.height  + actualHeight - docScroll > parentHeight  ? 'top'    :
                    placement == 'top'    && pos.top   - docScroll   - actualHeight < 0                         ? 'bottom' :
                    placement == 'right'  && pos.right + actualWidth > parentWidth                              ? 'left'   :
                    placement == 'left'   && pos.left  - actualWidth < parentLeft                               ? 'right'  :
                    placement

        $victip
          .removeClass('vic-'+orgPlacement)
          .addClass('vic-'+placement)
      }

      var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

      this.applyPlacement(calculatedOffset, placement)
      this.$vicelement.trigger('shown.bs.' + this.type)
    }
  }

  Tooltip.prototype.applyPlacement = function(offset, placement) {
    var replace
    var $victip   = this.tip()
    var width  = $victip[0].offsetWidth
    var height = $victip[0].offsetHeight

    // manually read margins because getBoundingClientRect includes difference
    var marginTop = parseInt($victip.css('margin-top'), 10)
    var marginLeft = parseInt($victip.css('margin-left'), 10)

    // we must check for NaN for ie 8/9
    if (isNaN(marginTop))  marginTop  = 0
    if (isNaN(marginLeft)) marginLeft = 0

    offset.top  = offset.top  + marginTop
    offset.left = offset.left + marginLeft

    $victip
      .offset(offset)
      .addClass('vic-in')

    // check to see if placing tip in new offset caused the tip to resize itself
    var actualWidth  = $victip[0].offsetWidth
    var actualHeight = $victip[0].offsetHeight

    if (placement == 'top' && actualHeight != height) {
      replace = true
      offset.top = offset.top + height - actualHeight
    }

    if (/bottom|top/.test(placement)) {
      var delta = 0

      if (offset.left < 0) {
        delta       = offset.left * -2
        offset.left = 0

        $victip.offset(offset)

        actualWidth  = $victip[0].offsetWidth
        actualHeight = $victip[0].offsetHeight
      }

      this.replaceArrow(delta - width + actualWidth, actualWidth, 'left')
    } else {
      this.replaceArrow(actualHeight - height, actualHeight, 'top')
    }

    if (replace) $victip.offset(offset)
  }

  Tooltip.prototype.replaceArrow = function(delta, dimension, position) {
    this.arrow().css(position, delta ? (50 * (1 - delta / dimension) + "%") : '')
  }

  Tooltip.prototype.setContent = function () {
    var $victip  = this.tip()
    var title = this.getTitle()

    $victip.find('.vic-tooltip-inner')[this.options.html ? 'html' : 'text'](title)
    $victip.removeClass('vic-fade vic-in vic-top vic-bottom vic-left vic-right')
  }

  Tooltip.prototype.hide = function () {
    var that = this
    var $victip = this.tip()
    var e    = $vic.Event('hide.bs.' + this.type)

    function complete() {
      if (that.hoverState != 'vic-in') $victip.detach()
    }

    this.$vicelement.trigger(e)

    if (e.isDefaultPrevented()) return

    $victip.removeClass('vic-in')

    $vic.support.transition && this.$victip.hasClass('vic-fade') ?
      $victip
        .one($vic.support.transition.end, complete)
        .vicemulateTransitionEnd(150) :
      complete()

    this.$vicelement.trigger('hidden.bs.' + this.type)

    return this
  }

  Tooltip.prototype.fixTitle = function () {
    var $vice = this.$vicelement
    if ($vice.attr('title') || typeof($vice.attr('data-original-title')) != 'string') {
      $vice.attr('data-original-title', $vice.attr('title') || '').attr('title', '')
    }
  }

  Tooltip.prototype.hasContent = function () {
    return this.getTitle()
  }

  Tooltip.prototype.getPosition = function () {
    var el = this.$vicelement[0]
    return $vic.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : {
      width: el.offsetWidth
    , height: el.offsetHeight
    }, this.$vicelement.offset())
  }

  Tooltip.prototype.getCalculatedOffset = function (placement, pos, actualWidth, actualHeight) {
    return placement == 'bottom' ? { top: pos.top + pos.height,   left: pos.left + pos.width / 2 - actualWidth / 2  } :
           placement == 'top'    ? { top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2  } :
           placement == 'left'   ? { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth } :
        /* placement == 'right' */ { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width   }
  }

  Tooltip.prototype.getTitle = function () {
    var title
    var $vice = this.$vicelement
    var o  = this.options

    title = $vice.attr('data-original-title')
      || (typeof o.title == 'function' ? o.title.call($vice[0]) :  o.title)

    return title
  }

  Tooltip.prototype.tip = function () {
    return this.$victip = this.$victip || $vic(this.options.template)
  }

  Tooltip.prototype.arrow = function () {
    return this.$vicarrow = this.$vicarrow || this.tip().find('.vic-tooltip-arrow')
  }

  Tooltip.prototype.validate = function () {
    if (!this.$vicelement[0].parentNode) {
      this.hide()
      this.$vicelement = null
      this.options  = null
    }
  }

  Tooltip.prototype.enable = function () {
    this.enabled = true
  }

  Tooltip.prototype.disable = function () {
    this.enabled = false
  }

  Tooltip.prototype.toggleEnabled = function () {
    this.enabled = !this.enabled
  }

  Tooltip.prototype.toggle = function (e) {
    var self = this
    if (e) {
      self = $vic(e.currentTarget).data('bs.' + this.type)
      if (!self) {
        self = new this.constructor(e.currentTarget, this.getDelegateOptions())
        $vic(e.currentTarget).data('bs.' + this.type, self)
      }
    }

    self.tip().hasClass('vic-in') ? self.leave(self) : self.enter(self)
  }

  Tooltip.prototype.destroy = function () {
    this.hide().$vicelement.off('.' + this.type).removeData('bs.' + this.type)
  }


  // TOOLTIP PLUGIN DEFINITION
  // =========================

  var old = $vic.fn.victooltip

  $vic.fn.victooltip = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-tooltip')
      var options = typeof option == 'object' && option

      if (!data) $victhis.data('bs.vic-tooltip', (data = new Tooltip(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $vic.fn.victooltip.Constructor = Tooltip


  // TOOLTIP NO CONFLICT
  // ===================

  $vic.fn.victooltip.noConflict = function () {
    $vic.fn.victooltip = old
    return this
  }

}($vic);

/* ========================================================================
 * Bootstrap: popover.js v3.0.3
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // POPOVER PUBLIC CLASS DEFINITION
  // ===============================

  var Popover = function (element, options) {
    this.init('popover', element, options)
  }

  if (!$vic.fn.victooltip) throw new Error('Popover requires tooltip.js')

  Popover.DEFAULTS = $vic.extend({} , $vic.fn.victooltip.Constructor.DEFAULTS, {
    placement: 'right'
  , trigger: 'click'
  , content: ''
  , template: '<div class="vic-popover"><div class="vic-arrow"></div><h3 class="vic-popover-title"></h3><div class="vic-popover-content"></div></div>'
  })


  // NOTE: POPOVER EXTENDS tooltip.js
  // ================================

  Popover.prototype = $vic.extend({}, $vic.fn.victooltip.Constructor.prototype)

  Popover.prototype.constructor = Popover

  Popover.prototype.getDefaults = function () {
    return Popover.DEFAULTS
  }

  Popover.prototype.setContent = function () {
    var $victip    = this.tip()
    var title   = this.getTitle()
    var content = this.getContent()

    $victip.find('.vic-popover-title')[this.options.html ? 'html' : 'text'](title)
    $victip.find('.vic-popover-content')[this.options.html ? 'html' : 'text'](content)

    $victip.removeClass('vic-fade vic-top vic-bottom vic-left vic-right vic-in')

    // IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
    // this manually by checking the contents.
    if (!$victip.find('.vic-popover-title').html()) $victip.find('.vic-popover-title').hide()
  }

  Popover.prototype.hasContent = function () {
    return this.getTitle() || this.getContent()
  }

  Popover.prototype.getContent = function () {
    var $vice = this.$vicelement
    var o  = this.options

    return $vice.attr('data-content')
      || (typeof o.content == 'function' ?
            o.content.call($vice[0]) :
            o.content)
  }

  Popover.prototype.arrow = function () {
    return this.$vicarrow = this.$vicarrow || this.tip().find('.vic-arrow')
  }

  Popover.prototype.tip = function () {
    if (!this.$victip) this.$victip = $vic(this.options.template)
    return this.$victip
  }


  // POPOVER PLUGIN DEFINITION
  // =========================

  var old = $vic.fn.vicpopover

  $vic.fn.vicpopover = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-popover')
      var options = typeof option == 'object' && option

      if (!data) $victhis.data('bs.vic-popover', (data = new Popover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $vic.fn.vicpopover.Constructor = Popover


  // POPOVER NO CONFLICT
  // ===================

  $vic.fn.vicpopover.noConflict = function () {
    $vic.fn.vicpopover = old
    return this
  }

}($vic);

/* ========================================================================
 * Bootstrap: scrollspy.js v3.0.3
 * http://getbootstrap.com/javascript/#scrollspy
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // SCROLLSPY CLASS DEFINITION
  // ==========================

  function ScrollSpy(element, options) {
    var href
    var process  = $vic.proxy(this.process, this)

    this.$vicelement       = $vic(element).is('body') ? $vic(window) : $vic(element)
    this.$vicbody          = $vic('body')
    this.$vicscrollElement = this.$vicelement.on('scroll.bs.scroll-spy.data-api', process)
    this.options        = $vic.extend({}, ScrollSpy.DEFAULTS, options)
    this.selector       = (this.options.target
      || ((href = $vic(element).attr('href')) && href.replace(/.*(?=#[^\s]+$vic)/, '')) //strip for ie7
      || '') + ' .nav li > a'
    this.offsets        = $vic([])
    this.targets        = $vic([])
    this.activeTarget   = null

    this.refresh()
    this.process()
  }

  ScrollSpy.DEFAULTS = {
    offset: 10
  }

  ScrollSpy.prototype.refresh = function () {
    var offsetMethod = this.$vicelement[0] == window ? 'offset' : 'position'

    this.offsets = $vic([])
    this.targets = $vic([])

    var self     = this
    var $victargets = this.$vicbody
      .find(this.selector)
      .map(function () {
        var $vicel   = $vic(this)
        var href  = $vicel.data('target') || $vicel.attr('href')
        var $vichref = /^#\w/.test(href) && $vic(href)

        return ($vichref
          && $vichref.length
          && [[ $vichref[offsetMethod]().top + (!$vic.isWindow(self.$vicscrollElement.get(0)) && self.$vicscrollElement.scrollTop()), href ]]) || null
      })
      .sort(function (a, b) { return a[0] - b[0] })
      .each(function () {
        self.offsets.push(this[0])
        self.targets.push(this[1])
      })
  }

  ScrollSpy.prototype.process = function () {
    var scrollTop    = this.$vicscrollElement.scrollTop() + this.options.offset
    var scrollHeight = this.$vicscrollElement[0].scrollHeight || this.$vicbody[0].scrollHeight
    var maxScroll    = scrollHeight - this.$vicscrollElement.height()
    var offsets      = this.offsets
    var targets      = this.targets
    var activeTarget = this.activeTarget
    var i

    if (scrollTop >= maxScroll) {
      return activeTarget != (i = targets.last()[0]) && this.activate(i)
    }

    for (i = offsets.length; i--;) {
      activeTarget != targets[i]
        && scrollTop >= offsets[i]
        && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
        && this.activate( targets[i] )
    }
  }

  ScrollSpy.prototype.activate = function (target) {
    this.activeTarget = target

    $vic(this.selector)
      .parents('.vic-active')
      .removeClass('vic-active')

    var selector = this.selector
      + '[data-target="' + target + '"],'
      + this.selector + '[href="' + target + '"]'

    var active = $vic(selector)
      .parents('li')
      .addClass('vic-active')

    if (active.parent('.vic-dropdown-menu').length)  {
      active = active
        .closest('li.vic-dropdown')
        .addClass('vic-active')
    }

    active.trigger('activate.bs.scrollspy')
  }


  // SCROLLSPY PLUGIN DEFINITION
  // ===========================

  var old = $vic.fn.vicscrollspy

  $vic.fn.vicscrollspy = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-scrollspy')
      var options = typeof option == 'object' && option

      if (!data) $victhis.data('bs.vic-scrollspy', (data = new ScrollSpy(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $vic.fn.vicscrollspy.Constructor = ScrollSpy


  // SCROLLSPY NO CONFLICT
  // =====================

  $vic.fn.vicscrollspy.noConflict = function () {
    $vic.fn.vicscrollspy = old
    return this
  }


  // SCROLLSPY DATA-API
  // ==================

  $vic(window).on('load', function () {
    $vic('[data-spy="scroll"]').each(function () {
      var $vicspy = $vic(this)
      $vicspy.scrollspy($vicspy.data())
    })
  })

}($vic);

/* ========================================================================
 * Bootstrap: tab.js v3.0.3
 * http://getbootstrap.com/javascript/#tabs
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // TAB CLASS DEFINITION
  // ====================

  var Tab = function (element) {
    this.element = $vic(element)
  }

  Tab.prototype.show = function () {
    var $victhis    = this.element
    var $vicul      = $victhis.closest('ul:not(.vic-dropdown-menu)')
    var selector = $victhis.data('target')

    if (!selector) {
      selector = $victhis.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$vic)/, '') //strip for ie7
    }

    if ($victhis.parent('li').hasClass('vic-active')) return

    var previous = $vicul.find('.vic-active:last a')[0]
    var e        = $vic.Event('show.bs.tab', {
      relatedTarget: previous
    })

    $victhis.trigger(e)

    if (e.isDefaultPrevented()) return

    var $victarget = $vic(selector)

    this.activate($victhis.parent('li'), $vicul)
    this.activate($victarget, $victarget.parent(), function () {
      $victhis.trigger({
        type: 'shown.bs.tab'
      , relatedTarget: previous
      })
    })
  }

  Tab.prototype.activate = function (element, container, callback) {
    var $vicactive    = container.find('> .vic-active')
    var transition = callback
      && $vic.support.transition
      && $vicactive.hasClass('vic-fade')

    function next() {
      $vicactive
        .removeClass('vic-active')
        .find('> .vic-dropdown-menu > .vic-active')
        .removeClass('vic-active')

      element.addClass('vic-active')

      if (transition) {
        element[0].offsetWidth // reflow for transition
        element.addClass('vic-in')
      } else {
        element.removeClass('vic-fade')
      }

      if (element.parent('.vic-dropdown-menu')) {
        element.closest('li.vic-dropdown').addClass('vic-active')
      }

      callback && callback()
    }

    transition ?
      $vicactive
        .one($vic.support.transition.end, next)
        .vicemulateTransitionEnd(150) :
      next()

    $vicactive.removeClass('vic-in')
  }


  // TAB PLUGIN DEFINITION
  // =====================

  var old = $vic.fn.victab

  $vic.fn.victab = function ( option ) {
    return this.each(function () {
      var $victhis = $vic(this)
      var data  = $victhis.data('bs.vic-tab')

      if (!data) $victhis.data('bs.vic-tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  $vic.fn.victab.Constructor = Tab


  // TAB NO CONFLICT
  // ===============

  $vic.fn.victab.noConflict = function () {
    $vic.fn.victab = old
    return this
  }


  // TAB DATA-API
  // ============

  $vic(document).on('click.bs.tab.data-api', '[data-toggle="vic-tab"], [data-toggle="vic-pill"]', function (e) {
    e.preventDefault()
    $vic(this).victab('show')
  })

}($vic);

/* ========================================================================
 * Bootstrap: affix.js v3.0.3
 * http://getbootstrap.com/javascript/#affix
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($vic) { "use strict";

  // AFFIX CLASS DEFINITION
  // ======================

  var Affix = function (element, options) {
    this.options = $vic.extend({}, Affix.DEFAULTS, options)
    this.$vicwindow = $vic(window)
      .on('scroll.bs.affix.data-api', $vic.proxy(this.checkPosition, this))
      .on('click.bs.affix.data-api',  $vic.proxy(this.checkPositionWithEventLoop, this))

    this.$vicelement = $vic(element)
    this.affixed  =
    this.unpin    = null

    this.checkPosition()
  }

  Affix.RESET = 'affix affix-top affix-bottom'

  Affix.DEFAULTS = {
    offset: 0
  }

  Affix.prototype.checkPositionWithEventLoop = function () {
    setTimeout($vic.proxy(this.checkPosition, this), 1)
  }

  Affix.prototype.checkPosition = function () {
    if (!this.$vicelement.is(':visible')) return

    var scrollHeight = $vic(document).height()
    var scrollTop    = this.$vicwindow.scrollTop()
    var position     = this.$vicelement.offset()
    var offset       = this.options.offset
    var offsetTop    = offset.top
    var offsetBottom = offset.bottom

    if (typeof offset != 'object')         offsetBottom = offsetTop = offset
    if (typeof offsetTop == 'function')    offsetTop    = offset.top()
    if (typeof offsetBottom == 'function') offsetBottom = offset.bottom()

    var affix = this.unpin   != null && (scrollTop + this.unpin <= position.top) ? false :
                offsetBottom != null && (position.top + this.$vicelement.height() >= scrollHeight - offsetBottom) ? 'bottom' :
                offsetTop    != null && (scrollTop <= offsetTop) ? 'top' : false

    if (this.affixed === affix) return
    if (this.unpin) this.$vicelement.css('top', '')

    this.affixed = affix
    this.unpin   = affix == 'bottom' ? position.top - scrollTop : null

    this.$vicelement.removeClass(Affix.RESET).addClass('vic-affix' + (affix ? '-' + affix : ''))

    if (affix == 'bottom') {
      this.$vicelement.offset({ top: document.body.offsetHeight - offsetBottom - this.$vicelement.height() })
    }
  }


  // AFFIX PLUGIN DEFINITION
  // =======================

  var old = $vic.fn.vicaffix

  $vic.fn.vicaffix = function (option) {
    return this.each(function () {
      var $victhis   = $vic(this)
      var data    = $victhis.data('bs.vic-affix')
      var options = typeof option == 'object' && option

      if (!data) $victhis.data('bs.vic-affix', (data = new Affix(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $vic.fn.vicaffix.Constructor = Affix


  // AFFIX NO CONFLICT
  // =================

  $vic.fn.vicaffix.noConflict = function () {
    $vic.fn.vicaffix = old
    return this
  }


  // AFFIX DATA-API
  // ==============

  $vic(window).on('load', function () {
    $vic('[data-spy="affix"]').each(function () {
      var $vicspy = $vic(this)
      var data = $vicspy.data()

      data.offset = data.offset || {}

      if (data.offsetBottom) data.offset.bottom = data.offsetBottom
      if (data.offsetTop)    data.offset.top    = data.offsetTop

      $vicspy.affix(data)
    })
  })

}($vic);
