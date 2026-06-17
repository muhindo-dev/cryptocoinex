<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Education\EducationArticle;
use App\Models\Education\EducationCategory;
use App\Models\Education\EducationProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationController extends Controller
{
    /** GET /trade/education */
    public function index(Request $request)
    {
        $categories = EducationCategory::orderBy('sort_order')->withCount('articles')->get();

        $activeSlug = $request->get('category');
        $activeCategory = $activeSlug ? $categories->firstWhere('slug', $activeSlug) : null;

        $articles = EducationArticle::query()
            ->with('category')
            ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->get('level')))
            ->orderBy('category_id')->orderBy('sort_order')
            ->get();

        $recommended = EducationArticle::with('category')->where('is_recommended', true)
            ->orderBy('sort_order')->limit(8)->get();

        $completed = $this->completedIds();
        $total = EducationArticle::count();

        return view('trading.education.index', compact(
            'categories', 'articles', 'recommended', 'activeCategory', 'completed', 'total'
        ));
    }

    /** GET /trade/education/{article} */
    public function show(EducationArticle $article)
    {
        $article->load('category');

        $sections = $this->parseSections($article->body);

        $next = EducationArticle::where('category_id', $article->category_id)
            ->where('sort_order', '>', $article->sort_order)
            ->orderBy('sort_order')->first();

        $related = EducationArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->orderBy('sort_order')->limit(4)->get();

        $isCompleted = $this->completedIds()->contains($article->id);

        return view('trading.education.show', compact('article', 'sections', 'next', 'related', 'isCompleted'));
    }

    /** POST /trade/education/{article}/complete — toggle completion. */
    public function complete(EducationArticle $article): JsonResponse
    {
        $row = EducationProgress::firstOrNew(['user_id' => Auth::id(), 'article_id' => $article->id]);

        if ($row->exists && $row->completed_at) {
            $row->delete();
            $completed = false;
        } else {
            $row->completed_at = now();
            $row->save();
            $completed = true;
        }

        return response()->json([
            'completed' => $completed,
            'total_completed' => $this->completedIds()->count(),
        ]);
    }

    private function completedIds()
    {
        return EducationProgress::where('user_id', Auth::id())
            ->whereNotNull('completed_at')
            ->pluck('article_id');
    }

    /**
     * Split a body into {heading, html} sections. Bodies use a simple format:
     * lines beginning with "## " start a section; everything else is paragraph
     * or list content (lines starting with "- " become list items).
     *
     * @return array<int, array{heading: ?string, html: string}>
     */
    private function parseSections(?string $body): array
    {
        if (! $body) {
            return [];
        }

        $sections = [];
        $current = ['heading' => null, 'lines' => []];
        $flush = function () use (&$sections, &$current) {
            if ($current['heading'] !== null || $current['lines']) {
                $sections[] = ['heading' => $current['heading'], 'html' => $this->renderLines($current['lines'])];
            }
        };

        foreach (preg_split('/\r?\n/', $body) as $line) {
            if (str_starts_with($line, '## ')) {
                $flush();
                $current = ['heading' => trim(substr($line, 3)), 'lines' => []];
            } else {
                $current['lines'][] = $line;
            }
        }
        $flush();

        return $sections;
    }

    private function renderLines(array $lines): string
    {
        $html = '';
        $inList = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }

                continue;
            }
            if (str_starts_with($line, '- ')) {
                if (! $inList) {
                    $html .= '<ul>';
                    $inList = true;
                }
                $html .= '<li>'.e(substr($line, 2)).'</li>';
            } else {
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $html .= '<p>'.e($line).'</p>';
            }
        }
        if ($inList) {
            $html .= '</ul>';
        }

        return $html;
    }
}
