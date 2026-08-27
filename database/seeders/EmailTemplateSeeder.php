<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EmailTemplateType;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

final class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'type' => EmailTemplateType::OrderConfirmation,
                'subject' => [
                    'en' => 'Order #{order_id} - Payment Confirmed',
                    'id' => 'Pesanan #{order_id} - Pembayaran Dikonfirmasi',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
<p>Your payment for order <strong>#{order_id}</strong> has been confirmed.</p>
<p>Product: {product_name}<br>
Total: {total}</p>
{download_section}
{preorder_info}
<p>Thank you for your purchase!</p>',
                    'id' => '<p>Halo {customer_name},</p>
<p>Pembayaran untuk pesanan <strong>#{order_id}</strong> telah dikonfirmasi.</p>
<p>Produk: {product_name}<br>
Total: {total}</p>
{download_section}
{preorder_info}
<p>Terima kasih!</p>',
                ],
                'variables' => ['customer_name', 'order_id', 'product_name', 'total', 'download_url', 'download_section', 'invoice_number', 'is_preorder', 'release_date', 'preorder_info'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::DownloadLink,
                'subject' => [
                    'en' => 'Your Download Link for {product_name}',
                    'id' => 'Link Unduhan untuk {product_name}',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
<p>Here is your download link for <strong>{product_name}</strong>:</p>
{download_section}
<p><em>This link will expire in 24 hours.</em></p>
<p>Thank you!</p>',
                    'id' => '<p>Halo {customer_name},</p>
<p>Berikut link unduhan untuk <strong>{product_name}</strong>:</p>
{download_section}
<p><em>Link ini akan kedaluwarsa dalam 24 jam.</em></p>
<p>Terima kasih!</p>',
                ],
                'variables' => ['customer_name', 'product_name', 'download_url', 'download_section', 'is_preorder', 'preorder_info'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::PreorderRelease,
                'subject' => [
                    'en' => '{product_name} is now available!',
                    'id' => '{product_name} sudah tersedia!',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
<p>Great news! <strong>{product_name}</strong> is now available for download.</p>
<p><a href="{download_url}" target="_blank">Download {product_name}</a></p>
<p><em>This link will expire in 24 hours.</em></p>
<p>Thank you for your patience!</p>',
                    'id' => '<p>Halo {customer_name},</p>
<p>Kabar baik! <strong>{product_name}</strong> sekarang sudah tersedia untuk diunduh.</p>
<p><a href="{download_url}" target="_blank">Unduh {product_name}</a></p>
<p><em>Link ini akan kedaluwarsa dalam 24 jam.</em></p>
<p>Terima kasih atas kesabaran Anda!</p>',
                ],
                'variables' => ['customer_name', 'product_name', 'download_url'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::Welcome,
                'subject' => [
                    'en' => 'Welcome to {site_name}!',
                    'id' => 'Selamat Datang di {site_name}!',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
<p>Thank you for registering at <strong>{site_name}</strong>.</p>
<p>You can now purchase and download digital products from our store.</p>
<p>Happy shopping!</p>',
                    'id' => '<p>Halo {customer_name},</p>
<p>Terima kasih telah mendaftar di <strong>{site_name}</strong>.</p>
<p>Anda sekarang dapat membeli dan mengunduh produk digital dari toko kami.</p>
<p>Selamat berbelanja!</p>',
                ],
                'variables' => ['customer_name', 'site_name'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::PasswordReset,
                'subject' => [
                    'en' => 'Reset Your {site_name} Password',
                    'id' => 'Atur Ulang Kata Sandi {site_name} Anda',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
<p>You are receiving this email because we received a password reset request for your account.</p>
<p><a href="{reset_url}" target="_blank">Reset Password</a></p>
<p><em>This password reset link will expire in 60 minutes.</em></p>
<p>If you did not request a password reset, no further action is required.</p>
<p>Best regards,<br><strong>{site_name} Team</strong></p>',
                    'id' => '<p>Halo {customer_name},</p>
<p>Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.</p>
<p><a href="{reset_url}" target="_blank">Atur Ulang Kata Sandi</a></p>
<p><em>Tautan ini akan kedaluwarsa dalam 60 menit.</em></p>
<p>Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini.</p>
<p>Salam hangat,<br><strong>Tim {site_name}</strong></p>',
                ],
                'variables' => ['customer_name', 'reset_url', 'site_name'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::EmailVerification,
                'subject' => [
                    'en' => 'Verify Your {site_name} Email Address',
                    'id' => 'Verifikasi Alamat Email {site_name} Anda',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
<p>Please verify your email address to activate your account.</p>
<p><a href="{verification_url}" target="_blank">Verify Email</a></p>
<p>If you did not create an account, no further action is required.</p>
<p>Best regards,<br><strong>{site_name} Team</strong></p>',
                    'id' => '<p>Halo {customer_name},</p>
<p>Silakan verifikasi alamat email Anda untuk mengaktifkan akun.</p>
<p><a href="{verification_url}" target="_blank">Verifikasi Email</a></p>
<p>Jika Anda tidak membuat akun, abaikan email ini.</p>
<p>Salam hangat,<br><strong>Tim {site_name}</strong></p>',
                ],
                'variables' => ['customer_name', 'verification_url', 'site_name'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::Newsletter,
                'subject' => [
                    'en' => '{site_name} Newsletter',
                    'id' => 'Newsletter {site_name}',
                ],
                'body' => [
                    'en' => '<p>Hello {customer_name},</p>
{newsletter_content}
<p>Thank you for subscribing!</p>
<p>Best regards,<br><strong>{site_name} Team</strong></p>',
                    'id' => '<p>Halo {customer_name},</p>
{newsletter_content}
<p>Terima kasih telah berlangganan!</p>
<p>Salam hangat,<br><strong>Tim {site_name}</strong></p>',
                ],
                'variables' => ['customer_name', 'site_name', 'newsletter_content'],
                'is_active' => true,
            ],
            [
                'type' => EmailTemplateType::AdminInvitation,
                'subject' => [
                    'en' => 'You have been invited to join as admin',
                    'id' => 'Anda diundang untuk bergabung sebagai admin',
                ],
                'body' => [
                    'en' => '<h1>Welcome!</h1>
<p>Hello {name},</p>
<p>You have been invited by {inviter_name} to join as a <strong>{role}</strong>.</p>
<p><a href="{invite_link}">Click here to set your password</a></p>
<p>This link will expire in 7 days.</p>',
                    'id' => '<h1>Selamat Datang!</h1>
<p>Halo {name},</p>
<p>Anda diundang oleh {inviter_name} untuk bergabung sebagai <strong>{role}</strong>.</p>
<p><a href="{invite_link}">Klik di sini untuk mengatur kata sandi Anda</a></p>
<p>Tautan ini akan kedaluwarsa dalam 7 hari.</p>',
                ],
                'variables' => ['name', 'email', 'role', 'invite_link', 'inviter_name'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }
    }
}
