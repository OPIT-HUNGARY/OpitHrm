// Generated by CoffeeScript 1.7.1
(function() {
  var __picker;

  $(document).data('notes', {});

  $.extend(true, $(document).data('notes'), {
    funcs: {
      clientSideListOrdering: function($self, inverse) {
        var header, index;
        header = $self.parent();
        index = header.index();
        header.closest('table').find('td').filter(function() {
          return $(this).index() === index;
        }).sort(function(a, b) {
          a = $(a).text();
          b = $(b).text();
          if ((isNaN(a) || isNaN(b) ? a > b : +a > +b)) {
            if (inverse) {
              return -1;
            } else {
              return 1;
            }
          } else {
            if (inverse) {
              return 1;
            } else {
              return -1;
            }
          }
        }, function() {
          return this.parentNode;
        });
        inverse = !inverse;
        $('#list-table').find('.fa-sort').removeClass('fa-sort-desc').removeClass('fa-sort-asc');
        $self.removeClass('fa-sort-asc').addClass(inverse ? 'fa-sort-desc' : 'fa-sort-asc');
        return inverse;
      },
      serverSideListOrdering: function($self, dataField, url, toRelplace) {
        var $form, index, order;
        index = $self.parent().index();
        $form = $('#searchFormWrapper').find('form');
        order = $form.find('#order_dir').val();
        order = order === 'desc' ? 'asc' : 'desc';
        $form.find('#order_field').val(dataField);
        $form.find('#order_dir').val(order);
        return $.ajax({
          method: 'POST',
          url: Routing.generate(url),
          data: 'showList=1&' + $form.serialize()
        }).done(function(data) {
          $('#' + toRelplace).html(data);
          $(document).data('notes').funcs.initPager();
          return $('#' + toRelplace).find('th').eq(index).find('i').addClass(order === 'desc' ? 'fa-sort-desc' : 'fa-sort-asc');
        });
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
      changeTravelStatus: function(statusId, travelRequestId, firstStatusId) {
        var reloadPage;
        reloadPage = true;
        return $.ajax({
          method: 'POST',
          url: Routing.generate('OpitNotesTravelBundle_request_state'),
          data: {
            'statusId': statusId,
            'travelRequestId': travelRequestId,
            'firstStatusId': firstStatusId
          }
        }).done(function(data) {
          var dialogWidth;
          if (data === 'error') {
            reloadPage = false;
            dialogWidth = 550;
            $('<div id="dialog-show-details-tr"></div>').html('You cannot change the status of the travel request because it has been already changed.').dialog({
              open: function() {
                return $('.ui-dialog-title').append('<i class="fa fa-exclamation-triangle"></i> Status cannot be changed');
              }
            });
            return {
              width: dialogWidth,
              maxHeight: $(window).outerHeight() - 100,
              modal: true,
              buttons: {
                Reload: function() {
                  location.reload();
                }
              }
            };
          }
        }).complete(function() {
          if (reloadPage === true) {
            return location.reload();
          }
        }).fail(function(data) {
          return console.warn('An error occured while setting new status for the request.');
        });
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
        $button = $('<div>');
        $button.html(text);
        $button.addClass(classes);
        $button.attr('id', id);
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
          $toggleIcon = $('<i>');
          $toggleIcon.addClass('fa fa-chevron-up default-right toggle-icon');
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
      },
      initListPageListeners: function() {
        $('.status-history').click(function(event) {
          event.preventDefault();
          return $.ajax({
            method: 'POST',
            url: Routing.generate('OpitNotesTravelBundle_travel_states_history'),
            data: {
              'id': $(this).find('.fa-book').data('id')
            }
          }).done(function(data) {
            var dialogWidth;
            dialogWidth = 550;
            $('<div id="dialog-show-details-tr"></div>').html(data).dialog({
              open: function() {
                return $('.ui-dialog-title').append('<i class="fa fa-book"></i> Status history');
              },
              width: dialogWidth,
              maxHeight: $(window).outerHeight() - 100,
              modal: true,
              buttons: {
                Close: function() {
                  $('#dialog-show-details-tr').dialog('destroy');
                }
              }
            });
          });
        });
        $('#travel_list #list-table').on('click', '.clickable', function() {
          var $changeState, firstStatusId, travelRequestId;
          $changeState = $(this).closest('tr').find('.changeState');
          travelRequestId = $(this).attr('data-tr-id');
          firstStatusId = $(this).parent().find('option:first-child').val();
          $.ajax({
            method: 'POST',
            url: Routing.generate('OpitNotesTravelBundle_travel_show_details'),
            data: {
              'id': travelRequestId
            }
          }).done(function(data) {
            var dialogWidth;
            dialogWidth = 550;
            $('<div id="dialog-show-details-tr"></div>').html(data).dialog({
              open: function() {
                return $('.ui-dialog-title').append('<i class="fa fa-list-alt"></i> Details');
              },
              width: dialogWidth,
              maxHeight: $(window).outerHeight() - 100,
              modal: true
            }, firstStatusId === '1' || firstStatusId === '3' ? {
              buttons: {
                'Send for approval': function() {
                  $changeState.addClass('dropdown-disabled');
                  $(document).data('notes').funcs.changeTravelStatus(2, travelRequestId, firstStatusId);
                  return $('#dialog-show-details-tr').dialog('destroy');
                },
                Close: function() {
                  $('#dialog-show-details-tr').dialog('destroy');
                }
              }
            } : {
              buttons: {
                Close: function() {
                  $('#dialog-show-details-tr').dialog('destroy');
                }
              }
            });
          });
        });
        $('.icon-disabled').on('click', function(event) {
          return event.preventDefault();
        });
        $('#list-table th .fa-trash-o').click(function() {
          $('.deleteMultipleTravelRequest').checkAll();
          return $('.deleteMultipleTravelExpense').checkAll();
        });
        $('.deleteSingeTravelRequest').click(function(event) {
          event.preventDefault();
          return $(document).data('notes').funcs.deleteSingleRequest('request', $(this));
        });
        return $('#delete').click(function() {
          var checkBoxClass, errorText, message, selectedTravelRequestRow, title, travelRequests, url, warningMessage;
          if ($('#userlistWrapper').length === 1) {
            title = 'User delete';
            message = 'user(s)';
            url = Routing.generate('OpitNotesUserBundle_user_delete');
            $(document).data('notes').funcs.deleteAction(title, message, url, '.list-delete-user');
            return false;
          } else if ($('#travel_list').length === 1) {
            warningMessage = 'Are you sure you want to delete the selected travel requests?';
            checkBoxClass = '.deleteMultipleTravelRequest';
            url = Routing.generate('OpitNotesTravelBundle_travel_delete');
            title = 'Travel request removal';
            errorText = 'The travel request could not be deleted due to an error.';
          } else {
            return false;
          }
          travelRequests = [];
          selectedTravelRequestRow = [];
          $(checkBoxClass).each(function() {
            if ($(this).is(':checked')) {
              travelRequests.push($(this).val());
              return selectedTravelRequestRow.push($(this).parent().parent());
            }
          });
          $('<div></div>').html(warningMessage).dialog({
            title: title,
            buttons: {
              Yes: function() {
                $.ajax({
                  method: 'POST',
                  url: url,
                  data: {
                    'id': travelRequests
                  }
                }).done(function(data) {
                  $(selectedTravelRequestRow).each(function() {
                    return $(this).remove();
                  });
                }).fail(function() {
                  return $('<div></div>').html(errorText).dialog({
                    title: 'Error'
                  });
                });
                $(this).dialog('close');
              },
              No: function() {
                $(this).dialog('close');
              }
            },
            close: function() {
              $(this).dialog('destroy');
            }
          });
        });
      },
      initPager: function() {
        var maxVisiblepages, newSelectedPage, requestUrl, selectedPageOffset, totalNumberOfPages;
        selectedPageOffset = $('#pager').data('offset');
        maxVisiblepages = $('#pager').data('max');
        newSelectedPage = $('#pager').find('[data-offset="' + selectedPageOffset + '"]');
        newSelectedPage.addClass('selected-page');
        totalNumberOfPages = $('#pager').data('pages');
        requestUrl = $('#pager').data('url');
        if (selectedPageOffset === totalNumberOfPages) {
          $('.fa-caret-right').addClass('visibility-hidden');
        }
        if (selectedPageOffset === 1) {
          $('.fa-caret-left').addClass('visibility-hidden');
        }
        if (totalNumberOfPages < maxVisiblepages) {
          $('.fa-caret-left').addClass('visibility-hidden');
          $('.fa-caret-right').addClass('visibility-hidden');
        }
        $('#pager').on('mousedown', 'span', function(event) {
          var $form, offset, requestData, self;
          self = $(this);
          offset = $(this).data('offset');
          $form = $('#searchFormWrapper').find('form');
          requestData = "offset=" + (offset - 1);
          if ($form.formIsEmpty() === true) {
            requestData = requestData + '&' + $form.serialize();
          }
          return $.ajax({
            method: 'POST',
            url: requestUrl,
            data: requestData
          }).done(function(data) {
            if (data.indexOf('error') < 0) {
              $('#list-table').parent().replaceWith(data);
              $(document).data('notes').funcs.initListPageListeners();
              return $(document).data('notes').funcs.initPager();
            }
          });
        });
        return $('#pager i').on('mousedown', function(event) {
          var $form, $selectedPage, offset, requestData, self;
          self = $(this);
          $selectedPage = $('.selected-page');
          if ($(this).hasClass('fa-caret-left')) {
            offset = selectedPageOffset - 1;
          } else if ($(this).hasClass('fa-caret-right')) {
            offset = selectedPageOffset + 1;
          }
          $form = $('#searchFormWrapper').find('form');
          requestData = "offset=" + (offset - 1);
          if ($form.formIsEmpty() === true) {
            requestData = requestData + '&' + $form.serialize();
          }
          return $.ajax({
            method: 'POST',
            url: requestUrl,
            data: requestData
          }).done(function(data) {
            var $newPagerItem, $pager, max, num, pages, _i, _ref;
            $selectedPage.removeClass('selected-page');
            $('#pager span').each(function() {
              return $(this).removeClass('selected-page');
            });
            $pager = $('#pager');
            $('#list-table').parent().replaceWith(data);
            offset = $('#pager').data('offset');
            pages = $('#pager').data('pages');
            max = $('#pager').data('max');
            if (offset > max) {
              if (self.hasClass('fa-caret-right')) {
                $('#pager span').remove();
                for (num = _i = 0, _ref = max - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; num = 0 <= _ref ? ++_i : --_i) {
                  $newPagerItem = $('<span>');
                  $newPagerItem.html(offset - num);
                  $newPagerItem.attr('data-offset', offset - num);
                  $newPagerItem.insertAfter($('#pager .fa-caret-left'));
                }
              }
              if (self.hasClass('fa-caret-left')) {
                if (offset < $('#pager').first().data('offset')) {
                  console.log('');
                } else {
                  $('#pager').html($pager.html());
                }
              }
            }
            $(document).data('notes').funcs.initListPageListeners();
            return $(document).data('notes').funcs.initPager();
          });
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
      wrapper: '<span class="relative"></span>',
      indicatorIcon: $('<i>')
    };
    $.extend(true, defaultOptions, options);
    if (options.showOn !== 'button') {
      $self.attr({
        readonly: 'readonly'
      }).addClass('icon-prefix-indent');
      defaultOptions.indicatorIcon.addClass('fa fa-calendar absolute input-prefix-position pointer');
      defaultOptions.indicatorIcon.click(function() {
        return $(this).parent().parent().children('input').focus();
      });
      $self.before(defaultOptions.wrapper);
      $self.prev().append(defaultOptions.indicatorIcon);
    }
    return $self;
  };

  if (!Modernizr.inputtypes.date) {
    $('input[type=date]').each(function() {
      var id, name;
      name = $(this).attr('name');
      id = $(this).attr('id');
      $(this).after('<input type="hidden" name="' + name + '" id="altDate' + id + '" />');
      return $(this).datepicker({
        altField: '#altDate' + id,
        altFormat: 'yy-mm-dd'
      });
    });
  }

  $(document).ajaxComplete(function(event, XMLHttpRequest, ajaxOptions) {
    var id;
    id = XMLHttpRequest.responseText.match(/id="([\w|-]+)"/);
    if ((id != null ? id[1] : void 0) != null) {
      return $("#" + id[1] + " *[title]").tipsy();
    }
  });

  $(document).ajaxError(function(event, request, settings) {
    var $sessionTimeout;
    if (window.location.href.indexOf('login') < -1) {
      if (settings.url.indexOf('unread') > -1) {
        $sessionTimeout = $('<div id="dialog-travelrequest-preview"></div>').html('Your session has timed out please login again.');
        return $sessionTimeout.dialog({
          open: function() {
            return $('.ui-dialog-title').append('<i class="fa fa-exclamation-circle"></i> Session timeout');
          },
          width: 550,
          maxHeight: $(window).outerHeight() - 100,
          modal: true,
          buttons: {
            Login: function() {
              return window.location.href = Routing.generate('OpitNotesUserBundle_security_login');
            }
          }
        });
      }
    }
  });

  $(document).ready(function() {
    return $('[title]').each(function() {
      return $(this).tipsy();
    });
  });

}).call(this);
