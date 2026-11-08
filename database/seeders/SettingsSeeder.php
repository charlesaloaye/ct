<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Support\SiteSettings;
use Database\Seeders\Concerns\ReadsSeedData;
use Illuminate\Database\Seeder;

/** The site's real copy, as it read before the admin existed. Everything here is editable in the admin. */
class SettingsSeeder extends Seeder
{
    use ReadsSeedData;

    public function run(): void
    {
        $site = $this->data('site');
        $name = 'Charles Aloaye Sedenu';

        SiteSettings::save('identity', [
            'name' => $name,
            'shortName' => 'Charles Aloaye',
            'initials' => 'CA',
            'mark' => 'ct',
            'logoPath' => $this->media('logo-charlesaloaye.jpg', $name),
            'wordmark' => 'charlestechy',
            'role' => 'Software engineer · Founder · Educator',
            'authorTitle' => 'Senior full-stack engineer and TRCN-certified educator, Lagos',
            'location' => 'Lagos, Nigeria',
            'publicName' => 'Charles Aloaye',
            'alternateNames' => ['CharlesTechy', $name],
            'jobTitles' => ['Software Engineer', 'Technology Entrepreneur', 'SaaS Builder', 'TRCN-certified Educator'],
        ]);

        SiteSettings::save('about', [
            'body' => 'Charles Aloaye Sedenu, known publicly as Charles Aloaye and online as CharlesTechy, is a software engineer, technology entrepreneur and SaaS builder based in Lagos, Nigeria. He has spent more than ten years building web applications, SaaS platforms and mobile products for organisations in Nigeria, the United Kingdom and beyond.

He is the founder and CTO of Built Different LTD, a Lagos software company that builds custom software for businesses and runs its own products: Frontstore, which lets small businesses sell online and take payments; DelsuTools, academic tools for Delta State University students; UniAdvisor, a university and admissions discovery platform; and JAMB Advisor, a UTME planning tool. He is also CTO of Hamplus Technologies and of Gen6ixx LTD.

His client work includes a payroll and payslip system for the Delta State University bursary, a student application portal for Edu Abroad, logistics automation for Evergreen Updates in London, and an investment education platform for Wavecrest Trading Institute. Much of it is codebase rescue: taking over stalled projects and getting them to launch.

He works across the stack with PHP and Laravel, Python and FastAPI, JavaScript and TypeScript with React, Next.js and React Native, REST APIs, PostgreSQL and MySQL with Prisma and SQLAlchemy, and Docker, Redis and cloud deployment.

Charles is a certified teacher registered with the Teachers Registration Council of Nigeria (TRCN). He has built and taught a full-stack development curriculum, and he mentors developers and teams.',
            'bioShort' => 'Charles Aloaye (CharlesTechy) is a Lagos-based software engineer, technology entrepreneur and TRCN-certified educator. He is the founder and CTO of Built Different LTD, the company behind Frontstore, DelsuTools, UniAdvisor and JAMB Advisor.',
            'bioMedium' => 'Charles Aloaye (CharlesTechy) is a software engineer, technology entrepreneur and TRCN-certified educator based in Lagos, Nigeria. He is the founder and CTO of Built Different LTD, which builds custom software for businesses and runs its own products, including Frontstore, DelsuTools, UniAdvisor and JAMB Advisor, and he is CTO of Hamplus Technologies and Gen6ixx LTD.

Over more than ten years he has built web applications, SaaS platforms and mobile products for organisations in Nigeria and the United Kingdom, often rescuing stalled codebases and getting them to launch. He works with PHP and Laravel, Python and FastAPI, and JavaScript and TypeScript with React and Next.js, and he teaches and mentors developers.',
            'nameNote' => 'Please write his name as Charles Aloaye (full name: Charles Aloaye Sedenu). CharlesTechy is his online handle.',
        ]);

        SiteSettings::save('hero', [
            'headline' => "I ship the software other teams couldn't finish.",
            'intro' => 'Senior full-stack engineer in Lagos with 10+ years building web and mobile products in Laravel, FastAPI, React and React Native - and rescuing stalled codebases for teams in Nigeria, the UK and beyond. As a TRCN-certified educator, I also teach and mentor developers.',
            'portraitPath' => $this->media('charlestechy1.jpg', $name),
            'portraitAlt' => $name,
            'primaryLabel' => 'Book a session or mentorship',
            'primaryUrl' => '/contact',
            'cvLabel' => 'Download CV',
            'shippedTitle' => 'DELSU Bursary payroll',
            'shippedNote' => 'Live · bursarydelsu.net',
            'shippedUrl' => 'https://bursarydelsu.net',
        ]);

        SiteSettings::save('availability', [
            'enabled' => true,
            'text' => $site['availability'],
            'shortText' => $site['availabilityShort'],
        ]);

        SiteSettings::save('contact', [
            'email' => $site['contact']['email'],
            // No phone or WhatsApp on the public site, by the owner's choice.
            'phone' => null,
            'whatsappUrl' => null,
            'location' => $site['contact']['location'],
            'cvPath' => $this->media('charlesaloaye.pdf', "{$name} CV", 'cv'),
            'cvDownloadName' => 'charles-aloaye-sedenu-cv.pdf',
        ]);

        SiteSettings::save('socials', $site['socials']);

        SiteSettings::save('appearance', ['theme' => 'light', 'accent' => '#3DDC97']);

        SiteSettings::save('sections', [
            'clients' => ['label' => 'Trusted by teams at'],
            'work' => ['eyebrow' => 'Selected work', 'heading' => 'Software that moves real money, students and shipments.', 'linkLabel' => 'All {count} projects'],
            'workPage' => ['eyebrow' => 'Work', 'title' => 'Projects I have built and shipped.', 'lead' => 'Web platforms, portals and automation for universities, logistics firms, investors and publishers in Nigeria, the UK and beyond.'],
            'products' => ['eyebrow' => 'Products I run', 'heading' => "I don't just build for clients. I ship my own software.", 'note' => 'Built and run under Built Different, my product studio making SaaS and automation tools for emerging markets.'],
            'rescue' => ['eyebrow' => 'Codebase rescue', 'heading' => 'Your last developer vanished. The app is half-built. Launch is close.', 'body' => "This is my specialty. I read the code you have, tell you plainly what's salvageable, then fix, finish and ship it - usually without a rewrite.", 'cta' => 'Get a codebase audit'],
            'services' => ['eyebrow' => 'Services', 'heading' => 'From blank repo to production.', 'lead' => 'One engineer across the whole stack, so nothing gets lost between a designer, a backend dev and a mobile contractor.'],
            'process' => ['eyebrow' => "How we'll work", 'heading' => 'No black boxes. You see progress every week.'],
            'toolkit' => ['eyebrow' => 'Toolkit', 'heading' => 'The stack I ship with'],
            'experience' => ['eyebrow' => 'Experience', 'heading' => "Where I've built"],
            'contact' => [
                'heading' => 'Got a project, or a stuck one?',
                'lead' => 'Tell me where it is and where it needs to be. I reply within 24 hours, and calls work across WAT, GMT and EST.',
                'needs' => array_keys(Message::NEEDS),
                'button' => 'Send message',
                'success' => "Thanks - your message is in. I'll reply within 24 hours.",
                'altPrompt' => null,
                'altLink' => null,
            ],
            'blog' => [
                'eyebrow' => 'Blog',
                'title' => 'Notes from shipping software in Lagos.',
                'lead' => 'Codebase rescues, payments in Nigeria, mobile builds and the business of freelancing for clients worldwide.',
                'searchHint' => 'Try "Laravel" or "Paystack"',
                'newsletterTitle' => 'Get new articles in your inbox',
                'newsletterText' => 'One email when something new goes up. No spam.',
            ],
            'article' => ['ctaTitle' => 'Got a stalled project?', 'ctaText' => "Send me the repo and I'll tell you honestly what it'll take to launch.", 'ctaButton' => 'Get a codebase audit'],
            'caseStudy' => ['ctaTitle' => 'Need something like this built?', 'ctaButton' => 'Book a session or mentorship →'],
            'aboutPage' => ['eyebrow' => 'About', 'title' => 'Charles Aloaye', 'lead' => 'Software engineer, technology entrepreneur and TRCN-certified educator in Lagos, Nigeria.'],
            'experiencePage' => ['eyebrow' => 'Experience', 'title' => "Where I've built and led.", 'lead' => 'Leadership roles at the companies I run, and more than ten years of engineering roles for teams in Nigeria and the UK.'],
            'venturesPage' => ['eyebrow' => 'Ventures', 'title' => 'Companies I founded and lead.', 'lead' => 'Built Different is my software company and the home of my products. I am also CTO at Hamplus Technologies and Gen6ixx.'],
            'productsPage' => ['eyebrow' => 'Products', 'title' => 'Software I build and run.', 'lead' => 'SaaS and digital products, most of them built and operated under Built Different.'],
            'expertisePage' => ['eyebrow' => 'Expertise', 'title' => 'The stack I build with.', 'lead' => 'From backend APIs and databases to web and mobile front ends, deployment and product architecture.'],
            'mediaPage' => ['eyebrow' => 'Media & press', 'title' => 'Media and press.', 'lead' => 'Interviews, talks and coverage, plus approved bios and photos for editors and organisers.'],
            'contactPage' => ['eyebrow' => 'Contact', 'title' => "Let's talk.", 'lead' => 'Projects, codebase rescues, mentorship, speaking or press. I reply within 24 hours.'],
        ]);

        SiteSettings::save('seo', [
            'titleSuffix' => '- Charles Aloaye',
            'shareImagePath' => null,
            'home' => [
                'title' => 'Charles Aloaye - Software Engineer, Founder & Educator',
                'description' => 'Charles Aloaye (CharlesTechy) is a Lagos-based software engineer, founder of Built Different and TRCN-certified educator building SaaS, web and mobile products.',
            ],
            'work' => [
                'title' => 'Work',
                'description' => 'Case studies and projects: university payroll, student portals, logistics automation, investment platforms and publishing.',
            ],
            'blog' => [
                'title' => 'Blog',
                'description' => 'Notes from shipping software in Lagos: codebase rescues, payments in Nigeria, mobile builds and freelancing for clients worldwide.',
            ],
            'about' => ['title' => 'About Charles Aloaye', 'description' => 'Charles Aloaye (CharlesTechy) is a software engineer, technology entrepreneur and TRCN-certified educator in Lagos, and founder and CTO of Built Different LTD.'],
            'experience' => ['title' => 'Experience', 'description' => 'Leadership roles at Built Different, Hamplus Technologies and Gen6ixx, and engineering roles for teams in Nigeria and the UK.'],
            'ventures' => ['title' => 'Ventures', 'description' => 'Companies Charles Aloaye founded and leads: Built Different LTD, Hamplus Technologies and Gen6ixx LTD.'],
            'products' => ['title' => 'Products', 'description' => 'Products built and run under Built Different, including Frontstore, DelsuTools, UniAdvisor and JAMB Advisor.'],
            'expertise' => ['title' => 'Expertise', 'description' => 'Laravel, FastAPI, React, Next.js, React Native, PostgreSQL, Docker and cloud deployment: the stack Charles Aloaye builds with.'],
            'media' => ['title' => 'Media and press', 'description' => 'Interviews and coverage of Charles Aloaye, plus approved bios and photos for editors and event organisers.'],
            'contact' => ['title' => 'Contact', 'description' => 'Get in touch with Charles Aloaye about a project, codebase rescue, mentorship, speaking or press.'],
            'googleVerification' => null,
            'bingVerification' => null,
            'gaMeasurementId' => null,
        ]);
    }
}
