// Generated by CoffeeScript 1.7.1
(function() {
  var $form, addForm, addFormAddButton, addFormDeleteButton, compareDays, createFormFieldSetChild, formFieldsetAddClasses, formFieldsetAddHeader, reCreateForm;

  compareDays = function() {
    var $accomodationWrapper, $errorMessage, accomodationDays, arrivalDate, departureDate, diffDays;
    departureDate = new Date($('#travelRequest_departure_date').val());
    arrivalDate = new Date($('#travelRequest_arrival_date').val());
    diffDays = (arrivalDate.getTime() - departureDate.getTime()) / (24 * 60 * 60 * 1000);
    accomodationDays = '0';
    $('#travelRequest_accomodations').find('.number-of-nights').each(function() {
      return accomodationDays = parseInt($(this).val()) + parseInt(accomodationDays);
    });
    $accomodationWrapper = $('#travelRequest_accomodations');
    if (diffDays > 0 && accomodationDays > diffDays) {
      if ($accomodationWrapper.children('label.custom-error').length === 0) {
        $errorMessage = $('<label>').html('Total accomodation duration can not exceed travel request duration.').addClass('error custom-error').addClass('margin-bottom-10 display-table-important').css('margin-left', '10px');
        $accomodationWrapper.prepend($errorMessage);
      }
      return false;
    } else {
      $accomodationWrapper.children('label.custom-error').remove();
      return true;
    }
  };

  addFormDeleteButton = function() {
    var $deleteButton;
    $deleteButton = $('<div>').html('<i class="fa fa-minus-square"></i>Delete').addClass('deleteFormFieldsetChild formFieldsetButton').addClass('form-fieldset-delete-button');
    $deleteButton.click(function() {
      return $(this).parent().remove();
    });
    return $deleteButton;
  };

  addFormAddButton = function(parameters) {
    return parameters.forEach(function(parameter) {
      var $button, $collection;
      $button = $('<div>').html("<i class='fa fa-plus-square'></i>" + parameter.text).addClass('addFormFieldsetChild formFieldsetButton').addClass('form-fieldset-add-button');
      $collection = parameter.parent.append($button);
      $collection.data('index', $collection.find(':input').length);
      return $button.click(function(e) {
        e.preventDefault();
        addForm($collection, $button, true);
      });
    });
  };

  formFieldsetAddClasses = function(formFieldsetIds) {
    return formFieldsetIds.forEach(function(formFieldsetId) {
      var $parent;
      $parent = $("#" + formFieldsetId).parent().addClass('formFieldset').addClass('padding-bottom-5 margin-top-20 margin-bottom-20');
      return formFieldsetAddHeader(formFieldsetId);
    });
  };

  formFieldsetAddHeader = function(formFieldsetId) {
    var $label;
    $label = $("#" + formFieldsetId).parent().children('label');
    return $label.replaceWith('<h3 class="background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em">' + $label.html() + '</h3>');
  };

  createFormFieldSetChild = function($self) {
    var $numberOfNights;
    $self.addClass('formFieldsetChild');
    $self.addClass('padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top');
    $self.find('.cost').parent().append($self.find('.currency'));
    $self.children().remove('label');
    $self.append(addFormDeleteButton());
    $numberOfNights = $self.find('.number-of-nights');
    if ($numberOfNights.length > 0) {
      $numberOfNights.on('change', function() {
        return compareDays();
      });
    }
    return $self;
  };

  reCreateForm = function(formFieldsetChildIds) {
    return formFieldsetChildIds.forEach(function(formFieldsetChildId) {
      return $("#" + formFieldsetChildId).children().each(function() {
        return createFormFieldSetChild($(this));
      });
    });
  };

  addForm = function($collectionHolder, $addButton, addListener) {
    var $newForm, index, newForm;
    index = $collectionHolder.data('index');
    newForm = $collectionHolder.data('prototype').replace('<label class="required">__name__label__</label>', '').replace(/__name__/g, index);
    $newForm = createFormFieldSetChild($(newForm));
    $newForm.find('.currency option[value=EUR]').attr('selected', 'selected');
    $collectionHolder.data('index', index + 1);
    return $addButton.before($newForm);
  };

  $(document).ready(function() {
    var $arrivalDate, $departureDate, $div, $generalData, requiredApprovals, travelCustomer;
    $('.changeState').on('change', function() {
      return $(document).data('notes').funcs.changeStateDialog($(this), $(document).data('notes').funcs.changeTravelRequestStatus, $(this).data('tr'));
    });
    if (!Modernizr.inputtypes.date) {
      $arrivalDate = $('#travelRequest_arrival_date');
      $departureDate = $('#travelRequest_departure_date');
      $('#altDatetravelRequest_arrival_date').val($arrivalDate.val());
      $('#altDatetravelRequest_departure_date').val($departureDate.val());
      $arrivalDate.val($arrivalDate.val().replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1"));
      $departureDate.val($departureDate.val().replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1"));
    }
    $('#travelRequest_user_ac').autocomplete({
      source: Routing.generate('OpitNotesTravelBundle_travel_userSearch', {
        role: 'all'
      }),
      minLength: 2,
      response: function(event, ui) {},
      select: function(event, ui) {
        $('#travelRequest_user').val(ui.item.id);
      }
    });
    $('#travelRequest_team_manager_ac').autocomplete({
      source: Routing.generate('OpitNotesTravelBundle_travel_userSearch', {
        role: 'team_manager'
      }),
      minLength: 2,
      select: function(event, ui) {
        $('#travelRequest_team_manager').val(ui.item.id);
      }
    });
    $('#travelRequest_general_manager_ac').autocomplete({
      source: Routing.generate('OpitNotesTravelBundle_travel_userSearch', {
        role: 'general_manager'
      }),
      minLength: 2,
      select: function(event, ui) {
        $('#travelRequest_general_manager').val(ui.item.id);
      }
    });
    $('.travel-status-history').click(function(event) {
      event.preventDefault();
      return $(document).data('notes').funcs.showTravelStatusHistory($(this).data('id'), 'tr');
    });
    travelCustomer = $('#travelRequest_customer_name');
    if (travelCustomer.val() === '') {
      travelCustomer.parent().css({
        visibility: 'hidden'
      });
    } else {
      $('#travelRequest_customer_related').val('0');
    }
    $('#travelRequest_customer_related').change(function() {
      if ($(this).val() === "0") {
        travelCustomer.parent().css({
          visibility: 'visible'
        });
        return travelCustomer.attr('required', 'required');
      } else {
        travelCustomer.parent().css({
          visibility: 'hidden'
        });
        return travelCustomer.removeAttr('required');
      }
    });
    requiredApprovals = $('<div>').addClass('formFieldset').addClass('padding-bottom-5 margin-top-20 margin-bottom-20');
    requiredApprovals.append($('<h3>').addClass('background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em').html('Required approvals'));
    $div = $('<div>').addClass('margin-left-1-em').append($('#travelRequest_team_manager_ac').parent()).append($('#travelRequest_general_manager_ac').parent());
    $('#travelRequest_general_manager').after(requiredApprovals.append($div));
    $('#travelRequest_departure_date, #travelRequest_arrival_date, #travelRequest_customer_related, #travelRequest_customer_name, #travelRequest_team_manager_ac, #travelRequest_general_manager_ac').parent().addClass('display-inline-block vertical-align-top margin-right-1-em');
    formFieldsetAddClasses(['travelRequest_destinations', 'travelRequest_accomodations']);
    reCreateForm(['travelRequest_destinations', 'travelRequest_accomodations']);
    addFormAddButton([
      {
        'text': 'Add destination',
        'parent': $('#travelRequest_destinations')
      }, {
        'text': 'Add accomodations',
        'parent': $('#travelRequest_accomodations')
      }
    ]);
    $generalData = $('<div>').addClass('formFieldset generalFormFieldset').addClass('padding-bottom-5 margin-top-20 margin-bottom-20').append($('#travelRequest_user_ac, #travelRequest_departure_date, #travelRequest_arrival_date, #travelRequest_customer_related, #travelRequest_customer_name, #travelRequest_trip_purpose').parent());
    $('#travelRequest').prepend($generalData);
    $('#travelRequest_arrival_date').parent().after('<br />');
    $(document).data('notes').funcs.createButton('Cancel', 'button display-inline-block', '', $('#travelRequest_add_travel_request').parent(), 'OpitNotesTravelBundle_travel_list');
    $(document).data('notes').funcs.makeElementToggleAble('h3', $('.formFieldset'));
    $('.disabled select, .disabled input').each(function() {
      return $(this).attr('disabled', 'disabled');
    });
    $('.disabled button').each(function() {
      return $(this).addClass('button-disabled').attr('disabled', 'disabled');
    });
    if ($('#travelRequest_add_travel_request').attr('disabled')) {
      $('.addFormFieldsetChild').each(function() {
        return $(this).remove();
      });
      return $('.deleteFormFieldsetChild').each(function() {
        return $(this).remove();
      });
    }
  });

  $form = $('#travelRequestForm');

  $.validator.addMethod('compare', function(value, element) {
    var arrivalDate, departureDate;
    departureDate = $('#travelRequest_departure_date').val();
    arrivalDate = $('#travelRequest_arrival_date').val();
    return departureDate <= arrivalDate;
  }, 'Arrival date should not be earlier than departure date.');

  $.validator.addMethod('checkId', function(value, element) {
    var $element, $idElement, id;
    $element = $(element);
    id = $(element).attr('id');
    $idElement = $('#' + id.substr(0, id.length - 3));
    if ($element.val()) {
      if (!$idElement.val()) {
        return false;
      } else {
        return true;
      }
    } else {
      return false;
    }
  }, 'This field is required.');

  $form.validate({
    ignore: [],
    rules: {
      "travelRequest[arrival_date]": "compare",
      "travelRequest[user_ac]": "checkId",
      "travelRequest[general_manager_ac]": "checkId"
    }
  });

  $('#travelRequest_add_travel_request').click(function(event) {
    event.preventDefault();
    if ($form.valid() && compareDays()) {
      $.ajax({
        method: 'POST',
        url: Routing.generate('OpitNotesTravelBundle_travel_show_details'),
        data: 'preview=1&' + $form.serialize()
      }).done(function(data) {
        var $preview;
        $preview = $('<div id="dialog-travelrequest-preview"></div>').html(data);
        return $preview.dialog({
          title: '<i class="fa fa-list-alt"></i> Details',
          close: function() {
            return $preview.dialog("destroy");
          },
          width: 550,
          maxHeight: $(window).outerHeight() - 100,
          modal: true,
          buttons: {
            Save: function() {
              $form.submit();
              $preview.dialog("destroy");
            },
            'Save & send for approval': function() {
              if (isNaN(window.location.href.slice(-1))) {
                $form.attr('action', $form.attr('action') + '/new/fa');
              } else {
                $form.attr('action', $form.attr('action') + '/fa');
              }
              $form.submit();
              $preview.dialog("destroy");
            },
            Cancel: function() {
              $preview.dialog("destroy");
            }
          }
        });
      }).fail(function() {
        return $('<div></div>').html('The travel request could not be saved due to an error.').dialog({
          title: 'Error'
        });
      });
    } else {
      $('.hasDatepicker').each(function() {
        if ($(this).hasClass('error')) {
          return $(this).parent().find('.fa-calendar').addClass('margin-top-12');
        }
      });
    }
  });

}).call(this);
