// Generated by CoffeeScript 1.7.1
(function() {
  $.extend(true, $(document).data('notes'), {
    funcs: {
      changeTravelStatus: function(statusId, travelRequestId, $spinner) {
        var dialogWidth, reloadPage;
        reloadPage = false;
        dialogWidth = 550;
        return $.ajax({
          method: 'POST',
          url: Routing.generate('OpitNotesTravelBundle_request_state'),
          data: {
            'statusId': statusId,
            'travelRequestId': travelRequestId
          }
        }).done(function(data) {
          if (data === 'error') {
            return $('<div id="dialog-tr-error"></div>').html('You cannot change the status of the travel request because it has been already changed.').dialog({
              open: function() {
                return $('.ui-dialog-title').append('<i class="fa fa-exclamation-triangle"></i> Status cannot be changed');
              },
              width: dialogWidth,
              buttons: {
                Reload: function() {
                  location.reload();
                }
              }
            });
          } else {
            return reloadPage = true;
          }
        }).complete(function() {
          $spinner.remove();
          if (reloadPage === true) {
            return location.reload();
          }
        }).fail(function(data) {
          var $changeState;
          $spinner.remove();
          $changeState = $('.changeState[data-tr="' + travelRequestId + '"]');
          $changeState.removeClass('dropdown-disabled');
          $changeState.prop('selectedIndex', 0);
          return $('<div id="dialog-tr-error"></div>').html('Status could not be changed due to an error.').dialog({
            open: function() {
              return $('.ui-dialog-title').append('<i class="fa fa-exclamation-triangle"></i> An error occurred');
            },
            width: dialogWidth,
            buttons: {
              Close: function() {
                $(this).dialog('destroy');
              }
            }
          });
        });
      }
    }
  });

}).call(this);