<?php

namespace guggach\helpers;

/** 
 * International format definitions for decimal separator, thousand separator, dates,
 * times and datetimes.
 * 
 * Is only used if php extension isn't loaded. Otherwise the official ICU standard is
 * used.
 * 
 * Returns an array per local settings. Set the option 'language' => 'de-CH' in yii 
 * config file.
 * 
 * Each language has xxx elements in their array like:
 * [0] = decimal separator ('.')
 * [1] = thousand separator (',')
 * [2] = date short ('y-m-d')
 * [3] = date medium ('Y-m-d')
 * [4] = date long  ('F j, Y')
 * [5] = date full ('l, F j, Y')
 * [6] = time short ('H:i')
 * [7] = time medium ('H:i:s')
 * [8] = time long ('g:i:sA')
 * [9] = time full ('g:i:sA T')
 * [10] = datetime short ('y-m-d H:i')
 * [11] = datetime medium ('Y-m-d H:i:s')
 * [12] = datetime long ('F j, Y g:i:sA')
 * [13] = datetime full ('l, F j, Y g:i:sA T')
 * [14] = currency code
 * 
 * @author Erik Ruedin <e.ruedin@guggach.com>
 * @version 0.1
 */
Class FormatDefs{
    
    static function definition($local) {
        
        $localDef = [
        'en-US' =>
        ['.', ',', 'm/d/y', 'm/d/Y', 'F j, Y', 'l, F j, Y', 'H:i', 'H:i:s', 'g:i:sA', 'g:i:sA T', 'm/d/y H:i', 'm/d/Y H:i:s', 'F j, Y g:i:sA', 'l, F j, Y g:i:sA T', 'USD' ],
        'de-CH' =>
        ['.', '\'', 'd.m.y', 'd.m.Y', 'j. F Y', 'l, j. F Y', 'H:i', 'H:i:s', 'G:i:s', 'G:i:s T', 'd.m.y H:i', 'd.m.Y H:i:s', 'F j, Y g:i:sA', 'l, F j, Y g:i:sA T', 'CHF' ],
        'de-DE' =>
        [',', '.', 'd.m.y', 'd.m.Y', 'j. F Y', 'l, j. F Y', 'H:i', 'H:i:s', 'G:i:s', 'G:i:s T', 'd.m.y H:i', 'd.m.Y H:i:s', 'F j, Y g:i:sA', 'l, F j, Y g:i:sA T', 'EUR' ],

        ];        

        if (isset($localDef[$local])){
            return $localDef[$local];
        } else{
            return [];
        }
    
    }
}



