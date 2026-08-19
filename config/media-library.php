<?php

return [
    // Reuses the same disk as everything else (S3 / S3-compatible) — keeps
    // the "never store files locally/in MySQL" rule from the original spec
    // consistent for avatars too, not just message attachments. Falls back
    // to FILESYSTEM_DISK so it always matches whatever the rest of the app
    // (AttachmentService) is configured to use.
    'disk_name' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 's3')),

    'max_file_size' => 1024 * 1024 * 5, // 5MB — generous for a profile photo

    // Conversions (the 'thumb' one registered on User) run inline by
    // default per registerMediaConversions()'s ->nonQueued() call. Flip
    // that to ->queued() later and this becomes true for background processing.
    'queue_conversions_by_default' => false,

    'queue_name' => '',

    'queue_connection_name' => env('QUEUE_CONNECTION', 'redis'),

    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    'temporary_directory_path' => null,

    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    'url_generator' => Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,

    'moves_media_on_update' => false,

    'version_urls' => false,

    'responsive_images' => [
        'width_calculator' => Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator::class,
        'use_tiny_placeholders' => true,
        'tiny_placeholder_generator' => Spatie\MediaLibrary\Support\TinyPlaceholderGenerator\Blurhash::class,
    ],

    'enable_vapor_uploads' => env('ENABLE_MEDIA_LIBRARY_VAPOR_UPLOADS', false),

    'image_optimizers' => [],

    'image_generators' => [
        Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
    ],

    'temporary_upload_model' => Spatie\MediaLibrary\MediaCollections\Models\TemporaryUpload::class,

    'file_namer' => Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    'prefix' => env('MEDIA_PREFIX', ''),
];
