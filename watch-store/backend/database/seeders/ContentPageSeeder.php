<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;

/**
 * Seeds the six fixed CMS pages with the copy that used to be hardcoded in
 * the Nuxt frontend, so migrating those pages to fetch from the API doesn't
 * regress what visitors see. Content is written as plain HTML using only
 * tags App\Support\HtmlSanitizer allows.
 */
class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            ContentPage::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'status' => ContentPage::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]
            );
        }
    }

    private function pages(): array
    {
        return [
            [
                'slug' => 'customer-service',
                'title' => 'Customer Service',
                'content' => <<<'HTML'
<h2>Contact Us</h2>
<p><strong>Phone:</strong> +977 1-4423456 / +977 9800000000</p>
<p><strong>Email:</strong> info@watchstore.com.np / support@watchstore.com.np</p>
<p><strong>Business Hours:</strong> Sunday - Friday: 10:00 AM - 8:00 PM, Saturday: 10:00 AM - 6:00 PM</p>
<h2>What We Offer</h2>
<ul>
<li><strong>Authentic Products</strong> — 100% genuine watches with manufacturer warranty</li>
<li><strong>Fast Delivery</strong> — quick shipping across Nepal within 2-5 business days</li>
<li><strong>Expert Support</strong> — professional watchmakers for maintenance and repair</li>
</ul>
<p>See also: FAQ, Shipping Information, Returns &amp; Exchanges, Privacy Policy, and Terms &amp; Conditions.</p>
HTML,
            ],
            [
                'slug' => 'shipping-info',
                'title' => 'Shipping Information',
                'content' => <<<'HTML'
<h2>Shipping Areas</h2>
<p>We ship to all major cities and towns across Nepal:</p>
<ul>
<li>Kathmandu Valley (1-2 business days)</li>
<li>Pokhara (2-3 business days)</li>
<li>Chitwan (2-3 business days)</li>
<li>Biratnagar (3-4 business days)</li>
<li>Other major cities (3-5 business days)</li>
<li>Remote areas (5-7 business days)</li>
</ul>
<h2>Shipping Costs</h2>
<table>
<thead><tr><th>Area</th><th>Cost</th></tr></thead>
<tbody>
<tr><td>Kathmandu Valley</td><td>Free</td></tr>
<tr><td>Major Cities</td><td>Rs. 150</td></tr>
<tr><td>Other Areas</td><td>Rs. 250</td></tr>
<tr><td>Remote Areas</td><td>Rs. 350</td></tr>
</tbody>
</table>
<p>Free shipping on orders above Rs. 10,000.</p>
<h2>Delivery Process</h2>
<ol>
<li>Order confirmation via email/SMS</li>
<li>Order processing (1-2 business days)</li>
<li>Shipping via courier partner</li>
<li>Tracking information sent to customer</li>
<li>Delivery to your address</li>
</ol>
<h2>Important Notes</h2>
<ul>
<li>Delivery times are estimates and may vary</li>
<li>Someone must be available to receive the package</li>
<li>Please provide accurate contact information</li>
<li>For remote areas, additional delivery time may be required</li>
<li>We are not responsible for delays caused by courier partners</li>
</ul>
HTML,
            ],
            [
                'slug' => 'returns-exchanges',
                'title' => 'Returns & Exchanges',
                'content' => <<<'HTML'
<h2>Return Policy</h2>
<p>We want you to be completely satisfied with your purchase. If you're not happy with your order, you may return it within 7 days of delivery.</p>
<ul>
<li>Items must be unused and in original packaging</li>
<li>All tags and accessories must be intact</li>
<li>Proof of purchase required</li>
<li>Return request must be made within 7 days</li>
</ul>
<h2>Exchange Policy</h2>
<p>You can exchange your purchase for a different size, color, or model within 7 days of delivery, subject to availability.</p>
<ul>
<li>Exchange only for items of equal or higher value</li>
<li>Price difference to be paid for higher value items</li>
<li>Store credit provided for lower value exchanges</li>
<li>One-time exchange per purchase</li>
</ul>
<h2>Non-Returnable Items</h2>
<ul>
<li>Customized or personalized watches</li>
<li>Items with visible signs of use</li>
<li>Items without original packaging</li>
<li>Items purchased during special sales/promotions</li>
</ul>
<h2>Refund Process</h2>
<p>Once we receive and inspect your return, we will process your refund within 5-7 business days.</p>
<ul>
<li>Refunds will be made to original payment method</li>
<li>For COD orders, refunds via bank transfer</li>
<li>Shipping costs are non-refundable</li>
<li>You will receive confirmation via email</li>
</ul>
<h2>How to Return</h2>
<ol>
<li>Contact our customer service at support@watchstore.com.np</li>
<li>Provide your order number and reason for return</li>
<li>Receive return authorization and shipping label</li>
<li>Package the item securely with all accessories</li>
<li>Ship to the address provided</li>
<li>Wait for inspection and refund processing</li>
</ol>
HTML,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => <<<'HTML'
<h2>Information We Collect</h2>
<p>We collect information you provide directly, including:</p>
<ul>
<li>Name, email address, phone number</li>
<li>Shipping and billing addresses</li>
<li>Payment information (processed securely)</li>
<li>Order history and preferences</li>
</ul>
<h2>How We Use Your Information</h2>
<ul>
<li>Process and fulfill your orders</li>
<li>Send order confirmations and updates</li>
<li>Provide customer support</li>
<li>Improve our products and services</li>
<li>Send promotional communications (with consent)</li>
</ul>
<h2>Data Security</h2>
<p>We implement appropriate security measures to protect your personal information. All payment transactions are encrypted using SSL technology. We do not store your complete credit card information on our servers.</p>
<h2>Information Sharing</h2>
<p>We do not sell, trade, or rent your personal information. We may share information with:</p>
<ul>
<li>Service providers who assist our operations</li>
<li>Payment processors for transactions</li>
<li>Shipping partners for delivery</li>
<li>Legal authorities when required by law</li>
</ul>
<h2>Your Rights</h2>
<ul>
<li>Access your personal information</li>
<li>Correct inaccurate information</li>
<li>Request deletion of your data</li>
<li>Opt out of marketing communications</li>
<li>Object to processing of your data</li>
</ul>
<h2>Cookies</h2>
<p>We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. You can manage your cookie preferences through your browser settings.</p>
<h2>Changes to This Policy</h2>
<p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>
<h2>Contact Us</h2>
<p>If you have questions about this privacy policy, please contact us at privacy@watchstore.com.np or call +977 1-4423456.</p>
HTML,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms & Conditions',
                'content' => <<<'HTML'
<h2>Acceptance of Terms</h2>
<p>By accessing and using WatchStore Nepal's website, you agree to be bound by these Terms &amp; Conditions. If you do not agree to these terms, please do not use our website.</p>
<h2>Product Information</h2>
<p>We strive to provide accurate product information, including:</p>
<ul>
<li>Product descriptions and specifications</li>
<li>Pricing and availability</li>
<li>Product images (may vary slightly from actual)</li>
</ul>
<p>We reserve the right to correct any errors and update information without prior notice.</p>
<h2>Pricing and Payment</h2>
<p>All prices are in Nepalese Rupees (NPR) and include VAT unless stated otherwise.</p>
<ul>
<li>Prices are subject to change without notice</li>
<li>We reserve the right to refuse any order</li>
<li>Payment must be received before order processing</li>
<li>We accept eSewa, Khalti, cards, and COD</li>
</ul>
<h2>Order Acceptance</h2>
<p>We reserve the right to accept or decline your order at any time, for any reason, including but not limited to product availability, errors in pricing, or suspected fraud.</p>
<h2>Shipping and Delivery</h2>
<p>Shipping times are estimates and not guaranteed. We are not liable for delays caused by natural disasters, courier partner delays, customs or regulatory issues, or force majeure events.</p>
<h2>Returns and Refunds</h2>
<p>Please refer to our Returns &amp; Exchanges policy for detailed information about return conditions, timeframes, and refund processes.</p>
<h2>Warranty</h2>
<p>All watches come with manufacturer warranty. Warranty claims must be made through our authorized service center. Warranty does not cover physical damage or water damage (unless specified), and is void if tampered with by unauthorized personnel.</p>
<h2>Intellectual Property</h2>
<p>All content on this website, including text, images, logos, and designs, is the property of WatchStore Nepal or its licensors and is protected by copyright laws.</p>
<h2>Limitation of Liability</h2>
<p>We shall not be liable for any indirect, incidental, special, or consequential damages arising from the use of our products or services, to the maximum extent permitted by law.</p>
<h2>Governing Law</h2>
<p>These terms and conditions are governed by and construed in accordance with the laws of Nepal. Any disputes shall be subject to the exclusive jurisdiction of the courts of Nepal.</p>
<h2>Changes to Terms</h2>
<p>We reserve the right to modify these terms at any time. Continued use of the website after changes constitutes acceptance of the updated terms.</p>
<h2>Contact Information</h2>
<p>For questions about these terms, please contact us at legal@watchstore.com.np or call +977 1-4423456.</p>
HTML,
            ],
        ];
    }
}
