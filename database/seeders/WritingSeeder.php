<?php

namespace Database\Seeders;

use App\Models\Writing;
use Illuminate\Database\Seeder;

/**
 * Charles's articles on Medium, DEV and Hashnode (checked November 2026).
 * Cross-posted pieces appear once: the Apify-published DEV versions, and the
 * first "Letter to young developers".
 */
class WritingSeeder extends Seeder
{
    private const WRITINGS = [
        ['Making My Instagram Analyzer Actor Safe For An AI Agent To Call On Its Own', 'DEV (Apify)', 'https://dev.to/apify/making-my-instagram-analyzer-actor-safe-for-an-ai-agent-to-call-on-its-own-3f3a', '2026-09-03'],
        ["I Wired My Instagram Analyzer Actor Straight Into Slack With An MCP Connector - Here's What Broke First", 'DEV (Apify)', 'https://dev.to/apify/i-wired-my-instagram-analyzer-actor-straight-into-slack-with-an-mcp-connector-heres-what-broke-2opc', '2026-09-01'],
        ['How to Process an Image Using ChatGPT', 'Medium', 'https://charlestechy.medium.com/how-to-process-an-image-using-chatgpt-a6a115a7362a', '2024-03-11'],
        ['Designing SQL Database for a blog application', 'DEV', 'https://dev.to/charlestechy/designing-sql-database-for-a-blog-application-2i4i', '2023-06-28'],
        ['Integrating React Native with Firebase: A comprehensive guide', 'Medium', 'https://charlestechy.medium.com/integrating-react-native-with-firebase-a-comprehensive-guide-268a255f670a', '2023-05-10'],
        ['Design Patterns: Best Practices for Writing Maintainable and Scalable Code', 'Medium', 'https://charlestechy.medium.com/design-patterns-best-practices-for-writing-maintainable-and-scalable-code-1be6dd5d5861', '2023-05-10'],
        ['Developing a mini blog just with SQL queries', 'Hashnode', 'https://charlestechy.hashnode.dev/developing-a-mini-blog-just-with-sql-queries-1', '2022-11-26'],
        ['How I got my first Apple laptop as a developer', 'Medium', 'https://charlestechy.medium.com/how-i-got-my-first-apple-laptop-as-a-developer-bd3bab60f591', '2022-10-16'],
        ['The Restored Broken Piece', 'Medium', 'https://charlestechy.medium.com/the-restored-broken-piece-story-teller-5a8d76a1a8f5', '2022-04-27'],
        ["My journey into tech part 1: The grandma's impact", 'Medium', 'https://charlestechy.medium.com/my-journey-into-tech-part-1-the-grandmas-impact-story-teller-e28ade46f719', '2022-04-27'],
        ['How to perform CRUD operations with MySQL using Arctype', 'Medium', 'https://charlestechy.medium.com/how-to-perform-crud-operations-with-mysql-using-arctype-your-favourite-sql-client-c666037ac274', '2022-01-15'],
        ['How to develop an HTML and JS source code viewer using PHP', 'Medium', 'https://charlestechy.medium.com/how-to-develop-an-html-and-js-source-code-viewer-using-php-8c4c5953343d', '2022-01-06'],
        ['How to execute a PHP cron job on the Da-manager control panel', 'Medium', 'https://charlestechy.medium.com/how-to-execute-a-php-cron-job-on-the-new-da-manager-control-panel-cpanel-634c7f094b77', '2022-01-05'],
        ['Difference between add-on, parked and sub-domains', 'Hashnode', 'https://charlestechy.hashnode.dev/difference-between-add-on-parked-and-a-sub-domains', '2020-10-07'],
        ['A program to calculate the area of a triangle using Python', 'DEV', 'https://dev.to/charlestechy/a-program-to-calculate-the-area-of-a-triangle-using-python-3ok6', '2020-09-02'],
        ['A Letter to young Developers', 'DEV', 'https://dev.to/charlestechy/a-letter-to-young-developers-3n2c', '2020-04-14'],
        ['Delta State University Survey Form', 'DEV', 'https://dev.to/charlestechy/delta-state-university-survey-form-by-sedenu-aloaye-charles-1ege', '2020-02-18'],
    ];

    public function run(): void
    {
        foreach (self::WRITINGS as $i => [$title, $publication, $url, $date]) {
            Writing::query()->updateOrCreate(['url' => $url], [
                'title' => $title, 'publication' => $publication, 'published_on' => $date,
                'status' => 'published', 'sort_order' => $i + 1,
            ]);
        }
    }
}
