<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function pricing()
    {
        return view('pages.pricing');
    }

    public function apiDocs()
    {
        return view('pages.api');
    }

    public function support()
    {
        return view('pages.support');
    }

    public function blog()
    {
        return view('pages.blog');
    }

    public function terms()
    {
        return $this->legalPage('terms');
    }

    public function privacy()
    {
        return $this->legalPage('privacy');
    }

    public function refundPolicy()
    {
        return $this->legalPage('refund');
    }

    private function legalPage(string $page)
    {
        $pages = $this->legalPages();
        abort_unless(isset($pages[$page]), 404);

        return view('pages.legal', [
            'legal' => $pages[$page],
        ]);
    }

    private function legalPages(): array
    {
        return [
            'terms' => [
                'title' => 'Terms of Service',
                'description' => 'Terms for using TrustVerifySMS SMM services, virtual numbers, wallet checkout and account tools.',
                'updated' => 'November 2026',
                'intro' => 'These terms govern your use of TrustVerifySMS. By creating an account, funding a wallet or placing an order, you agree to use the marketplace lawfully and follow each platform\'s own rules.',
                'sections' => [
                    [
                        'heading' => 'Accounts',
                        'paragraphs' => ['You are responsible for your account credentials, wallet activity and orders placed from your account. Keep API keys private and revoke keys you no longer use.'],
                    ],
                    [
                        'heading' => 'Orders and wallet',
                        'paragraphs' => ['Orders are paid from your XAF wallet after balance checks. Product pages show price, minimum/maximum quantities and available status before purchase. Delivery times and final status depend on the selected service and supplier.'],
                    ],
                    [
                        'heading' => 'Acceptable use',
                        'list' => [
                            'Do not use the service for unlawful, fraudulent, deceptive or abusive activity.',
                            'Do not bypass platform rules, impersonate others or access accounts without authorization.',
                            'Do not use virtual numbers to evade verification systems where prohibited.',
                            'Follow the terms of Instagram, TikTok, Facebook, YouTube, Telegram, WhatsApp and any other platform you target.',
                        ],
                    ],
                    [
                        'heading' => 'Suppliers and delivery',
                        'paragraphs' => ['Some services are fulfilled by third-party suppliers. We show verified catalogue information where available, but supplier-side delays, partial delivery or platform-side removal can affect outcomes. Support can review order status and eligible remedies.'],
                    ],
                    [
                        'heading' => 'Payments and refunds',
                        'paragraphs' => ['Wallet deposits are credited only after payment verification. Refund eligibility depends on the product type, order status and supplier result. See the Refund Policy for the review process.'],
                    ],
                    [
                        'heading' => 'Liability',
                        'paragraphs' => ['The marketplace is provided as-is. We are not liable for indirect losses, platform account actions, supplier outages or outcomes caused by misuse of a service.'],
                    ],
                    [
                        'heading' => 'Changes',
                        'paragraphs' => ['We may update these terms as the marketplace evolves. Continued use after an update means you accept the revised terms.'],
                    ],
                ],
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'description' => 'How TrustVerifySMS collects, uses and protects account, order, payment and support data.',
                'updated' => 'November 2026',
                'intro' => 'This policy explains what data TrustVerifySMS handles to operate SMM services, virtual numbers, wallet checkout and support.',
                'sections' => [
                    [
                        'heading' => 'Data we collect',
                        'list' => [
                            'Account details such as name, email and password.',
                            'Order details such as product, quantity, target link/details and status.',
                            'Wallet and payment references needed to verify deposits.',
                            'Support messages, tickets and related order context.',
                            'Basic technical logs needed for security, debugging and abuse prevention.',
                        ],
                    ],
                    [
                        'heading' => 'How we use data',
                        'paragraphs' => ['We use data to create accounts, process orders, verify payments, deliver products, prevent fraud, provide support and keep order history available in your dashboard.'],
                    ],
                    [
                        'heading' => 'Payments',
                        'paragraphs' => ['Payment details are handled by the payment provider. We store payment references and verification status needed to credit wallets, not full card numbers.'],
                    ],
                    [
                        'heading' => 'Sharing',
                        'paragraphs' => ['We share only the data needed to fulfil an order with suppliers or payment providers, and only as required to operate the service. We do not sell customer data.'],
                    ],
                    [
                        'heading' => 'Retention and security',
                        'paragraphs' => ['We keep account, order and transaction records while your account is active and as needed for support, accounting and legal obligations. Access is restricted by role and sensitive credentials are not exposed in the storefront.'],
                    ],
                    [
                        'heading' => 'Your choices',
                        'paragraphs' => ['You can update account details, revoke API keys and request support for account or data questions through the support centre.'],
                    ],
                ],
            ],
            'refund' => [
                'title' => 'Refund Policy',
                'description' => 'How TrustVerifySMS reviews refunds for wallet deposits, SMM orders and virtual-number products.',
                'updated' => 'November 2026',
                'intro' => 'Refunds are reviewed case by case from the order status, supplier result and product terms. Keep your order ID ready when contacting support.',
                'sections' => [
                    [
                        'heading' => 'Wallet deposits',
                        'paragraphs' => ['Deposits are credited only after payment verification. Duplicate or unverified deposits are not credited automatically; include the payment reference when asking support to review a deposit.'],
                    ],
                    [
                        'heading' => 'SMM orders',
                        'paragraphs' => ['SMM delivery depends on the selected service and supplier. If an order is cancelled before supplier submission, or a supplier reports a failed/undelivered result, support can review eligibility for a wallet credit or other remedy under the product terms.'],
                    ],
                    [
                        'heading' => 'Virtual numbers',
                        'paragraphs' => ['Virtual-number products are for receiving SMS verification codes during the active window shown for the order. If no code is received, support will review the order status, expiry and supplier result before deciding the outcome.'],
                    ],
                    [
                        'heading' => 'How to request a review',
                        'list' => [
                            'Open a support ticket or contact support from your account.',
                            'Include the order ID, product name and payment/order reference.',
                            'Wait for support to verify supplier status before a wallet adjustment is made.',
                        ],
                    ],
                    [
                        'heading' => 'Non-refundable cases',
                        'paragraphs' => ['Refunds may be declined when a product was delivered as described, when the target platform removed or penalized the result after delivery, or when the order was completed before the request.'],
                    ],
                ],
            ],
        ];
    }
}
