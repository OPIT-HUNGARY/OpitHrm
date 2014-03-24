// Generated by CoffeeScript 1.7.1
(function() {
  var $addCompanyTagLink, $addUserTagLink, $expensesPaidByMe, $expensesPaidByMeDesc, $expensesPaidByOpit, $expensesPaidByOpitDesc, $form, $formFieldset, $generalFormFields, $generalFormFieldset, $perDiem, addNewAdvanceReceived, addNewForm, calculateAdvancesPayback, calculatePerDiem, convertCurrency, createCustomField, createDeleteButton, createDeleteExpenseButton, createTableRow, expenseDateChange, reCreateAdvances, reCreateExpenses, setAvailableCurrencies, setCurrenciesArray, validateAllExpenseDates, validateExpenseDate;

  $('.travel-status-history').click(function(event) {
    var id;
    event.preventDefault();
    id = $(this).data('id');
    return $(document).data('notes').funcs.showTravelStatusHistory(id, 'te');
  });

  calculateAdvancesPayback = function() {
    var amount, spent;
    spent = [];
    amount = 0;
    $('.elementContainer .currency-listen').each(function() {
      var $amountEl, amountSpent;
      $amountEl = $(this).closest('.formFieldsetChild').find('.amount');
      amount = parseInt($amountEl.val());
      amountSpent = $(this).val();
      if (spent[amountSpent] === void 0) {
        spent[amountSpent] = amount;
      } else {
        spent[amountSpent] += amount;
      }
      if (isNaN(spent[amountSpent])) {
        return console.warn("Value is not a number (" + ($amountEl.attr('id')) + ")");
      }
    });
    return $('.generalFormFieldset .te-advances-received-currency').each(function() {
      var $advancesSpent, $closestAdvancesReceived, advancePayBack, advancesReceived, advancesSpent;
      $closestAdvancesReceived = $(this).closest('.advances-received');
      advancesSpent = spent[$(this).val()];
      advancesReceived = $closestAdvancesReceived.find('.te-advances-received').val();
      advancePayBack = parseInt(advancesReceived - parseInt(advancesSpent));
      $advancesSpent = $closestAdvancesReceived.find('.te-advances-spent');
      $advancesSpent.html(advancesSpent === void 0 ? '0' : isNaN(advancesSpent) ? '0' : advancesSpent);
      return $closestAdvancesReceived.find('.te-advances-payback').html(advancePayBack ? advancePayBack < 0 ? '0' : advancePayBack : isNaN(advancePayBack) ? advancesReceived === '' ? '0' : advancesReceived : '0');
    });
  };

  createDeleteButton = function() {
    var $deleteButton;
    $deleteButton = $('<div>').addClass('form-fieldset-delete-button deleteFormFieldsetChild formFieldsetButton').html('<i class="fa fa-minus-square"></i>Delete');
    $deleteButton.on('click', function() {
      $(this).parent().remove();
      return calculateAdvancesPayback();
    });
    return $deleteButton;
  };

  createDeleteExpenseButton = function($parent) {
    var $deleteButton;
    $deleteButton = $('<i>').addClass('fa fa-minus-square color-red hover-cursor-pointer margin-top-24');
    $deleteButton.on('click', function() {
      $(this).closest('.advances-received').remove();
      setAvailableCurrencies();
      return calculateAdvancesPayback();
    });
    return $parent.append($('<div>').addClass('display-inline-block vertical-align-top margin-right-1-em').append($deleteButton));
  };

  validateAllExpenseDates = function() {
    var isDateValid;
    isDateValid = true;
    $('.formFieldsetChild').each(function() {
      var expenseDateField;
      expenseDateField = $(this).find('input[type=date]');
      validateExpenseDate(expenseDateField);
      if (expenseDateField.parent().children('.custom-label-error').length > 0) {
        isDateValid = false;
      }
    });
    return isDateValid;
  };

  validateExpenseDate = function(self) {
    var date;
    date = self.val();
    self.addClass('display-inline-block');
    if (date > $('#travelExpense_arrivalDateTime_date').val() || date < $('#travelExpense_departureDateTime_date').val()) {
      if (self.parent().children('.custom-label-error').length < 1) {
        return self.parent().append($('<label>').addClass('custom-label-error').text('Invalid expense date.'));
      }
    } else {
      return self.parent().children().remove('.custom-label-error');
    }
  };

  expenseDateChange = function(parent) {
    var $dateOfExpenseSpent;
    $dateOfExpenseSpent = parent.find('input[type=date]');
    if ($dateOfExpenseSpent.attr('id').indexOf('userPaidExpenses') > -1) {
      return $dateOfExpenseSpent.on('change', function() {
        return validateExpenseDate($(this));
      });
    }
  };

  reCreateExpenses = function(self) {
    var $container, $selectedExpense;
    $selectedExpense = $('<span>').addClass('selected-expense').html(self.find('.te-expense-type').find(':selected').text());
    $selectedExpense.addClass('margin-0 color-white display-block align-center background-color-dark-grey');
    self.children('label:first').remove();
    $container = $('<div>').addClass('formFieldsetChild padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top');
    $container.append(self);
    $container.append(createDeleteButton());
    $container.prepend($selectedExpense);
    $container.find('.amount').parent().append($container.find('.currency'));
    $container.find('.amount-listen').on('change', function() {
      return calculateAdvancesPayback();
    });
    $container.find('.currency-listen').on('change', function() {
      return calculateAdvancesPayback();
    });
    expenseDateChange($container);
    return $container;
  };

  createCustomField = function(className, labelText, content) {
    var $customFieldInline;
    $customFieldInline = $('<div>').addClass('display-inline-block vertical-align-top margin-right-1-em');
    $customFieldInline.append($('<label>').html(labelText));
    $customFieldInline.append($('<div>').addClass(className).html(content));
    return $customFieldInline;
  };

  reCreateAdvances = function() {
    var $advancesReceivedCurrencyParent, $generalFormFieldset, $teAdvancesReceived, collectionIndex;
    collectionIndex = 0;
    $teAdvancesReceived = $('#travelExpense_advancesReceived');
    $generalFormFieldset = $('.generalFormFieldset');
    $teAdvancesReceived.parent().children('label').remove();
    $teAdvancesReceived.children().each(function() {
      return $(this).find('label').remove();
    });
    $('.te-advances-received').parent().addClass('display-inline-block vertical-align-top margin-right-1-em');
    $advancesReceivedCurrencyParent = $('.te-advances-received-currency').parent().addClass('display-inline-block vertical-align-top margin-right-1-em').prepend($('<label>').html('Currency'));
    $('.te-advances-received').each(function(index) {
      var $advancesReceived, $advancesSpent, $selfParent, $teAdvances;
      $advancesSpent = createCustomField('te-advances-spent custom-field', 'Advances spent', '0');
      $selfParent = $(this).parent().prepend($('<label>').html('Advances received')).after($advancesSpent);
      $advancesSpent.after(createCustomField('te-advances-payback custom-field', 'Advances payback', '0'));
      collectionIndex++;
      $teAdvances = $('#travelExpense_advancesReceived_' + index);
      $advancesReceived = $('<div>').addClass('advances-received').addClass('margin-top-5 margin-bottom-5').append($teAdvances);
      $generalFormFieldset.append($advancesReceived);
      return createDeleteExpenseButton($teAdvances);
    });
    $teAdvancesReceived.data('index', collectionIndex);
    $generalFormFieldset.on('change', '.te-advances-received', function() {
      return calculateAdvancesPayback();
    });
    $generalFormFieldset.on('change', '.te-advances-received-currency', function() {
      return calculateAdvancesPayback();
    });
    return calculateAdvancesPayback();
  };

  setCurrenciesArray = function(array) {
    return $($('#travelExpense_advancesReceived').data('prototype')).find('option').each(function() {
      return array.push($(this).val());
    });
  };

  setAvailableCurrencies = function(doSplice) {
    var availableCurrencies, selectAbleCurrencies, selectedCurrencies;
    availableCurrencies = [];
    selectedCurrencies = [];
    selectAbleCurrencies = [];
    setCurrenciesArray(availableCurrencies);
    if ($('.te-advances-received-currency').length > 0) {
      $('.te-advances-received-currency').each(function() {
        return selectedCurrencies.push($(this).find('option:selected').val());
      });
      selectedCurrencies.forEach(function(selectedCurrency) {
        return availableCurrencies.forEach(function(availableCurrency) {
          if (availableCurrency === selectedCurrency) {
            if (availableCurrencies.indexOf(availableCurrency) > -1) {
              return availableCurrencies.splice(availableCurrencies.indexOf(availableCurrency), 1);
            }
          }
        });
      });
      $('.te-advances-received-currency option').not(':selected').each(function() {
        return $(this).remove();
      });
      selectAbleCurrencies = availableCurrencies.slice();
      if (!doSplice) {
        selectAbleCurrencies.splice(0, 1);
      }
      $('.te-advances-received-currency').each(function() {
        var $self;
        $self = $(this);
        return selectAbleCurrencies.forEach(function(selectAbleCurrency) {
          var option;
          option = $('<option>');
          option.attr('value', selectAbleCurrency);
          option.html(selectAbleCurrency);
          return $self.append(option);
        });
      });
      calculateAdvancesPayback();
    }
    return availableCurrencies;
  };

  addNewAdvanceReceived = function(collectionHolder) {
    var $advancesPayback, $advancesSpent, $availableCurrencies, $newAdvancesReceived, availableCurrencies, index, prototype;
    availableCurrencies = setAvailableCurrencies();
    if (availableCurrencies.length > 0) {
      prototype = collectionHolder.data('prototype');
      index = collectionHolder.data('index');
      prototype = prototype.replace('<label class="required">__name__label__</label>', '');
      $newAdvancesReceived = $(prototype.replace(/__name__/g, index)).addClass('advances-received').addClass('margin-top-5 margin-bottom-5');
      $newAdvancesReceived.children('div').children('div').each(function() {
        return $(this).addClass('display-inline-block vertical-align-top margin-right-1-em');
      });
      $advancesPayback = createCustomField('te-advances-payback custom-field', 'Advances payback', '0');
      $advancesSpent = createCustomField('te-advances-spent custom-field', 'Advances spent', '0');
      $newAdvancesReceived.find('.te-advances-received').parent().after($advancesSpent);
      $advancesSpent.after($advancesPayback);
      createDeleteExpenseButton($newAdvancesReceived.children('div'));
      $('.generalFormFieldset .addFormFieldsetChild').before($newAdvancesReceived);
      collectionHolder.data('index', index + 1);
      $availableCurrencies = $newAdvancesReceived.find('.te-advances-received-currency');
      $availableCurrencies.children().remove();
      availableCurrencies.forEach(function(availableCurrency) {
        var option;
        option = $('<option>');
        option.attr('value', availableCurrency);
        option.html(availableCurrency);
        return $availableCurrencies.append(option);
      });
      $availableCurrencies.on('change', function() {
        return setAvailableCurrencies(true);
      });
    }
    return calculateAdvancesPayback();
  };

  addNewForm = function(collectionHolder, parent) {
    var $formFieldsetChild, $selectedExpense, index, newForm, prototype;
    index = collectionHolder.data('index');
    prototype = collectionHolder.data('prototype');
    prototype = prototype.replace('<label class="required">__name__label__</label>', '');
    newForm = prototype.replace(/__name__/g, index);
    $selectedExpense = $('<span>').addClass('selected-expense').html('Expense type');
    $selectedExpense.addClass('margin-0 color-white display-block align-center background-color-dark-grey');
    $formFieldsetChild = $('<div>').addClass('formFieldsetChild').addClass('padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top');
    $formFieldsetChild.append(newForm);
    $formFieldsetChild.append(createDeleteButton());
    $formFieldsetChild.prepend($selectedExpense);
    expenseDateChange($($formFieldsetChild));
    $formFieldsetChild.find('.currency option[value=EUR]').attr('selected', 'selected');
    collectionHolder.data('index', index + 1);
    $formFieldsetChild.find('.amount').parent().append($formFieldsetChild.find('.currency'));
    $formFieldsetChild.find('.amount-listen, .currency-listen').on('change', function() {
      return calculateAdvancesPayback();
    });
    return parent.find('.addFormFieldsetChild').before($formFieldsetChild);
  };

  createTableRow = function(text, value, rowTitle) {
    var $row, $textColumn, $valueColumn;
    $row = $('<tr>');
    $textColumn = $('<td>').addClass('bgGrey bold').html(text + ' <i class="fa fa-clock-o" title="' + rowTitle + '"></i>');
    $valueColumn = $('<td>').text(value + ' EUR');
    if (text === 'Total') {
      $textColumn.html('');
      $valueColumn.html('<strong>Total</strong><br /> ' + value + ' EUR');
    }
    $row.append($textColumn);
    $row.append($valueColumn);
    return $row;
  };

  $perDiem = $('<div>').addClass('per-diem-details-wrapper');

  $perDiem.addClass('display-inline-block vertical-align-top float-right margin-top-5 margin-bottom-10');

  convertCurrency = function(originCode, destinationCode, value) {
    if (originCode === destinationCode) {
      return value;
    } else {
      return curConverter.convertCurrency(originCode, destinationCode, value).toFixed(2);
    }
  };

  calculatePerDiem = function(departureDate, arrivalDate) {
    var arrival, arrivalObj, arrivalPrefix, departure, departureObj, departurePrefix;
    departurePrefix = '#travelExpense_departureDateTime_time_';
    arrivalPrefix = '#travelExpense_arrivalDateTime_time_';
    departure = "" + departureDate + " " + ($(departurePrefix + 'hour').val()) + ":" + ($(departurePrefix + 'minute').val());
    departureObj = new Date(departure);
    arrival = "" + arrivalDate + " " + ($(arrivalPrefix + 'hour').val()) + ":" + ($(arrivalPrefix + 'minute').val());
    arrivalObj = new Date(arrival);
    if (arrivalObj > departureObj) {
      return $.ajax({
        method: 'POST',
        url: Routing.generate('OpitNotesTravelBundle_expense_perdiem'),
        data: {
          arrival: arrival,
          departure: departure
        }
      }).done(function(data) {
        var $perDiemTable;
        $('.perDiemTable').remove();
        $perDiemTable = $('<table>').addClass('perDiemTable bordered margin-top-10');
        if (data['totalTravelHoursOnSameDay'] > 0) {
          $perDiemTable.append(createTableRow('One day trip', data['totalPerDiem'], "Hours traveled " + data['totalTravelHoursOnSameDay'] + "."));
        } else {
          $perDiemTable.append(createTableRow('Departure day', data['departurePerDiem'], "Hours traveled on departure day " + data['departureHours'] + "."));
          $perDiemTable.append(createTableRow("Full days (" + data['daysBetween'] + ")", data['daysBetweenPerDiem'], "Number of full days " + data['daysBetween'] + "."));
          $perDiemTable.append(createTableRow('Arrival day', data['arrivalPerDiem'], "Hours traveled on arrival day " + data['arrivalHours'] + "."));
          $perDiemTable.append(createTableRow('Total', data['totalPerDiem']));
        }
        return $perDiem.append($perDiemTable);
      });
    }
  };

  convertCurrency = function(originCode, destinationCode, value) {
    return curConverter.convertCurrency(originCode, destinationCode, value);
  };

  $(document).ready(function() {
    var $addCompanyTagLink, $addNewAdvance, $addUserTagLink, $advancesReceived, $buttonParent, $perDiemAmountsTable, $secondFormFieldset, $thirdFormFieldset, arrivalDate, arrivalTime, companyPaidExpensesIndex, departureDate, departureTime, paidExpenses, userPaidExpensesIndex;
    setAvailableCurrencies();
    $buttonParent = $('#travelExpense_add_travel_expense').parent();
    $(document).data('notes').funcs.createButton('Cancel', 'button display-inline-block', '', $buttonParent, 'OpitNotesTravelBundle_travel_list');
    $(document).data('notes').funcs.makeElementToggleAble('h3', $('.formFieldset'), '.elementContainer');
    arrivalDate = $('#travelExpense_arrivalDateTime_date').css({
      display: 'inline-block'
    });
    arrivalTime = $('#travelExpense_arrivalDateTime_time').addClass('display-inline-block vertical-align-top margin-right-1-em time-picker');
    departureDate = $('#travelExpense_departureDateTime_date').css({
      display: 'inline-block'
    });
    departureTime = $('#travelExpense_departureDateTime_time').addClass('display-inline-block vertical-align-top margin-right-1-em time-picker');
    if (!Modernizr.inputtypes.date) {
      arrivalDate.datepicker('destroy');
      departureDate.datepicker('destroy');
      $('input[type=date]').each(function() {
        var dateVal;
        dateVal = $(this).val();
        return $(this).val($(this).val().replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1"));
      });
    } else {
      arrivalDate.attr('readonly', 'readonly');
      departureDate.attr('readonly', 'readonly');
    }
    $secondFormFieldset = $('#travelExpense').children('.formFieldset:nth-child(2)');
    $thirdFormFieldset = $('#travelExpense').children('.formFieldset:nth-child(3)');
    $addCompanyTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add company expense</div>').addClass('formFieldsetButton form-fieldset-add-button');
    $addCompanyTagLink.on('click', function() {
      return addNewForm($('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)'));
    });
    $addUserTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add user expense</div>').addClass('formFieldsetButton form-fieldset-add-button');
    $addUserTagLink.on('click', function() {
      return addNewForm($('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)'));
    });
    $secondFormFieldset.append($('<div>').addClass('elementContainer').append($addUserTagLink));
    $thirdFormFieldset.append($('<div>').addClass('elementContainer').append($addCompanyTagLink));
    companyPaidExpensesIndex = 0;
    userPaidExpensesIndex = 0;
    paidExpenses = [$('#travelExpense_companyPaidExpenses'), $('#travelExpense_userPaidExpenses')];
    paidExpenses.forEach(function(paidExpense) {
      var paidExpenseChildren;
      paidExpenseChildren = paidExpense.children('div');
      if (paidExpenseChildren.length > 0) {
        return paidExpenseChildren.each(function() {
          if ('travelExpense_companyPaidExpenses' === paidExpense.attr('id')) {
            $thirdFormFieldset.find('.addFormFieldsetChild').before(reCreateExpenses($(this)));
            return companyPaidExpensesIndex++;
          } else if ('travelExpense_userPaidExpenses' === paidExpense.attr('id')) {
            $secondFormFieldset.find('.addFormFieldsetChild').before(reCreateExpenses($(this)));
            return userPaidExpensesIndex++;
          }
        });
      }
    });
    $('#travelExpense_companyPaidExpenses').data('index', companyPaidExpensesIndex);
    $('#travelExpense_userPaidExpenses').data('index', userPaidExpensesIndex);
    $('#travelExpense_companyPaidExpenses').parent().children('label').remove();
    $('#travelExpense_userPaidExpenses').parent().children('label').remove();
    $('#travelExpense').css({
      display: 'block'
    });
    $perDiemAmountsTable = $('<table>').addClass('per-diem-amounts-slab bordered width-100');
    $.ajax({
      method: 'POST',
      url: Routing.generate('OpitNotesTravelBundle_expense_perdiemvalues')
    }).done(function(data) {
      var $tdAmount, $tdHours, $tr, key, value, _results;
      _results = [];
      for (key in data) {
        value = data[key];
        $tr = $('<tr>');
        $tdHours = $('<td>').attr('width', '100px');
        $tdHours.text("Over " + key + " hours");
        $tdAmount = $('<td>').text(value + ' EUR');
        $tr.append($tdHours);
        $tr.append($tdAmount);
        _results.push($perDiemAmountsTable.append($tr));
      }
      return _results;
    });
    $perDiemAmountsTable.prepend($('<tr>').append($('<td>').attr('colspan', 2).html('Per diem is calculated considering the following slab.')));
    $perDiem.append($perDiemAmountsTable);
    $('.generalFormFieldset').find('br').last().remove();
    $perDiem.append($perDiemAmountsTable);
    $('.generalFormFieldset').append($perDiem);
    $('.fa-question-circle').on('mouseover', function() {
      return $(this).parent().parent().find('.formFieldsetDescription').removeClass('display-none');
    });
    $('.fa-question-circle').on('mouseout', function() {
      return $('.formFieldsetDescription').addClass('display-none');
    });
    if (!isNewTravelExpense) {
      calculatePerDiem(departureDate.val(), arrivalDate.val());
    }
    $('#travelExpense_departureDateTime_time_hour, #travelExpense_departureDateTime_time_minute, #travelExpense_arrivalDateTime_time_hour, #travelExpense_arrivalDateTime_time_minute').on('change', function() {
      return calculatePerDiem(departureDate.val(), arrivalDate.val());
    });
    reCreateAdvances();
    $advancesReceived = $('#travelExpense_advancesReceived');
    $addNewAdvance = $('<div>').addClass('addFormFieldsetChild formFieldsetButton').html('<i class="fa fa-plus-square"></i>Add advances received');
    $addNewAdvance.addClass('form-fieldset-add-button margin-left-0-important');
    $('.generalFormFieldset').append($addNewAdvance);
    $addNewAdvance.on('click', function() {
      return addNewAdvanceReceived($advancesReceived);
    });
    if ($('#travelExpense_add_travel_expense').hasClass('button-disabled')) {
      $('.addFormFieldsetChild').each(function() {
        return $(this).remove();
      });
      $('.deleteFormFieldsetChild').each(function() {
        return $(this).remove();
      });
      $('.fa-minus-square').each(function() {
        return $(this).remove();
      });
    }
    return $('.changeState').on('change', function() {
      return $(document).data('notes').funcs.changeStateDialog($(this), $(document).data('notes').funcs.changeTravelExpenseStatus, $(this).data('te'));
    });
  });

  $formFieldset = $('<div>').addClass('formFieldset padding-bottom-5 margin-top-20 margin-bottom-20');

  $generalFormFieldset = $formFieldset.clone().addClass('generalFormFieldset clearfix padding-10');

  $expensesPaidByMe = $formFieldset.clone().append($('<h3>').addClass('background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em cursor-pointer').html('Expenses paid by me <i class="fa fa-question-circle"></i>'));

  $expensesPaidByOpit = $formFieldset.clone().append($('<h3>').addClass('background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em cursor-pointer').html('Expenses paid by opit <i class="fa fa-question-circle"></i>'));

  $('#travelExpense').prepend($expensesPaidByOpit);

  $('#travelExpense').prepend($expensesPaidByMe);

  $('#travelExpense').prepend($generalFormFieldset);

  $('#travelExpense').addClass('travelForm');

  $generalFormFields = $('<div>').addClass('display-inline-block');

  $generalFormFieldset.append($generalFormFields);

  $expensesPaidByOpitDesc = $('<div>').addClass('formFieldsetDescription').addClass('short-description display-none position-absolute padding-5').html('Expenses paid by OPIT (already paid by OPIT).');

  $expensesPaidByMeDesc = $('<div>').addClass('formFieldsetDescription').addClass('short-description display-none position-absolute padding-5').html('Expenses paid by employee (payable to your own bank account).');

  $expensesPaidByOpit.append($expensesPaidByOpitDesc);

  $expensesPaidByMe.append($expensesPaidByMeDesc);

  $('.formFieldset').on('change', '.te-expense-type', function() {
    return $(this).closest('.formFieldsetChild').children('.selected-expense').html($("#" + ($(this).attr('id')) + " :selected").text());
  });

  $('.te-claim').each(function(index) {
    $(this).parent().addClass('display-inline-block vertical-align-top margin-right-1-em');
    $generalFormFields.append($(this).parent());
    if ($(this).hasClass('display-none')) {
      $(this).removeClass('display-none');
      $(this).parent().addClass('display-none');
    }
    if (index % 2) {
      return $generalFormFields.append($('<br>'));
    }
  });

  $addCompanyTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add company expense</div>').addClass('formFieldsetButton form-fieldset-add-button');

  $addCompanyTagLink.on('click', function() {
    return addNewForm($('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)'));
  });

  $addUserTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add user expense</div>').addClass('formFieldsetButton form-fieldset-add-button');

  $addUserTagLink.on('click', function() {
    return addNewForm($('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)'));
  });

  $form = $('#travelExpenseForm');

  $.validator.addMethod('compare', function(value, element) {
    var arrival, arrivalDate, departure, departureDate;
    departureDate = $('#travelExpense_departureDateTime_date').val();
    arrivalDate = $('#travelExpense_arrivalDateTime_date').val();
    if (departureDate.indexOf('-')) {
      arrivalDate = arrivalDate.replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1");
      departureDate = departureDate.replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1");
    }
    departure = departureDate + ' ' + $('#travelExpense_departureDateTime_time_hour').val() + ':' + $('#travelExpense_departureDateTime_time_minute').val();
    arrival = arrivalDate + ' ' + $('#travelExpense_arrivalDateTime_time_hour').val() + ':' + $('#travelExpense_arrivalDateTime_time_minute').val();
    $('#travelExpense_arrivalDateTime_time_minute').css({
      border: 'solid 1px rgb(170, 170, 170)'
    });
    return new Date(departure) < new Date(arrival);
  }, 'Arrival date should not be earlier than departure date.');

  $form.validate({
    ignore: [],
    rules: {
      'travelExpense[arrivalDateTime][time][minute]': 'compare',
      'travelExpense[taxIdentification]': {
        maxlength: 11
      },
      'travelExpense[toSettle]': {
        digits: true
      }
    }
  });

  $('#travelExpense_add_travel_expense').on('click', function(event) {
    event.preventDefault();
    if (!$(this).hasClass('button-disabled')) {
      if ($form.valid() && validateAllExpenseDates()) {
        if (!Modernizr.inputtypes.date) {
          $('input[type=date]').each(function() {
            return $(this).parent().find('input[type=hidden]').val($(this).val().replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$1-$2"));
          });
        }
        return $.ajax({
          method: 'POST',
          url: Routing.generate('OpitNotesTravelBundle_expense_show_details'),
          data: 'preview=1&' + $form.serialize()
        }).done(function(data) {
          var $preview;
          $preview = $('<div id="dialog-travelrequest-preview"></div>').html(data);
          return $preview.dialog({
            open: function() {
              return $('.ui-dialog-title').append('<i class="fa fa-list-alt"></i> Details');
            },
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
              Cancel: function() {
                $preview.dialog("destroy");
              }
            }
          });
        }).fail(function() {
          return $('<div></div>').html('The travel expense could not be saved due to an error.').dialog({
            title: 'Error'
          });
        });
      } else {
        return $('.hasDatepicker').each(function() {
          if ($(this).hasClass('error')) {
            return $(this).parent().find('.fa-calendar').addClass('margin-top-12');
          }
        });
      }
    }
  });

  $('#travelRequestPreview').on('click', function() {
    $.ajax({
      method: 'POST',
      url: Routing.generate('OpitNotesTravelBundle_travel_show_details'),
      data: {
        'id': $(this).attr('data-tr-id')
      }
    }).done(function(data) {
      var $previewTr;
      $previewTr = $('<div id="dialog-show-details-tr"></div>');
      $previewTr.html(data).dialog({
        open: function() {
          return $('.ui-dialog-title').append('<i class="fa fa-list-alt"></i> Details');
        },
        close: function() {
          return $previewTr.dialog("destroy");
        },
        width: 550,
        maxHeight: $(window).outerHeight() - 100,
        modal: true
      });
    });
  });

}).call(this);
