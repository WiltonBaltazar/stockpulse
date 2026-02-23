<?php

return [
    'show_warnings' => false,

    'public_path' => public_path(),

    'options' => [
        // Shared hosting often blocks /tmp or has restricted open_basedir.
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => storage_path('app/dompdf/temp'),
        'chroot' => realpath(base_path()) ?: base_path(),
        'log_output_file' => storage_path('app/dompdf/logs/dompdf.log'),
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'DejaVu Sans',
        'enable_remote' => false,
        'enable_php' => false,
        'enable_javascript' => true,
        'dpi' => 96,
    ],
];

