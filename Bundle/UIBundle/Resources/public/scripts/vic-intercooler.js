////////////////////////////////////

/**
 * Intercooler.js - there is no need to be upset.
 */
var VicIntercooler = VicIntercooler || (function() {
  'use strict'; // inside function for better merging

  // work around zepto build issue TODO - fix me
  if((typeof Zepto !== "undefined") && ($ == null)) {
    $ = Zepto
  }

  //--------------------------------------------------
  // Vars
  //--------------------------------------------------
  var USE_DATA = false;
  var USE_ACTUAL_HTTP_METHOD = false;

  var _MACROS = $.map(['v-ic-get-from', 'v-ic-post-to', 'v-ic-put-to', 'v-ic-patch-to', 'v-ic-delete-from',
                       'v-ic-style-src', 'v-ic-attr-src', 'v-ic-prepend-from', 'v-ic-append-from', 'v-ic-action'],
                      function(elt){ return fixICAttributeName(elt) });

  var _scrollHandler = null;
  var _UUID = 1;
  var _readyHandlers = [];

  var _isDependentFunction = function(src, dest) {
    if (!src || !dest) {
      return false;
    }

    // For two urls to be considered dependant, either one must contain all
    // of the path arguments the other has, like so:
    //  - chomp off everything after ? or #. This is a design decision, so this
    //    function will fail to determine dependencies for sites that store
    //    their model IDs in query/hash params. If your usecase is not covered
    //    by this you need to implement this function yourself by overriding
    //    Intercooler.setIsDependentFunction(function(src, dest) { return bool; });
    //  - split by / to get the individual path elements, clear out empty values,
    //    then simply compare them
    var asrc = src.split(/[\?#]/, 1)[0].split("/").filter(function(e) {
      return e != "";
    });

    var adest = dest.split(/[\?#]/, 1)[0].split("/").filter(function(e) {
      return e != "";
    });

    // ignore purely local tags (local transport)
    if (asrc == "" || adest == "") {
      return false;
    }
    return adest.slice(0, asrc.length).join("/") == asrc.join("/") ||
    asrc.slice(0, adest.length).join("/") == adest.join("/");
  };

  //============================================================
  // Base Swap Definitions
  //============================================================
  function remove(elt) {
    elt.remove();
  }

  function showIndicator(elt) {
    if (elt.closest('.v-ic-use-transition').length > 0) {
      elt.data('v-ic-use-transition', true);
      elt.removeClass('v-ic-use-transition');
    } else {
      elt.show();
    }
  }

  function hideIndicator(elt) {
    if (elt.data('v-ic-use-transition')) {
      elt.data('v-ic-use-transition', null);
      elt.addClass('v-ic-use-transition');
    } else {
      elt.hide();
    }
  }

  function fixICAttributeName(s) {
    if (USE_DATA) {
      return 'data-' + s;
    } else {
      return s;
    }
  }

  function getICAttribute(element, attributeName) {
    return element.attr(fixICAttributeName(attributeName));
  }

  function setICAttribute(element, attributeName, attributeValue) {
    element.attr(fixICAttributeName(attributeName), attributeValue);
  }

  function prepend(parent, responseContent) {
    try {
      parent.prepend(responseContent);
    } catch (e) {
      log(parent, formatError(e), "ERROR");
    }
    if (getICAttribute(parent, 'v-ic-limit-children')) {
      var limit = parseInt(getICAttribute(parent, 'v-ic-limit-children'));
      if (parent.children().length > limit) {
        parent.children().slice(limit, parent.children().length).remove();
      }
    }
  }

  function append(parent, responseContent) {
    try {
      parent.append(responseContent);
    } catch (e) {
      log(parent, formatError(e), "ERROR");
    }
    if (getICAttribute(parent, 'v-ic-limit-children')) {
      var limit = parseInt(getICAttribute(parent, 'v-ic-limit-children'));
      if (parent.children().length > limit) {
        parent.children().slice(0, parent.children().length - limit).remove();
      }
    }
  }

  //============================================================
  // Utility Methods
  //============================================================
  function triggerEvent(elt, event, args){
    if($.zepto) {
      event = event.split(".").reverse().join(":");
    }
    elt.trigger(event, args);
  }

  function log(elt, msg, level) {
    if (elt == null) {
      elt = $('body');
    }
    triggerEvent(elt, "log.ic", [msg, level, elt]);
    if (level == "ERROR") {
      if (window.console) {
        window.console.log("VicIntercooler Error : " + msg);
      }
      var errorUrl = closestAttrValue($('body'), 'v-ic-post-errors-to');
      if (errorUrl) {
        $.post(errorUrl, {'error': msg});
      }
    }
  }

  function uuid() {
    return _UUID++;
  }

  function icSelectorFor(elt) {
    return getICAttributeSelector("v-ic-id='" + getIntercoolerId(elt) + "'");
  }

  function parseInterval(str) {
    log(null, "POLL: Parsing interval string " + str, 'DEBUG');
    if (str == "null" || str == "false" || str == "") {
      return null;
    } else if (str.lastIndexOf("ms") == str.length - 2) {
      return parseFloat(str.substr(0, str.length - 2));
    } else if (str.lastIndexOf("s") == str.length - 1) {
      return parseFloat(str.substr(0, str.length - 1)) * 1000;
    } else {
      return 1000;
    }
  }

  function getICAttributeSelector(attribute) {
    return "[" + fixICAttributeName(attribute) + "]";
  }

  function initScrollHandler() {
    if (_scrollHandler == null) {
      _scrollHandler = function() {
        $(getICAttributeSelector("v-ic-trigger-on='scrolled-into-view'")).each(function() {
          var _this = $(this);
          if (isScrolledIntoView(getTriggeredElement(_this)) && _this.data('v-ic-scrolled-into-view-loaded') != true) {
            _this.data('v-ic-scrolled-into-view-loaded', true);
            fireICRequest(_this);
          }
        });
      };
      $(window).scroll(_scrollHandler);
    }
  }

  function currentUrl() {
    return window.location.pathname + window.location.search + window.location.hash;
  }

  // taken from turbolinks.js
  function createDocument(html) {
    var doc = null;
    if (/<(html|body)/i.test(html)) {
      doc = document.documentElement.cloneNode();
      doc.innerHTML = html;
    } else {
      doc = document.documentElement.cloneNode(true);
      doc.querySelector('body').innerHTML = html;
    }
    return $(doc);
  }

  //============================================================
  // Request/Parameter/Include Processing
  //============================================================
  function getTarget(elt) {
    return getTargetImpl(elt, 'v-ic-target')
  }

  function getTargetImpl(elt, attibuteName) {
    var closest = $(elt).closest(getICAttributeSelector(attibuteName));
    var targetValue = getICAttribute(closest, attibuteName);
    if (targetValue == 'this') {
      return closest;
    } else if (targetValue && targetValue.indexOf('this.') != 0) {
      if (targetValue.indexOf('closest ') == 0) {
        return elt.closest(targetValue.substr(8));
      } else if (targetValue.indexOf('find ') == 0) {
        return elt.find(targetValue.substr(5));
      } else {
        return $(targetValue);
      }
    } else {
      return elt;
    }
  }

  function processHeaders(elt, xhr) {
    elt = $(elt);
    triggerEvent(elt, "beforeHeaders.ic", [elt, xhr]);
    log(elt, "response headers: " + xhr.getAllResponseHeaders(), "DEBUG");
    var target = null;

    // set page title by header
    if (xhr.getResponseHeader("X-VIC-Title")) {
      document.title = xhr.getResponseHeader("X-VIC-Title");
    }

    if (xhr.getResponseHeader("X-VIC-Refresh")) {
      var pathsToRefresh = xhr.getResponseHeader("X-VIC-Refresh").split(",");
      log(elt, "X-VIC-Refresh: refreshing " + pathsToRefresh, "DEBUG");
      $.each(pathsToRefresh, function(i, str) {
        refreshDependencies(str.replace(/ /g, ""), elt);
      });
    }

    if (xhr.getResponseHeader("X-VIC-Script")) {
      log(elt, "X-VIC-Script: evaling " + xhr.getResponseHeader("X-VIC-Script"), "DEBUG");
      globalEval(xhr.getResponseHeader("X-VIC-Script"), [["elt", elt]]);
    }

    if (xhr.getResponseHeader("X-VIC-Redirect")) {
      log(elt, "X-VIC-Redirect: redirecting to " + xhr.getResponseHeader("X-VIC-Redirect"), "DEBUG");
      window.location = xhr.getResponseHeader("X-VIC-Redirect");
    }

    if (xhr.getResponseHeader("X-VIC-CancelPolling") == "true") {
      cancelPolling(elt.closest(getICAttributeSelector('v-ic-poll')));
    }

    if (xhr.getResponseHeader("X-VIC-ResumePolling") == "true") {
      var pollingElt = elt.closest(getICAttributeSelector('v-ic-poll'));
      setICAttribute(pollingElt, 'v-ic-pause-polling', null);
      startPolling(pollingElt);
    }

    if (xhr.getResponseHeader("X-VIC-SetPollInterval")) {
      var pollingElt = elt.closest(getICAttributeSelector('v-ic-poll'));
      cancelPolling(pollingElt);
      setICAttribute(pollingElt, 'v-ic-poll', xhr.getResponseHeader("X-VIC-SetPollInterval"));
      startPolling(pollingElt);
    }

    if (xhr.getResponseHeader("X-VIC-Open")) {
      log(elt, "X-VIC-Open: opening " + xhr.getResponseHeader("X-VIC-Open"), "DEBUG");
      window.open(xhr.getResponseHeader("X-VIC-Open"));
    }

    var triggerValue = xhr.getResponseHeader("X-VIC-Trigger");
    if (triggerValue) {
      log(elt, "X-VIC-Trigger: found trigger " + triggerValue, "DEBUG");
      target = getTarget(elt);
      // Deprecated API
      if (xhr.getResponseHeader("X-VIC-Trigger-Data")) {
        var triggerArgs = $.parseJSON(xhr.getResponseHeader("X-VIC-Trigger-Data"));
        triggerEvent(target, triggerValue, triggerArgs);
      } else {
        if (triggerValue.indexOf("{") >= 0) {
          $.each($.parseJSON(triggerValue), function(event, args) {
            triggerEvent(target, event, args);
          });
        } else {
          triggerEvent(target, triggerValue, []);
        }
      }
    }

    var localVars = xhr.getResponseHeader("X-VIC-Set-Local-Vars");
    if (localVars) {
      $.each($.parseJSON(localVars), function(key, val) {
        localStorage.setItem(key, val);
      });
    }

    if (xhr.getResponseHeader("X-VIC-Remove")) {
      if (elt) {
        var removeVal = xhr.getResponseHeader("X-VIC-Remove");
        removeVal += ''; // normalize as string for zapto
        var removeValAsInterval = parseInterval(removeVal);
        log(elt, "X-VIC-Remove header found.", "DEBUG");
        target = getTarget(elt);
        if(removeVal == "true" || removeValAsInterval == null) {
          remove(target);
        } else {
          target.addClass('v-ic-removing');
          setTimeout(function () {
            remove(target);
          }, removeValAsInterval);
        }
      }
    }

    triggerEvent(elt, "afterHeaders.ic", [elt, xhr]);

    return true;
  }


  function beforeRequest(elt) {
    elt.addClass('disabled');
    elt.data('v-ic-request-in-flight', true);
  }

  function requestCleanup(indicator, elt) {
    if (indicator.length > 0) {
      hideIndicator(indicator);
    }
    elt.removeClass('disabled');
    elt.data('v-ic-request-in-flight', false);
    if (elt.data('v-ic-next-request')) {
      elt.data('v-ic-next-request')["req"]();
      elt.data('v-ic-next-request', null);
    }
  }

  function replaceOrAddMethod(data, actualMethod) {
    if ($.type(data) === "string") {
      var regex = /(&|^)_method=[^&]*/;
      var content = "&_method=" + actualMethod;
      if (regex.test(data)) {
        return data.replace(regex, content)
      } else {
        return data + content;
      }
    } else {
      data.append("_method", actualMethod);
      return data;
    }
  }

  /*
    Is the provided text a valid JavaScript identifier path?

    We should also probably check if an identifier is a JavaScript keyword here.
  */
  function isIdentifier(txt) {
    return /^[$A-Z_][0-9A-Z_$]*$/i.test(txt);
  }

  /*
    Evaluate a script snippet provided by the user.

    script: A string. If this is an identifier, it is assumed to be a callable, retrieved from the
    global namespace, and called. If it is a compound statement, it is evaluated using eval.
    args: A list of [name, value] tuples. These will be injected into the namespace of evaluated
    scripts, and be passed as arguments to safe evaluations.
  */
  // It would be nice to use the spread operator here globalEval(script, ...args) - but it breaks
  // uglify and isn't supported in some older browsers.
  function globalEval(script, args) {
    var names = [];
    var values = [];
    if (args) {
        for (var i = 0; i < args.length; i++) {
            names.push(args[i][0]);
            values.push(args[i][1]);
        }
    }
    if (isIdentifier(script)) {
        return window[script].apply(this, values);
    } else {
        var outerfunc  = window["eval"].call(
            window,
            '(function (' + names.join(", ") + ') {' + script + '})'
        );
        return outerfunc.apply(this, values);
    }
  }

  function closestAttrValue(elt, attr) {
    var closestElt = $(elt).closest(getICAttributeSelector(attr));
    if (closestElt.length > 0) {
      return getICAttribute(closestElt, attr);
    } else {
      return null;
    }
  }

  function formatError(e) {
    var msg = e.toString() + "\n";
    try {
      msg += e.stack;
    } catch (e) {
      // ignore
    }
    return msg;
  }

  function handleRemoteRequest(elt, type, url, data, success) {

    beforeRequest(elt);

    data = replaceOrAddMethod(data, type);

    // Spinner support
    var indicator = findIndicator(elt);
    if (indicator.length > 0) {
      showIndicator(indicator);
    }

    var requestId = uuid();
    var requestStart = new Date();
    var actualRequestType;
    if(USE_ACTUAL_HTTP_METHOD) {
      actualRequestType = type;
    } else {
      actualRequestType = type == 'GET' ? 'GET' : 'POST';
    }

    var ajaxSetup = {
      type: actualRequestType,
      url: url,
      data: data,
      dataType: 'text',
      headers: {
        "Accept": "text/html-partial, */*; q=0.9",
        "X-VIC-Request": true,
        "X-HTTP-Method-Override": type
      },
      beforeSend: function(xhr, settings) {
        triggerEvent(elt, "beforeSend.ic", [elt, data, settings, xhr, requestId]);
        log(elt, "before AJAX request " + requestId + ": " + type + " to " + url, "DEBUG");
        var onBeforeSend = closestAttrValue(elt, 'v-ic-on-beforeSend');
        if (onBeforeSend) {
          globalEval(onBeforeSend, [["elt", elt], ["data", data], ["settings", settings], ["xhr", xhr]]);
        }
        maybeInvokeLocalAction(elt, "-beforeSend");
      },
      success: function(data, textStatus, xhr) {
        triggerEvent(elt, "success.ic", [elt, data, textStatus, xhr, requestId]);
        triggerEvent($(document), "success.v-ic", [elt, data, textStatus, xhr, requestId]);
        log(elt, "AJAX request " + requestId + " was successful.", "DEBUG");
        var onSuccess = closestAttrValue(elt, 'v-ic-on-success');
        if (onSuccess) {
          if (globalEval(onSuccess, [["elt", elt], ["data", data], ["textStatus", textStatus], ["xhr", xhr]]) == false) {
            return;
          }
        }

        var beforeHeaders = new Date();
        try {
          if (processHeaders(elt, xhr)) {
            log(elt, "Processed headers for request " + requestId + " in " + (new Date() - beforeHeaders) + "ms", "DEBUG");
            var beforeSuccess = new Date();

            if (xhr.getResponseHeader("X-VIC-PushURL") || closestAttrValue(elt, 'v-ic-push-url') == "true") {
              try {
                requestCleanup(indicator, elt); // clean up before snap-shotting HTML
                var newUrl = xhr.getResponseHeader("X-VIC-PushURL") || closestAttrValue(elt, 'v-ic-src');
                if(_history) {
                  _history.snapshotForHistory(newUrl);
                } else {
                  throw "History support not enabled";
                }
              } catch (e) {
                log(elt, "Error during history snapshot for " + requestId + ": " + formatError(e), "ERROR");
              }
            }

            success(data, textStatus, elt, xhr);

            log(elt, "Process content for request " + requestId + " in " + (new Date() - beforeSuccess) + "ms", "DEBUG");
          }
          triggerEvent(elt, "after.success.ic", [elt, data, textStatus, xhr, requestId]);
          maybeInvokeLocalAction(elt, "-success");
        } catch (e) {
          log(elt, "Error processing successful request " + requestId + " : " + formatError(e), "ERROR");
        }
      },
      error: function(xhr, status, str) {
        triggerEvent(elt, "error.ic", [elt, status, str, xhr]);
        var onError = closestAttrValue(elt, 'v-ic-on-error');
        if (onError) {
          globalEval(onError, [["elt", elt], ["status", status], ["str", str], ["xhr", xhr]]);
        }
        processHeaders(elt, xhr);
        maybeInvokeLocalAction(elt, "-error");
        log(elt, "AJAX request " + requestId + " to " + url + " experienced an error: " + str, "ERROR");
      },
      complete: function(xhr, status) {
        log(elt, "AJAX request " + requestId + " completed in " + (new Date() - requestStart) + "ms", "DEBUG");
        requestCleanup(indicator, elt);
        try {
          if ($.contains(document, elt[0])) {
            triggerEvent(elt, "complete.ic", [elt, data, status, xhr, requestId]);
          } else {
            triggerEvent($('body'), "complete.ic", [elt, data, status, xhr, requestId]);
          }
        } catch (e) {
          log(elt, "Error during complete.ic event for " + requestId + " : " + formatError(e), "ERROR");
        }
        var onComplete = closestAttrValue(elt, 'v-ic-on-complete');
        if (onComplete) {
          globalEval(onComplete, [["elt", elt], ["xhr", xhr], ["status", status]]);
        }
        maybeInvokeLocalAction(elt, "-complete");
      }
    };
    if ($.type(data) != "string") {
      ajaxSetup.dataType = null;
      ajaxSetup.processData = false;
      ajaxSetup.contentType = false;
    }

    triggerEvent($(document), "beforeAjaxSend.ic", [ajaxSetup, elt]);

    if(ajaxSetup.cancel) {
      requestCleanup(indicator, elt);
    } else {
      $.ajax(ajaxSetup)
    }
  }

  function findIndicator(elt) {
    var indicator = null;
    elt = $(elt);
    if (getICAttribute(elt, 'v-ic-indicator')) {
      indicator = $(getICAttribute(elt, 'v-ic-indicator')).first();
    } else {
      indicator = elt.find(".v-ic-indicator").first();
      if (indicator.length == 0) {
        var parent = closestAttrValue(elt, 'v-ic-indicator');
        if (parent) {
          indicator = $(parent).first();
        } else {
          if (elt.next().is('.v-ic-indicator')) {
            indicator = elt.next();
          }
        }
      }
    }
    return indicator;
  }

  function processIncludes(data, str) {
    if ($.trim(str).indexOf("{") == 0) {
      var obj = $.parseJSON(str);
      $.each(obj, function(name, value) {
        data = appendData(data, name, value);
      });
    } else {
      $(str).each(function() {
        var obj = $(this).serializeArray();
        $.each(obj, function(i, input) {
          data = appendData(data, input.name, input.value);
        });
      });
    }
    return data;
  }

  function processLocalVars(data, str) {
    $(str.split(",")).each(function() {
      var key = $.trim(this);
      var item = localStorage.getItem(key);
      if(item) {
        data = appendData(data, key, item);
      }
    });
    return data;
  }

  function appendData(data, string, value) {
    if ($.type(data) === "string") {
      if($.type(value) !== "string") {
        value = JSON.stringify(value);
      }
      return data + "&" + string + "=" + encodeURIComponent(value);
    } else {
      data.append(string, value);
      return data;
    }
  }

  function getParametersForElement(verb, elt, triggerOrigin) {
    var target = getTarget(elt);
    var data = null;

    if (elt.is('form') && elt.attr('enctype') == 'multipart/form-data') {
      data = new FormData(elt[0]);
      data = appendData(data, 'v-ic-request', true);
    } else {
      data = "v-ic-request=true";
      // if the element is in a form, include the entire form
      var closestForm = elt.closest('form');
      if (elt.is('form') || (verb != "GET" && closestForm.length > 0)) {
        data += "&" + closestForm.serialize();
        // include data from a focused button (to capture clicked button value)
        var buttonData = elt.data('v-ic-last-clicked-button');
        if(buttonData) {
          data = appendData(data, buttonData.name, buttonData.value);
        }
      } else { // otherwise include the element
        data += "&" + elt.serialize();
      }
    }

    var promptText = closestAttrValue(elt, 'v-ic-prompt');
    if (promptText) {
      var promptVal = prompt(promptText);
      if (promptVal) {
        var promptParamName = closestAttrValue(elt, 'v-ic-prompt-name') || 'v-ic-prompt-value';
        data = appendData(data, promptParamName, promptVal);
      } else {
        return null;
      }
    }

    if (elt.attr('id')) {
      data = appendData(data, 'v-ic-element-id', elt.attr('id'));
    }
    if (elt.attr('name')) {
      data = appendData(data, 'v-ic-element-name', elt.attr('name'));
    }
    if (getICAttribute(target, 'v-ic-id')) {
      data = appendData(data, 'v-ic-id', getICAttribute(target, 'v-ic-id'));
    }
    if (target.attr('id')) {
      data = appendData(data, 'v-ic-target-id', target.attr('id'));
    }
    if (triggerOrigin && triggerOrigin.attr('id')) {
      data = appendData(data, 'v-ic-trigger-id', triggerOrigin.attr('id'));
    }
    if (triggerOrigin && triggerOrigin.attr('name')) {
      data = appendData(data, 'v-ic-trigger-name', triggerOrigin.attr('name'));
    }
    var includeAttr = closestAttrValue(elt, 'v-ic-include');
    if (includeAttr) {
      data = processIncludes(data, includeAttr);
    }
    var localVars = closestAttrValue(elt, 'v-ic-local-vars');
    if (localVars) {
      data = processLocalVars(data, localVars);
    }
    $(getICAttributeSelector('v-ic-global-include')).each(function() {
      data = processIncludes(data, getICAttribute($(this), 'v-ic-global-include'));
    });
    data = appendData(data, 'v-ic-current-url', currentUrl());

    var selectFromResp = closestAttrValue(elt, 'v-ic-select-from-response');
    if(selectFromResp) {
      data = appendData(data, 'v-ic-select-from-response', selectFromResp);
    }

    log(elt, "request parameters " + data, "DEBUG");

    return data;
  }

  function maybeSetIntercoolerInfo(elt) {
    var target = getTarget(elt);
    getIntercoolerId(target);
    if (elt.data('elementAdded.ic') != true) {
      elt.data('elementAdded.ic', true);
      triggerEvent(elt, "elementAdded.ic");
    }
  }

  function getIntercoolerId(elt) {
    if (!getICAttribute(elt, 'v-ic-id')) {
      setICAttribute(elt, 'v-ic-id', uuid());
    }
    return getICAttribute(elt, 'v-ic-id');
  }

  //============================================================
  // Tree Processing
  //============================================================

  function processNodes(elt) {
    elt = $(elt);
    if (elt.length > 1) {
      elt.each(function() {
        processNodes(this);
      });
    } else {
      processMacros(elt);
      processSources(elt);
      processPolling(elt);
      processEventSources(elt);
      processTriggerOn(elt);
      processRemoveAfter(elt);
      processAddClasses(elt);
      processRemoveClasses(elt);
    }
  }

  function fireReadyStuff(elt) {
    triggerEvent(elt, 'nodesProcessed.ic');
    $.each(_readyHandlers, function(i, handler) {
      try {
        handler(elt);
      } catch (e) {
        log(elt, formatError(e), "ERROR");
      }
    });
  }

  function autoFocus(elt) {
    elt.find('[autofocus]').last().focus();
  }

  function processMacros(elt) {
    $.each(_MACROS, function(i, macro) {
      if (elt.closest('.v-ic-ignore').length == 0) {
        if (elt.is('[' + macro + ']')) {
          processMacro(macro, elt);
        }
        elt.find('[' + macro + ']').each(function() {
          var _this = $(this);
          if (_this.closest('.v-ic-ignore').length == 0) {
            processMacro(macro, _this);
          }
        });
      }
    });
  }

  function processSources(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      if (elt.is(getICAttributeSelector("v-ic-src"))) {
        maybeSetIntercoolerInfo(elt);
      }
      elt.find(getICAttributeSelector("v-ic-src")).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          maybeSetIntercoolerInfo(_this);
        }
      });
    }
  }

  function processPolling(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      if (elt.is(getICAttributeSelector("v-ic-poll"))) {
        maybeSetIntercoolerInfo(elt);
        startPolling(elt);
      }
      elt.find(getICAttributeSelector("v-ic-poll")).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          maybeSetIntercoolerInfo(_this);
          startPolling(_this);
        }
      });
    }
  }

  function processTriggerOn(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      handleTriggerOn(elt);
      elt.find(getICAttributeSelector('v-ic-trigger-on')).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          handleTriggerOn(_this);
        }
      });
    }
  }

  function processRemoveAfter(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      handleRemoveAfter(elt);
      elt.find(getICAttributeSelector('v-ic-remove-after')).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          handleRemoveAfter(_this);
        }
      });
    }
  }

  function processAddClasses(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      handleAddClasses(elt);
      elt.find(getICAttributeSelector('v-ic-add-class')).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          handleAddClasses(_this);
        }
      });
    }
  }

  function processRemoveClasses(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      handleRemoveClasses(elt);
      elt.find(getICAttributeSelector('v-ic-remove-class')).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          handleRemoveClasses(_this);
        }
      });
    }
  }

  function processEventSources(elt) {
    if (elt.closest('.v-ic-ignore').length == 0) {
      handleEventSource(elt);
      elt.find(getICAttributeSelector('v-ic-sse-src')).each(function() {
        var _this = $(this);
        if (_this.closest('.v-ic-ignore').length == 0) {
          handleEventSource(_this);
        }
      });
    }
  }

  //============================================================
  // Polling support
  //============================================================

  function startPolling(elt) {
    if (elt.data('v-ic-poll-interval-id') == null && getICAttribute(elt, 'v-ic-pause-polling') != 'true') {
      var interval = parseInterval(getICAttribute(elt, 'v-ic-poll'));
      if (interval != null) {
        var selector = icSelectorFor(elt);
        var repeats = parseInt(getICAttribute(elt, 'v-ic-poll-repeats')) || -1;
        var currentIteration = 0;
        log(elt, "POLL: Starting poll for element " + selector, "DEBUG");
        var timerId = setInterval(function() {
          var target = $(selector);
          triggerEvent(elt, "onPoll.ic", target);
          if ((target.length == 0) || (currentIteration == repeats) || elt.data('v-ic-poll-interval-id') != timerId) {
            log(elt, "POLL: Clearing poll for element " + selector, "DEBUG");
            clearTimeout(timerId);
          } else {
            fireICRequest(target);
          }
          currentIteration++;
        }, interval);
        elt.data('v-ic-poll-interval-id', timerId);
      }
    }
  }

  function cancelPolling(elt) {
    if (elt.data('v-ic-poll-interval-id') != null) {
      clearTimeout(elt.data('v-ic-poll-interval-id'));
      elt.data('v-ic-poll-interval-id', null);
    }
  }

  //============================================================----
  // Dependency support
  //============================================================----

  function refreshDependencies(dest, src) {
    log(src, "refreshing dependencies for path " + dest, "DEBUG");
    $(getICAttributeSelector('v-ic-src')).each(function() {
      var fired = false;
      var _this = $(this);
      if (verbFor(_this) == "GET" && getICAttribute(_this, 'v-ic-deps') != 'ignore' ) {
        if (isDependent(dest, getICAttribute(_this, 'v-ic-src'))) {
          if (src == null || $(src)[0] != _this[0]) {
            fireICRequest(_this);
            fired = true;
          }
        } else if (isICDepsDependent(dest, getICAttribute(_this, 'v-ic-deps')) || getICAttribute(_this, 'v-ic-deps') == "*") {
          if (src == null || $(src)[0] != _this[0]) {
            fireICRequest(_this);
            fired = true;
          }
        }
      }
      if (fired) {
        log(_this, "depends on path " + dest + ", refreshing...", "DEBUG")
      }
    });
  }

  function isICDepsDependent(src, dest) {
    if(dest) {
      var paths = dest.split(",");
      for (var i = 0; i < paths.length; i++) {
        var str = paths[i].trim();
        if(isDependent(src, str)) {
          return true;
        }
      }
    }
    return false;
  }

  function isDependent(src, dest) {
    return !!_isDependentFunction(src, dest);
  }

  //============================================================----
  // Trigger-On support
  //============================================================----

  function verbFor(elt) {
    elt = $(elt);
    if (getICAttribute(elt, 'v-ic-verb')) {
      return getICAttribute(elt, 'v-ic-verb').toUpperCase();
    }
    return "GET";
  }

  function eventFor(attr, elt) {
    if (attr == "default") {
      elt = $(elt);
      if (elt.is('button')) {
        return 'click';
      } else if (elt.is('form')) {
        return 'submit';
      } else if (elt.is('input, textarea, select, button')) {
        return 'change';
      } else {
        return 'click';
      }
    } else {
      return attr;
    }
  }

  function preventDefault(elt, evt) {
    return elt.is('form') ||
          (elt.is('input[type="submit"], button') && elt.closest('form').length == 1) ||
          (elt.is('a') && elt.is('[href]') && elt.attr('href').indexOf('#') != 0);
  }

  function handleRemoveAfter(elt) {
    elt = $(elt);
    if (getICAttribute(elt, 'v-ic-remove-after')) {
      var interval = parseInterval(getICAttribute(elt, 'v-ic-remove-after'));
      setTimeout(function() {
        remove(elt);
      }, interval);
    }
  }

  function parseAndApplyClass(classInfo, elt, operation) {
    var cssClass = "";
    var delay = 50;
    if (classInfo.indexOf(":") > 0) {
      var split = classInfo.split(':');
      cssClass = split[0];
      delay = parseInterval(split[1]);
    } else {
      cssClass = classInfo;
    }
    setTimeout(function() {
      elt[operation](cssClass)
    }, delay);
  }

  function handleAddClasses(elt) {
    elt = $(elt);
    if (getICAttribute(elt, 'v-ic-add-class')) {
      var values = getICAttribute(elt, 'v-ic-add-class').split(",");
      var arrayLength = values.length;
      for (var i = 0; i < arrayLength; i++) {
        parseAndApplyClass($.trim(values[i]), elt, 'addClass');
      }
    }
  }

  function handleRemoveClasses(elt) {
    elt = $(elt);
    if (getICAttribute(elt, 'v-ic-remove-class')) {
      var values = getICAttribute(elt, 'v-ic-remove-class').split(",");
      var arrayLength = values.length;
      for (var i = 0; i < arrayLength; i++) {
        parseAndApplyClass($.trim(values[i]), elt, 'removeClass');
      }
    }
  }

  function handleEventSource(elt) {
    elt = $(elt);
    if (getICAttribute(elt, 'v-ic-sse-src')) {
      var evtSrcUrl = getICAttribute(elt, 'v-ic-sse-src');
      var eventSource = initEventSource(elt, evtSrcUrl);
      elt.data('v-ic-event-sse-source', eventSource);
      elt.data('v-ic-event-sse-map', {});
    }
  }

  function initEventSource(elt, evtSrcUrl) {
    var eventSource = Intercooler._internal.initEventSource(evtSrcUrl);
    eventSource.onmessage = function(e) {
      processICResponse(e.data, elt, false);
    };
    return eventSource;
  }

  function registerSSE(sourceElement, event) {
    var source = sourceElement.data('v-ic-event-sse-source');
    var eventMap = sourceElement.data('v-ic-event-sse-map');
    if(source.addEventListener && eventMap[event] != true) {
      source.addEventListener(event, function(){
        sourceElement.find(getICAttributeSelector('v-ic-trigger-on')).each(function(){
          var _that = $(this);
          if(_that.attr('v-ic-trigger-on') == "sse:" + event) {
            fireICRequest(_that);
          }
        });
      })
    }
  }

  function getTriggeredElement(elt) {
    var triggerFrom = getICAttribute(elt, 'v-ic-trigger-from');
    if(triggerFrom) {
      if (triggerFrom == "document") {
        return $(document);
      } else if (triggerFrom == "window") {
        return $(window);
      } else {
        return $(triggerFrom);
      }
    } else {
      return elt;
    }
  }

  function handleTriggerOn(elt) {
    if (getICAttribute(elt, 'v-ic-trigger-on')) {
      // record button or submit input click info
      if(elt.is('form')) {
        elt.on('click focus', 'input, button, select, textarea', function(e){
          if($(this).is('input[type="submit"], button') && $(this).is("[name]")) {
            elt.data('v-ic-last-clicked-button', {name:$(this).attr("name"), value:$(this).val()})
          } else {
            elt.data('v-ic-last-clicked-button', null)
          }
        });
      }
      if (getICAttribute(elt, 'v-ic-trigger-on') == 'load') {
        fireICRequest(elt);
      } else if (getICAttribute(elt, 'v-ic-trigger-on') == 'scrolled-into-view') {
        initScrollHandler();
        setTimeout(function() {
          triggerEvent($(window), 'scroll');
        }, 100); // Trigger a scroll in case element is already viewable
      } else {
        var triggerOn = getICAttribute(elt, 'v-ic-trigger-on').split(" ");
        if(triggerOn[0].indexOf("sse:") == 0) {
          //Server-sent event, find closest event source and register for it
          var sourceElt = elt.closest(getICAttributeSelector('v-ic-sse-src'));
          if(sourceElt) {
            registerSSE(sourceElt, triggerOn[0].substr(4))
          }
        } else {
        var triggerOn = getICAttribute($(elt), 'v-ic-trigger-on').split(" ");
        var event = eventFor(triggerOn[0], $(elt));
        $(getTriggeredElement(elt)).on(event, function(e) {
            var onBeforeTrigger = closestAttrValue(elt, 'v-ic-on-beforeTrigger');
            if (onBeforeTrigger) {
              if (globalEval(onError, [["elt", elt], ["evt", e], ["elt", elt]]) == false) {
                log(elt, "v-ic-trigger cancelled by ic-on-beforeTrigger", "DEBUG");
                return false;
              }
            }

            if (triggerOn[1] == 'changed') {
              var currentVal = elt.val();
              var previousVal = elt.data('v-ic-previous-val');
              elt.data('v-ic-previous-val', currentVal);
              if (currentVal != previousVal) {
                fireICRequest(elt);
              }
            } else {
              fireICRequest(elt);
            }
            if (preventDefault(elt, e)) {
              e.preventDefault();
              return false;
            }
            return true;
          });
          if(event && (event.indexOf("timeout:") == 0)) {
            setTimeout(function () {
            $(getTriggeredElement(elt)).trigger(event);
            }, parseInterval(event.split(":")[1]));
          }
        }
      }
    }
  }

  //============================================================----
  // Macro support
  //============================================================----

  function macroIs(macro, constant) {
    return macro == fixICAttributeName(constant);
  }

  function processMacro(macro, elt) {
    // action attributes
    if (macroIs(macro, 'v-ic-post-to')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-post-to'));
      setIfAbsent(elt, 'v-ic-verb', 'POST');
      setIfAbsent(elt, 'v-ic-trigger-on', 'default');
      setIfAbsent(elt, 'v-ic-deps', 'ignore');
    }
    if (macroIs(macro, 'v-ic-put-to')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-put-to'));
      setIfAbsent(elt, 'v-ic-verb', 'PUT');
      setIfAbsent(elt, 'v-ic-trigger-on', 'default');
      setIfAbsent(elt, 'v-ic-deps', 'ignore');
    }
    if (macroIs(macro, 'v-ic-patch-to')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-patch-to'));
      setIfAbsent(elt, 'v-ic-verb', 'PATCH');
      setIfAbsent(elt, 'v-ic-trigger-on', 'default');
      setIfAbsent(elt, 'v-ic-deps', 'ignore');
    }
    if (macroIs(macro, 'v-ic-get-from')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-get-from'));
      setIfAbsent(elt, 'v-ic-trigger-on', 'default');
      setIfAbsent(elt, 'v-ic-deps', 'ignore');
    }
    if (macroIs(macro, 'v-ic-delete-from')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-delete-from'));
      setIfAbsent(elt, 'v-ic-verb', 'DELETE');
      setIfAbsent(elt, 'v-ic-trigger-on', 'default');
      setIfAbsent(elt, 'v-ic-deps', 'ignore');
    }
    if (macroIs(macro, 'v-ic-action')) {
      setIfAbsent(elt, 'v-ic-trigger-on', 'default');
    }

    // non-action attributes
    var value = null;
    var url = null;
    if (macroIs(macro, 'v-ic-style-src')) {
      value = getICAttribute(elt, 'v-ic-style-src').split(":");
      var styleAttribute = value[0];
      url = value[1];
      setIfAbsent(elt, 'v-ic-src', url);
      setIfAbsent(elt, 'v-ic-target', 'this.style.' + styleAttribute);
    }
    if (macroIs(macro, 'v-ic-attr-src')) {
      value = getICAttribute(elt, 'v-ic-attr-src').split(":");
      var attribute = value[0];
      url = value[1];
      setIfAbsent(elt, 'v-ic-src', url);
      setIfAbsent(elt, 'v-ic-target', 'this.' + attribute);
    }
    if (macroIs(macro, 'v-ic-prepend-from')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-prepend-from'));
      setIfAbsent(elt, 'v-ic-swap-style', 'prepend');
    }
    if (macroIs(macro, 'v-ic-append-from')) {
      setIfAbsent(elt, 'v-ic-src', getICAttribute(elt, 'v-ic-append-from'));
      setIfAbsent(elt, 'v-ic-swap-style', 'append');
    }
  }

  function setIfAbsent(elt, attr, value) {
    if (getICAttribute(elt, attr) == null) {
      setICAttribute(elt, attr, value);
    }
  }

  //============================================================----
  // Utilities
  //============================================================----

  function isScrolledIntoView(elem) {
    elem = $(elem);
    if (elem.height() == 0 && elem.width() == 0) {
       return false;
    }
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = elem.offset().top;
    var elemBottom = elemTop + elem.height();

    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom)
    && (elemBottom <= docViewBottom) && (elemTop >= docViewTop));
  }

  function maybeScrollToTarget(elt, target) {
    if (closestAttrValue(elt, 'v-ic-scroll-to-target') != "false" &&
    (closestAttrValue(elt, 'v-ic-scroll-to-target') == 'true' ||
    closestAttrValue(target, 'v-ic-scroll-to-target') == 'true')) {
      var offset = -50; // -50 px default offset padding
      if (closestAttrValue(elt, 'v-ic-scroll-offset')) {
        offset = parseInt(closestAttrValue(elt, 'v-ic-scroll-offset'));
      } else if (closestAttrValue(target, 'v-ic-scroll-offset')) {
        offset = parseInt(closestAttrValue(target, 'v-ic-scroll-offset'));
      }
      var currentPosition = target.offset().top;
      var portalTop = $(window).scrollTop();
      var portalEnd = portalTop + window.innerHeight;
      //if the current top of this element is not visible, scroll it to the top position
      if (currentPosition < portalTop || currentPosition > portalEnd) {
        offset += currentPosition;
        $('html,body').animate({scrollTop: offset}, 400);
      }
    }
  }

  function getTransitionDuration(elt, target) {
    var transitionDuration = closestAttrValue(elt, 'v-ic-transition-duration');
    if (transitionDuration) {
      return parseInterval(transitionDuration);
    }
    transitionDuration = closestAttrValue(target, 'v-ic-transition-duration');
    if (transitionDuration) {
      return parseInterval(transitionDuration);
    }
    target = $(target);
    var duration = 0;
    var durationStr = target.css('transition-duration');
    if (durationStr) {
      duration += parseInterval(durationStr);
    }
    var delayStr = target.css('transition-delay');
    if (delayStr) {
      duration += parseInterval(delayStr);
    }
    return duration;
  }

  function closeSSESource(elt) {
    var src = elt.data('v-ic-event-sse-source');
    try {
      if(src) {
        src.close();
      }
    } catch (e) {
      log(elt, "Error closing ServerSentEvent source" + e, "ERROR");
    }
  }

  function beforeSwapCleanup(target) {
    target.find(getICAttributeSelector('v-ic-sse-src')).each(function() {
      closeSSESource($(this));
    });
    triggerEvent(target, 'beforeSwap.ic');
  }

  function processICResponse(responseContent, elt, forHistory) {
    if (responseContent && responseContent != "" && responseContent != " ") {

      log(elt, "response content: \n" + responseContent, "DEBUG");
      var target = getTarget(elt);

      var contentToSwap = maybeFilter(responseContent, closestAttrValue(elt, 'v-ic-select-from-response'));

      if (closestAttrValue(elt, 'v-ic-fix-ids') == "true") {
        fixIDs(contentToSwap);
      }

      var doSwap = function() {
        if (closestAttrValue(elt, 'v-ic-replace-target') == "true") {
          try {
            beforeSwapCleanup(target);
            closeSSESource(target);
            target.replaceWith(contentToSwap);
            target = contentToSwap;
          } catch (e) {
            log(elt, formatError(e), "ERROR");
          }
          processNodes(contentToSwap);
          fireReadyStuff(target);
          autoFocus(target);
        } else {
          if (getICAttribute(elt, 'v-ic-swap-style') == "prepend") {
            prepend(target, contentToSwap);
            processNodes(contentToSwap);
            fireReadyStuff(target);
            autoFocus(target);
          } else if (getICAttribute(elt, 'v-ic-swap-style') == "append") {
            append(target, contentToSwap);
            processNodes(contentToSwap);
            fireReadyStuff(target);
            autoFocus(target);
          } else {
            try {
              beforeSwapCleanup(target);
              target.empty().append(contentToSwap);
            } catch (e) {
              log(elt, formatError(e), "ERROR");
            }
            target.children().each(function() {
              processNodes(this);
            });
            fireReadyStuff(target);
            autoFocus(target);
          }
          if (forHistory != true) {
            maybeScrollToTarget(elt, target);
          }
        }
      };

      if (target.length == 0) {
        //TODO cgross - refactor getTarget to return printable string here
        log(elt, "Invalid target for element: " + getICAttribute(elt.closest(getICAttributeSelector('v-ic-target')), 'v-ic-target'), "ERROR");
        return;
      }

      var delay = getTransitionDuration(elt, target);
      target.addClass('v-ic-transitioning');
      setTimeout(function() {
        try {
          doSwap();
        } catch (e) {
          log(elt, "Error during content swap : " + formatError(e), "ERROR");
        }
        setTimeout(function() {
          try {
            target.removeClass('v-ic-transitioning');
            if(_history) {
              _history.updateHistory();
            }
            triggerEvent(target, "complete_transition.ic", [target]);
          } catch (e) {
            log(elt, "Error during transition complete : " + formatError(e), "ERROR");
          }
        }, 20);
      }, delay);
    } else {
      log(elt, "Empty response, nothing to do here.", "DEBUG");
    }
  }

  function maybeFilter(newContent, filter) {
    var asQuery;
    if ($.zepto) {
      var newDoc = createDocument(newContent);
      asQuery = $(newDoc).find('body').contents();
    } else {
      asQuery = $($.parseHTML(newContent, null, true));
    }
    if (filter) {
      return walkTree(asQuery, filter).contents();
    } else {
      return asQuery;
    }
  }

  function walkTree(elt, filter) {
    return elt.filter(filter).add(elt.find(filter));
  }

  function fixIDs(contentToSwap) {
    var fixedIDs = {};
    walkTree(contentToSwap, "[id]").each(function() {
      var originalID = $(this).attr("id");
      var fixedID;
      do {
        fixedID = "v-ic-fixed-id-" + uuid();
      } while ($("#" + fixedID).length > 0);
      fixedIDs[originalID] = fixedID;
      $(this).attr("id", fixedID);
    });
    walkTree(contentToSwap, "label[for]").each(function () {
      var originalID = $(this).attr("for");
      $(this).attr("for", fixedIDs[originalID] || originalID);
    });
    walkTree(contentToSwap, "*").each(function () {
      $.each(this.attributes, function () {
        if (this.value.indexOf("#") !== -1) {
          this.value = this.value.replace(/#([-_A-Za-z0-9]+)/g, function(match, originalID) {
            return "#" + (fixedIDs[originalID] || originalID);
          });
        }
      })
    });
  }

  function getStyleTarget(elt) {
    var val = closestAttrValue(elt, 'v-ic-target');
    if (val && val.indexOf("this.style.") == 0) {
      return val.substr(11)
    } else {
      return null;
    }
  }

  function getAttrTarget(elt) {
    var val = closestAttrValue(elt, 'v-ic-target');
    if (val && val.indexOf("this.") == 0) {
      return val.substr(5)
    } else {
      return null;
    }
  }

    function fireICRequest(elt, alternateHandler) {
    elt = $(elt);

    var triggerOrigin = elt;
    if (!elt.is(getICAttributeSelector('v-ic-src')) && getICAttribute(elt, 'v-ic-action') == undefined) {
      elt = elt.closest(getICAttributeSelector('v-ic-src'));
    }

    var confirmText = closestAttrValue(elt, 'v-ic-confirm');
    if (confirmText) {
      if (!confirm(confirmText)) {
        return;
      }
    }

    if("true" == closestAttrValue(elt, 'v-ic-disable-when-doc-hidden')) {
      if(document['hidden']) {
        return;
      }
    }

    if("true" == closestAttrValue(elt, 'v-ic-disable-when-doc-inactive')) {
      if(!document.hasFocus()) {
        return;
      }
    }

    if (elt.length > 0) {
      var icEventId = uuid();
      elt.data('v-ic-event-id', icEventId);
      var invokeRequest = function() {

        // if an existing request is in flight for this element, push this request as the next to be executed
        if (elt.data('v-ic-request-in-flight') == true) {
          elt.data('v-ic-next-request', {"req" : invokeRequest});
          return;
        }

        if (elt.data('v-ic-event-id') == icEventId) {
          var styleTarget = getStyleTarget(elt);
          var attrTarget = styleTarget ? null : getAttrTarget(elt);
          var verb = verbFor(elt);
          var url = getICAttribute(elt, 'v-ic-src');
          if (url) {
            var success = alternateHandler || function(data) {
              if (styleTarget) {
                elt.css(styleTarget, data);
              } else if (attrTarget) {
                elt.attr(attrTarget, data);
              } else {
                processICResponse(data, elt);
                if (verb != 'GET') {
                  refreshDependencies(getICAttribute(elt, 'v-ic-src'), elt);
                }
              }
            };
            var data = getParametersForElement(verb, elt, triggerOrigin);
            if(data) {
              handleRemoteRequest(elt, verb, url, data, success);
            }
          }
          maybeInvokeLocalAction(elt, "");
        }
      };

      var triggerDelay = closestAttrValue(elt, 'v-ic-trigger-delay');
      if (triggerDelay) {
        setTimeout(invokeRequest, parseInterval(triggerDelay));
      } else {
        invokeRequest();
      }
    }
  }

  function maybeInvokeLocalAction(elt, modifier) {
    var actions = getICAttribute(elt, 'ic' + modifier + '-action');
    if (actions) {
      invokeLocalAction(elt, actions, modifier);
    }
  }

  function invokeLocalAction(elt, actions, modifier) {
    var actionTargetVal = closestAttrValue(elt, 'ic' + modifier + '-action-target');
    if(actionTargetVal === null && modifier !== "") {
      actionTargetVal = closestAttrValue(elt, 'v-ic-action-target');
    }

    var target = null;
    if(actionTargetVal) {
      target = getTargetImpl(elt, 'v-ic-action-target');
    } else {
      target = getTarget(elt);
    }
    var actionArr = actions.split(";");

    var actionsArr = [];
    var delay = 0;

    $.each(actionArr, function(i, actionStr) {
      var actionDef = $.trim(actionStr);
      var action = actionDef;
      var actionArgs = [];
      if (actionDef.indexOf(":") > 0) {
        action = actionDef.substr(0, actionDef.indexOf(":"));
        actionArgs = computeArgs(actionDef.substr(actionDef.indexOf(":") + 1, actionDef.length));
      }
      if (action == "") {
        // ignore blanks
      } else if (action == "delay") {
        if (delay == null) {
          delay = 0;
        }
        delay += parseInterval(actionArgs[0] + "");  // custom interval increase
      } else {
        if (delay == null) {
          delay = 420; // 420ms default interval increase (400ms jQuery default + 20ms slop)
        }
        actionsArr.push([delay, makeApplyAction(target, action, actionArgs)]);
        delay = null;
      }
    });

    delay = 0;
    $.each(actionsArr, function(i, action) {
      delay += action[0];
      setTimeout(action[1], delay);
    });
  }

  function computeArgs(args) {
    try {
      return eval("[" + args + "]")
    } catch (e) {
      return [$.trim(args)];
    }
  }

  function makeApplyAction(target, action, args) {
    return function() {
      var func = target[action] || window[action];
      if (func) {
        func.apply(target, args);
      } else {
        log(target, "Action " + action + " was not found", "ERROR");
      }
    };
  }

  //============================================================
  // History Support
  //============================================================

  function newIntercoolerHistory(storage, history, slotLimit, historyVersion) {

    /* Constants */
    var HISTORY_SUPPORT_SLOT = 'v-ic-history-support';
    var HISTORY_SLOT_PREFIX = "v-ic-hist-elt-";

    /* Instance Vars */
    var historySupportData = JSON.parse(storage.getItem(HISTORY_SUPPORT_SLOT));
    var _snapshot = null;

    // Reset history if the history config has changed
    if (historyConfigHasChanged(historySupportData)) {
      log(getTargetForHistory($('body')), "Intercooler History configuration changed, clearing history", "INFO");
      clearHistory();
    }

    if (historySupportData == null) {
      historySupportData = {
        slotLimit: slotLimit,
        historyVersion: historyVersion,
        lruList: []
      };
    }

    /* Instance Methods  */
    function historyConfigHasChanged(historySupportData) {
      return historySupportData == null ||
      historySupportData.slotLimit != slotLimit ||
      historySupportData.historyVersion != historyVersion ||
      historySupportData.lruList == null
    }

    function clearHistory() {
      var keys = [];
      for (var i = 0; i < storage.length; i++) {
        if (storage.key(i).indexOf(HISTORY_SLOT_PREFIX) == 0) {
          keys.push(storage.key(i));
        }
      }
      for (var j = 0; j < keys.length; j++) {
        storage.removeItem(keys[j]);
      }
      storage.removeItem(HISTORY_SUPPORT_SLOT);
      historySupportData = {
        slotLimit: slotLimit,
        historyVersion: historyVersion,
        lruList: []
      };
    }

    function updateLRUList(url) {
      var lruList = historySupportData.lruList;
      var currentIndex = lruList.indexOf(url);
      var t = getTargetForHistory($('body'));
      // found in current list, shift it to the end
      if (currentIndex >= 0) {
        log(t, "URL found in LRU list, moving to end", "INFO");
        lruList.splice(currentIndex, 1);
        lruList.push(url);
      } else {
        // not found, add and shift if necessary
        log(t, "URL not found in LRU list, adding", "INFO");
        lruList.push(url);
        if (lruList.length > historySupportData.slotLimit) {
          var urlToDelete = lruList.shift();
          log(t, "History overflow, removing local history for " + urlToDelete, "INFO");
          storage.removeItem(HISTORY_SLOT_PREFIX + urlToDelete);
        }
      }

      // save history metadata
      storage.setItem(HISTORY_SUPPORT_SLOT, JSON.stringify(historySupportData));
      return lruList;
    }

    function saveHistoryData(restorationData) {
      var content = JSON.stringify(restorationData);
      try {
        storage.setItem(restorationData.id, content);
      } catch (e) {
        //quota error, nuke local cache
        try {
          clearHistory();
          storage.setItem(restorationData.id, content);
        } catch (e) {
          log(getTargetForHistory($('body')), "Unable to save intercooler history with entire history cleared, is something else eating " +
          "local storage? History Limit:" + slotLimit, "ERROR");
        }
      }
    }

    function makeHistoryEntry(html, yOffset, url) {
      var restorationData = {
        "url": url,
        "id": HISTORY_SLOT_PREFIX + url,
        "content": html,
        "yOffset": yOffset,
        "timestamp": new Date().getTime()
      };
      updateLRUList(url);
      // save to the history slot
      saveHistoryData(restorationData);
      return restorationData;
    }

    function addPopStateHandler(windowToAdd) {
      if (windowToAdd.onpopstate == null || windowToAdd.onpopstate['v-ic-on-pop-state-handler'] != true) {
        var currentOnPopState = windowToAdd.onpopstate;
        windowToAdd.onpopstate = function(event) {
          triggerEvent(getTargetForHistory($('body')), 'handle.onpopstate.ic');
          if (!handleHistoryNavigation(event)) {
            if (currentOnPopState) {
              currentOnPopState(event);
            }
          }
          triggerEvent(getTargetForHistory($('body')), 'pageLoad.ic');
        };
        windowToAdd.onpopstate['v-ic-on-pop-state-handler'] = true;
      }
    }

    function updateHistory() {
      if (_snapshot) {
        pushUrl(_snapshot.newUrl, currentUrl(), _snapshot.oldHtml, _snapshot.yOffset);
        _snapshot = null;
      }
    }

    function pushUrl(newUrl, originalUrl, originalHtml, yOffset) {

      var historyEntry = makeHistoryEntry(originalHtml, yOffset, originalUrl);
      history.replaceState({"v-ic-id": historyEntry.id}, "", "");

      var t = getTargetForHistory($('body'));
      var restorationData = makeHistoryEntry(t.html(), window.pageYOffset, newUrl);
      history.pushState({'v-ic-id': restorationData.id}, "", newUrl);

      triggerEvent(t, "pushUrl.ic", [t, restorationData]);
    }

    function handleHistoryNavigation(event) {
      var data = event.state;
      if (data && data['v-ic-id']) {
        var historyData = JSON.parse(storage.getItem(data['v-ic-id']));
        if (historyData) {
          processICResponse(historyData["content"], getTargetForHistory($('body')), true);
          if (historyData["yOffset"]) {
            window.scrollTo(0, historyData["yOffset"])
          }
          return true;
        } else {
          $.get(currentUrl(), {'v-ic-restore-history': true}, function(data, status) {
            var newDoc = createDocument(data);
            var replacementHtml = getTargetForHistory(newDoc).html();
            processICResponse(replacementHtml, getTargetForHistory($('body')), true);
          });
        }
      }
      return false;
    }

    function getTargetForHistory(elt) {
      var explicitHistoryTarget = elt.find(getICAttributeSelector('v-ic-history-elt'));
      if (explicitHistoryTarget.length > 0) {
        return explicitHistoryTarget;
      } else {
        return elt;
      }
    }

    function snapshotForHistory(newUrl) {
      var t = getTargetForHistory($('body'));
      triggerEvent(t, "beforeHistorySnapshot.ic", [t]);
      _snapshot = {
        newUrl: newUrl,
        oldHtml: t.html(),
        yOffset: window.pageYOffset
      };
    }

    function dumpLocalStorage() {
      var str = "";
      var keys = [];
      for (var x in storage) {
        keys.push(x);
      }
      keys.sort();
      var total = 0;
      for (var i in keys) {
        var size = (storage[keys[i]].length * 2);
        total += size;
        str += keys[i] + "=" + (size / 1024 / 1024).toFixed(2) + " MB\n";
      }
      return str + "\nTOTAL LOCAL STORAGE: " + (total / 1024 / 1024).toFixed(2) + " MB";
    }

    function supportData() {
      return historySupportData;
    }

    /* API */
    return {
      clearHistory: clearHistory,
      updateHistory: updateHistory,
      addPopStateHandler: addPopStateHandler,
      snapshotForHistory: snapshotForHistory,
      _internal: {
        addPopStateHandler: addPopStateHandler,
        supportData: supportData,
        dumpLocalStorage: dumpLocalStorage,
        updateLRUList: updateLRUList
      }
    };
  }

  function getSlotLimit() {
    return 20;
  }

  function refresh(val) {
    if (typeof val == 'string' || val instanceof String) {
      refreshDependencies(val);
    } else {
      fireICRequest(val);
    }
    return VicIntercooler;
  }

  var _history = null;
  try {
    _history = newIntercoolerHistory(localStorage, window.history, getSlotLimit(), .1);
  } catch(e) {
    log($('body'), "Could not initialize history", "WARN");
  }

  //============================================================
  // Local references transport
  //============================================================

  if($.ajaxTransport) {
    $.ajaxTransport("text", function(options, origOptions) {
        if (origOptions.url[0] == "#") {
          var ltAttr = fixICAttributeName("v-ic-local-");
          var src = $(origOptions.url);
          var rsphdr = [];
          var status = 200;
          var statusText = "OK";
          src.each(function(i, el) {
            $.each(el.attributes, function(j, attr) {
              if (attr.name.substr(0, ltAttr.length) == ltAttr) {
                var lhName = attr.name.substring(ltAttr.length);
                if (lhName == "status") {
                  var statusLine = attr.value.match(/(\d+)\s?(.*)/);
                  if (statusLine != null) {
                    status = statusLine[1];
                    statusText = statusLine[2];
                  } else {
                    status = "500";
                    statusText = "Attribute Error";
                  }
                } else {
                  rsphdr.push(lhName + ": " + attr.value);
                }
              }
            });
          });
          var rsp = src.length > 0 ? src.html() : "";
          return {
            send: function(reqhdr, completeCallback) {
              completeCallback(status, statusText, {html: rsp}, rsphdr.join("\n"));
            },
            abort: function() {
            }
          };
        } else {
          return null;
        }
      }
    );

  }

  //============================================================
  // Bootstrap
  //============================================================

  function init() {
    var elt = $('body');
    processNodes(elt);
    fireReadyStuff(elt);
    if(_history) {
      _history.addPopStateHandler(window);
    }
    if($.zepto) {
      $('body').data('zeptoDataTest', {});
      if(typeof($('body').data('zeptoDataTest')) == "string") {
        console.log("!!!! Please include the data module with Zepto!  Intercooler requires full data support to function !!!!")
      }
    }
    if (location.search && location.search.indexOf("v-ic-launch-debugger=true") >= 0) {
      VicIntercooler.debug();
    }
  }

  $(function() {
    init();
  });

  /* ===================================================
   * API
   * =================================================== */
  return {
    refresh: refresh,
    history: _history,
    triggerRequest: fireICRequest,
    processNodes: processNodes,
    closestAttrValue: closestAttrValue,
    verbFor: verbFor,
    isDependent: isDependent,
    getTarget: getTarget,
    processHeaders: processHeaders,
    setIsDependentFunction: function(func) {
      _isDependentFunction = func;
    },
    ready: function(readyHandler) {
      _readyHandlers.push(readyHandler);
    },
    debug: function() {
      var debuggerUrl = closestAttrValue('body', 'v-ic-debugger-url') ||
      "https://intercoolerreleases-leaddynocom.netdna-ssl.com/intercooler-debugger.js";
      $.getScript(debuggerUrl)
      .fail(function(jqxhr, settings, exception) {
        log($('body'), formatError(exception), "ERROR");
      });
    },
    _internal: {
      init: init,
      replaceOrAddMethod: replaceOrAddMethod,
      initEventSource: function(url) {
        return new EventSource(url);
      },
      globalEval: globalEval
    }
  };
})();
