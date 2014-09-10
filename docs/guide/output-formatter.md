Data Formatter
==============

For formatting of outputs Yii provides a formatter class to make date more readable for users.
[[yii\i18n\Formatter]] is a helper class that is registered as an [application component](concept-components.md) name `formatter` by default.

It provides a set of methods for data formatting purpose such as date/time values, numbers and other commonly used formats in a localized way.
The formatter can be used in two different ways.

1. Using the formatting methods(all formatter methods prefixed with `as`) directly:

   ```php
   echo Yii::$app->formatter->asDate('2014-01-01', 'long'); // output: January 1, 2014
   echo Yii::$app->formatter->asPercent(0.125, 2); // output: 12.50%
   echo Yii::$app->formatter->asEmail('cebe@example.com'); // output: <a href="mailto:cebe@example.com">cebe@example.com</a>
   echo Yii::$app->formatter->asBoolean(true); // output: Yes
   ```

2. Using the [[yii\i18n\Formatter::format()|format()]] method using the format name.
   This method is used by classes like GridView and DetailView where you can specify the data format of a column in the
   widget config.

   ```php
   echo Yii::$app->formatter->format('2014-01-01', 'date'); // output: January 1, 2014
   // you can also use an array to specify parameters for the format method:
   // `2` is the value for the $decimals parameter of the asPercent()-method.
   echo Yii::$app->formatter->format(0.125, ['percent', 2]); // output: 12.50%
   ```

All output of the formatter is localized when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
You can configure the [[yii\i18n\Formatter::locale|locale]] property of the formatter for this. If not configured, the
application [[language]] is used as the locale. See the [Section on internationaization](tutorial-i18n.md) for more details.

 * > Note that formatting may differ between different versions of the ICU library compiled with PHP.
 * > You
 *


Formatter is a helper component to format machine readable data of type date, time, different number types 
in user readable formats. Most of this types are in countries differently formatted (eg. date: US -> '10/31/2014,
or DE -> '31.10.2014' or ISO -> '2014-10-31'). Same with decimals and currency values (eg. currency: US-> '$ 13,250.22' or
de-DE -> '13.250,22 �' or de-CH ->'CHF 13'250.22')


Formatter uses the php extension "intl" if the extension is loaded. "Intl" uses [ICU standard](http://site.icu-project.org/) driven
by IBM. "intl" internally knows all formats of all countries and it translates month or day names into the corrcect language. 
Unfortunately ICU don't use same format patterns like php (eg. ICU: 'yyyy-mm-dd' php: 'Y-m-d' or icu: 'yy-m-d' php: 'y-n-j'). 
Therefore formatter class has built in a pattern conversions from php to icu or icu to php. Formatter communicates in their interface
functions per standard with php patterns, but it's also possible to communicate with icu patterns. (compare patterns see [PDF](http://www.guggach.com/tl_files/yii2/Difference%20of%20Date%20formats%20%20used%20in%20Yii2.pdf))

If "intl" isn't loaded formatter works also in the same way. Even the named date, time or datetime formats from icu "short", "medium", "long"
and "full" are supported. Without a separate localized format definition US formats are used. Formatter provides a possibility to enter
localized format patterns in an array (class formatDefs). Formatter uses this definitions but can't translate month or day names into the
correct language.

If the application should support localized outputs "intl" extension should be enabled in php.ini. If Apache (xampp on windows only) doesn't
start correctly then you must install a library of Microsoft ([Visual C++ Redistributable](http://www.microsoft.com/de-de/download/details.aspx?id=30679))

Installation / Configuration
----------------------------
Formatter class must be registered in Yii config (eg. app/config/web.php) in section 'components'. The class must be specified. All other parameters are 
optional. The following example shows all parameters and explain their default values. In most cases default values are ideal.

```php
'components' => [ 
    'formatter' => [ 
        'class' => 'guggach\helpers\Formatter',  
        // 'dateFormat' => 'medium', // default: 'medium'  
        // 'timeFormat' => 'medium',  // default: 'medium'  
        // 'datetimeFormat' => 'medium'  // default: 'medium'  
        // 'dbFormat' => ['date' => 'Y-m-d','time' => 'H:i:s', 'dbtimeshort'=>'H:i' ,'datetime' => 'Y-m-d H:i:s',  
                        'dbdatetimeshort' => 'Y-m-d H:i']  
        // 'local' => 'de-CH' // default: yii::$app->locale  
        // 'timezone' => 'Europe/Berlin' // default: yii::$app->timezone  
        // 'nullDisplay => '<span class="not-set">(not set)</span>' // default '(not set)' translated with yii::t  
        // 'booleanFormat' => ['No', 'Yes']  // default: ['No', 'Yes'] translated with yii::t  
        // 'numberFormatOptions' => []  // see intl NumberFormatter  
        // 'numberTextFormatOptions' => []  // see intl NumberFormatter  
        // 'decimalSeparator' => '.'  // default ICU locale definition  or FormatDefs class  
        // 'thousandSeparator' => ','  // default ICU locale definition  or FormatDefs class  
        // 'currencyCode' => 'USD'  // default ICU locale definition or FormatDefs class  
        // 'RoundingIncrement' => 0.01  // default to number of decimals, currency = 0.01 (except Switzerland 0.05)  
        // 'sizeFormat' => []  // default ['base' = 1024 , 'decimals' = 2, 'decimalSeparator' = null]  
   ]; 
```

Details for each parameter are described in formatter.php.

Using formatter
---------------
Formatter is a general component which is registered in `yii::$app->formatter`. Mostly used funtions are `yii::$app->formatter->format()`
or `yii::$app->formatter->unformat()`.

Usage in Code, example:


	$formattedValueString = yii::$app->formatter->format($value, ['date' , 'd-m-y' , 'php']);
	
	$value = yii::$app->formatter->unformat($formattedValueString, ['currency']);


Format and unformat function has minimal two parameter like 

`(un)format( mixed $value , [ 'format as' , 'optional 1', 'optional 2' ... ])`

1. $value (must): machine readable date, time, datetime or number which hast to be formatted.
2. 'format as' (must): defines what type the value is and what kind of format is expected.
3. 'Option n': The number of paramters and the content is dependant of 'format as'. Details see in further chapters.

In following chapters all formatters are described in detail with input parameters.


###Format or unformat as 'date' or 'time or 'datetime

`format(date (mixed), ['date'/'time'/'datetime' , 'target format pattern', 'input format pattern' , 'format type'])`

####Input:
1. date mixed (must):  
It can be a Unix timestamp, DateTime object or a date string. A string must be in ISO format ('2014-10-05') or in local
date format (de-DE: '05.10.2014'). If another format is given the 'input format pattern' must be specified (eg. US: 'M/d/Y')
2. 'date'/'time'/'datetime' (must):  
A timestamp (DateTime object) can be formatted in a date (eg. '2014-10-05') or a time (eg. 15:20:30) or in a
datetime (eg. '2014-10-05 15:20:30'). This parameter specifies which of these three formats should be done.
3. 'target format pattern' (optinal):  
Formatter has four predefined format patterns: `short` ('y-n-j' = 14-10-25), `medium`('Y-m-d' = 2014-10-25), `long` ('F j, Y' = October 
25, 2014) or 'full' ('l, F j, Y' = Saturday, October 25, 2014). An individual format can be defined by a pattern string like 'd. F Y' ( 25. October 2014).
The individual pattern string is per default in 'php' syntax. Alternatively ICU pattern (dd. MMMM yyyy) could be used. (see format type)
4. 'input format pattern' (optional):  
If input date is a string formatter convert into a DateTime object internally. Formatter recognize ISO format (2014-10-25) or locale format.
All other formats will produce a false result unless the input format is defined in this parameter. Default format pattern is php.
5. 'format type' (optional):  
Per default formatter communicates with 'php' format patterns independent if  ICU is used or not. This makes format handling in Yii easier because
a developer is concentrating to one format all over the application. Nevertheless ICU patterns can be used if this parameter is 'icu'.

####Output:
Formatted string like '25th October 2014'.

  

`unformat(date (string), ['date'/'time'/'datetime', 'target format', 'input format pattern', 'format type'])`

####Input:
1. date as string:  
Formatted date as string. The string must be in ISO format ('2014-10-05') or in local
date format (de-DE: '05.10.2014'). If another format is given the 'input format pattern' must be specified (eg. US: 'M/d/Y').
2. 'date'/'time'/'datetime' (must):  
Defines if input is a date or a time or a datetime string.
3. 'target format' (optional):  
Valid values are `'db'`or `'timestamp'`. Default is 'db' because a user readable date string must be stored in a database. Databases mostly
accept ISO format ('2014-10-25') only. If format is different the database format can be configured in variable `dbFormat` (array).
4. 'input format pattern' (optional):  
see format date function
5. 'format type' (optional): 
see format date function

###Format as Timestamp

`format ('date'/'time'/'datetime' (string), ['timestamp', 'input format pattern'])`

####Input:

Parameter see 'format date'.

####Output:

Long integer (64bit or float) with Unix Timestamp in seconds from 01/01/1970.


###Format or unformat as Integer

`format(value (mixed), ['integer' , 'thousandSeparator'])`

`unformat(value (string), 'integer')`

####Input:
1. value (integer, float, numeric string) (must):  
In format function a numeric string or a float or an integer is necessary. A float with decimals will be mathematically rounded to an integer. 
The unformat function needs a string which can have thousand separators but it must be numeric.
2. 'integer' (string)(must):  
This is the name of formatting function which is used. 
3. 'thousandSeparator' (boolean) (optional):  
Valid values are `true`or `false`. Default is `true`. If value is true the output is formatted with thousand separator concerning the locale
definition or the value of the variable 'thousandSeparator'.

####Output:
Formatted string like '23,456,698'.

###Format or unformat as Double, Number, Decimal
Double, Number, Decimal are all synonyms for floating numbers with decimals.

`format(value (mixed), ['double', decimals (int) , roundIncrement (float), grouping (boolean)`

`unformat(value (string), 'double')`

####Input:
1. value (float, numeric string) (must):  
In format function a numeric string or a float is necessary. A float with decimals will be mathematically rounded to the number of decimals. 
The unformat function needs a string which can have thousand separators but it must be numeric.
2. 'double' (string) (must):  
This is the name of the formatting function. As synonym 'decimals' and 'number' could be used.
3. decimals (integer) (optional):  
Number of decimals after comma. Per default 2 is set. If the number of decimals is less than 6 zeros are filled until number of decimals 
(eg. decimals = 4 -> 3.42 -> 3.4200). 
4. RoundIncrement (float) (optinal):  
If roundIncrement isn't set the float will be rounded to last requested decimal with mathematical rule (eg. decimals = 3 -> 3.45662 --> 3.457). 
If roundIncrement is set to '0.01' float will be rounded to this value (eg. decimals = 3 -> 3.45662 -> 3.46000).
5. Grouping (boolean) (optional):  
If value is true the float will be formatted with thousand separator otherwise not. Default is true.

####Output:
Formatted string like '3,456,698.65'

###Format or unformat as currency

`format(value (mixed), ['currency', 'currency code' (string) , roundIncrement (float), grouping (boolean)`

`unformat(value (string), 'currency')`

###Input:
1. value (float, numeric string) (must):  
In format function a numeric string or a float is necessary. The currency amount with decimals will be mathematically rounded to two decimals. 
The unformat function needs a string which can have thousand separators but it must be numeric.
2. 'currency' (string) (must):  
This is the name of the formatting function.
3. 'currency code' (string) (optional):  
Normally currency code is provided by ICU from locale setting. If another currency is needed the code as string can be set here.
4. RoundIncrement (float) (optinal):  
If roundIncrement isn't set the float will be rounded to last requested decimal with mathematical rule. 
If roundIncrement is set to '0.05' (Switzerland with 5 cents only) float will be rounded to this value (eg. 3.26 -> 3.25 or 3.275 -> 3.30).
5. Grouping (boolean) (optional):  
If value is true the float will be formatted with thousand separator otherwise not. Default is true.

####Output:
Formatted string like '$ 13,256.23'

###Format or unformat as scientific

`format(value (mixed), ['scientific', decimals (integer))`

`unformat(value (string), 'scientific')`

Scientific formats a float to a scientifc string like '23216' to '2.32166E4'.

###Format or unformat as percent

`format(value (mixed), ['percent', decimals (integer) , grouping (boolean)`

`unformat(value (string), 'percent')`

###Input:
1. value (float, numeric string) (must):  
In format function a numeric string or a float is necessary. Value is a factor like '0.75' -> '75%'.
The unformat function needs a string which can have thousand separators but it must be numeric. It will be converted back to a factor. '75%' -> '0.75'
2. 'percent':  
This is the name of the formatting function.
3. decimals (integer) (optional):  
Number of decimals. The formatter rounds a float to the number of decimals.
4. Grouping (boolean) (optional):  
If value is true the percent value will be formatted with thousand separator otherwise not. Default is true.

####Output:
Formatted string like '75.05%'. The unformatter produces a float as factor like 0.7505.

###Further formatters
Formatter has formatting rules for Text, HTML, Email, Boolean etc. 

`format(value (mixed), 'name of formatter')`

Formatters are:  
`boolean:`		Ouput is a string with 'Yes' or 'No' translated to locale setting.  
`email:`		Converts an email address to a 'mailto' link.  
`html:`			Converts a string with html-purifier to avoid XSS attacks.  
`image:`		Formats a html image tag around a path to image.  
`NText:`		Formats the value as an HTML-encoded plain text with newlines converted into breaks.  
`Paragraphs:`	Formats the value as HTML-encoded text paragraphs. Each text paragraph is enclosed within a `<p>` tag.  
`raw:`			Formats the value as is without any formatting. Null values are showed as 'not set'.  
`size:`			Formats a number as byte, kilobyte, megabyte etc. depending on the size of the number. Normally 'Mb' is given unless optional parameter is true.  
`text:`			Formats the value as an HTML-encoded plain text.  
`url:`			Formats the value as a hyperlink. If necessary it adds 'https://'.  

Other functions
---------------

###getLocale()
Shows the current used locale setting in format like 'de-DE'.

###setLocale($locale)
Per default formatter uses locale information from application configuration. If another local definition is requested on the fly this function changes the internal pattern settings, decimal sign and thousand separator to the requested locale. It return this formatter object to enable chaining.

###getDecimalSeparator()
Shows the current separator for decimal number.

###setDecimalSeparator($sign)
Per default formatter uses the decimal separator sign from ICU concerning locale setting. With setDecimalSeparator the value can be overridden.

###getThousandSeparator()
Shows the current separator for thousand grouping.

###SetThousandSeparator($sign)
Per default formatter uses the thousand separator sign from ICU concerning locale setting. With setThousandSeparator the value can be overridden.

###convertPatternPhpToIcu($pattern, $type)
Converts a php date or time format pattern from PHP syntax to ICU syntax (eg. 'Y-m-d' -> 'yyyy-MM-dd'). If pattern is 'short', 'medium', 'long' or 'full' then the parameter 'type' must be defined like 'date', 'time' or 'datetime'. In this case the function delivers the correct ICU pattern (eg. 'medium' and 'date' -> 'dd-MM-yyyy'.

###convertPatternIcuToPhp($pattern, $type)
Converts a ICU date or time format pattern from ICU syntax to PHP syntax (eg. 'yyyy-MM-dd' -> 'Y-m-d'). If pattern is 'short', 'medium', 'long' or 'full' then the parameter 'type' must be defined like 'date', 'time' or 'datetime'. In this case the function delivers the correct PHP pattern (eg. 'medium' and 'date' -> 'd-m-Y'.


