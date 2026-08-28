<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Akun Demo
    |--------------------------------------------------------------------------
    |
    | Satu akun admin yang boleh dicoba siapa saja. Seluruh isi perusahaan demo
    | dibangun ulang setiap kali ada yang masuk dan jarak dari pemulihan
    | terakhir sudah melewati `reset_after_hours`.
    |
    | Kosongkan `email` untuk mematikan seluruh fitur demo.
    |
    */

    'email' => env('DEMO_EMAIL', 'demo@aldeftech.com'),

    'password' => env('DEMO_PASSWORD', 'demo12345'),

    'name' => 'Admin Demo',

    /*
    | Perusahaan tersendiri, bukan perusahaan pelanggan. Akun demo berperan
    | admin, dan admin melihat seluruh karyawan di perusahaannya - menaruhnya
    | di perusahaan asli berarti membuka data absensi pelanggan kepada siapa
    | pun yang mencoba demo.
    */
    'company' => 'Aldef Tech Demo',

    'reset_after_hours' => 24,

    /*
    | Banyaknya isi contoh yang dibuat ulang tiap pemulihan. Dashboard yang
    | kosong tidak menunjukkan apa pun tentang produknya.
    */
    'employees' => 6,

    'attendance_days' => 14,

];
