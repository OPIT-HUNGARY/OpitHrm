$(document).ready ->
    $companyPaidExpense = $('#companyPaidExpense').html()
    $employeePaidExpense = $('#employeePaidExpense').html()
    totalExpense = parseInt($companyPaidExpense) + parseInt($employeePaidExpense)
    $('#companyPaidExpenseVal').html $companyPaidExpense
    $('#employeePaidExpenseVal').html $employeePaidExpense
    $('#totalExpenseVal').html totalExpense
$('.fa-print').on 'mousedown', ->
    window.print()