<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\School;
use App\Models\StudentData;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vocation;
use App\Support\SyncUserContentPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Bulk content for feed / pagination / ranking tests.
 *
 * Run alone (after normal seed):
 *   php artisan db:seed --class=LargeThreadSeeder
 */
class LargeThreadSeeder extends Seeder
{
    private const EXTRA_USERS = 45;

    private const THREAD_COUNT = 1000;

    private const TITLE_PREFIX = 'Тест дискусија #';

    /** @var list<string> */
    private const TITLE_TOPICS = [
        'Совети за учење',
        'Прашање за домашни',
        'Искуство со матура',
        'Препорака за факултет',
        'Дискусија за спорт',
        'Помош со математика',
        'Англиски вежби',
        'Проекти и идеи',
        'Кариера после средно',
        'Ментално здравје',
        'Технологија и алатки',
        'Училишни настани',
        'Слободно време',
        'Стипендии и можности',
        'Програмирање за почетници',
    ];

    public function run(): void
    {
        $forums = Forum::query()->orderBy('id')->get();
        $schools = School::query()->with('forum')->get();
        $vocations = Vocation::query()->orderBy('id')->get();

        if ($forums->isEmpty()) {
            $this->command?->error('No forums found. Run ForumSeeder first.');

            return;
        }

        if ($schools->isEmpty() || $vocations->isEmpty()) {
            $this->command?->error('No schools/vocations found. Run OnboardingReferenceSeeder + ForumSeeder first.');

            return;
        }

        $this->command?->info('Creating bulk authors…');
        $authors = $this->ensureAuthors($schools, $vocations);

        $this->command?->info('Clearing previous bulk threads…');
        Thread::withTrashed()
            ->where('title', 'like', self::TITLE_PREFIX.'%')
            ->forceDelete();

        $this->command?->info('Seeding '.self::THREAD_COUNT.' threads across '.$forums->count().' forums and '.$authors->count().' authors…');
        $this->seedThreads($forums, $authors);

        $this->command?->info('Refreshing forum thread counts…');
        $this->refreshForumCounts();

        $this->command?->info('LargeThreadSeeder done.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function ensureAuthors($schools, $vocations)
    {
        $sync = app(SyncUserContentPermissions::class);
        $password = Hash::make('password');
        $now = now();

        for ($i = 1; $i <= self::EXTRA_USERS; $i++) {
            $email = "bulk_user_{$i}@example.com";

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'username' => "bulk_user_{$i}",
                    'password' => $password,
                    'role' => 'user',
                    'email_verified_at' => $now,
                    'onboarding_completed_at' => $now,
                ],
            );

            /** @var School $school */
            $school = $schools[($i - 1) % $schools->count()];
            /** @var Vocation $vocation */
            $vocation = $vocations[($i - 1) % $vocations->count()];

            StudentData::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $school->id,
                    'vocation_id' => $vocation->id,
                    'grade' => (($i - 1) % 4) + 1,
                ],
            );

            if ($school->forum !== null) {
                $user->forums()->syncWithoutDetaching([$school->forum->id]);
            }

            $sync->handle($user->fresh(['studentData.school.forum']));
        }

        // Mix new bulk authors with existing onboarded students.
        return User::query()
            ->whereNotNull('onboarding_completed_at')
            ->where('role', 'user')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Forum>  $forums
     * @param  \Illuminate\Support\Collection<int, User>  $authors
     */
    private function seedThreads($forums, $authors): void
    {
        $forumIds = $forums->pluck('id')->all();
        $authorIds = $authors->pluck('id')->all();
        $forumCount = count($forumIds);
        $authorCount = count($authorIds);
        $topicCount = count(self::TITLE_TOPICS);

        $rows = [];
        $now = now();

        for ($i = 1; $i <= self::THREAD_COUNT; $i++) {
            $forumId = $forumIds[($i - 1) % $forumCount];
            $authorId = $authorIds[($i - 1) % $authorCount];
            $topic = self::TITLE_TOPICS[($i - 1) % $topicCount];
            $daysAgo = ($i * 3) % 45;
            $hoursAgo = ($i * 5) % 24;
            $createdAt = $now->copy()->subDays($daysAgo)->subHours($hoursAgo);

            $rows[] = [
                'title' => self::TITLE_PREFIX.$i.' — '.$topic,
                'description' => "<p>Автоматски генерирана дискусија #{$i} за тестирање на feed, пагинација и рангирање.</p><p>Тема: {$topic}.</p>",
                'upvotes' => ($i * 7) % 120,
                'views' => 20 + (($i * 13) % 2500),
                'user_id' => $authorId,
                'forum_id' => $forumId,
                'is_anonymous' => $i % 11 === 0,
                'edited_at' => $i % 17 === 0 ? $createdAt->copy()->addHours(2) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if (count($rows) >= 100) {
                DB::table('threads')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('threads')->insert($rows);
        }
    }

    private function refreshForumCounts(): void
    {
        Forum::query()->each(function (Forum $forum): void {
            $forum->forceFill([
                'threads_count' => $forum->threads()->count(),
            ])->saveQuietly();
        });
    }
}
