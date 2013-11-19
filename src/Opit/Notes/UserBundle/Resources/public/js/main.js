/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
$(".menu div").click(function(){
    $(".menu div").removeClass("active");
    $(this).addClass("active");
});
});
