<?php

namespace App\Actions\Blog;

use App\Models\BlogPost;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenAI;

class GenerateBlogPost
{
    use AsAction;

    public string $commandSignature = 'blog:generate {--topic= : Custom topic for the blog post}';

    public function handle(?string $topic = null): BlogPost
    {
        $settings = app(GeneralSettings::class);
        $siteName = $settings->site_name;
        $themeName = $settings->active_theme;

        $topic ??= $this->generateTopic($themeName);

        $client = OpenAI::client(config('services.openai.key', env('OPENAI_API_KEY')));

        $response = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a content writer for {$siteName}, an ecommerce store. Write engaging, SEO-friendly blog posts. Always respond with valid JSON containing: title, excerpt (2-3 sentences), content (HTML with h2/h3/p tags, 600-900 words). The store theme is: {$themeName}.",
                ],
                [
                    'role' => 'user',
                    'content' => "Write a blog post about: {$topic}",
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        $data = json_decode($response->choices[0]->message->content, true);

        return BlogPost::create([
            'title' => ['en' => $data['title']],
            'slug' => Str::slug($data['title']),
            'excerpt' => ['en' => $data['excerpt']],
            'content' => ['en' => $data['content']],
            'status' => 'published',
            'ai_generated' => true,
            'published_at' => now(),
        ]);
    }

    public function asCommand(Command $command): void
    {
        $post = $this->handle($command->option('topic'));
        $command->info("Blog post created: {$post->getTranslation('title', 'en')}");
        $command->line("URL: /blog/{$post->slug}");
    }

    private function generateTopic(string $theme): string
    {
        $topics = match ($theme) {
            'Activities' => [
                'Top 5 outdoor activities to try this season',
                'How to prepare for your first adventure tour',
                'The best group activities for team building',
                'Travel tips for experience-seekers',
                'Why experiences make the best gifts',
            ],
            default => [
                'How to choose the right product for your needs',
                'Top trending products this season',
                'Gift ideas for every budget',
                'Sustainable shopping: what to look for',
                'Customer favorites: our best-sellers explained',
            ],
        };

        return $topics[array_rand($topics)];
    }
}
