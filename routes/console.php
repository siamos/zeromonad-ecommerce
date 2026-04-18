<?php

use App\Actions\Blog\GenerateBlogPost;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the blog generation as an Artisan command via Action
Artisan::command('blog:generate {--topic= : Custom topic for the blog post}', function () {
    GenerateBlogPost::make()->asCommand($this);
})->purpose('Generate an AI blog post');

// Daily AI blog post at 08:00
Schedule::call(fn () => GenerateBlogPost::run())->dailyAt('08:00');
