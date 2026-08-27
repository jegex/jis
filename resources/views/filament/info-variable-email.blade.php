@php
    use App\Enums\EmailTemplateType;

    $type = match ($type) {
        EmailTemplateType::OrderConfirmation => '<code>{customer_name}</code>, <code>{order_id}</code>, <code>{order_number}</code>, <code>{product_name}</code>, <code>{total}</code>, <code>{download_url}</code>, <code>{download_section}</code>, <code>{invoice_number}</code>, <code>{is_preorder}</code>, <code>{release_date}</code>, <code>{preorder_info}</code>',
        EmailTemplateType::DownloadLink => '<code>{customer_name}</code>, <code>{product_name}</code>, <code>{download_url}</code>, <code>{download_section}</code>, <code>{is_preorder}</code>, <code>{preorder_info}</code>',
        EmailTemplateType::PreorderRelease => '<code>{customer_name}</code>, <code>{product_name}</code>, <code>{download_url}</code>',
        EmailTemplateType::PasswordReset => '<code>{customer_name}</code>, <code>{reset_url}</code>, <code>{site_title}</code>',
        EmailTemplateType::EmailVerification => '<code>{customer_name}</code>, <code>{verification_url}</code>, <code>{site_title}</code>',
        EmailTemplateType::Welcome => '<code>{customer_name}</code>, <code>{site_title}</code>',
        EmailTemplateType::Newsletter => '<code>{customer_name}</code>, <code>{site_title}</code>, <code>{newsletter_content}</code> <= masih dikembangkan',
        EmailTemplateType::AdminInvitation => '<code>{name}</code>, <code>{email}</code>, <code>{role}</code>, <code>{invite_link}</code>, <code>{inviter_name}</code>',
        default => '',
    };
@endphp
<div class="info-variable-email">
    <p>
        <strong>Supports HTML. Available variables:</strong> {!! $type !!}
    </p>
    <style>
        .info-variable-email code {
            font-size: small;
            padding: 2px 4px;
            background-color: var(--gray-100);
        }

        @supports (color:color-mix(in lab, red, red)) {
            .info-variable-email code:where(.dark,.dark *) {
                background-color: color-mix(in oklab, var(--color-white) 5%, transparent);
            }
        }
    </style>
</div>
