<?php

namespace App\Models\PostTypes;

use Illuminate\Database\Eloquent\Builder;

class Job extends BasePostType
{
    protected $table = 'pt_job';

    protected $fillable = [
        'tenant_id',
        'post_id',
        'company_name',
        'employment_type',
        'workplace_type',
        'title_override',
        'location_city',
        'country_code',
        'min_salary',
        'max_salary',
        'salary_currency',
        'salary_period',
        'experience_level',
        'responsibilities',
        'requirements',
        'benefits',
        'deadline_at',
        'apply_url',
        'extra',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'benefits' => 'array',
        'extra' => 'array',
    ];

    /**
     * Get the post family configuration
     */
    public static function getPostFamilyConfig(): array
    {
        return [
            'name' => 'Jobs',
            'description' => 'Job Postings and Career Opportunities',
            'table' => 'pt_job',
            'route_prefix' => 'jobs',
            'kind' => 'job',
            'icon' => 'briefcase',
        ];
    }

    /**
     * Get the filter options for this post type
     */
    public static function getFilterOptions(): array
    {
        return [
            'employment_type' => [
                'full_time' => 'Full Time',
                'part_time' => 'Part Time',
                'contract' => 'Contract',
                'internship' => 'Internship',
            ],
            'workplace_type' => [
                'onsite' => 'On-site',
                'hybrid' => 'Hybrid',
                'remote' => 'Remote',
            ],
            'experience_level' => [
                'junior' => 'Junior',
                'mid' => 'Mid-level',
                'senior' => 'Senior',
                'lead' => 'Lead',
            ],
        ];
    }

    /**
     * Apply filters to query
     */
    public function scopeWithFilters($query, array $filters = []): Builder
    {
        if (isset($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        if (isset($filters['workplace_type'])) {
            $query->where('workplace_type', $filters['workplace_type']);
        }

        if (isset($filters['experience_level'])) {
            $query->where('experience_level', $filters['experience_level']);
        }

        if (isset($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        if (isset($filters['company'])) {
            $query->where('company_name', 'like', '%' . $filters['company'] . '%');
        }

        if (isset($filters['min_salary']) && $filters['min_salary']) {
            $query->where('min_salary', '>=', $filters['min_salary']);
        }

        if (isset($filters['max_salary']) && $filters['max_salary']) {
            $query->where('max_salary', '<=', $filters['max_salary']);
        }

        if (isset($filters['deadline_from'])) {
            $query->where('deadline_at', '>=', $filters['deadline_from']);
        }

        if (isset($filters['deadline_to'])) {
            $query->where('deadline_at', '<=', $filters['deadline_to']);
        }

        return $query;
    }

    /**
     * Get the schema.org structured data
     */
    public function getStructuredData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $this->title_override ?: $this->post->title,
            'description' => $this->post->excerpt,
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $this->company_name,
            ],
            'datePosted' => $this->post->published_at?->toISOString(),
            'employmentType' => strtoupper(str_replace('_', '_', $this->employment_type)),
            'url' => $this->post->url,
        ];

        if ($this->deadline_at) {
            $data['validThrough'] = $this->deadline_at->toISOString();
        }

        if ($this->location_city || $this->country_code) {
            $data['jobLocation'] = [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $this->location_city,
                    'addressCountry' => $this->country_code,
                ],
            ];
        }

        if ($this->workplace_type === 'remote') {
            $data['jobLocationType'] = 'TELECOMMUTE';
        }

        if ($this->min_salary || $this->max_salary) {
            $data['baseSalary'] = [
                '@type' => 'MonetaryAmount',
                'currency' => $this->salary_currency ?: 'USD',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $this->min_salary,
                    'maxValue' => $this->max_salary,
                    'unitText' => strtoupper($this->salary_period ?: 'YEAR'),
                ],
            ];
        }

        return $data;
    }

    /**
     * Get the search facets for this post type
     */
    public function getSearchFacets(): array
    {
        return [
            'employment_type' => $this->employment_type,
            'workplace_type' => $this->workplace_type,
            'experience_level' => $this->experience_level,
            'country_code' => $this->country_code,
            'company_name' => $this->company_name,
            'has_salary' => !is_null($this->min_salary) || !is_null($this->max_salary),
            'is_remote' => $this->workplace_type === 'remote',
        ];
    }

    /**
     * Get displayable attributes for admin interface
     */
    public function getDisplayAttributes(): array
    {
        return [
            'Company' => $this->company_name,
            'Employment Type' => ucfirst(str_replace('_', ' ', $this->employment_type)),
            'Workplace' => ucfirst($this->workplace_type),
            'Location' => $this->getLocationDisplayAttribute(),
            'Experience Level' => $this->experience_level ? ucfirst($this->experience_level) : 'Not specified',
            'Salary Range' => $this->getSalaryDisplayAttribute(),
            'Deadline' => $this->deadline_at ? $this->deadline_at->format('M j, Y') : 'Not set',
        ];
    }

    /**
     * Scope to active jobs (not past deadline)
     */
    public function scopeActive($query): Builder
    {
        return $query->where(function ($q) {
            $q->where('deadline_at', '>', now())
              ->orWhereNull('deadline_at');
        });
    }

    /**
     * Scope to expired jobs
     */
    public function scopeExpired($query): Builder
    {
        return $query->where('deadline_at', '<', now())
                    ->whereNotNull('deadline_at');
    }

    /**
     * Check if job is expired
     */
    public function isExpired(): bool
    {
        return $this->deadline_at && $this->deadline_at < now();
    }

    /**
     * Get location display string
     */
    public function getLocationDisplayAttribute(): string
    {
        $parts = array_filter([$this->location_city, $this->country_code]);
        return implode(', ', $parts) ?: 'Not specified';
    }

    /**
     * Get salary display string
     */
    public function getSalaryDisplayAttribute(): string
    {
        if (!$this->min_salary && !$this->max_salary) {
            return 'Not specified';
        }

        $currency = $this->salary_currency ?: 'USD';
        $period = $this->salary_period ? '/' . $this->salary_period : '';

        if ($this->min_salary && $this->max_salary) {
            return $currency . ' ' . number_format($this->min_salary) . ' - ' . number_format($this->max_salary) . $period;
        } elseif ($this->min_salary) {
            return $currency . ' ' . number_format($this->min_salary) . '+' . $period;
        } else {
            return $currency . ' ' . number_format($this->max_salary) . $period;
        }
    }

    /**
     * Get the effective job title (with override if set)
     */
    public function getEffectiveTitleAttribute(): string
    {
        return $this->title_override ?: $this->post->title;
    }
}