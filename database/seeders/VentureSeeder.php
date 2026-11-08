<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Venture;
use Database\Seeders\Concerns\ReadsSeedData;
use Illuminate\Database\Seeder;

/**
 * Companies and their products, as confirmed by Charles. Descriptions come
 * from what each product's own website says; no invented figures.
 */
class VentureSeeder extends Seeder
{
    use ReadsSeedData;

    public function run(): void
    {
        $bd = Venture::query()->updateOrCreate(['slug' => 'built-different'], [
            'name' => 'Built Different LTD',
            'role' => 'Founder & CTO',
            'is_founder' => true,
            'url' => 'https://builtdifferent.ng',
            'description' => 'A Lagos software company that designs and builds custom software, mobile apps, AI integrations and cloud systems for businesses, and the parent company of Charles\'s products: Frontstore, DelsuTools, UniAdvisor and JAMB Advisor.',
            'status' => 'featured',
            'sort_order' => 1,
        ]);

        Venture::query()->updateOrCreate(['slug' => 'hamplus-technologies'], [
            'name' => 'Hamplus Technologies',
            'role' => 'CTO',
            'is_founder' => false,
            'url' => 'https://hamplustech.com',
            'logo_path' => $this->media('client-hamplus.jpg', 'Hamplus Technologies', 'media/clients'),
            'years' => '2020 - Now',
            'description' => 'A technology company where Charles leads the delivery of client applications.',
            'status' => 'published',
            'sort_order' => 2,
        ]);

        Venture::query()->updateOrCreate(['slug' => 'gen6ixx'], [
            'name' => 'Gen6ixx LTD',
            'role' => 'CTO',
            'is_founder' => false,
            'status' => 'published',
            'sort_order' => 3,
        ]);

        // Products: link the existing ones to Built Different and add the rest.
        $products = [
            'FrontStore' => [
                'name' => 'Frontstore', 'url' => 'https://frontstore.ng', 'domain' => 'frontstore.ng', 'role' => 'Founder', 'mono' => 'Fs',
                'category' => 'Commerce · SaaS', 'logo' => 'product-frontstore.png',
                'description' => 'Lets small businesses build an online store in under 2 minutes, take orders automatically, handle checkout inside WhatsApp and manage payments and delivery.',
            ],
            'Delsutools' => [
                'name' => 'DelsuTools', 'url' => 'https://delsutools.ng', 'domain' => 'delsutools.ng', 'role' => 'Founder', 'mono' => 'Dt',
                'category' => 'Education · Student tools', 'logo' => 'product-delsutools.png',
                'description' => 'Free academic tools for DELSU and Nigerian university students: a 5.0 GPA calculator, CBT practice, cut-off marks, school fees schedules and past questions.',
            ],
            'UniAdvisor' => [
                'name' => 'UniAdvisor', 'url' => 'https://uniadvisor.ng', 'domain' => 'uniadvisor.ng', 'role' => 'Founder', 'mono' => 'Ua',
                'category' => 'Education · Admissions', 'logo' => 'product-uniadvisor.png',
                'description' => 'Helps students explore 140+ accredited Nigerian universities, compare cut-off marks and fees, check admission eligibility and find scholarships.',
            ],
            'JAMB Advisor' => [
                'name' => 'JAMB Advisor', 'url' => 'https://jambadvisor.com', 'domain' => 'jambadvisor.com', 'role' => 'Founder', 'mono' => 'Ja',
                'category' => 'Education · UTME', 'logo' => 'product-jambadvisor.png',
                'description' => 'Free UTME planning: checks subject combinations, calculates aggregate scores, runs CBT practice and tracks the CAPS admission journey.',
            ],
            'StudyAbroad.ng' => [
                'name' => 'StudyAbroad.ng', 'url' => 'https://studyabroad.ng', 'domain' => 'studyabroad.ng', 'role' => null, 'mono' => 'Sa',
                'category' => 'Education · Study abroad', 'logo' => 'product-studyabroad.png',
                'description' => 'Study abroad discovery for Nigerian and African students: verified universities, entry requirements, scholarships and student visa guidance.',
            ],
            'ScholarshipHQ.ng' => [
                'name' => 'ScholarshipHQ.ng', 'url' => 'https://scholarshiphq.ng', 'domain' => 'scholarshiphq.ng', 'role' => null, 'mono' => 'Sh',
                'category' => 'Education · Scholarships', 'logo' => 'product-scholarshiphq.png',
                'description' => 'Scholarship search and matching directory for Nigerian students, linking to the official portals of local and international awards.',
            ],
        ];

        $order = 1;
        foreach ($products as $match => $p) {
            $product = Product::query()->where('name', $match)->orWhere('name', $p['name'])->first() ?? new Product();
            $product->fill([
                'venture_id' => $bd->id,
                'name' => $p['name'], 'url' => $p['url'], 'domain' => $p['domain'], 'role' => $p['role'], 'mono' => $p['mono'],
                'category' => $p['category'], 'description' => $p['description'],
                'logo_path' => $this->media($p['logo'], $p['name'], 'media/products'),
                'status' => $product->status ?: 'published',
                'sort_order' => $order++,
            ]);
            $product->proof ??= null;
            $product->save();
        }

        // Products outside Built Different keep their place after these.
        Product::query()->whereNull('venture_id')->get()->each(fn ($p) => $p->update(['sort_order' => $order++]));
    }
}
