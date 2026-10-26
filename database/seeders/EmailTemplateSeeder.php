<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'template_key' => 'welcome_email',
                'name' => 'User Welcome Email',
                'subject' => 'Welcome to {company_name}!',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #0c8be8;">Welcome, {user_name}!</h2>
                    <p>Thank you for registering at {company_name}. Your account has been created successfully.</p>
                    <p>You can now log in, manage domains, and access your billing dashboard.</p>
                    <div style="margin: 30px 0;"><a href="{login_url}" style="background-color: #0c8be8; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Login to Your Account</a></div>
                    <p>Best regards,<br>{company_name} Team</p>
                </div>',
                'available_placeholders' => ['{company_name}', '{user_name}', '{login_url}'],
            ],
            [
                'template_key' => 'domain_registered',
                'name' => 'Domain Registered Successfully',
                'subject' => 'Domain {domain_name} Has Been Registered!',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #10b981;">Domain Registered!</h2>
                    <p>Dear {user_name},</p>
                    <p>We are pleased to inform you that your domain <strong>{domain_name}</strong> has been successfully registered.</p>
                    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Domain:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>{domain_name}</strong></td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Period:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">{years} Year(s)</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Expires:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">{expires_at}</td></tr>
                    </table>
                    <p><a href="{manage_url}" style="background-color: #0c8be8; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px;">Manage Domain & DNS</a></p>
                </div>',
                'available_placeholders' => ['{user_name}', '{domain_name}', '{years}', '{expires_at}', '{manage_url}'],
            ],
            [
                'template_key' => 'domain_renewed',
                'name' => 'Domain Renewed Successfully',
                'subject' => 'Domain {domain_name} Has Been Renewed!',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #10b981;">Domain Renewed!</h2>
                    <p>Dear {user_name},</p>
                    <p>Your domain <strong>{domain_name}</strong> has been successfully renewed for {years} year(s).</p>
                    <p>New expiration date: <strong>{expires_at}</strong></p>
                </div>',
                'available_placeholders' => ['{user_name}', '{domain_name}', '{years}', '{expires_at}'],
            ],
            [
                'template_key' => 'domain_expiring',
                'name' => 'Domain Expiration Reminder',
                'subject' => 'URGENT: Domain {domain_name} expires in {days_left} days',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #f59e0b;">Domain Expiration Alert</h2>
                    <p>Dear {user_name},</p>
                    <p>Your domain <strong>{domain_name}</strong> is set to expire on <strong>{expires_at}</strong> ({days_left} days remaining).</p>
                    <p>To avoid service disruption and potential domain loss, please renew your domain as soon as possible.</p>
                    <p><a href="{renew_url}" style="background-color: #f59e0b; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Renew Domain Now</a></p>
                </div>',
                'available_placeholders' => ['{user_name}', '{domain_name}', '{expires_at}', '{days_left}', '{renew_url}'],
            ],
            [
                'template_key' => 'invoice_created',
                'name' => 'New Invoice Generated',
                'subject' => 'New Invoice #{invoice_number} Generated',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #0c8be8;">Invoice #{invoice_number}</h2>
                    <p>Dear {user_name},</p>
                    <p>A new invoice #{invoice_number} has been generated for your account.</p>
                    <p>Amount Due: <strong>{total_amount}</strong></p>
                    <p>Due Date: <strong>{due_date}</strong></p>
                    <p><a href="{pay_url}" style="background-color: #0c8be8; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px;">View & Pay Invoice</a></p>
                </div>',
                'available_placeholders' => ['{user_name}', '{invoice_number}', '{total_amount}', '{due_date}', '{pay_url}'],
            ],
            [
                'template_key' => 'invoice_paid',
                'name' => 'Invoice Payment Confirmation',
                'subject' => 'Payment Received for Invoice #{invoice_number}',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #10b981;">Payment Received!</h2>
                    <p>Dear {user_name},</p>
                    <p>We have successfully received your payment of <strong>{amount_paid}</strong> for Invoice #{invoice_number}.</p>
                    <p>Thank you for your business!</p>
                </div>',
                'available_placeholders' => ['{user_name}', '{invoice_number}', '{amount_paid}'],
            ],
            [
                'template_key' => 'two_factor_code',
                'name' => '2FA One-Time Passcode',
                'subject' => 'Your Verification Code: {code}',
                'body_html' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2>Two-Factor Authentication</h2>
                    <p>Use the following one-time code to complete your login or action:</p>
                    <h1 style="letter-spacing: 5px; color: #0c8be8;">{code}</h1>
                    <p>This code expires in 10 minutes. If you did not request this, please change your password immediately.</p>
                </div>',
                'available_placeholders' => ['{code}'],
            ],
        ];

        foreach ($templates as $tmpl) {
            EmailTemplate::updateOrCreate(
                ['template_key' => $tmpl['template_key']],
                $tmpl
            );
        }
    }
}
