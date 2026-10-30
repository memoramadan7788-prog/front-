<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $tech = [
            'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Node.js', 'Python', 'Java', 'C#',
            'FastAPI', 'HTML & CSS', 'SQL', 'MySQL', 'PostgreSQL', 'Redis', 'REST APIs', 'GraphQL', 'Git',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'Linux', 'CI/CD', 'Automated Testing', 'Cybersecurity',
            'Data Analysis', 'Microsoft Excel', 'Power BI', 'Tableau', 'Machine Learning', 'Figma',
        ];
        $soft = [
            'Communication', 'Teamwork', 'Problem Solving', 'Time Management', 'Leadership', 'Adaptability',
            'Project Management', 'Recruiting', 'Human Resources', 'Agile', 'Scrum', 'Critical Thinking',
            'Stakeholder Management', 'Conflict Resolution', 'Customer Service', 'Negotiation', 'Attention to Detail',
        ];

        foreach ($tech as $name) {
            DB::table('skills')->updateOrInsert(
                ['name' => $name],
                ['type' => 'tech', 'accepted' => true, 'created_at' => now(), 'updated_at' => now()],
            );
        }
        foreach ($soft as $name) {
            DB::table('skills')->updateOrInsert(
                ['name' => $name],
                ['type' => 'soft', 'accepted' => true, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }
}
