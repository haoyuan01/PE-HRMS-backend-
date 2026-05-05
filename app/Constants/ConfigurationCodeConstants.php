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
    const AUTH_LOGIN_MAX_ATTEMPTS = 'auth_login_max_attempts';
    const AUTH_LOGIN_LOCKOUT_DURATION_MINUTES = 'auth_login_lockout_duration_minutes';

    // image configurations
    const IMAGE_MAX_SIZE_MB = 'image_max_size_mb';
    const IMAGE_ALLOWED_TYPES = 'image_allowed_types';

}
