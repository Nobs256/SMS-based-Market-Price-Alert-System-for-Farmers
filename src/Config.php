<?php
namespace App;

class Config {
    // Database Credentials
    public const DB_HOST = 'localhost';
    public const DB_NAME = 'market_price_db';
    public const DB_USER = 'root';
    public const DB_PASS = '';

    // Ego SMS API Credentials (for Plain API)
    public const EGO_SMS_USERNAME = 'your user name';
    public const EGO_SMS_PASSWORD = 'yourpassword';
    public const EGO_SMS_SENDER_ID = 'sender id';
    public const EGO_SMS_API_ENDPOINT = 'ego sms end point';
}