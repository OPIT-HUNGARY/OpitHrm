$loginIcon = $("<i>").attr("class", "fa fa-sign-in");
$usernameIcon = $("<i>").attr("class", "fa fa-user");
$passwordIcon = $("<i>").attr("class", "fa fa-key");
$usernameText = $("<span>").html(" Username");
$passwordText = $("<span>").html(" Password");

$usernamePlaceholder = $("<div>").addClass("placeholder");
$usernamePlaceholder.append($usernameIcon);
$usernamePlaceholder.append($usernameText);

$passwordPlaceholder = $("<div>").addClass("placeholder");
$passwordPlaceholder.append($passwordIcon);
$passwordPlaceholder.append($passwordText);

usernameField = $("#_username").val("");
usernameField.parent().append($usernamePlaceholder);

passwordField = $("#_password").val(""); 
passwordField.parent().append($passwordPlaceholder);

usernameField
  .focus -> $usernameText.addClass("hidden");

usernameField
  .focusout -> if ($(this).val() is "") then $usernameText.removeClass("hidden");

passwordField
  .focus -> $passwordText.addClass("hidden");

passwordField
  .focusout -> if ($(this).val() is "") then $passwordText.removeClass("hidden");
  
$(":button").append(" ").append($loginIcon);