// Generated by CoffeeScript 1.6.3
(function() {
  var $form, url;

  $form = $('#searchTravelForm');

  url = $form.attr('action');

  $('#searchButton').click(function() {
    event.preventDefault();
    if (!$form.formIsEmpty()) {
      return;
    }
    $.ajax({
      method: 'POST',
      url: url,
      data: $form.serialize()
    }).done(function(response) {
      $('#list-table').html(response);
    });
  });

  $('#resetButton').click(function() {
    $.ajax({
      method: 'POST',
      url: url,
      data: {
        'resetForm': true
      }
    }).done(function(response) {
      $('#list-table').html(response);
    });
  });

  $('#searchFormTitle').click(function() {
    $(this).children('i').toggleClass('fa-chevron-down');
    return $(this).next().slideToggle();
  });

}).call(this);
