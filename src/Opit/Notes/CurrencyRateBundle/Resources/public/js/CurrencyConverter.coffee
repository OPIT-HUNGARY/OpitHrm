class CurrencyConverter
    defaultRate: 1.0
    
    # Constructor
    constructor: (@rates) ->
        throw new Error("Error. Rates are missing.") if not @rates?
    
    # Convert currency from origin code to destination code
    convertCurrency: (originCode, destinationCode, value) ->
        # If originCode is equal to be destinationCode then return with the value
        if originCode.toUpperCase() is destinationCode.toUpperCase()
            return value
        # If destinationCode is HUF then the rate will be 1
        if 'HUF' is destinationCode.toUpperCase()
            destinationRate = @defaultRate
        else
            destinationRate = @getRateOfCurrency(destinationCode)
        
        result = parseFloat(value) / destinationRate
        
        # If originCode is not HUF then convert to origin currency
        if 'HUF' isnt originCode.toUpperCase()
            originRate = @getRateOfCurrency(originCode)
            result = parseFloat(result) * originRate
            
        return result.toFixed(2)
    
    # Get the currency's rate from the rates property
    getRateOfCurrency: (currencyCode) ->
        currencyCode = currencyCode.toUpperCase()
        throw new Error("Error. Rate could not be found.") if not @rates[currencyCode]
        
        return @rates[currencyCode]
        
window.CurrencyConverter = CurrencyConverter