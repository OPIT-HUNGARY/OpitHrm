$usernameIcon = $("<i>").attr "class", "fa fa-user"
$passwordIcon = $("<i>").attr "class", "fa fa-key"
$placeholder = $("<div>").addClass "placeholder position-absolute"

$usernameParent = $("#_username").parent()
$passwordParent = $("#_password").parent()

$usernameParent.addClass 'margin-bottom-10'
$passwordParent.addClass 'margin-bottom-10'

$usernameParent.append $placeholder.clone().append $usernameIcon
$passwordParent.append $placeholder.clone().append $passwordIcon

$button = $(":button")
$button.addClass 'background-color-orange float-left color-white border-radius-5'
$button.append ' <i class="fa fa-sign-in"></i>'