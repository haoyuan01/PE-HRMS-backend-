<?php

namespace App\Constants;

class RegexConstants
{
    /**
     * accept alpha, comma, hyphen, dot, single quote, spaces only
     */
    const PERSON_NAME = '/^[a-z ,.\'-]+$/i';

    /**
     * at least 1 upper, 1 lower, 1 number, 1 special character, min:8 max:20
     */
    const PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\\\`~!@#$%^&*()_+[\]{}:;"\'<,>.?\/|])[A-Za-z\d\\\`~!@#$%^&*()_+[\]{}:;"\'<,>.?\/|]{8,20}$/';

    /**
     * accept alpha with spaces and hyphen only
     */
    const ALPHA_SPACE_HYPHEN = '/^[\pL\s\-]+$/u';

    /**
     * accept alpha-numeric with spaces and hyphen and underscore only
     */
    const ALPHA_NUMERIC_SPACE_DASH = '/^[\w\-\s]+$/';

    /**
     * accept integer or numeric format as decimal(18,6). eg: 123456789012.123456
     */
    const CURRENCY = '/^\d{1,12}(\.\d{1,6})?$/';

    /**
     * accept html tag except javascript tag
     */
    const HTML = '/^(?!.*(<script[\s\S]*?\>|<\/script\>)).*/';

    /**
     * must have "+" at front eg: +123 (min:1,max:5)
     */
    const PHONE_CODE = '/^(\+)(\d{1,4})$/';

    /**
     * number/integer only
     */
    const NUMBER = '/^[0-9]+$/';

    /**
     * accept integer only (positive or zero)
     */
    const INTEGER = '/^\d+$/';

    /**
     * accept signed integer (positive or negative)
     */
    const SIGNED_INTEGER = '/^-?\d+$/';

    /**
     * accept decimal numbers (positive or zero)
     * e.g. 123, 123.45, 0.5
     */
    const DECIMAL = '/^\d+(\.\d+)?$/';

    /**
     * accept decimal with max 2 decimal places
     * e.g. 123, 123.1, 123.12
     */
    const DECIMAL_2 = '/^\d+(\.\d{1,2})?$/';

    /**
     * accept all character, except "@;:<>", to prevent sql injection
     */
    const INJECTION_PROTECTED = '/^[^;:：；<>《》=@]*$/';

    /**
     * accept all character, except ";:<>", to prevent sql injection
     */
    const DYNAMIC_SEARCH_INJECTION_PROTECTED = '/^[^;:：；<>《》=]*$/';

    /**
     * accept all character, except ";<>", to prevent sql injection
     */
    const WEB_URL = '/^[^;；<>《》]*$/';

    /**
     * accept url format
     */
    const URL = '/^(https?:\/\/)?([\w\-]+\.)+[\w\-]+(\/[\w\-._~:?#[\]@!$&\'()*+,;=]*)?$/i';

    /**
     * accept all character, except ";<>", to prevent sql injection
     */
    const HEX_COLOR_CODE = '/^#([a-f0-9]{6}|[a-f0-9]{3})$/i';
    const HEX_COLOR_CODE_WITH_OXFF = '/^0xFF[0-9A-Fa-f]{6}$/';

    /**
     * accept email format
     */
    const EMAIL = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
}