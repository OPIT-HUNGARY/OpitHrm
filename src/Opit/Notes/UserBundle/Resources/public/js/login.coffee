$usernameIcon = $("<i>").attr "class", "fa fa-user"
$passwordIcon = $("<i>").attr "class", "fa fa-key"
$placeholder = $("<div>").addClass "placeholder"

$("#_username").parent().append $placeholder.clone().append $usernameIcon
$("#_password").parent().append $placeholder.clone().append $passwordIcon

$(":button").append ' <i class="fa fa-sign-in"></i>'