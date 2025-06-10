<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $companies = [
            [
                'name' => 'Tech Innovations Inc.',
                'description' => 'Leading technology company specializing in software development and digital solutions.'
            ],
            [
                'name' => 'Global Marketing Solutions',
                'description' => 'Full-service marketing agency providing digital marketing and brand management services.'
            ],
            [
                'name' => 'HealthCare Partners',
                'description' => 'Healthcare services company providing medical care and wellness programs.'
            ],
            [
                'name' => 'Financial Advisors Group',
                'description' => 'Investment and financial planning services for individuals and businesses.'
            ],
            [
                'name' => 'Educational Resources Ltd.',
                'description' => 'Educational technology company creating learning management systems and online courses.'
            ],
            [
                'name' => 'Manufacturing Excellence Corp.',
                'description' => 'Industrial manufacturing company specializing in automotive parts and components.'
            ],
            [
                'name' => 'Green Energy Solutions',
                'description' => 'Renewable energy company focusing on solar and wind power installations.'
            ],
            [
                'name' => 'Retail Management Systems',
                'description' => 'Point-of-sale and inventory management software for retail businesses.'
            ],
            [
                'name' => 'Construction & Engineering Ltd.',
                'description' => 'Full-service construction company handling residential and commercial projects.'
            ],
            [
                'name' => 'Food Service Distributors',
                'description' => 'Food distribution company serving restaurants and hospitality businesses.'
            ],
            [
                'name' => 'Creative Design Studio',
                'description' => 'Graphic design and branding agency specializing in visual identity and web design.'
            ],
            [
                'name' => 'Logistics & Transportation Co.',
                'description' => 'Freight and logistics company providing shipping and supply chain solutions.'
            ],
            [
                'name' => 'Real Estate Ventures',
                'description' => 'Commercial and residential real estate development and property management company.'
            ],
            [
                'name' => 'Consulting Services Group',
                'description' => 'Business consulting firm helping companies optimize operations and strategy.'
            ],
            [
                'name' => 'Media & Entertainment Corp.',
                'description' => 'Digital media company producing content for streaming platforms and social media.'
            ],
            [
                'name' => 'Security Systems International',
                'description' => 'Cybersecurity and physical security solutions for enterprise clients.'
            ],
            [
                'name' => 'Telecommunications Network',
                'description' => 'Telecom infrastructure company providing internet and communication services.'
            ],
            [
                'name' => 'Agricultural Technologies',
                'description' => 'AgTech company developing smart farming solutions and crop management systems.'
            ],
            [
                'name' => 'Tourism & Hospitality Group',
                'description' => 'Hotel and resort management company operating luxury accommodations worldwide.'
            ],
            [
                'name' => 'Research & Development Labs',
                'description' => 'Scientific research company conducting studies in biotechnology and pharmaceuticals.'
            ]
        ];

        foreach ($companies as $companyData) {
            Company::create([
                'name' => $companyData['name'],
                'description' => $companyData['description'],
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ]);
        }

        // Create additional random companies to reach 25+ total
        for ($i = 0; $i < 5; $i++) {
            Company::create([
                'name' => $faker->company(),
                'description' => $faker->catchPhrase() . ' ' . $faker->bs(),
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}