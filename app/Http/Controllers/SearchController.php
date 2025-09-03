<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Job;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, programs, jobs, posts
        $sort = $request->get('sort', 'relevance'); // relevance, date, title
        $perPage = 12;

        $results = [
            'programs' => collect(),
            'jobs' => collect(),
            'posts' => collect(),
            'total' => 0
        ];

        if (!empty($query)) {
            if ($type === 'all' || $type === 'programs') {
                $results['programs'] = $this->searchPrograms($query, $sort);
            }

            if ($type === 'all' || $type === 'jobs') {
                $results['jobs'] = $this->searchJobs($query, $sort);
            }

            if ($type === 'all' || $type === 'posts') {
                $results['posts'] = $this->searchPosts($query, $sort);
            }

            $results['total'] = $results['programs']->count() + 
                               $results['jobs']->count() + 
                               $results['posts']->count();
        }

        // If searching for a specific type, paginate those results
        if ($type !== 'all' && !empty($query)) {
            switch ($type) {
                case 'programs':
                    $results['programs'] = $this->searchPrograms($query, $sort, true, $perPage);
                    break;
                case 'jobs':
                    $results['jobs'] = $this->searchJobs($query, $sort, true, $perPage);
                    break;
                case 'posts':
                    $results['posts'] = $this->searchPosts($query, $sort, true, $perPage);
                    break;
            }
        }

        // Get search suggestions
        $suggestions = $this->getSearchSuggestions($query);

        return view('search.index', compact('query', 'type', 'sort', 'results', 'suggestions'));
    }

    private function searchPrograms($query, $sort = 'relevance', $paginate = false, $perPage = 12)
    {
        $searchQuery = Program::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('requirements', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%")
                  ->orWhere('type', 'LIKE', "%{$query}%")
                  ->orWhere('organization', 'LIKE', "%{$query}%");
            });

        // Add sorting
        switch ($sort) {
            case 'date':
                $searchQuery->orderBy('created_at', 'desc');
                break;
            case 'title':
                $searchQuery->orderBy('title', 'asc');
                break;
            case 'deadline':
                $searchQuery->orderBy('deadline', 'asc');
                break;
            default: // relevance
                $searchQuery->orderByRaw("
                    CASE 
                        WHEN title LIKE '%{$query}%' THEN 1
                        WHEN description LIKE '%{$query}%' THEN 2
                        WHEN organization LIKE '%{$query}%' THEN 3
                        ELSE 4
                    END
                ");
        }

        if ($paginate) {
            return $searchQuery->paginate($perPage)->appends(request()->query());
        }

        return $searchQuery->limit(6)->get();
    }

    private function searchJobs($query, $sort = 'relevance', $paginate = false, $perPage = 12)
    {
        $searchQuery = Job::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('requirements', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%")
                  ->orWhere('type', 'LIKE', "%{$query}%")
                  ->orWhere('company', 'LIKE', "%{$query}%")
                  ->orWhere('industry', 'LIKE', "%{$query}%");
            });

        // Add sorting
        switch ($sort) {
            case 'date':
                $searchQuery->orderBy('created_at', 'desc');
                break;
            case 'title':
                $searchQuery->orderBy('title', 'asc');
                break;
            case 'salary':
                $searchQuery->orderBy('salary_max', 'desc');
                break;
            default: // relevance
                $searchQuery->orderByRaw("
                    CASE 
                        WHEN title LIKE '%{$query}%' THEN 1
                        WHEN company LIKE '%{$query}%' THEN 2
                        WHEN description LIKE '%{$query}%' THEN 3
                        ELSE 4
                    END
                ");
        }

        if ($paginate) {
            return $searchQuery->paginate($perPage)->appends(request()->query());
        }

        return $searchQuery->limit(6)->get();
    }

    private function searchPosts($query, $sort = 'relevance', $paginate = false, $perPage = 12)
    {
        $searchQuery = Post::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%")
                  ->orWhere('excerpt', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%")
                  ->orWhere('tags', 'LIKE', "%{$query}%")
                  ->orWhere('author', 'LIKE', "%{$query}%");
            });

        // Add sorting
        switch ($sort) {
            case 'date':
                $searchQuery->orderBy('published_at', 'desc');
                break;
            case 'title':
                $searchQuery->orderBy('title', 'asc');
                break;
            default: // relevance
                $searchQuery->orderByRaw("
                    CASE 
                        WHEN title LIKE '%{$query}%' THEN 1
                        WHEN category LIKE '%{$query}%' THEN 2
                        WHEN content LIKE '%{$query}%' THEN 3
                        ELSE 4
                    END
                ");
        }

        if ($paginate) {
            return $searchQuery->paginate($perPage)->appends(request()->query());
        }

        return $searchQuery->limit(6)->get();
    }

    private function getSearchSuggestions($query)
    {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }

        $suggestions = [];

        // Get popular search terms from program types, locations, organizations
        $programSuggestions = Program::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('type', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%")
                  ->orWhere('organization', 'LIKE', "%{$query}%");
            })
            ->select('type', 'location', 'organization')
            ->limit(5)
            ->get();

        foreach ($programSuggestions as $program) {
            if (stripos($program->type, $query) !== false) {
                $suggestions[] = $program->type;
            }
            if (stripos($program->location, $query) !== false) {
                $suggestions[] = $program->location;
            }
            if (stripos($program->organization, $query) !== false) {
                $suggestions[] = $program->organization;
            }
        }

        // Get job suggestions
        $jobSuggestions = Job::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('company', 'LIKE', "%{$query}%")
                  ->orWhere('industry', 'LIKE', "%{$query}%")
                  ->orWhere('type', 'LIKE', "%{$query}%");
            })
            ->select('company', 'industry', 'type')
            ->limit(5)
            ->get();

        foreach ($jobSuggestions as $job) {
            if (stripos($job->company, $query) !== false) {
                $suggestions[] = $job->company;
            }
            if (stripos($job->industry, $query) !== false) {
                $suggestions[] = $job->industry;
            }
            if (stripos($job->type, $query) !== false) {
                $suggestions[] = $job->type;
            }
        }

        return array_unique(array_filter($suggestions));
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = $this->getSearchSuggestions($query);
        
        // Add popular searches if no specific suggestions
        if (empty($suggestions)) {
            $popularTerms = [
                'scholarship', 'internship', 'fellowship', 'remote work', 
                'engineering', 'marketing', 'design', 'finance'
            ];
            
            $suggestions = array_filter($popularTerms, function($term) use ($query) {
                return stripos($term, $query) !== false;
            });
        }

        return response()->json(array_slice($suggestions, 0, 8));
    }
}