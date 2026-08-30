<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $general = FaqCategory::updateOrCreate(
            ['slug' => 'general'],
            ['name' => 'General', 'sort_order' => 1]
        );

        $items = [
            ['Are your watches authentic?', 'Yes, all our watches are 100% authentic and come with manufacturer warranty. We source directly from authorized distributors and brand representatives.'],
            ['What payment methods do you accept?', 'We accept eSewa, Khalti, major credit/debit cards, and Cash on Delivery (COD) for orders within Kathmandu valley.'],
            ['How long does delivery take?', 'Delivery within Kathmandu valley takes 1-2 business days. For other major cities, it takes 2-5 business days depending on your location.'],
            ['Do you offer international shipping?', 'Currently, we only ship within Nepal. We are working on expanding our shipping capabilities to international destinations.'],
            ['What is your return policy?', 'We accept returns within 7 days of delivery for unused items in original packaging. Please refer to our Returns & Exchanges page for detailed information.'],
            ['Do you provide warranty on watches?', 'Yes, all watches come with manufacturer warranty ranging from 1-5 years depending on the brand. We also provide our own 1-year store warranty.'],
            ['Can I track my order?', 'Yes — use the Track Order page with your order number and phone number to see live status and delivery history at any time.'],
            ['Do you offer watch servicing and repair?', 'Yes, we have certified watchmakers who provide professional servicing, repair, and maintenance for all major watch brands.'],
            ['How can I contact customer support?', 'You can reach us via phone at +977 1-4423456, email at support@watchstore.com.np, or via the WhatsApp button on any product page.'],
            ['Are the prices shown inclusive of VAT?', 'Yes, all prices shown on our website are inclusive of applicable taxes and VAT.'],
        ];

        foreach ($items as $index => [$question, $answer]) {
            FaqItem::updateOrCreate(
                ['faq_category_id' => $general->id, 'question' => $question],
                ['answer' => "<p>{$answer}</p>", 'sort_order' => $index + 1, 'status' => FaqItem::STATUS_PUBLISHED]
            );
        }
    }
}
