<?php

namespace App\Constants;

/**
 * Status Codes
 * 
 * This class provides a centralized way to manage all API status codes.
 * Each constant is named descriptively and assigned a numeric value.
 */
class StatusCode
{
    // HTTP status equivalents
    public const OK = 1000;
    public const STATUS_LOGIN_SUCCESS = 1001;
    public const STATUS_LOGIN_SUCCESS_NO_TOKEN = 1002;
    public const STATUS_LOGOUT_SUCCESS = 1003;
    public const STATUS_CREATED_201 = 1004;
    public const STATUS_ACCEPTED_202 = 1005;
    public const STATUS_NO_CONTENT_204 = 1006;
    
    // Error codes
    public const STATUS_BAD_REQUEST_400 = 2000;
    public const STATUS_UNAUTHORIZED_401 = 2001;
    public const STATUS_FORBIDDEN_403 = 2002;
    public const STATUS_NOT_FOUND_404 = 2003;
    public const STATUS_VALIDATION_ERROR_422 = 2004;
    public const STATUS_LOGIN_INVALID_CREDENTIALS = 2005;
    public const STATUS_SERVER_ERROR_500 = 2006;
    
    // User related codes
    public const STATUS_CREATED_USER_SUCCESSFULLY = 3000;
    public const STATUS_CREATED_USER_UNSUCCESSFULLY = 3001;
    public const STATUS_GET_USER_SUCCESSFULLY = 3002;
    public const STATUS_GET_USER_UNSUCCESSFULLY = 3003;
    public const STATUS_GET_USER_NOT_FOUND = 3004;
    public const STATUS_UPDATE_USER_SUCCESSFULLY = 3005;
    public const STATUS_UPDATE_USER_UNSUCCESSFULLY = 3006;
    public const STATUS_DELETE_USER_SUCCESSFULLY = 3007;
    public const STATUS_DELETE_USER_UNSUCCESSFULLY = 3008;
} 