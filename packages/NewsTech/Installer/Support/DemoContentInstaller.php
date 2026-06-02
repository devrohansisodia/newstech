<?php

namespace NewsTech\Installer\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Models\Author;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Category\Models\Category;
use NewsTech\Comment\Models\Comment;
use NewsTech\Menu\Models\MenuGroup;
use NewsTech\Menu\Models\MenuItem;
use NewsTech\Newsletter\Models\NewsletterSubscriber;
use NewsTech\Page\Models\Page;
use NewsTech\Reader\Models\Reader;
use NewsTech\Tag\Models\Tag;

class DemoContentInstaller
{
    /**
     * @param  array<string, string>  $assets
     * @return array<string, int|string>
     */
    public function seed(bool $force, array $assets, ?AdminUser $adminUser = null): array
    {
        $categories = $this->seedCategories($force);
        $tags = $this->seedTags($force);
        $authors = $this->seedAuthors($force, $assets);
        $pages = $this->seedPages($force);
        $articles = $this->seedArticles($force, $assets, $categories, $tags, $authors);
        $menuSummary = $this->seedMenus($force, $categories, $pages);
        $readerSummary = $this->seedReaderAndBookmarks($force, $articles);
        $commentSummary = $this->seedComments($force, $articles, $adminUser);
        $subscriberSummary = $this->seedSubscribers($force);
        $advertisementSummary = $this->seedAdvertisement($force, $assets, $adminUser);

        return [
            'categories' => count($categories),
            'tags' => count($tags),
            'authors' => count($authors),
            'articles' => count($articles),
            'pages' => count($pages),
            'menu_groups' => $menuSummary['menu_groups'],
            'menu_items' => $menuSummary['menu_items'],
            'readers' => $readerSummary['readers'],
            'bookmarks' => $readerSummary['bookmarks'],
            'history_rows' => $readerSummary['history_rows'],
            'comments' => $commentSummary,
            'subscribers' => $subscriberSummary,
            'advertisements' => $advertisementSummary,
        ];
    }

    /**
     * @return array<string, Category>
     */
    protected function seedCategories(bool $force): array
    {
        $definitions = [
            'politics' => ['name' => 'Politics', 'description' => 'Government, elections, legislation, and policy reporting.'],
            'business' => ['name' => 'Business', 'description' => 'Markets, companies, labor, and economic shifts.'],
            'technology' => ['name' => 'Technology', 'description' => 'Product launches, regulation, startups, and digital infrastructure.'],
            'sports' => ['name' => 'Sports', 'description' => 'Match coverage, leagues, athlete profiles, and analysis.'],
            'entertainment' => ['name' => 'Entertainment', 'description' => 'Film, streaming, music, celebrity, and culture coverage.'],
            'world' => ['name' => 'World', 'description' => 'International affairs, diplomacy, conflict, and regional developments.'],
            'health' => ['name' => 'Health', 'description' => 'Public health, hospitals, medicine, and wellness reporting.'],
            'science' => ['name' => 'Science', 'description' => 'Research, space, climate, and scientific discovery coverage.'],
            'education' => ['name' => 'Education', 'description' => 'Schools, universities, policy, and learning innovation.'],
            'lifestyle' => ['name' => 'Lifestyle', 'description' => 'Travel, food, design, personal finance, and modern living.'],
        ];

        $categories = [];
        $sortOrder = 1;

        foreach ($definitions as $slug => $definition) {
            $categories[$slug] = $this->upsertModel(Category::class, ['slug' => $slug], [
                'name' => $definition['name'],
                'description' => $definition['description'],
                'meta_title' => $definition['name'].' News | '.config('newstech.brand.name'),
                'meta_description' => 'Latest '.$definition['name'].' coverage, analysis, and newsroom updates from '.config('newstech.brand.name').'.',
                'status' => true,
                'sort_order' => $sortOrder++,
            ], $force);
        }

        return $categories;
    }

    /**
     * @return array<string, Tag>
     */
    protected function seedTags(bool $force): array
    {
        $definitions = [
            'breaking-news' => 'Fast-moving developments and urgent updates.',
            'analysis' => 'Deeper context and newsroom analysis.',
            'exclusive' => 'Original reporting and exclusive interviews.',
            'policy-watch' => 'Policy decisions, budgets, and legislation.',
            'elections' => 'Election campaigns, polling, and voting updates.',
            'markets' => 'Stocks, bonds, rates, and company results.',
            'startups' => 'Startup launches, venture funding, and founders.',
            'ai' => 'Artificial intelligence, automation, and platform strategy.',
            'cybersecurity' => 'Cyber incidents, resilience, and security policy.',
            'climate' => 'Climate adaptation, emissions, and weather trends.',
            'public-health' => 'Community health, outbreaks, and prevention.',
            'research' => 'Studies, labs, and academic findings.',
            'campus' => 'Higher education, student life, and universities.',
            'schools' => 'K-12 education, classrooms, and district operations.',
            'media' => 'Broadcast, publishing, and creator economy shifts.',
            'streaming' => 'Entertainment platforms and distribution strategy.',
            'football' => 'Matchday coverage and team performance.',
            'wellness' => 'Daily habits, fitness, and balanced living.',
            'travel' => 'Destinations, hospitality, and mobility trends.',
            'editor-picks' => 'Highlighted editorial selections.',
        ];

        $tags = [];

        foreach ($definitions as $slug => $description) {
            $tags[$slug] = $this->upsertModel(Tag::class, ['slug' => $slug], [
                'name' => Str::of($slug)->replace('-', ' ')->headline()->toString(),
                'description' => $description,
                'meta_title' => Str::of($slug)->replace('-', ' ')->headline()->toString().' | '.config('newstech.brand.name'),
                'meta_description' => $description,
                'status' => true,
            ], $force);
        }

        return $tags;
    }

    /**
     * @param  array<string, string>  $assets
     * @return array<string, Author>
     */
    protected function seedAuthors(bool $force, array $assets): array
    {
        $definitions = [
            'riya-sen' => ['name' => 'Riya Sen', 'email' => 'riya.sen@newstech.test', 'designation' => 'Political Editor', 'asset' => 'authors.riya_sen'],
            'arjun-mehta' => ['name' => 'Arjun Mehta', 'email' => 'arjun.mehta@newstech.test', 'designation' => 'Business Correspondent', 'asset' => 'authors.arjun_mehta'],
            'maya-kapoor' => ['name' => 'Maya Kapoor', 'email' => 'maya.kapoor@newstech.test', 'designation' => 'Technology Reporter', 'asset' => 'authors.maya_kapoor'],
            'dev-malhotra' => ['name' => 'Dev Malhotra', 'email' => 'dev.malhotra@newstech.test', 'designation' => 'Sports Writer', 'asset' => 'authors.dev_malhotra'],
            'sana-qureshi' => ['name' => 'Sana Qureshi', 'email' => 'sana.qureshi@newstech.test', 'designation' => 'Health and Science Reporter', 'asset' => 'authors.sana_qureshi'],
            'neel-rohatgi' => ['name' => 'Neel Rohatgi', 'email' => 'neel.rohatgi@newstech.test', 'designation' => 'Culture and Lifestyle Editor', 'asset' => 'authors.neel_rohatgi'],
        ];

        $authors = [];

        foreach ($definitions as $slug => $definition) {
            $authors[$slug] = $this->upsertModel(Author::class, ['slug' => $slug], [
                'name' => $definition['name'],
                'email' => $definition['email'],
                'designation' => $definition['designation'],
                'bio' => $definition['name'].' leads demo newsroom coverage with a focus on clear reporting, sharper context, and public-facing editorial credibility.',
                'avatar' => $assets[$definition['asset']] ?? null,
                'facebook_url' => null,
                'twitter_url' => 'https://example.com/'.Str::slug($definition['name']),
                'linkedin_url' => 'https://www.linkedin.com/in/'.Str::slug($definition['name']),
                'website_url' => null,
                'meta_title' => $definition['name'].' | '.config('newstech.brand.name'),
                'meta_description' => 'Recent reporting and profile information for '.$definition['name'].' at '.config('newstech.brand.name').'.',
                'status' => true,
            ], $force);
        }

        return $authors;
    }

    /**
     * @return array<string, Page>
     */
    protected function seedPages(bool $force): array
    {
        $pages = [];

        $definitions = [
            'about' => ['title' => 'About Us', 'description' => 'Learn how the NewsTech demo newsroom is structured and what the editorial product demonstrates.'],
            'contact' => ['title' => 'Contact Us', 'description' => 'Reach the NewsTech editorial, partnerships, or support desks.'],
            'privacy-policy' => ['title' => 'Privacy Policy', 'description' => 'How the NewsTech demo handles subscriptions, reader accounts, and analytics-friendly data practices.'],
            'terms' => ['title' => 'Terms & Conditions', 'description' => 'Baseline terms for reading, sharing, and using the NewsTech demo site.'],
            'editorial-policy' => ['title' => 'Editorial Policy', 'description' => 'Standards for sourcing, corrections, attribution, and editorial independence.'],
            'advertise' => ['title' => 'Advertise With Us', 'description' => 'Overview of sponsorship placements, audience focus, and branded placement options.'],
        ];

        foreach ($definitions as $slug => $definition) {
            $pages[$slug] = $this->upsertModel(Page::class, ['slug' => $slug], [
                'title' => $definition['title'],
                'content' => $this->pageContent($definition['title']),
                'status' => true,
                'meta_title' => $definition['title'].' | '.config('newstech.brand.name'),
                'meta_description' => $definition['description'],
                'focus_keyword' => Str::of($definition['title'])->lower()->toString(),
            ], $force);
        }

        return $pages;
    }

    /**
     * @param  array<string, string>  $assets
     * @param  array<string, Category>  $categories
     * @param  array<string, Tag>  $tags
     * @param  array<string, Author>  $authors
     * @return array<int, Article>
     */
    protected function seedArticles(bool $force, array $assets, array $categories, array $tags, array $authors): array
    {
        $headlines = $this->articleHeadlines();
        $authorRotation = array_values($authors);
        $categoryTagMap = $this->categoryTagMap();
        $articles = [];
        $articlesPerCategory = app()->runningUnitTests()
            ? (int) config('newstech-installer.demo.testing_articles_per_category', 2)
            : (int) config('newstech-installer.demo.production_articles_per_category', 8);

        $position = 0;

        foreach ($categories as $slug => $category) {
            $featuredImagePath = $assets['categories.'.$slug.'_cover'] ?? null;
            $inlineImagePath = $assets['categories.'.$slug.'_detail'] ?? $featuredImagePath;
            $selectedHeadlines = array_slice($headlines[$slug], 0, $articlesPerCategory);

            foreach ($selectedHeadlines as $index => $headline) {
                $focusKeyword = Str::of($headline)->lower()->replace([',', ':'], '')->toString();
                $articleSlug = Str::slug($headline);
                $author = $authorRotation[$position % count($authorRotation)];
                $publishedAt = Carbon::now()->subHours($position * 3 + 2);

                $article = $this->upsertArticle(
                    slug: $articleSlug,
                    attributes: [
                        'category_id' => $category->getKey(),
                        'author_id' => $author->getKey(),
                        'title' => $headline,
                        'excerpt' => $this->articleExcerpt($headline, $category->name),
                        'content' => $this->articleContent(
                            title: $headline,
                            category: $category,
                            focusKeyword: $focusKeyword,
                            featuredImagePath: $featuredImagePath,
                            inlineImagePath: $inlineImagePath
                        ),
                        'featured_image' => $featuredImagePath,
                        'view_count' => 180 + ($position * 11),
                        'status' => 'published',
                        'is_featured' => $position < 8 || $index === 0,
                        'is_breaking' => $position < 5 || $index === 1,
                        'published_at' => $publishedAt,
                        'scheduled_at' => null,
                        'meta_title' => $headline.' | '.config('newstech.brand.name'),
                        'meta_description' => $this->articleMetaDescription($headline, $category->name),
                        'focus_keyword' => $focusKeyword,
                    ],
                    tagIds: collect($categoryTagMap[$slug])
                        ->map(fn (string $tagSlug) => $tags[$tagSlug]->getKey())
                        ->take(3)
                        ->all(),
                    force: $force
                );

                $articles[] = $article;
                $position++;
            }
        }

        return $articles;
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, Page>  $pages
     * @return array{menu_groups:int,menu_items:int}
     */
    protected function seedMenus(bool $force, array $categories, array $pages): array
    {
        $headerMenu = $this->upsertModel(MenuGroup::class, ['location' => 'header'], [
            'name' => 'Header Navigation',
            'status' => true,
        ], $force);

        $footerMenu = $this->upsertModel(MenuGroup::class, ['location' => 'footer'], [
            'name' => 'Footer Navigation',
            'status' => true,
        ], $force);

        $mobileMenu = $this->upsertModel(MenuGroup::class, ['location' => 'mobile'], [
            'name' => 'Mobile Navigation',
            'status' => true,
        ], $force);

        $menuItems = [
            [$headerMenu, 'Home', 'custom_url', ['url' => '/', 'sort_order' => 1]],
            [$headerMenu, 'Politics', 'category', ['category_id' => $categories['politics']->getKey(), 'sort_order' => 2]],
            [$headerMenu, 'Business', 'category', ['category_id' => $categories['business']->getKey(), 'sort_order' => 3]],
            [$headerMenu, 'Technology', 'category', ['category_id' => $categories['technology']->getKey(), 'sort_order' => 4]],
            [$headerMenu, 'World', 'category', ['category_id' => $categories['world']->getKey(), 'sort_order' => 5]],
            [$headerMenu, 'About Us', 'page', ['page_id' => $pages['about']->getKey(), 'sort_order' => 6]],
            [$headerMenu, 'Contact Us', 'page', ['page_id' => $pages['contact']->getKey(), 'sort_order' => 7]],
            [$footerMenu, 'About Us', 'page', ['page_id' => $pages['about']->getKey(), 'sort_order' => 1]],
            [$footerMenu, 'Privacy Policy', 'page', ['page_id' => $pages['privacy-policy']->getKey(), 'sort_order' => 2]],
            [$footerMenu, 'Terms & Conditions', 'page', ['page_id' => $pages['terms']->getKey(), 'sort_order' => 3]],
            [$footerMenu, 'Editorial Policy', 'page', ['page_id' => $pages['editorial-policy']->getKey(), 'sort_order' => 4]],
            [$footerMenu, 'Advertise With Us', 'page', ['page_id' => $pages['advertise']->getKey(), 'sort_order' => 5]],
            [$mobileMenu, 'Home', 'custom_url', ['url' => '/', 'sort_order' => 1]],
            [$mobileMenu, 'Sports', 'category', ['category_id' => $categories['sports']->getKey(), 'sort_order' => 2]],
            [$mobileMenu, 'Entertainment', 'category', ['category_id' => $categories['entertainment']->getKey(), 'sort_order' => 3]],
            [$mobileMenu, 'Lifestyle', 'category', ['category_id' => $categories['lifestyle']->getKey(), 'sort_order' => 4]],
            [$mobileMenu, 'Contact Us', 'page', ['page_id' => $pages['contact']->getKey(), 'sort_order' => 5]],
        ];

        foreach ($menuItems as [$group, $label, $type, $values]) {
            $this->upsertModel(MenuItem::class, [
                'menu_group_id' => $group->getKey(),
                'label' => $label,
                'type' => $type,
            ], array_merge([
                'url' => null,
                'parent_id' => null,
                'page_id' => null,
                'category_id' => null,
                'status' => true,
                'opens_in_new_tab' => false,
            ], $values), $force);
        }

        return [
            'menu_groups' => 3,
            'menu_items' => count($menuItems),
        ];
    }

    /**
     * @param  array<int, Article>  $articles
     * @return array{readers:int,bookmarks:int,history_rows:int}
     */
    protected function seedReaderAndBookmarks(bool $force, array $articles): array
    {
        $reader = $this->upsertModel(Reader::class, ['email' => 'reader@newstech.test'], [
            'name' => 'Demo Reader',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now()->subDay(),
            'bio' => 'Demo reader account for local bookmarking, history, and account flows.',
            'website' => 'https://example.com/demo-reader',
        ], $force);

        $folder = $this->upsertModel(BookmarkFolder::class, [
            'reader_id' => $reader->getKey(),
            'slug' => 'weekend-reads',
        ], [
            'name' => 'Weekend Reads',
            'sort_order' => 1,
        ], $force);

        $bookmarks = 0;
        $historyRows = 0;

        foreach (array_slice($articles, 0, min(4, count($articles))) as $index => $article) {
            $this->upsertModel(Bookmark::class, [
                'reader_id' => $reader->getKey(),
                'article_id' => $article->getKey(),
            ], [
                'folder_id' => $index < 2 ? $folder->getKey() : null,
            ], $force);
            $bookmarks++;

            $this->upsertModel(ReaderArticleHistory::class, [
                'reader_id' => $reader->getKey(),
                'article_id' => $article->getKey(),
            ], [
                'last_viewed_at' => now()->subHours($index + 1),
                'view_count' => 1 + $index,
            ], $force);
            $historyRows++;
        }

        return [
            'readers' => 1,
            'bookmarks' => $bookmarks,
            'history_rows' => $historyRows,
        ];
    }

    /**
     * @param  array<int, Article>  $articles
     */
    protected function seedComments(bool $force, array $articles, ?AdminUser $adminUser = null): int
    {
        $count = 0;

        foreach (array_slice($articles, 0, min(3, count($articles))) as $index => $article) {
            /** @var Comment $parent */
            $parent = $this->upsertModel(Comment::class, [
                'article_id' => $article->getKey(),
                'email' => 'reader'.($index + 1).'@example.test',
                'parent_id' => null,
            ], [
                'name' => 'Reader '.($index + 1),
                'website' => null,
                'content' => 'This demo article presents the story well and gives the homepage a realistic discussion thread.',
                'status' => 'approved',
                'is_spam' => false,
                'spam_reason' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'NewsTech Installer',
                'approved_at' => now()->subHours(2),
                'moderated_at' => now()->subHours(2),
                'moderated_by' => $adminUser?->getKey(),
            ], $force);
            $count++;

            $this->upsertModel(Comment::class, [
                'article_id' => $article->getKey(),
                'email' => 'editor-reply@example.test',
                'parent_id' => $parent->getKey(),
            ], [
                'name' => 'NewsTech Desk',
                'website' => null,
                'content' => 'Thanks for reading. The demo site includes sample replies so threaded discussion can be reviewed immediately after install.',
                'status' => 'approved',
                'is_spam' => false,
                'spam_reason' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'NewsTech Installer',
                'approved_at' => now()->subHour(),
                'moderated_at' => now()->subHour(),
                'moderated_by' => $adminUser?->getKey(),
            ], $force);
            $count++;
        }

        return $count;
    }

    protected function seedSubscribers(bool $force): int
    {
        $emails = [
            'briefing@newstech.test',
            'audience@newstech.test',
            'sponsor@newstech.test',
        ];

        foreach ($emails as $index => $email) {
            $this->upsertModel(NewsletterSubscriber::class, ['email' => $email], [
                'unsubscribe_token' => hash('sha256', $email),
                'status' => NewsletterSubscriber::STATUS_ACTIVE,
                'source' => $index === 0 ? 'homepage' : 'article',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'NewsTech Installer',
                'subscribed_at' => now()->subDays($index + 1),
                'unsubscribed_at' => null,
            ], $force);
        }

        return count($emails);
    }

    protected function seedAdvertisement(bool $force, array $assets, ?AdminUser $adminUser = null): int
    {
        $this->upsertModel(Advertisement::class, ['slug' => 'city-bank-homepage-banner'], [
            'name' => 'City Bank Homepage Banner',
            'type' => Advertisement::TYPE_IMAGE,
            'status' => Advertisement::STATUS_ACTIVE,
            'slot_key' => 'homepage_top',
            'title' => 'City Bank Growth Campaign',
            'image_path' => $assets['advertisements.homepage_banner'] ?? null,
            'target_url' => 'https://example.com/city-bank-campaign',
            'html_content' => null,
            'open_in_new_tab' => true,
            'nofollow' => false,
            'sponsored' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => null,
            'priority' => 10,
            'impressions_count' => 0,
            'clicks_count' => 0,
            'created_by' => $adminUser?->getKey(),
            'updated_by' => $adminUser?->getKey(),
        ], $force);

        return 1;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function articleHeadlines(): array
    {
        return [
            'politics' => [
                'Cabinet Coalition Opens Summer Session With Spending Pledge',
                'Election Panel Tightens Rules for Digital Campaign Ads',
                'Opposition Bloc Targets Fuel Levy in Assembly Debate',
                'Regional Leaders Press Capital on Flood Recovery Funds',
                'Budget Talks Shift After Tax Relief Package Wins Support',
                'Parliament Committee Seeks Timeline for Housing Reform',
                'Governor Signals New Push for District Health Spending',
                'Parties Reset Ground Strategy Ahead of Municipal Vote',
            ],
            'business' => [
                'Retail Chains Lift Hiring Plans Ahead of Festival Quarter',
                'Bond Market Rally Extends After Surprise Inflation Cooldown',
                'Airport Expansion Deal Gives Logistics Firms New Capacity',
                'Midcap Shares Lead Market Higher on Domestic Demand Bets',
                'Factory Survey Shows Export Orders Stabilizing in May',
                'Power Utilities Seek Fresh Capital for Grid Upgrades',
                'Consumer Brands Rework Pricing as Input Costs Ease',
                'Hotel Operators Report Strong Summer Booking Momentum',
            ],
            'technology' => [
                'Chip Designers Back New Local Packaging Corridor',
                'AI Hiring Race Pushes Startups Toward Faster Product Releases',
                'Cloud Providers Expand Data Center Footprint Across Region',
                'Fintech Platforms Add New Fraud Controls for Retail Users',
                'Telecom Upgrade Plan Opens Door to Lower Latency Services',
                'Device Makers Bet on Premium Cameras for Holiday Cycle',
                'Cyber Teams Drill for Supply Chain Intrusion Scenarios',
                'University Labs Win Backing for Battery Storage Research',
            ],
            'sports' => [
                'League Leaders Hold Nerve in Rain-Delayed Title Decider',
                'National Team Camps Open With Youth Prospects in Focus',
                'Club Owners Back Stadium Upgrade Ahead of New Season',
                'Coach Rotates Squad After Heavy Travel Stretch',
                'Marathon Circuit Adds Night Race to Downtown Calendar',
                'Women\'s Side Extends Unbeaten Run With Late Winner',
                'Broadcast Deal Raises Expectations for Regional League',
                'Injury Update Gives Fans Hope Before Weekend Clash',
            ],
            'entertainment' => [
                'Streaming Platforms Reset Release Calendars for Holiday Window',
                'Festival Jury Highlights Regional Films in Packed Finale',
                'Studio Betting on Mid-Budget Drama as Audience Tastes Shift',
                'Concert Promoters Expand Arena Dates After Presale Rush',
                'Award Season Strategy Starts Early for Documentary Labels',
                'Writers Room Boom Fuels New Talent Pipeline for Series Work',
                'Box Office Rebound Helps Smaller Theatres Plan Refresh',
                'Music Labels Turn to Live Sessions for Fan Retention',
            ],
            'world' => [
                'Trade Delegations Reopen Talks After Border Logistics Snag',
                'Regional Summit Puts Food Security at Center of Agenda',
                'Aid Corridors Expand as Weather Threatens Coastal Villages',
                'Diplomatic Push Intensifies Over Maritime Shipping Route',
                'Currency Pressure Shapes Election Messaging Across Neighbors',
                'Foreign Ministers Meet to Stabilize Energy Supply Chain',
                'City Rebuild Efforts Gain Pace After International Pledges',
                'Observers Track Voter Turnout in Closely Watched Poll',
            ],
            'health' => [
                'District Hospitals Add Weekend Clinics to Ease Wait Times',
                'Public Health Teams Track Early Monsoon Infection Rise',
                'Pharmacy Chains Expand Preventive Screening Partnerships',
                'Nutrition Program Targets Schools With New Meal Grants',
                'Doctors Push for Faster Rural Ambulance Dispatch Network',
                'Mental Health Helplines See Steady Uptake Among Students',
                'Research Group Studies Long-Term Benefits of Sleep Clinics',
                'Vaccination Drive Focuses on High-Risk Urban Wards',
            ],
            'science' => [
                'Telescope Array Captures Sharpest View of Distant Dust Ring',
                'Climate Scientists Model Faster Heat Stress in River Cities',
                'Ocean Team Maps Deepwater Habitat Near Shipping Channel',
                'Materials Lab Finds Cheaper Route for Solar Cell Coating',
                'Space Startup Schedules New Launch Window for Research CubeSat',
                'Wildlife Survey Records Rare Return of Grassland Species',
                'Seismic Study Revises Risk Outlook for Mountain Corridor',
                'University Network Shares Open Data on Air Quality Trends',
            ],
            'education' => [
                'School District Pilots Extended Learning Blocks in Core Subjects',
                'Colleges Expand Need-Based Aid Before Admission Cycle Opens',
                'Teacher Training Push Focuses on Early Literacy Recovery',
                'Edtech Providers Add Local Language Support for Classrooms',
                'Parents Seek Clarity on New Attendance and Assessment Rules',
                'University Researchers Test Hybrid Labs for Distance Learners',
                'Campus Housing Projects Gain Funding After Enrollment Surge',
                'Career Counselors Build New Pathways Into Technical Roles',
            ],
            'lifestyle' => [
                'Neighborhood Food Markets Turn to Late-Night Weekend Formats',
                'Design Studios Blend Local Craft With Modern Apartment Trends',
                'Travel Operators See Demand Shift Toward Short Coastal Breaks',
                'Personal Finance Coaches Warn Against Credit Card Reward Traps',
                'Fitness Communities Embrace Low-Equipment Morning Sessions',
                'Home Garden Retailers Report Strong Demand for Native Plants',
                'Chefs Build Seasonal Menus Around Regional Produce Networks',
                'Remote Workers Look to Smaller Cities for Better Housing Value',
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function categoryTagMap(): array
    {
        return [
            'politics' => ['policy-watch', 'elections', 'analysis'],
            'business' => ['markets', 'analysis', 'editor-picks'],
            'technology' => ['ai', 'cybersecurity', 'startups'],
            'sports' => ['football', 'breaking-news', 'editor-picks'],
            'entertainment' => ['streaming', 'media', 'editor-picks'],
            'world' => ['breaking-news', 'analysis', 'climate'],
            'health' => ['public-health', 'research', 'analysis'],
            'science' => ['research', 'climate', 'analysis'],
            'education' => ['campus', 'schools', 'policy-watch'],
            'lifestyle' => ['travel', 'wellness', 'editor-picks'],
        ];
    }

    protected function articleExcerpt(string $headline, string $categoryName): string
    {
        return $headline.' anchors the '.$categoryName.' desk with a polished summary, realistic metadata, and linked demo coverage for homepage and detail-page review.';
    }

    protected function articleMetaDescription(string $headline, string $categoryName): string
    {
        return $headline.' is part of the '.$categoryName.' demo coverage on '.config('newstech.brand.name').', with search-ready metadata and publish-ready newsroom structure.';
    }

    protected function articleContent(
        string $title,
        Category $category,
        string $focusKeyword,
        ?string $featuredImagePath,
        ?string $inlineImagePath,
    ): string {
        $featuredImageUrl = $featuredImagePath ? asset('storage/'.$featuredImagePath) : null;
        $inlineImageUrl = $inlineImagePath ? asset('storage/'.$inlineImagePath) : $featuredImageUrl;
        $categoryUrl = route('newstech.categories.show', ['slug' => $category->slug]);
        $aboutUrl = route('newstech.about');

        $imageBlock = $inlineImageUrl
            ? '<figure><img src="'.$inlineImageUrl.'" alt="'.$category->name.' coverage illustration"><figcaption>Installer demo illustration for the '.$category->name.' desk.</figcaption></figure>'
            : '';

        return implode('', [
            '<h2>'.$title.'</h2>',
            '<p>'.$focusKeyword.' leads the latest '.strtolower($category->name).' update, giving editors a realistic article body with enough copy for homepage cards, detail pages, and the SEO toolkit to review.</p>',
            '<p>The demo install is designed to feel presentable from the first page load, so each story includes structured headings, internal links, descriptive metadata, and a publish-ready flow that mirrors real newsroom usage.</p>',
            $imageBlock,
            '<h3>Why this matters now</h3>',
            '<p>Readers landing on the homepage should see category variety, featured coverage, and rich internal linking. This story contributes to that layout while pointing to the <a href="'.$categoryUrl.'">'.$category->name.' section</a> and related editorial pages.</p>',
            '<p>For release-readiness review, the content also links back to <a href="'.$aboutUrl.'">About NewsTech</a> and uses storage-backed local media only, so no remote image dependency is required for a complete demo install.</p>',
            $featuredImageUrl ? '<p><a href="'.$featuredImageUrl.'">Open the story visual directly</a> for asset-path validation during manual QA.</p>' : '',
        ]);
    }

    protected function pageContent(string $title): string
    {
        return implode('', [
            '<h2>'.$title.'</h2>',
            '<p>This installer-managed page gives the demo site a complete information architecture immediately after setup, including realistic copy, reusable SEO fields, and navigation-friendly structure.</p>',
            '<p>The goal is not legal finality or publication-specific policy advice. It is a presentable, editable baseline that demonstrates how static pages fit into the NewsTech package-driven CMS foundation.</p>',
            '<h3>How to adapt this page</h3>',
            '<p>Replace this demo text with your real newsroom content, connect it to menus or campaigns as needed, and continue refining the editorial experience through the centralized Admin and Frontend packages.</p>',
        ]);
    }

    protected function upsertArticle(string $slug, array $attributes, array $tagIds, bool $force): Article
    {
        /** @var ?Article $existingArticle */
        $existingArticle = Article::query()->where('slug', $slug)->first();

        if ($existingArticle && ! $force) {
            $existingArticle->tags()->syncWithoutDetaching($tagIds);

            return $existingArticle;
        }

        /** @var Article $article */
        $article = $this->upsertModel(
            modelClass: Article::class,
            uniqueAttributes: ['slug' => $slug],
            values: $attributes,
            force: $force
        );

        $article->tags()->sync($tagIds);

        return $article;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $uniqueAttributes
     * @param  array<string, mixed>  $values
     */
    protected function upsertModel(string $modelClass, array $uniqueAttributes, array $values, bool $force): Model
    {
        /** @var ?Model $existingModel */
        $existingModel = $modelClass::query()->where($uniqueAttributes)->first();

        if ($existingModel) {
            if ($force) {
                $existingModel->fill($values);
                $existingModel->save();
            }

            return $existingModel;
        }

        /** @var Model $model */
        $model = $modelClass::query()->create(array_merge($uniqueAttributes, $values));

        return $model;
    }
}
