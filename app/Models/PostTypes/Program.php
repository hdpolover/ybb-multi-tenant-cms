<?php

namespace App\Models\PostTypes;

use Illuminate\Database\Eloquent\Builder;

class Program extends BasePostType
{
    protected $table = 'pt_program';

    protected $fillable = [
        'tenant_id',
        'post_id',
        'program_type',
        'organizer_name',
        'location_text',
        'country_code',
        'deadline_at',
        'is_rolling',
        'funding_type',
        'stipend_amount',
        'fee_amount',
        'program_length_text',
        'eligibility_text',
        'apply_url',
        'extra',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'is_rolling' => 'boolean',
        'stipend_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'extra' => 'array',
    ];

    /**
     * Get the post family configuration
     */
    public static function getPostFamilyConfig(): array
    {
        return [
            'name' => 'Programs',
            'description' => 'Scholarships, Opportunities, and Internships',
            'table' => 'pt_program',
            'route_prefix' => 'opportunities',
            'kind' => 'program',
            'icon' => 'academic-cap',
        ];
    }

    /**
     * Get the filter options for this post type
     */
    public static function getFilterOptions(): array
    {
        return [
            'program_type' => [
                'scholarship' => 'Scholarship',
                'opportunity' => 'Opportunity',
                'internship' => 'Internship',
            ],
            'funding_type' => [
                'fully_funded' => 'Fully Funded',
                'partially_funded' => 'Partially Funded',
                'unfunded' => 'Unfunded',
            ],
            'is_rolling' => [
                '1' => 'Rolling Deadline',
                '0' => 'Fixed Deadline',
            ],
        ];
    }

    /**
     * Apply filters to query
     */
    public function scopeWithFilters($query, array $filters = []): Builder
    {
        if (isset($filters['program_type'])) {
            $query->where('program_type', $filters['program_type']);
        }

        if (isset($filters['funding_type'])) {
            $query->where('funding_type', $filters['funding_type']);
        }

        if (isset($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        if (isset($filters['is_rolling'])) {
            $query->where('is_rolling', (bool) $filters['is_rolling']);
        }

        if (isset($filters['deadline_from'])) {
            $query->where('deadline_at', '>=', $filters['deadline_from']);
        }

        if (isset($filters['deadline_to'])) {
            $query->where('deadline_at', '<=', $filters['deadline_to']);
        }

        if (isset($filters['has_stipend']) && $filters['has_stipend']) {
            $query->whereNotNull('stipend_amount')->where('stipend_amount', '>', 0);
        }

        return $query;
    }

    /**
     * Get the schema.org structured data
     */
    public function getStructuredData(): array
    {
        $type = $this->program_type === 'scholarship' ? 'Scholarship' : 'EducationalOccupationalProgram';
        
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $this->post->title,
            'description' => $this->post->excerpt,
            'provider' => [
                '@type' => 'Organization',
                'name' => $this->organizer_name,
            ],
            'url' => $this->post->url,
        ];

        if ($this->deadline_at) {
            $data['applicationDeadline'] = $this->deadline_at->toISOString();
        }

        if ($this->location_text) {
            $data['location'] = [
                '@type' => 'Place',
                'name' => $this->location_text,
            ];
        }

        if ($this->stipend_amount) {
            $data['offers'] = [
                '@type' => 'Offer',
                'price' => $this->stipend_amount,
                'priceCurrency' => 'USD', // Could be dynamic
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
            'program_type' => $this->program_type,
            'funding_type' => $this->funding_type,
            'country_code' => $this->country_code,
            'is_rolling' => $this->is_rolling,
            'has_stipend' => !is_null($this->stipend_amount) && $this->stipend_amount > 0,
            'organizer' => $this->organizer_name,
        ];
    }

    /**
     * Get displayable attributes for admin interface
     */
    public function getDisplayAttributes(): array
    {
        return [
            'Program Type' => ucfirst(str_replace('_', ' ', $this->program_type)),
            'Organizer' => $this->organizer_name,
            'Location' => $this->location_text ?: 'Not specified',
            'Deadline' => $this->is_rolling ? 'Rolling' : ($this->deadline_at ? $this->deadline_at->format('M j, Y') : 'Not set'),
            'Funding' => $this->funding_type ? ucfirst(str_replace('_', ' ', $this->funding_type)) : 'Not specified',
            'Stipend' => $this->stipend_amount ? '$' . number_format($this->stipend_amount, 2) : 'Not specified',
        ];
    }

    /**
     * Scope to active programs (not past deadline)
     */
    public function scopeActive($query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_rolling', true)
              ->orWhere('deadline_at', '>', now())
              ->orWhereNull('deadline_at');
        });
    }

    /**
     * Scope to expired programs
     */
    public function scopeExpired($query): Builder
    {
        return $query->where('is_rolling', false)
                    ->where('deadline_at', '<', now())
                    ->whereNotNull('deadline_at');
    }

    /**
     * Check if program is expired
     */
    public function isExpired(): bool
    {
        return !$this->is_rolling && 
               $this->deadline_at && 
               $this->deadline_at < now();
    }

    /**
     * Get deadline status
     */
    public function getDeadlineStatusAttribute(): string
    {
        if ($this->is_rolling) {
            return 'rolling';
        }

        if (!$this->deadline_at) {
            return 'unknown';
        }

        if ($this->deadline_at < now()) {
            return 'expired';
        }

        $daysLeft = now()->diffInDays($this->deadline_at, false);
        
        if ($daysLeft <= 7) {
            return 'urgent';
        } elseif ($daysLeft <= 30) {
            return 'soon';
        } else {
            return 'open';
        }
    }
}