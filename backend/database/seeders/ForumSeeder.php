<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\School;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    /**
     * General/topic forum names. Slugs are generated to match the frontend.
     *
     * @var list<string>
     */
    private const GENERAL_FORUMS = [
        'Општи дискусии',
        'Државна матура',
        'Помош при учење',
        'Вештачка интелегенција',
        'Факултети',
        'Странски јазици',
        'Кариера и професии',
        'Студии во странство',
        'Ментално здравје',
        'Воннаставни активности',
        'Технологија и програмирање',
        'Забава и култура',
        'Спорт',
        'Социјални прашања',
        'Претстави се',
        'Слободни дискусии',
    ];

    public function run(): void
    {
        $this->seedGeneralForums();
        $this->seedSchoolForums();
    }

    private function seedGeneralForums(): void
    {
        foreach (self::GENERAL_FORUMS as $name) {
            $slug = Slug::make($name);

            Forum::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => 'Форум за темата „'.$name.'“.',
                    'type' => 'general',
                    'school_id' => null,
                    'imageUrl' => '/icons/'.$slug.'.svg',
                    'bannerUrl' => 'https://picsum.photos/seed/'.$slug.'/1200/300',
                    'threads_count' => 0,
                    'members_count' => 0,
                ],
            );
        }
    }

    /**
     * One forum per school, linked via `school_id`. The school (and its city)
     * remain the single source of truth for the name/location.
     */
    private function seedSchoolForums(): void
    {
        School::with('city')->each(function (School $school) {
            $slug = Slug::make($school->name.' '.$school->city->name);

            Forum::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'name' => $school->name,
                    'slug' => $slug,
                    'description' => 'Форум на '.$school->name.' ('.$school->city->name.').',
                    'type' => 'school',
                    'imageUrl' => '/icons/uciliste.svg',
                    'bannerUrl' => 'https://picsum.photos/seed/'.$slug.'/1200/300',
                    'threads_count' => 0,
                    'members_count' => 0,
                ],
            );
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Forum;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $topicForums = [
            ['name' => 'Општо', 'description' => 'Општи дискусии за сè и сешто'],
            ['name' => 'Државна матура', 'description' => 'Совети, искуства и прашања за државната матура.'],
            ['name' => 'Помош при учење', 'description' => 'Прашај и помагај со домашни, проекти и учење.'],
            ['name' => 'Вештачка интелигенција', 'description' => 'Сè за AI, алатки и нови технологии.'],
            ['name' => 'Факултети', 'description' => 'Избор на факултет, запис и студентски живот.'],
            ['name' => 'Странски јазици', 'description' => 'IELTS, TOEFL, Goethe и учење јазици.'],
            ['name' => 'Кариерно водење', 'description' => 'Совети за кариера и професионален развој.'],
            ['name' => 'Студии во странство', 'description' => 'Апликации, стипендии и живот во странство.'],
            ['name' => 'Ментално здравје', 'description' => 'Поддршка и разговори за менталното здравје.'],
            ['name' => 'Воннаставни активности', 'description' => 'Клубови, натпревари и вон-училишни активности.'],
            ['name' => 'Технологија и програмирање', 'description' => 'Програмирање, гаџети и технолошки новости.'],
            ['name' => 'Забава и култура', 'description' => 'Филмови, музика, книги и поп-култура.'],
            ['name' => 'Спорт', 'description' => 'Сè поврзано со спорт и физичка активност.'],
            ['name' => 'Социјални прашања', 'description' => 'Дебати за актуелни социјални теми.'],
            ['name' => 'Претстави се', 'description' => 'Ново тука? Претстави се на заедницата.'],
            ['name' => 'Off-topic', 'description' => 'Сè што не спаѓа во другите категории.'],
        ];

        foreach ($topicForums as $forum) {
            Forum::firstOrCreate(
                ['name' => $forum['name']],
                [
                    'description' => $forum['description'],
                    'type' => 'topic',
                    'imageUrl' => 'https://via.placeholder.com/150',
                    'bannerUrl' => 'https://via.placeholder.com/1200x300',
                    'school_id' => null,
                    'user_id' => null, // moderator assigned later
                ]
            );
        }
    }
}