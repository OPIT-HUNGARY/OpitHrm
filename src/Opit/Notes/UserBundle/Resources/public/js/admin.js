// Generated by CoffeeScript 1.7.1
(function() {
  var deleteJobTitle, inverse;

  $("#addJobTitle").click(function() {
    return $.ajax({
      method: 'GET',
      url: Routing.generate('OpitNotesUserBundle_admin_show_jobtitle', {
        id: 0
      })
    }).done(function(data) {
      $('<div id="dialog-editjobtitle"></div>').html(data).dialog({
        title: '<i class="fa fa-list-alt"></i> Create job title',
        width: 750,
        modal: true,
        buttons: {
          Create: function() {
            return $.ajax({
              type: 'POST',
              global: false,
              url: Routing.generate('OpitNotesUserBundle_admin_add_jobtitle', {
                id: 0
              }),
              data: $('#addjobtitle_frm').serialize()
            }).done(function(data) {
              var response;
              response = data;
              return $.ajax({
                type: 'POST',
                global: false,
                url: Routing.generate('OpitNotesUserBundle_admin_list_jobtitle'),
                data: {
                  "showList": 1
                }
              }).done(function(data) {
                var validationResult;
                $('#list-table').html(data);
                validationResult = $(document).data('notes').funcs.showAlert(response, "create", "Job title created successfully");
                if (validationResult === true) {
                  return $('#dialog-editjobtitle').dialog("destroy");
                }
              });
            });
          },
          Close: function() {
            $('#dialog-editjobtitle').dialog("destroy");
          }
        }
      });
      return;
    });
  });

  $("#list-table").on("click", ".list-jobtitle", function() {
    var id;
    id = $(this).attr("data-id");
    return $.ajax({
      method: 'GET',
      url: Routing.generate('OpitNotesUserBundle_admin_show_jobtitle', {
        id: id
      })
    }).done(function(data) {
      $('<div id="dialog-editjobtitle"></div>').html(data).dialog({
        title: '<i class="fa fa-list-alt"></i> Edit job title',
        width: 750,
        modal: true,
        buttons: {
          Save: function() {
            return $.ajax({
              type: 'POST',
              global: false,
              url: Routing.generate('OpitNotesUserBundle_admin_add_jobtitle', {
                id: id
              }),
              data: $('#addjobtitle_frm').serialize()
            }).done(function(data) {
              var response;
              response = data;
              return $.ajax({
                type: 'POST',
                global: false,
                url: Routing.generate('OpitNotesUserBundle_admin_list_jobtitle'),
                data: {
                  "showList": 1
                }
              }).done(function(data) {
                var validationResult;
                $('#list-table').html(data);
                validationResult = $(document).data('notes').funcs.showAlert(response, "create", "Job title modified successfully");
                if (validationResult === true) {
                  return $('#dialog-editjobtitle').dialog("destroy");
                }
              });
            });
          },
          Close: function() {
            $('#dialog-editjobtitle').dialog("destroy");
          }
        }
      });
      return;
    });
  });

  $('#delete').click(function() {
    return deleteJobTitle();
  });

  $('#list-table').on("click", ".delete-single-jobtitle", function() {
    var $checkbox;
    $checkbox = $(this).closest('tr').find(':checkbox');
    $checkbox.prop('checked', true);
    return deleteJobTitle();
  });

  deleteJobTitle = function() {
    var url;
    url = Routing.generate('OpitNotesUserBundle_admin_delete_jobtitle');
    return $(document).data('notes').funcs.deleteAction('Job title delete', 'job title(s)', url, '.list-delete-jobtitle');
  };

  $('#list-table').on("click", "th .fa-trash-o", function() {
    return $('.list-delete-jobtitle').filter(function() {
      return !this.disabled;
    }).checkAll();
  });

  inverse = false;

  $('form').on('click', '.fa-sort', function() {
    return inverse = $(document).data('notes').funcs.clientSideListOrdering($(this), inverse);
  });

  $('form').on('click', '.order-text', function() {
    return inverse = $(document).data('notes').funcs.clientSideListOrdering($(this).parent().find('i'), inverse);
  });

}).call(this);
