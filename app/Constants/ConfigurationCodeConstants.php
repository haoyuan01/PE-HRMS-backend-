<?php

namespace App\Constants;

class ConfigurationCodeConstants
{
    // value types
    const VALUE_TYPE_STRING = 'string';
    const VALUE_TYPE_INTEGER = 'integer';
    const VALUE_TYPE_BOOLEAN = 'boolean';
    const VALUE_TYPE_JSON = 'json';
    const VALUE_TYPE_FLOAT = 'float';

    // auth configurations
    const AUTH_RATE_LIMIT = 'auth_rate_limit';
    const AUTH_TOKEN_EXPIRY_DAYS = 'auth_token_expiry_days';
    
}
