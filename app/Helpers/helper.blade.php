<?php
if (!function_exists('validate_base64')) {

    /**
     * Validate a base64 content.
     *
     * @param string $base64data
     * @param array $allowedMime example ['png', 'jpg', 'jpeg']
     * @return bool
     */
    function validate_base64($base64data, array $allowedMime)
    {
        // strip out data uri scheme information (see RFC 2397)
        if (strpos($base64data, ';base64') !== false) {
            list(, $base64data) = explode(';', $base64data);
            list(, $base64data) = explode(',', $base64data);
        }
    
        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            return false;
        }
    
        // decoding and then reeconding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            return false;
        }
    
        $binaryData = base64_decode($base64data);
    
        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFile, $binaryData);
    
        // guard Against Invalid MimeType
        $allowedMime = Arr::flatten($allowedMime);
    
        // no allowedMimeTypes, then any type would be ok
        if (empty($allowedMime)) {
            return true;
        }
    
        // Check the MimeTypes
        $validation = Illuminate\Support\Facades\Validator::make(
            ['file' => new Illuminate\Http\File($tmpFile)],
            ['file' => 'mimes:' . implode(',', $allowedMime)]
        );
    
        return !$validation->fails();
    }
}

if (!function_exists('removeTrailingZeroAfterComa')) {
    function removeTrailingZeroAfterComa($number, $coma = '.')
    {
        if (!isset($number)) {
            return '';
        }
        $number = (string) $number;
        if (false !== strpos($number, $coma)) {
            $number = rtrim(rtrim($number, '0'), $coma);
        }

        return $number;
    }
}

const DEFAULT_ROUNDING_DIGIT = 3;

if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimal = '.', $thousand = ',', $rounding = null)
    {
        $number = (string) $number;
        if ($number === null || strlen($number) == 0 || (strlen($number) == 1 && $number[0] == '-')) {
            return '-';
        }
        if($rounding !== null){
            $number = sprintf('%.' . $rounding .'f' ,round($number, $rounding));
            $number = (string) $number;
        }
        $negative = $number[0] == '-' ? '-' : '';
        if ($negative == '-') {
            $number = substr($number, 1);
        }
        $broken_number = explode($decimal, $number);
        $broken_number_length = strlen($broken_number[0]) - 1;
        $formatted_number = '';
        while ($broken_number_length >= 0) {
            $prefix = $thousand;
            $indexStart = $broken_number_length - 2;
            $number_length = 3;
            if ($indexStart <= 0) {
                $prefix = '';
                if ($indexStart < 0) {
                    $number_length += $indexStart;
                    $indexStart = 0;
                }
            }
            $formatted_number = substr_replace($formatted_number, $prefix . substr($broken_number[0], $indexStart, $number_length), 0, 0);
            $broken_number_length -= 3;
        }
        $final_formatted_number = $formatted_number . (isset($broken_number[1]) ? ($decimal . $broken_number[1]) : '');
        return $negative . removeTrailingZeroAfterComa($final_formatted_number);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($dateString, $fromFormat = null, $toFormat = 'd M Y', $useTranslate = false)
    {
        if ($dateString === null || strlen($dateString) == 0) {
            return '-';
        }
        if ($fromFormat === null) {
            $dateCarbon = Carbon\Carbon::parse($dateString);
        } else {
            $dateCarbon = Carbon\Carbon::createFromFormat($fromFormat, $dateString);
        }
        $method = 'format';
        if ($useTranslate) {
            $method = 'translatedFormat';
        }
        return $dateCarbon->{$method}($toFormat);
    }
}


if (!function_exists('formatNumberRounding')) {
    function formatNumberRounding($number, $rounding = DEFAULT_ROUNDING_DIGIT, $decimal = '.', $thousand = ',')
    {
        return formatNumber($number, $decimal, $thousand, $rounding);
    }
}