<?php

namespace Tests\Feature\Trading;

use App\Models\Education\EducationArticle;
use App\Models\User;
use Database\Seeders\EducationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EducationSeeder::class);
    }

    private function student(): User
    {
        return User::create(['name' => 'S', 'email' => 's@s.com', 'password' => bcrypt('x'), 'role' => 'student']);
    }

    public function test_seeder_produces_full_course(): void
    {
        $this->assertSame(6, \App\Models\Education\EducationCategory::count());
        $this->assertGreaterThanOrEqual(40, EducationArticle::count());
        $this->assertSame(0, EducationArticle::whereNull('body')->count());
        $this->assertSame(0, EducationArticle::whereNull('youtube_id')->count());
    }

    public function test_index_and_category_filter_render(): void
    {
        $u = $this->student();
        $this->actingAs($u)->get('/trade/education')->assertStatus(200)->assertSee('Trading Academy');
        $this->actingAs($u)->get('/trade/education?category=how-to-trade')->assertStatus(200);
    }

    public function test_article_detail_renders_with_video(): void
    {
        $u = $this->student();
        $article = EducationArticle::first();
        $res = $this->actingAs($u)->get("/trade/education/{$article->slug}");
        $res->assertStatus(200)->assertSee($article->title);
        $res->assertSee('youtube-nocookie.com', false); // lazy embed url present
    }

    public function test_complete_toggle(): void
    {
        $u = $this->student();
        $article = EducationArticle::first();

        $this->actingAs($u)->post("/trade/education/{$article->slug}/complete")
            ->assertJson(['completed' => true]);
        $this->assertDatabaseHas('education_progress', ['user_id' => $u->id, 'article_id' => $article->id]);

        $this->actingAs($u)->post("/trade/education/{$article->slug}/complete")
            ->assertJson(['completed' => false]);
        $this->assertDatabaseMissing('education_progress', ['user_id' => $u->id, 'article_id' => $article->id]);
    }

    public function test_guest_blocked(): void
    {
        $this->get('/trade/education')->assertRedirect();
    }
}
