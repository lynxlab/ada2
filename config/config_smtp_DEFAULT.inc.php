<?php
/**
 * SMTP CONFIGURATION
 */
define ('ADA_SMTP_HOST', 'localhost');
define ('ADA_SMTP_PORT', 25);
define ('ADA_SMTP_AUTH', false);
define ('ADA_SMTP_AUTHTYPE', ''); // One of: 'CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2' or leave empty
define ('ADA_SMTP_SECURE', null); // use PHPMailer constants (like PHPMailer::ENCRYPTION_SMTPS) or null
define ('ADA_SMTP_USERNAME', '');
define ('ADA_SMTP_PASSWORD', '');
define ('ADA_SMTP_DEBUG', false);
