<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'IT & Software', 'Marketing', 'Finance', 'Human Resources', 'Sales',
            'Engineering', 'Design', 'Customer Support', 'Healthcare', 'Education',
        ] as $name) {
            DB::table('job_categories')->updateOrInsert(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()],
            );
        }
    }
}
