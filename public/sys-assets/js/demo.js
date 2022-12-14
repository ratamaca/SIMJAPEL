(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory();
  } else {
    root.pxDemo = factory();
  }
}(this, function() {
  'use strict';

  function attachOnLoadHandler(cb) {
    if (window.attachEvent) {
      window.attachEvent('onload', cb);
    } else if (window.onload) {
      var curronload = window.onload;

      window.onload = function(evt) {
        curronload(evt);
        cb(evt);
      };
    } else {
      window.onload = cb;
    }
  }

  var locationParts = String(document.location).replace(/\\/g, '/').split('/');
  var DIST_PATH = base + 'sys-assets';

  var pxDemo = (function() {

    // Constants

    var COLORS = [
      '#0288D1',
      '#FF4081',
      '#4CAF50',
      '#D32F2F',
      '#FFC107',
      '#673AB7',
      '#FF5722',
      '#CDDC39',
      '#795548',
      '#607D8B',
      '#009688',
      '#E91E63',
      '#9E9E9E',
      '#E040FB',
      '#00BCD4',
    ];

    var BACKGROUNDS = [
      DIST_PATH + '/images/bgs/1.jpg',
    ];

    var THEMES = [
      'default',
      'asphalt',
      'purple-hills',
      'adminflare',
      'dust',
      'frost',
      'fresh',
      'silver',
      'clean',
      'white',
      'candy-black',
      'candy-blue',
      'candy-red',
      'candy-orange',
      'candy-green',
      'candy-purple',
      'candy-cyan',
      'mint-dark',
      'dark-blue',
      'dark-red',
      'dark-orange',
      'dark-green',
      'dark-purple',
      'dark-cyan',
      'darklight-blue',
      'darklight-red',
      'darklight-orange',
      'darklight-green',
      'darklight-purple',
      'darklight-cyan',
    ];

    var demoSettings = (function loadDemoSettings() {
      var result = {
        theme:         THEMES[0],
      };

      var cookie = ';' + document.cookie + ';';

      var re;
      var found;

      for (var key in result) {
        if (Object.prototype.hasOwnProperty.call(result, key)) {
          re = new RegExp(';\\s*' + encodeURIComponent('px-demo-' + key) + '\\s*=\\s*([^;]+)\\s*;');
          found = cookie.match(re);

          if (found) {
            result[key] = decodeURIComponent(found[1]);
          }
        }
      }

      // Guards
      result.footer        = [ 'static', 'bottom', 'fixed' ].indexOf(result.footer) !== -1 ? result.footer : 'bottom';
      result.theme         = THEMES.indexOf(result.theme) !== -1 ? result.theme : THEMES[0];

      return result;
    })();

    var CURRENT_THEME = demoSettings.theme;

    function setSidebarState(state) {
      $('#px-demo-sidebar input').prop('disabled', state === 'disabled');
      $('#px-demo-sidebar-loader')[CURRENT_THEME.indexOf('dark') === -1 ? 'removeClass': 'addClass']('form-loading-inverted');
      $('#px-demo-sidebar-loader')[state === 'disabled' ? 'show': 'hide']();
    }

    // Private

    function updateDemoSettings(settings) {
      $.extend(demoSettings, settings);

      for (var key in demoSettings) {
        if (Object.prototype.hasOwnProperty.call(demoSettings, key)) {
          document.cookie =
            encodeURIComponent('px-demo-' + key) + '=' +
            encodeURIComponent(demoSettings[key])+";path=/";
        }
      }
    }

    function _createStylesheetLink(href, className, cb) {
      var head = document.getElementsByTagName('head')[0];
      var link = document.createElement('link');

      link.className = className;
      link.type      = 'text/css';
      link.rel       = 'stylesheet';
      link.href      = href;

      var r = false;

      link.onload = link.onreadystatechange = function() {
        if (!r && (!this.readyState || this.readyState === 'complete')) {
          r = true;

          var links = document.getElementsByClassName(className);

          if (links.length > 1) {
            for (var i = 1, l = links.length; i < l; i++) {
              head.removeChild(links[i]);
            }
          }

          document.documentElement.className =
            document.documentElement.className.replace(/\s*px-demo-no-transition/, '');
        }

        if (cb) { cb(); }
      };

      document.documentElement.className += ' px-demo-no-transition';

      return link;
    }

    function setTheme(themeName) {
      if (themeName === CURRENT_THEME) { return; }

      CURRENT_THEME = themeName;

      var _isDark   = themeName.indexOf('dark') !== -1;
      var _isRtl    = document.getElementsByTagName('html')[0].getAttribute('dir') === 'rtl';
      var themePath = DIST_PATH + '/css/themes/' + themeName + (_isRtl ? '.rtl' : '') + '.min.css';

      var linksToLoad = [];

      // Switch between light and dark assets

      var _assetCls = [ 'px-demo-stylesheet-core', 'px-demo-stylesheet-bs', 'px-demo-stylesheet-widgets' ];
      var _assetLink;

      function _assetReplacer(match, path, name, suffix) {
        return path + name.replace('-dark', '') + (_isDark ? '-dark' : '') + suffix;
      }

      for (var _i = 0, _l = _assetCls.length; _i < _l; _i++) {
        _assetLink = (document.getElementsByClassName(_assetCls[_i]) || [])[0] || null;

        if (_assetLink) {
          linksToLoad.push(
            [ _assetLink.getAttribute('href').replace(/^(.*?)([^\/\.]+)((?:\.rtl)?(?:\.min)?\.css)$/, _assetReplacer), _assetCls[_i] ]
          );
        }
      }

      linksToLoad.push([ themePath, 'px-demo-stylesheet-theme' ]);

      var linksContainer = document.createDocumentFragment();
      var loadedLinks = 0;

      function _cb() {
        loadedLinks++;

        if (loadedLinks < linksToLoad.length) { return; }

        setSidebarState('enabled');
      }

      for (var i = 0, l = linksToLoad.length; i < l; i++) {
        linksContainer.appendChild(_createStylesheetLink(linksToLoad[i][0], linksToLoad[i][1], _cb));
      }

      document.getElementsByTagName('head')[0].insertBefore(
        linksContainer,
        document.getElementsByClassName('px-demo-stylesheet-core')[0]
      );
    }

    function loadTheme() {
      setTheme(demoSettings.theme);
    }

    function loadRtl() {
      if (demoSettings.rtl !== '1') { return; }

      document.getElementsByTagName("html")[0].setAttribute('dir', 'rtl');
    }

    function placeNav(side) {
      var navEl  = document.getElementById('px-demo-nav');

      navEl.className =
        navEl.className
          .replace(new RegExp("^\\s*px-nav-(?:left|right)\\s*", 'i'), '')
          .replace(new RegExp("\\s*px-nav-(?:left|right)\\s*$", 'i'), '')
          .replace(new RegExp("\\s+px-nav-(?:left|right)\\s+", 'ig'), ' ') +
        ' px-nav-' + side;
    }

    function setFooterPosition(pos) {
      var footer = document.getElementById('px-demo-footer');

      if (!footer) { return; }

      footer.className = footer.className
        .replace(/^\s*px-footer-(?:bottom|fixed)\s*/i, '')
        .replace(/\s*px-footer-(?:bottom|fixed)\s*$/i, '')
        .replace(/\s+px-footer-(?:bottom|fixed)\s+/gi, ' ') +
        ((pos === 'bottom' || pos === 'fixed') ? (' px-footer-' + pos) : '');
    }

    function capitalizeAllLetters(str, splitter) {
      var parts = str.split(splitter || ' ');

      for (var i = 0, l = parts.length; i < l; i++) {
        parts[i] = parts[i].charAt(0).toUpperCase() + parts[i].slice(1);
      }

      return parts.join(' ');
    }

    // Public

    function shuffle(a) {
      var j;
      var x;
      var i;

      for (i = a.length; i; i -= 1) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
      }
    }

    function getRandomData(max, min) {
      return Math.floor(Math.random() * ((max || 100) - (min || 0))) + (min || 0);
    }

    function getRandomColors(count) {
      if (count && count > COLORS.length) {
        throw new Error('Have not enough colors');
      }

      var clrLeft = count || COLORS.length;
      var source  = [].concat(COLORS);
      var result  = [];

      while (clrLeft-- > 0) {
        result.unshift(source[source.length > 1 ? getRandomData(source.length - 1) : 0]);
        source.splice(source.indexOf(result[0]), 1);
      }

      shuffle(result);

      return result;
    }

    function initializeDemo() {
      $('input[name="px-demo-current-theme"]').on('change', function() {
        setSidebarState('disabled');

        var themeName = THEMES.indexOf(this.value) !== -1 ? this.value : THEMES[0];

        updateDemoSettings({ theme: themeName });
        setTheme(themeName);
      });


      // Initialize "close" button
      //

      $('#demo-px-nav-box .close').on('click', function(e) {
        e.preventDefault();

        var $box     = $(this).parents('.px-nav-box').addClass('no-animation');
        var $wrapper = $('<div></div>').css({ overflow: 'hidden' });

        // Remove close button
        $(this).remove();

        $wrapper
          .insertBefore($box)
          .append($box)
          .animate({
            opacity: 0,
            height:  'toggle',
          }, 400, function() {
            $wrapper.remove();
          });
      });
    }

    function initializeBgsDemo(selector, defaultBgIndex, overlay, afterCall) {
      var isBgSet = false;

      if (defaultBgIndex) {
        $(selector).pxResponsiveBg({
          backgroundImage: BACKGROUNDS[defaultBgIndex - 1],
          overlay:         overlay,
        });

        isBgSet = true;

        if (afterCall) { afterCall(isBgSet); }
      }

      var elementsHtml = '<a href="#" class="px-demo-bgs-container px-demo-bgs-clear">&times;</a>';

      for (var i = 0, l = BACKGROUNDS.length; i < l; i++) {
        elementsHtml += '<a href="#" class="px-demo-bgs-container"><img src="' + BACKGROUNDS[i] + '" alt=""></a>';
      }

      var $block = $('<div class="px-demo-bgs">' + elementsHtml + '</div>');

      $block.on('click', '.px-demo-bgs-container', function(e) {
        e.preventDefault();

        var $container = $(this);

        if ($container.hasClass('px-demo-bgs-clear')) {
          if (!isBgSet) { return; }

          $(selector).pxResponsiveBg('destroy', true);

          isBgSet = false;

          if (afterCall) { afterCall(isBgSet); }
        } else {
          if (isBgSet) { $(selector).pxResponsiveBg('destroy'); }

          $(selector).pxResponsiveBg({
            backgroundImage: $container.find('> img').attr('src'),
            overlay:         overlay,
          });

          isBgSet = true;

          if (afterCall) { afterCall(isBgSet); }
        }
      });

      $('body').append($block);
    }

    function initializeDemoSidebar() {
      var sidebarEl = document.createElement('DIV');

      sidebarEl.id          = 'px-demo-sidebar';
      sidebarEl.className   = 'px-sidebar-right bg-primary';
      sidebarEl.style.width = '242px';
      sidebarEl.innerHTML   = '<a href="#" id="px-demo-sidebar-toggle" class="bg-primary b-y-1 b-l-1 text-default" data-toggle="sidebar" data-target="#px-demo-sidebar"><i class="fa fa-cogs"></i></a><div id="px-demo-sidebar-loader" class="form-loading form-loading-inverted"></div>';

      var contentEl = document.createElement('DIV');

      contentEl.className = 'px-sidebar-content';
      sidebarEl.appendChild(contentEl);

      var content  = '';
      var navEl    = document.getElementById('px-demo-nav');

      content += '<div id="px-demo-togglers">';

      // Themes

      content += '<h6 class="px-demo-sidebar-header bg-primary darker b-y-1">SETTING THEMES</h6>';
      content += '<div class="px-demo-themes-list clearfix bg-primary">';

      for (var i = 0, l = THEMES.length; i < l; i++) {
        content += '<label class="px-demo-themes-item">';

          content += '<input type="radio" class="px-demo-themes-toggler" name="px-demo-current-theme" value="' + THEMES[i] + '"' + (demoSettings.theme === THEMES[i] ? ' checked' : '') + '>';
          content += '<img src="' + DIST_PATH + '/images/themes/' + THEMES[i] + '.png" class="px-demo-themes-thumbnail">';
          content += '<div class="px-demo-themes-title font-weight-semibold"><span class="text-white">' + capitalizeAllLetters(THEMES[i], '-') + '</span><div class="bg-primary"></div></div>';

        content += '</label>';
      }

      content += '</div>';

      contentEl.innerHTML = content;
      document.body.appendChild(sidebarEl);
    }

    // Return

    return {
      COLORS: COLORS,

      shuffle:         shuffle,
      getRandomData:   getRandomData,
      getRandomColors: getRandomColors,

      initializeDemo:        initializeDemo,
      initializeBgsDemo:     initializeBgsDemo,
      initializeDemoSidebar: initializeDemoSidebar,

      loadTheme: loadTheme,
      loadRtl:   loadRtl,
    };
  })();

  return pxDemo;
}));
