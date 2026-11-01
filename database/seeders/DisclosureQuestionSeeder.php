<?php

namespace Database\Seeders;

use App\Models\DisclosureQuestion;
use Illuminate\Database\Seeder;

class DisclosureQuestionSeeder extends Seeder
{
    /**
     * Default credentialing disclosure questionnaire.
     *
     * Questions are keyed by their text so the seeder can be re-run safely
     * after wording or ordering changes.
     *
     * @var list<array{category: string, question: string, is_required: bool}>
     */
    private array $questions = [
        [
            'category' => 'Professional Conduct',
            'question' => 'Has your professional license, certification, or registration ever been denied, suspended, revoked, restricted, or placed on probation?',
            'is_required' => true,
        ],
        [
            'category' => 'Professional Conduct',
            'question' => 'Have you ever been reprimanded, sanctioned, or disciplined by a licensing board or professional society?',
            'is_required' => true,
        ],
        [
            'category' => 'Professional Conduct',
            'question' => 'Has any hospital, health system, or employer ever restricted, suspended, or terminated your clinical privileges?',
            'is_required' => true,
        ],
        [
            'category' => 'Licensure & Certification',
            'question' => 'Has your DEA registration ever been denied, restricted, suspended, or revoked?',
            'is_required' => true,
        ],
        [
            'category' => 'Licensure & Certification',
            'question' => 'Has your Medicare or Medicaid enrollment ever been denied, suspended, or terminated?',
            'is_required' => true,
        ],
        [
            'category' => 'Licensure & Certification',
            'question' => 'Has any board certification ever been denied, revoked, or allowed to lapse for reasons other than non-payment?',
            'is_required' => false,
        ],
        [
            'category' => 'Criminal & Legal',
            'question' => 'Have you ever been convicted of, or pleaded guilty or nolo contendere to, a felony or misdemeanor (excluding minor traffic violations)?',
            'is_required' => true,
        ],
        [
            'category' => 'Criminal & Legal',
            'question' => 'Are there any pending criminal charges against you?',
            'is_required' => true,
        ],
        [
            'category' => 'Criminal & Legal',
            'question' => 'Have you ever been named in a civil lawsuit involving your professional services?',
            'is_required' => false,
        ],
        [
            'category' => 'Malpractice & Claims',
            'question' => 'Has any malpractice claim, settlement, or judgment ever been filed or paid on your behalf?',
            'is_required' => true,
        ],
        [
            'category' => 'Malpractice & Claims',
            'question' => 'Has any professional liability insurer ever declined, cancelled, or refused to renew your coverage?',
            'is_required' => true,
        ],
        [
            'category' => 'Health & Impairment',
            'question' => 'Do you currently have any physical or mental condition that impairs your ability to practice safely?',
            'is_required' => true,
        ],
        [
            'category' => 'Health & Impairment',
            'question' => 'Have you ever been treated for substance abuse or participated in a rehabilitation program?',
            'is_required' => false,
        ],
        [
            'category' => 'Health & Impairment',
            'question' => 'Have you ever participated in a physician health program or similar monitoring program?',
            'is_required' => false,
        ],
    ];

    public function run(): void
    {
        foreach ($this->questions as $index => $question) {
            DisclosureQuestion::query()->updateOrCreate(
                ['question' => $question['question']],
                [
                    'category' => $question['category'],
                    'response_type' => 'yes_no_na',
                    'is_required' => $question['is_required'],
                    'is_active' => true,
                    'order' => $index + 1,
                ],
            );
        }
    }
}
