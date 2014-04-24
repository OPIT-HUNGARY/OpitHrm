// Generated by CoffeeScript 1.7.1
(function() {
  var loadWeather, __dialog, __picker;

  $(document).data('notes', {});

  $.extend(true, $(document).data('notes'), {
    funcs: {
      initDateInputs: function($container) {
        var $dateInputs;
        $dateInputs = $container ? $container.find('input[type=date]') : $('input[type=date]');
        if (!Modernizr.inputtypes.date) {
          return $dateInputs.each(function() {
            var id, name;
            name = $(this).attr('name');
            id = $(this).attr('id');
            $(this).after('<input type="hidden" name="' + name + '" id="altDate' + id + '" value="' + $.datepicker.formatDate($.datepicker.ISO_8601, new Date($(this).val())) + '" />');
            return $(this).datepicker({
              altField: '#altDate' + id,
              altFormat: $.datepicker.ISO_8601
            });
          });
        }
      },
      deleteSingleRequest: function(type, self) {
        var $checkbox;
        $checkbox = self.closest('tr').find(':checkbox');
        $checkbox.prop('checked', true);
        $('<div></div>').html("Are you sure you want to delete the travel " + type + "?").dialog({
          title: 'Travel request removal',
          buttons: {
            Yes: function() {
              $.ajax({
                method: 'POST',
                url: type === 'expense' ? Routing.generate('OpitNotesTravelBundle_expense_delete') : Routing.generate('OpitNotesTravelBundle_travel_delete'),
                data: {
                  'id': self.data('id')
                }
              }).done(function(data) {
                if (data === '0') {
                  self.parent().parent().remove();
                }
              }).fail(function() {
                return $('<div></div>').html("The travel " + type + " could not be deleted due to an error.").dialog({
                  title: 'Error'
                });
              });
              $(document).data('notes').funcs.initListPageListeners();
              $(document).data('notes').funcs.initPager();
              $(this).dialog('close');
            },
            No: function() {
              $checkbox.prop('checked', false);
              $(this).dialog('close');
            }
          },
          close: function() {
            $(this).dialog('destroy');
          }
        });
      },
      deleteAction: function(title, message, url, identifier) {
        if ($(identifier + ':checked').length > 0) {
          return $('<div></div>').html('Are you sure you want to delete the ' + message + '?').dialog({
            title: title,
            buttons: {
              Yes: function() {
                $.ajax({
                  method: 'POST',
                  url: url,
                  data: $(identifier).serialize()
                }).done(function(data) {
                  if (data[0].userRelated) {
                    return $(document).data('notes').funcs.showAlert(data, 'create', 'Deletion not allowed for roles with relations', true);
                  } else {
                    $(identifier + ':checked').closest('tr').remove();
                  }
                }).fail(function() {
                  return $('<div></div>').html('The ' + message + ' could not be deleted due to an error.').dialog({
                    title: 'Error'
                  });
                });
                $(document).data('notes').funcs.initListPageListeners();
                $(document).data('notes').funcs.initPager();
                $(this).dialog('close');
              },
              No: function() {
                $(identifier + ':checkbox').attr('checked', false);
                $(this).dialog('close');
              }
            },
            close: function() {
              $(this).dialog('destroy');
            }
          });
        }
      },
      showAlert: function(response, actionType, message, forceClass) {
        var errorString, i, returnVal, _i, _len, _ref;
        $('#reply-message').addClass("alert-message");
        if (typeof response === !"string") {
          response = $.parseJSON(response);
        }
        if ((response[0] != null) && response[0].response === 'error') {
          if ("update" === actionType || "create" === actionType) {
            errorString = "<ul>";
            _ref = response[0].errorMessage;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              i = _ref[_i];
              errorString += "<li>" + i + "</li>";
            }
            errorString += "</ul>";
            $('#reply-message').html("<i class='fa fa-exclamation-triangle'></i> <strong>Error messages:</strong>" + errorString).removeClass('success-message').addClass('error-message');
          } else if ("delete" === actionType) {
            $('#list-reply-message').html("<i class='fa fa-exclamation-triangle'></i> Error, while tried to delete the user(s)! <i class='float-right fa fa-chevron-circle-up'></i> ").removeClass('success-message').addClass('error-message').fadeIn(200).delay(5000).slideUp(1000);
          }
          returnVal = false;
        } else {
          $('#list-reply-message').html("<i class='fa fa-check-square'></i> " + message + "! <i class='float-right fa fa-chevron-circle-up'></i> ").addClass("alert-message").addClass('success-message').fadeIn(200).delay(2000).slideUp(1000);
          $('#reply-message').removeClass('alert-message error-message').empty();
          returnVal = true;
        }
        if (forceClass) {
          $('#list-reply-message').removeClass('success-message').addClass('error-message');
        }
        return returnVal;
      },
      createButton: function(text, classes, id, $parent, redirectAction) {
        var $button;
        if ($parent == null) {
          $parent = '';
        }
        if (redirectAction == null) {
          redirectAction = '';
        }
        $button = $('<div>').html(text).addClass(classes).attr('id', id);
        if ('' !== redirectAction) {
          $button.on('click', function() {
            return window.location.href = Routing.generate(redirectAction);
          });
        }
        if ('' !== parent) {
          $parent.append($button);
        }
        return $button;
      },
      makeElementToggleAble: function(parent, $toggleItems, elementToToggle) {
        if (elementToToggle == null) {
          elementToToggle = '';
        }
        return $toggleItems.each(function() {
          var $parent, $toggleIcon, self;
          $parent = $(this).find(parent);
          self = $(this);
          $toggleIcon = $('<i>').addClass('fa fa-chevron-up toggle-icon').addClass('color-white background-color-orange border-radius-5 cursor-pointer float-right');
          $toggleIcon.on('click', function() {
            var $elementToToggle;
            if ('' !== elementToToggle) {
              $elementToToggle = self.find(elementToToggle);
              if (!$elementToToggle.is(':animated')) {
                $toggleIcon.toggleClass('fa-chevron-down');
                return $elementToToggle.slideToggle();
              }
            } else {
              if (!$parent.next().is(':animated')) {
                $toggleIcon.toggleClass('fa-chevron-down');
                return $parent.next().slideToggle();
              }
            }
          });
          return $parent.append($toggleIcon);
        });
      }
    }
  });


  /*
   * jQuery datepicker extension
   * Datepicker extended by custom rendering possibility
   *
   * @author Sven Henneböle <henneboele@opit.hu>
   * @version 1.0
   * @depends jQuery
   *
   * @param object  options List of options
   */

  __picker = $.fn.datepicker;

  $.fn.datepicker = function(options) {
    var $self, defaultOptions;
    __picker.apply(this, [options]);
    $self = this;
    options = options || {};
    defaultOptions = {
      wrapper: '<div></div>',
      indicatorIcon: $('<i>')
    };
    $.extend(true, defaultOptions, options);
    if (options.showOn !== 'button') {
      $self.attr({
        readonly: 'readonly'
      }).addClass('icon-prefix-indent');
      defaultOptions.indicatorIcon.addClass('fa fa-calendar position-absolute input-prefix-position cursor-pointer');
      defaultOptions.indicatorIcon.click(function() {
        return $(this).parent().parent().children('input').focus();
      });
      $self.before(defaultOptions.wrapper);
      $self.prev().append(defaultOptions.indicatorIcon);
    }
    return $self;
  };

  $.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
    _title: function(title) {
      if (!this.options.title) {
        title.html("&#160;");
      } else {
        title.html(this.options.title);
      }
    }
  }));

  __dialog = $.fn.dialog;

  $.fn.dialog = function(options) {
    __dialog.apply(this, [options]);
    this.mCustomScrollbar();
    return $(this).on('dialogresizestop', function(event, ui) {
      return $(this).mCustomScrollbar('update');
    });
  };

  $(document).ajaxComplete(function(event, XMLHttpRequest, ajaxOptions) {
    var id;
    id = XMLHttpRequest.responseText.match(/id="([\w|-]+)"/);
    if ((id != null ? id[1] : void 0) != null) {
      return $("#" + id[1] + " *[title]").tipsy();
    }
  });

  $(document).ajaxError(function(event, request, settings) {
    var $sessionTimeout, loginUrl;
    if (window.location.href.indexOf('login') <= -1 && 403 === request.status) {
      loginUrl = Routing.generate('OpitNotesUserBundle_security_login');
      $sessionTimeout = $('<div id="dialog-travelrequest-preview"></div>').html("Your session has timed out please <a href='" + loginUrl + "'>login</a> again.");
      return $sessionTimeout.dialog({
        title: '<i class="fa fa-exclamation-circle"></i> Session timeout',
        width: 550,
        maxHeight: $(window).outerHeight() - 100,
        modal: true,
        buttons: {
          Login: function() {
            return window.location.href = loginUrl;
          }
        }
      });
    }
  });

  $(document).keydown(function(e) {
    if (e.ctrlKey && e.altKey && e.keyCode === 87) {
      if ($('#weather-dialog').length > 0) {
        return;
      }
      if (!$.fn.simpleWeather) {
        return $.getScript('/libs/simpleWeather/js/jquery.simpleWeather.min.js', function(data, textStatus, jqxhr) {
          $('head').append($('<link rel="stylesheet" type="text/css" />').attr('href', '/libs/simpleWeather/css/simpleWeather.css'));
          return loadWeather('Budapest');
        });
      }
    }
  });

  loadWeather = function(location, woeid, reinit) {
    var $weatherContainer;
    if (reinit == null) {
      reinit = false;
    }
    if (!reinit) {
      $weatherContainer = $('<div id="weather-dialog" />');
    } else {
      $weatherContainer = $('#weather-dialog');
    }
    return $.simpleWeather({
      location: location,
      woeid: woeid,
      unit: 'c',
      success: function(weather) {
        var html;
        html = "<h2><i class=\"icon-" + weather.code + "\"></i>" + weather.temp + "&deg;" + weather.units.temp + "</h2> <ul> <li>" + weather.city + ", " + weather.region + "</li> <li class=\"currently\">" + weather.currently + "</li> <li>" + weather.wind.direction + " " + weather.wind.speed + " " + weather.units.speed + "</li> </ul> <button class=\"js-geolocation\" style=\"display: none;\">Use Your Location</button>";
        $weatherContainer.html(html);
        if (!reinit) {
          $weatherContainer.dialog({
            width: 550,
            height: 350,
            close: function() {
              return $(this).dialog('destroy');
            }
          });
        }
        if (Modernizr.geolocation) {
          $('.js-geolocation').on('click.weather', function() {
            return navigator.geolocation.getCurrentPosition(function(position) {
              return loadWeather("" + position.coords.latitude + "," + position.coords.longitude, '', true);
            });
          });
          $('.js-geolocation').show();
        } else {
          $('.js-geolocation').off('click.weather');
          $('.js-geolocation').hide();
        }
      },
      error: function(error) {
        console.log("<p>" + error + "</p>");
      }
    });
  };

  $(document).ready(function() {
    $(document).data('notes').funcs.initDateInputs();
    return $('[title]').each(function() {
      return $(this).tipsy();
    });
  });

}).call(this);
