<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailTemplateType: string
{
    case Welcome = 'welcome';
    case OrderConfirmation = 'order_confirmation';
    case DownloadLink = 'download_link';
    case PreorderRelease = 'preorder_release';
    case Newsletter = 'newsletter';
    case PasswordReset = 'password_reset';
    case EmailVerification = 'email_verification';
    case AdminInvitation = 'admin_invitation';
}
