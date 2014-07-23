#include the CurrencConvert js files.(coffee and js)
require '../../Resources/public/js/CurrencyConverter'

casper.test.begin 'Currency convertet test', 10, suite = (test) ->
    # test rates.
    rates =
        EUR: 300.00
        USD: 200.00
        GBP: 360.00
   
    # test currencyConverter instanse
    curConverter = new CurrencyConverter(rates)
    test.comment 'Start testing the currency converter objecct.'
    test.assertInstanceOf curConverter, CurrencyConverter, 'curConverter is a CurrencyConverter object'
    test.assertEqual curConverter.defaultRate, 1.0, 'The defaultRate property is 1.0'
    # test conversion functions
    test.info 'Test the conversion function.'
    test.assertEqual curConverter.convertCurrency('HUF', 'HUF', 100), 100.00, 'HUF to HUF conversion is correct.'
    test.assertEqual curConverter.convertCurrency('EUR', 'EUR', 500), 500.00, 'EUR to EUR conversion is correct.'
    test.assertEqual curConverter.convertCurrency('EUR', 'HUF', 300), '90000.00', 'EUR to HUF conversion is correct.'
    test.assertEqual curConverter.convertCurrency('HUF', 'EUR', 300), '1.00', 'HUF to EUR conversion is correct.'
    test.assertEqual curConverter.convertCurrency('EUR', 'USD', 300), '450.00', 'EUR to USD conversion is correct.'
    test.assertEqual curConverter.convertCurrency('USD', 'EUR', 300), '200.00', 'USD to EUR conversion is correct.'
    test.assertEqual curConverter.convertCurrency('GBP', 'USD', 600), '1080.00', 'GBP to USD conversion is correct.'
    
    test.info 'Test the get rate of currency function.'
    test.assertEqual curConverter.getRateOfCurrency('GBP'), 360.00, 'GBP equals 360.'
    
    test.done()
