<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DiagnoseBranding extends Command
{
    protected $signature = 'blomfree:branding-diagnose';

    protected $description = 'Print where the uploaded site logo + CEO image are stored and whether the public URL is reachable';

    public function handle(): int
    {
        $this->line('');
        $this->info('BLOMFREE — branding diagnostic');
        $this->line(str_repeat('=', 60));

        $this->line('APP_URL .................. '.(string) config('app.url'));
        $this->line('public disk URL prefix ... '.(string) config('filesystems.disks.public.url'));
        $this->line('public disk root ......... '.(string) config('filesystems.disks.public.root'));
        $this->line('public/storage symlink ... '.$this->describeSymlink(public_path('storage')));
        $this->line('');

        foreach (['site_logo_path', 'ceo_image_path'] as $key) {
            $this->line('Setting key: '.$key);
            $path = Setting::get($key);

            if (blank($path)) {
                $this->warn('  not set (nothing uploaded yet)');
                $this->line('');
                continue;
            }

            $disk = Storage::disk('public');
            $exists = $disk->exists($path);

            $this->line('  stored path .......... '.$path);
            $this->line('  full disk path ....... '.$disk->path($path));
            $this->line('  file exists on disk .. '.($exists ? 'yes' : 'NO — file is missing'));
            $this->line('  Storage::url() ....... '.$disk->url($path));
            $this->line('  Setting::publicUrl() . '.(string) Setting::publicUrl($key));
            $this->line('');
        }

        $this->line('Open one of the Storage::url() values above in a browser. If it');
        $this->line('returns 404, the public/storage symlink is wrong or APP_URL is');
        $this->line('mismatched. If it returns the image, the front-end will too.');
        $this->line('');

        return self::SUCCESS;
    }

    private function describeSymlink(string $path): string
    {
        if (! file_exists($path) && ! is_link($path)) {
            return 'MISSING — run `php artisan storage:link`';
        }

        if (! is_link($path)) {
            return 'NOT a symlink at '.$path;
        }

        return readlink($path) ?: '(unreadable)';
    }
}
