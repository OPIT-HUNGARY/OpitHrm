$placeholder = $("<div>").addClass "placeholder position-absolute"
$("#_username").parent().addClass('margin-bottom-10').append $placeholder.clone().append $("<i>").attr("class", "fa fa-user")
$("#_password").parent().addClass('margin-bottom-10').append $placeholder.clone().append $("<i>").attr("class", "fa fa-key")
$(":button").addClass('background-color-green float-left color-white border-radius-5').append(' <i class="fa fa-sign-in"></i>')